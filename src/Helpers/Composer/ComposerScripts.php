<?php

namespace GemaDigital\Helpers\Composer;

class ComposerScripts
{
    /**
     * Handle the post-install Composer event.
     */
    public static function postInstall(): void
    {
        match (DIRECTORY_SEPARATOR) {
            '/' => postInstallUnix(),
            '\\' => postInstallWindows(),
        };
    }

    /**
     * Post install Unix
     */
    public static function postInstallUnix(): void
    {
        //
    }

    /**
     * Post install Windows
     */
    public static function postInstallWindows(): void
    {
        //
    }
}
