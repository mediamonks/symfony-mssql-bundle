<?php

namespace MediaMonks\MssqlBundle\Doctrine\DBAL\Driver\PDODblib;

use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\DBAL\Connection as DoctrineConnection;
use MediaMonks\MssqlBundle\Helper\ConnectionHelper;
use MediaMonks\MssqlBundle\Helper\PlatformHelper;
use MediaMonks\MssqlBundle\Platforms\DblibPlatform;
use MediaMonks\MssqlBundle\Schema\DblibSchemaManager;

/**
 * @author Scott Morken <scott.morken@pcmail.maricopa.edu>
 * @author Robert Slootjes <robert@mediamonks.com>
 */
class Driver implements \Doctrine\DBAL\Driver
{
    const NAME = 'pdo_dblib';

    /**
     * Attempts to establish a connection with the underlying driver.
     *
     * @param array $params
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     * @return \Doctrine\DBAL\Driver\Connection
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        if (PlatformHelper::isWindows()) {
            return $this->connectWindows($params, $username, $password, $driverOptions);
        }
        return $this->connectUnix($params, $username, $password, $driverOptions);
    }

    /**
     * @param array $params
     * @param null $username
     * @param null $password
     * @param array $driverOptions
     * @return PDOConnection
     */
    protected function connectWindows(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new PDOConnection(
            $this->constructPdoDsnWindows($params),
            $username,
            $password,
            $driverOptions
        );
    }

    /**
     * @param array $params
     * @param null $username
     * @param null $password
     * @param array $driverOptions
     * @return Connection
     */
    protected function connectUnix(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        $connection = new Connection(
            $this->constructPdoDsnUnix($params),
            $username,
            $password,
            $driverOptions
        );

        ConnectionHelper::setConnectionOptions($connection);

        return $connection;
    }

    /**
     * Constructs the Dblib PDO DSN.
     *
     * @return string  The DSN.
     */
    public function constructPdoDsn(array $params)
    {
        if (PlatformHelper::isWindows()) {
            return $this->constructPdoDsnWindows($params);
        }
        return $this->constructPdoDsnUnix($params);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function constructPdoDsnWindows(array $params)
    {
        $dsn = 'sqlsrv:server=';

        if (isset($params['host'])) {
            $dsn .= $params['host'];
        }

        if (isset($params['port']) && !empty($params['port'])) {
            $dsn .= ',' . $params['port'];
        }

        if (isset($params['dbname'])) {
            $dsn .= ';Database=' . $params['dbname'];
        }
        return $dsn;
    }

    /**
     * @param array $params
     * @return string
     */
    public function constructPdoDsnUnix(array $params)
    {
        $dsn = 'dblib:';
        if (isset($params['host'])) {
            $dsn .= 'host=' . $params['host'] . ';';
        }
        if (isset($params['port'])) {
            $dsn .= 'port=' . $params['port'] . ';';
        }
        if (isset($params['dbname'])) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        }
        if (isset($params['charset'])) {
            $dsn .= 'charset=' . $params['charset'] . ';';
        }

        return $dsn;
    }

    /**
     * @return DblibPlatform
     */
    public function getDatabasePlatform()
    {
        return new DblibPlatform();
    }

    /**
     * @param DoctrineConnection $connection
     * @return DblibSchemaManager
     */
    public function getSchemaManager(DoctrineConnection $connection)
    {
        return new DblibSchemaManager($connection);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param DoctrineConnection $connection
     * @return mixed
     */
    public function getDatabase(DoctrineConnection $connection)
    {
        $params = $connection->getParams();
        return $params['dbname'];
    }
}
