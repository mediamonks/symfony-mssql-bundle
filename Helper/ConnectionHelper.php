<?php

namespace MediaMonks\MssqlBundle\Helper;

class ConnectionHelper
{
    /**
     * @param $connection
     */
    public static function setConnectionOptions(\PDO $connection)
    {
        if (PlatformHelper::isWindows()) {
            return;
        }

        $connection->exec('SET ANSI_WARNINGS ON');
        $connection->exec('SET ANSI_PADDING ON');
        $connection->exec('SET ANSI_NULLS ON');
        $connection->exec('SET QUOTED_IDENTIFIER ON');
        $connection->exec('SET CONCAT_NULL_YIELDS_NULL ON');
    }

    /**
     * @param $query
     * @param array $values
     * @return void|mixed
     */
    public static function updateQuery($query, array $values = [])
    {
        if (PlatformHelper::isWindows()) {
            return $query;
        }

        for ($i = 0, $offset = 0; $pos = strpos($query, '?', $offset); $i++) {
            $offset = $pos + 1;
            if (isset($values[$i]) && is_string($values[$i])) {
                $query = substr_replace($query, 'N?', $pos, 1);
                $offset++;
            }
        }
        return $query;
    }
}
