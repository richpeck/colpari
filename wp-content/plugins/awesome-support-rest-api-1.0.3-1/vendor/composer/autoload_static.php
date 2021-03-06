<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6262c48ae614cda26e2edac8ff14975a
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPAS_API\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPAS_API\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6262c48ae614cda26e2edac8ff14975a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6262c48ae614cda26e2edac8ff14975a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
