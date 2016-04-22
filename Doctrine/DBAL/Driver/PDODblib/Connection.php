<?php

namespace MediaMonks\MssqlBundle\Doctrine\DBAL\Driver\PDODblib;

use Doctrine\DBAL\Driver\Connection as BaseConnection;
use Doctrine\DBAL\Driver\PDOConnection;

class Connection extends PDOConnection implements BaseConnection
{
    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->exec('ROLLBACK TRANSACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->exec('COMMIT TRANSACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->exec('BEGIN TRANSACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null)
    {
        $stmt = $this->query('SELECT SCOPE_IDENTITY()');
        $id   = $stmt->fetchColumn();
        $stmt->closeCursor();
        return $id;
    }
}
