<?php

namespace MediaMonks\MssqlBundle\Doctrine\ORM\Persisters\Entity;

use Doctrine\ORM\Persisters\Entity\BasicEntityPersister as BaseBasicEntityPersister;

class BasicEntityPersister extends BaseBasicEntityPersister
{
    /**
     * {@inheritdoc}
     */
    public function executeInserts()
    {
        if (!$this->queuedInserts) {
            return [];
        }

        $postInsertIds  = [];
        $idGenerator    = $this->class->idGenerator;
        $isPostInsertId = $idGenerator->isPostInsertGenerator();
        $tableName      = $this->class->getTableName();

        foreach ($this->queuedInserts as $entity) {
            $insertData = $this->prepareInsertData($entity);

            $types  = [];
            $params = [];

            if (isset($insertData[$tableName])) {
                foreach ($insertData[$tableName] as $column => $value) {
                    $types[]  = $this->columnTypes[$column];
                    $params[] = $value;
                }
            }

            $this->conn->executeUpdate($this->getInsertSQL(), $params, $types);

            if ($isPostInsertId) {
                $generatedId     = $idGenerator->generate($this->em, $entity);
                $id              = [
                    $this->class->identifier[0] => $generatedId
                ];
                $postInsertIds[] = [
                    'generatedId' => $generatedId,
                    'entity'      => $entity,
                ];
            } else {
                $id = $this->class->getIdentifierValues($entity);
            }

            if ($this->class->isVersioned) {
                $this->assignDefaultVersionValue($entity, $id);
            }
        }

        $this->queuedInserts = [];

        return $postInsertIds;
    }
}
