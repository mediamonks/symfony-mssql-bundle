<?php

namespace MediaMonks\MssqlBundle\Helper;

class PlatformHelper
{
    /**
     * @return bool
     */
    public static function isWindows()
    {
        return stristr(PHP_OS, 'WIN');
    }
}
