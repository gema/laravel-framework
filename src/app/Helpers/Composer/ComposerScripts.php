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
                exec('ln -s "../vendor/backpack/crud/src/public/packages" "public/packages"');
                exec('ln -s "../packages/GemaDigital/Framework/src/public/gemadigital" "public/gemadigital"');
                break;
            case '\\': // windows
                exec('mklink /J "public\packages" "vendor\backpack\crud\src\public\packages"');
                exec('mklink /J "public\gemadigital" "packages\GemaDigital\Framework\src\public\gemadigital"');
                break;
        }
    }
}
