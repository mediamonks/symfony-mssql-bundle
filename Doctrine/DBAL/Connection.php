<?php

namespace MediaMonks\MssqlBundle\Doctrine\DBAL;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as BaseConnection;
use MediaMonks\MssqlBundle\Helper\ConnectionHelper;

class Connection extends BaseConnection
{
    /**
     * @inheritdoc
     */
    public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null)
    {
        $query = ConnectionHelper::updateQuery($query, $params);
        return parent::executeQuery($query, $params, $types, $qcp);
    }

    /**
     * @inheritdoc
     */
    public function executeUpdate($query, array $params = [], array $types = [])
    {
        $query = ConnectionHelper::updateQuery($query, $params);
        return parent::executeUpdate($query, $params, $types);
    }

}
