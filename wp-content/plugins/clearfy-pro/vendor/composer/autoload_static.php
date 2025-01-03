<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite7dea4450716d94cee4bd31d216d6ce2
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPShop\\ClearfyPro\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPShop\\ClearfyPro\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite7dea4450716d94cee4bd31d216d6ce2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite7dea4450716d94cee4bd31d216d6ce2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite7dea4450716d94cee4bd31d216d6ce2::$classMap;

        }, null, ClassLoader::class);
    }
}
