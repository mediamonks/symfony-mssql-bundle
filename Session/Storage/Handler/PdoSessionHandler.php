<?php

namespace MediaMonks\MssqlBundle\Session\Storage\Handler;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler as BasePdoSessionHandler;

class PdoSessionHandler extends BasePdoSessionHandler
{
    /**
     * PdoSessionHandler constructor.
     * @param null $pdoOrDsn
     * @param array $options
     */
    public function __construct($pdoOrDsn = null, array $options = [])
    {
        $options['lockMode'] = BasePdoSessionHandler::LOCK_NONE;
        parent::__construct($pdoOrDsn, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return base64_decode(parent::read($sessionId));
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        return parent::write($sessionId, base64_encode($data));
    }
}
