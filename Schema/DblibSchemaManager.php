<?php

namespace MediaMonks\MssqlBundle\Schema;

use Doctrine\DBAL\Schema\SQLServerSchemaManager;

class DblibSchemaManager extends SQLServerSchemaManager
{
    protected function _getPortableSequenceDefinition($sequence)
    {
        return end($sequence);
    }

    public function createDatabase($name)
    {
        $query = "CREATE DATABASE $name";
        if ($this->_conn->options['database_device']) {
            $query .= ' ON ' . $this->_conn->options['database_device'];
            $query .= $this->_conn->options['database_size'] ? '=' .
                $this->_conn->options['database_size'] : '';
        }
        return $this->_conn->standaloneQuery($query, null, true);
    }

    /**
     * lists all database sequences
     *
     * @param string|null $database
     * @return array
     */
    public function listSequences($database = null)
    {
        $query      = "SELECT name FROM sysobjects WHERE xtype = 'U'";
        $tableNames = $this->_conn->fetchAll($query);

        return array_map([$this->_conn->formatter, 'fixSequenceName'], $tableNames);
    }
}
