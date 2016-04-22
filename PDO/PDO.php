<?php

namespace MediaMonks\MssqlBundle\PDO;

use MediaMonks\MssqlBundle\Helper\ConnectionHelper;
use MediaMonks\MssqlBundle\Doctrine\DBAL\Driver\PDODblib\Driver;

class PDO extends \PDO
{
    /**
     * PDO constructor.
     * @param $host
     * @param $port
     * @param $dbname
     * @param $username
     * @param $passwd
     * @param $options
     */
    public function __construct($host, $port, $dbname, $username, $passwd, $options)
    {
        $driver = new Driver();
        $dsn = $driver->constructPdoDsn([
            'host'   => $host,
            'port'   => $port,
            'dbname' => $dbname
        ]);

        parent::__construct($dsn, $username, $passwd, $options);

        ConnectionHelper::setConnectionOptions($this);
    }
}
