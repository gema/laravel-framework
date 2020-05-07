<?php

namespace GemaDigital\Framework\app\Helpers\Composer;

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
                exec('unlink "public/packages"');
                exec('ln -s "../vendor/backpack/crud/src/public/packages" "public/packages"');
                exec('unlink "public/gemadigital"');
                exec('ln -s "../vendor/gemadigital/framework/src/public/gemadigital" "public/gemadigital"');

                // ElFinder Assets
                exec('mkdir -p foo "vendor/backpack/crud/src/public/packages/barryvdh/elfinder"');
                foreach (['css', 'img', 'js', 'sounds'] as $folder) {
                    exec('unlink "vendor/backpack/crud/src/public/packages/barryvdh/elfinder/' . $folder . '"');
                    exec('ln -s "../../../../../../../../vendor/studio-42/elfinder/' . $folder . '" "vendor/backpack/crud/src/public/packages/barryvdh/elfinder/' . $folder . '"');
                }
                break;
            case '\\': // windows
                exec('if exist "public\packages" rmdir "public\packages" /s /q');
                exec('mklink /J "public\packages" "vendor\backpack\crud\src\public\packages"');
                exec('if exist "public\gemadigital" rmdir "public\gemadigital" /s /q');
                exec('mklink /J "public\gemadigital" "vendor\gemadigital\framework\src\public\gemadigital"');

                // ElFinder Assets
                exec('if not exist "vendor/backpack/crud/src/public/packages/barryvdh/elfinder" mkdir "vendor/backpack/crud/src/public/packages/barryvdh/elfinder"');
                foreach (['css', 'img', 'js', 'sounds'] as $folder) {
                    exec('if exist "vendor/backpack/crud/src/public/packages/barryvdh/elfinder/' . $folder . '" rmdir "vendor/backpack/crud/src/public/packages/barryvdh/elfinder/' . $folder . '" /s /q');
                    exec('mklink /J "vendor/backpack/crud/src/public/packages/barryvdh/elfinder/' . $folder . '" "vendor/studio-42/elfinder/' . $folder . '"');
                }
                break;
        }
    }
}
