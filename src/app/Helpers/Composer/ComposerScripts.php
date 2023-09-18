<?php

namespace GemaDigital\Framework\app\Helpers\Composer;

use Composer\Script\Event;

class ComposerScripts
{
    /**
     * Handle the post-install Composer event.
     *
     * @return void
     */
    public static function postInstall(): void
    {
        switch (DIRECTORY_SEPARATOR) {
            case '/': // unix
                exec('unlink "public/gemadigital"');
                exec('ln -s "../vendor/gemadigital/framework/src/public/gemadigital" "public/gemadigital"');
                break;
            case '\\': // windows
                exec('if exist "public\gemadigital" rmdir "public\gemadigital" /s /q');
                exec('mklink /J "public\gemadigital" "vendor\gemadigital\framework\src\public\gemadigital"');
                break;
        }
    }
}
