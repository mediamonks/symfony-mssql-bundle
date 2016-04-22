<?php

namespace MediaMonks\MssqlBundle\Platforms;

use Doctrine\DBAL\Platforms\SQLServer2008Platform;

/**
 * The DblibPlatform provides the behavior, features and SQL dialect of the MsSQL database platform.
 */
class DblibPlatform extends SQLServer2008Platform
{
    /**
     * {@inheritDoc}
     */
    public function getClobTypeDeclarationSQL(array $field)
    {
        return 'NVARCHAR(MAX)';
    }
}
