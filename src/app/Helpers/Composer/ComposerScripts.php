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
                exec('ln -s "public\packages" "vendor\backpack\crud\src\public\packages"');
                exec('ln -s "public\img\flags" "packages\gemadigital\framework\src\public\img\flags"');
                break;
            case '\\': // windows
                exec('mklink /J "public\packages" "vendor\backpack\crud\src\public\packages"');
                exec('mklink /J "public\gemadigital" "packages\gemadigital\framework\src\public\gemadigital"');
                break;
        }
    }
}
