<?php

namespace MediaMonks\MssqlBundle\Session\Storage\Handler;

/**
 * @note this is a copy of the regular PdoSessionHandler from Symfony but modified to work with dblib
 */
class PdoSessionHandler2 implements \SessionHandlerInterface
{
    /**
     * No locking is done. This means sessions are prone to loss of data due to
     * race conditions of concurrent requests to the same session. The last session
     * write will win in this case. It might be useful when you implement your own
     * logic to deal with this like an optimistic approach.
     */
    const LOCK_NONE = 0;

    /**
     * Creates an application-level lock on a session. The disadvantage is that the
     * lock is not enforced by the database and thus other, unaware parts of the
     * application could still concurrently modify the session. The advantage is it
     * does not require a transaction.
     * This mode is not available for SQLite and not yet implemented for oci and sqlsrv.
     */
    const LOCK_ADVISORY = 1;

    /**
     * Issues a real row lock. Since it uses a transaction between opening and
     * closing a session, you have to be careful when you use same database connection
     * that you also use for your application logic. This mode is the default because
     * it's the only reliable solution across DBMSs.
     */
    const LOCK_TRANSACTIONAL = 2;

    /**
     * @var \PDO|null PDO instance or null when not connected yet
     */
    private $pdo;

    /**
     * @var string|null|false DSN string or null for session.save_path or false when lazy connection disabled
     */
    private $dsn = false;

    /**
     * @var string Database driver
     */
    private $driver;

    /**
     * @var string Table name
     */
    private $table = 'sessions';

    /**
     * @var string Column for session id
     */
    private $idCol = 'sess_id';

    /**
     * @var string Column for session data
     */
    private $dataCol = 'sess_data';

    /**
     * @var string Column for lifetime
     */
    private $lifetimeCol = 'sess_lifetime';

    /**
     * @var string Column for timestamp
     */
    private $timeCol = 'sess_time';

    /**
     * @var string Username when lazy-connect
     */
    private $username = '';

    /**
     * @var string Password when lazy-connect
     */
    private $password = '';

    /**
     * @var array Connection options when lazy-connect
     */
    private $connectionOptions = [];

    /**
     * @var int The strategy for locking, see constants
     */
    private $lockMode = self::LOCK_TRANSACTIONAL;

    /**
     * It's an array to support multiple reads before closing which is manual, non-standard usage.
     *
     * @var \PDOStatement[] An array of statements to release advisory locks
     */
    private $unlockStatements = [];

    /**
     * @var bool True when the current session exists but expired according to session.gc_maxlifetime
     */
    private $sessionExpired = false;

    /**
     * @var bool Whether a transaction is active
     */
    private $inTransaction = false;

    /**
     * @var bool Whether gc() has been called
     */
    private $gcCalled = false;

    /**
     * Constructor.
     *
     * You can either pass an existing database connection as PDO instance or
     * pass a DSN string that will be used to lazy-connect to the database
     * when the session is actually used. Furthermore it's possible to pass null
     * which will then use the session.save_path ini setting as PDO DSN parameter.
     *
     * List of available options:
     *  * db_table: The name of the table [default: sessions]
     *  * db_id_col: The column where to store the session id [default: sess_id]
     *  * db_data_col: The column where to store the session data [default: sess_data]
     *  * db_lifetime_col: The column where to store the lifetime [default: sess_lifetime]
     *  * db_time_col: The column where to store the timestamp [default: sess_time]
     *  * db_username: The username when lazy-connect [default: '']
     *  * db_password: The password when lazy-connect [default: '']
     *  * db_connection_options: An array of driver-specific connection options [default: array()]
     *  * lock_mode: The strategy for locking, see constants [default: LOCK_TRANSACTIONAL]
     *
     * @param \PDO|string|null $pdoOrDsn A \PDO instance or DSN string or null
     * @param array $options An associative array of options
     *
     * @throws \InvalidArgumentException When PDO error mode is not PDO::ERRMODE_EXCEPTION
     */
    public function __construct($pdoOrDsn = null, array $options = [])
    {
        if ($pdoOrDsn instanceof \PDO) {
            if (\PDO::ERRMODE_EXCEPTION !== $pdoOrDsn->getAttribute(\PDO::ATTR_ERRMODE)) {
                throw new \InvalidArgumentException(sprintf('"%s" requires PDO error mode attribute be set to throw Exceptions (i.e. $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION))',
                    __CLASS__));
            }

            $this->pdo    = $pdoOrDsn;
            $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } else {
            $this->dsn = $pdoOrDsn;
        }

        $this->table             = isset($options['db_table']) ? $options['db_table'] : $this->table;
        $this->idCol             = isset($options['db_id_col']) ? $options['db_id_col'] : $this->idCol;
        $this->dataCol           = isset($options['db_data_col']) ? $options['db_data_col'] : $this->dataCol;
        $this->lifetimeCol       = isset($options['db_lifetime_col']) ? $options['db_lifetime_col'] : $this->lifetimeCol;
        $this->timeCol           = isset($options['db_time_col']) ? $options['db_time_col'] : $this->timeCol;
        $this->username          = isset($options['db_username']) ? $options['db_username'] : $this->username;
        $this->password          = isset($options['db_password']) ? $options['db_password'] : $this->password;
        $this->connectionOptions = isset($options['db_connection_options']) ? $options['db_connection_options'] : $this->connectionOptions;
        $this->lockMode          = self::LOCK_NONE;
    }

    /**
     * Returns true when the current session exists but expired according to session.gc_maxlifetime.
     *
     * Can be used to distinguish between a new session and one that expired due to inactivity.
     *
     * @return bool Whether current session expired
     */
    public function isSessionExpired()
    {
        return $this->sessionExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        if (null === $this->pdo) {
            $this->connect($this->dsn ?: $savePath);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        try {
            return base64_decode($this->doRead($sessionId));
        } catch (\PDOException $e) {
            $this->rollback();

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        // We delay gc() to close() so that it is executed outside the transactional and blocking read-write process.
        // This way, pruning expired sessions does not block them from being started while the current session is used.
        $this->gcCalled = true;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        // delete the record associated with this id
        $sql = "DELETE FROM $this->table WHERE $this->idCol = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            $this->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $data = base64_encode($data);

        $maxlifetime = (int)ini_get('session.gc_maxlifetime');

        try {
            // always use update instead of merge since it gives issues on dblib
            $query = "UPDATE $this->table SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time WHERE $this->idCol = :id;";

            $updateStmt = $this->pdo->prepare($query);
            $updateStmt->bindParam(':id', $sessionId);
            $updateStmt->bindParam(':data', $data);
            $updateStmt->bindParam(':lifetime', $maxlifetime);
            $updateStmt->bindValue(':time', time());
            $updateStmt->execute();

            // When MERGE is not supported, like in Postgres, we have to use this approach that can result in
            // duplicate key errors when the same session is written simultaneously (given the LOCK_NONE behavior).
            // We can just catch such an error and re-execute the update. This is similar to a serializable
            // transaction with retry logic on serialization failures but without the overhead and without possible
            // false positives due to longer gap locking.
            if (!$updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->pdo->prepare(
                        "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)"
                    );
                    $insertStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
                    $insertStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
                    $insertStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
                    $insertStmt->bindValue(':time', time(), \PDO::PARAM_INT);
                    $insertStmt->execute();
                } catch (\PDOException $e) {
                    // Handle integrity violation SQLSTATE 23000 (or a subclass like 23505 in Postgres) for duplicate keys
                    if (0 === strpos($e->getCode(), '23')) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\PDOException $e) {
            $this->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->commit();

        while ($unlockStmt = array_shift($this->unlockStatements)) {
            $unlockStmt->execute();
        }

        if ($this->gcCalled) {
            $this->gcCalled = false;

            // delete the session records that have expired
            $sql = "DELETE FROM $this->table WHERE $this->lifetimeCol + $this->timeCol < :time";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $stmt->execute();
        }

        if (false !== $this->dsn) {
            $this->pdo = null; // only close lazy-connection
        }

        return true;
    }

    /**
     * Lazy-connects to the database.
     *
     * @param string $dsn DSN string
     */
    private function connect($dsn)
    {
        $this->pdo = new \PDO($dsn, $this->username, $this->password, $this->connectionOptions);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Helper method to commit a transaction.
     */
    private function commit()
    {
        if ($this->inTransaction) {
            try {
                // commit read-write transaction which also releases the lock
                if ('sqlite' === $this->driver) {
                    $this->pdo->exec('COMMIT');
                } else {
                    $this->pdo->commit();
                }
                $this->inTransaction = false;
            } catch (\PDOException $e) {
                $this->rollback();

                throw $e;
            }
        }
    }

    /**
     * Helper method to rollback a transaction.
     */
    private function rollback()
    {
        // We only need to rollback if we are in a transaction. Otherwise the resulting
        // error would hide the real problem why rollback was called. We might not be
        // in a transaction when not using the transactional locking behavior or when
        // two callbacks (e.g. destroy and write) are invoked that both fail.
        if ($this->inTransaction) {
            if ('sqlite' === $this->driver) {
                $this->pdo->exec('ROLLBACK');
            } else {
                $this->pdo->rollBack();
            }
            $this->inTransaction = false;
        }
    }

    /**
     * Reads the session data in respect to the different locking strategies.
     *
     * We need to make sure we do not return session data that is already considered garbage according
     * to the session.gc_maxlifetime setting because gc() is called after read() and only sometimes.
     *
     * @param string $sessionId Session ID
     *
     * @return string The session data
     */
    private function doRead($sessionId)
    {
        $this->sessionExpired = false;

        $selectSql  = $this->getSelectSql();
        $selectStmt = $this->pdo->prepare($selectSql);
        $selectStmt->bindParam(':id', $sessionId);
        $selectStmt->execute();

        $sessionRows = $selectStmt->fetchAll(\PDO::FETCH_NUM);

        if ($sessionRows) {
            if ($sessionRows[0][1] + $sessionRows[0][2] < time()) {
                $this->sessionExpired = true;

                return '';
            }

            return is_resource($sessionRows[0][0]) ? stream_get_contents($sessionRows[0][0]) : $sessionRows[0][0];
        }

        return '';
    }

    /**
     * Return a locking or nonlocking SQL query to read session information.
     *
     * @return string The SQL string
     *
     * @throws \DomainException When an unsupported PDO driver is used
     */
    private function getSelectSql()
    {
        return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id";
    }

    /**
     * Return a PDO instance.
     *
     * @return \PDO
     */
    protected function getConnection()
    {
        if (null === $this->pdo) {
            $this->connect($this->dsn ?: ini_get('session.save_path'));
        }

        return $this->pdo;
    }
}
