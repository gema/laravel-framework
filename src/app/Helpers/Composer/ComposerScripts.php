<?php

namespace GemaDigital\Framework\App\Helpers\Composer;

use Composer\Script\Event;

class ComposerScripts
{
    /**
     * Handle the post-install Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postInstall()
    {
        switch (DIRECTORY_SEPARATOR) {
            case '/': // unix
                exec('unlink "public\packages"');
                exec('ln -s "vendor/backpack/crud/src/public/packages" "public/packages"');
                exec('unlink "public\gemadigital"');
                exec('ln -s "packages/GemaDigital/Framework/src/public/gemadigital" "public/gemadigital"');
                break;
            case '\\': // windows
                exec('if exist "public\packages" rmdir "public\packages" /s /q');
                exec('mklink /J "public\packages" "vendor\backpack\crud\src\public\packages"');
                exec('if exist "public\gemadigital" rmdir "public\gemadigital" /s /q');
                exec('mklink /J "public\gemadigital" "packages\GemaDigital\Framework\src\public\gemadigital"');
                break;
        }
    }
}
