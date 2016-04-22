<?php

namespace MediaMonks\MssqlBundle\Composer;

use Composer\Script\CommandEvent;
use Symfony\Component\Finder\Finder;

/**
 * @author Robert Slootjes <robert@mediamonks.com>
 */
class ScriptHandler
{
    /**
     * @param CommandEvent $event
     */
    public static function ensureDoctrineORMOverrides(CommandEvent $event)
    {
        $doctrineOrmPath = self::getDoctrineOrmPath();
        if(!file_exists($doctrineOrmPath)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($doctrineOrmPath)
            ->exclude([
                'Decorator',
                'Event',
                'Id',
                'Internal',
                'Proxy',
                'Query',
                'Tools',
                'Repository',
                'Tools',
                'Utility'
            ]);

        $baseFind    = 'use Doctrine\ORM\Persisters\Entity\\';
        $baseReplace = 'use MediaMonks\MssqlBundle\Doctrine\ORM\Persisters\Entity\\';
        $replaces    = [
            'BasicEntityPersister',
            'SingleTablePersister'
        ];

        foreach ($finder as $file) {
            $data = file_get_contents($file->getRealpath());
            if (strpos($data, $baseFind) === false) {
                continue;
            }
            foreach ($replaces as $replace) {
                $data = str_replace($baseFind . $replace, $baseReplace . $replace, $data);
            }
            file_put_contents($file->getRealpath(), $data);
        }
    }

    /**
     * @return string
     */
    public static function getDoctrineOrmPath()
    {
        if(strpos(__DIR__, 'vendor/mediamonks') === false) {
            return __DIR__ . '/../../../../vendor/doctrine/orm/lib/'; // for local development
        }
        return __DIR__ . '/../../../doctrine/orm/lib/'; // when installed as a package
    }
}
