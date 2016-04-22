<?php

namespace MediaMonks\MssqlBundle\Doctrine\ORM\Persisters\Entity;

use Doctrine\ORM\Persisters\Entity\JoinedSubclassPersister as BaseJoinedSubclassPersister;

class JoinedSubclassPersister extends BaseJoinedSubclassPersister
{
    /**
     * {@inheritdoc}
     */
    public function executeInserts()
    {
        throw new \Exception('Not implemented with UTF8 support yet, implement it or avoid joined subclasses');
    }
}
