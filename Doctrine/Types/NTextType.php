<?php


namespace MediaMonks\MssqlBundle\Doctrine\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class NTextType extends Type
{
    const NTEXT = 'ntext';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NTEXT;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'NTEXT';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}