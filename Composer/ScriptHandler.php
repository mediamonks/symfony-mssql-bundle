<?php

namespace MediaMonks\MssqlBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Finder\Finder;

/**
 * @author Robert Slootjes <robert@mediamonks.com>
 */
class ScriptHandler
{
    const DOCTRINE_ORM_PACKAGE_PATH = '/doctrine/orm/lib/';

    /**
     * @param Event $event
     */
    public static function ensureDoctrineORMOverrides(Event $event)
    {
        $doctrineOrmPath = self::getDoctrineOrmPath($event);
        if (!file_exists($doctrineOrmPath)) {
            return; // Doctrine ORM does not seem be installed
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
        $replaces    = ['BasicEntityPersister'];

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
     * @param Event $event
     * @return string
     */
    public static function getDoctrineOrmPath(Event $event)
    {
        return $event->getComposer()->getConfig()->get('vendor-dir') . self::DOCTRINE_ORM_PACKAGE_PATH;
    }
}
