<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit96bff1a0bd3cf6ea997ccb19027442b7
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Phiber\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Phiber\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Phiber\\entity\\entity' => __DIR__ . '/../..' . '/entity/entity.php',
        'Phiber\\oosql\\collection' => __DIR__ . '/../..' . '/collection.php',
        'Phiber\\oosql\\oogen' => __DIR__ . '/../..' . '/oogen.php',
        'Phiber\\oosql\\oosql' => __DIR__ . '/../..' . '/oosql.php',
        'Phiber\\oosql\\vendor\\mysql' => __DIR__ . '/..',
        'Phiber\\oosql\\vendor\\pgsql' => __DIR__ . '/..',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit96bff1a0bd3cf6ea997ccb19027442b7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit96bff1a0bd3cf6ea997ccb19027442b7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit96bff1a0bd3cf6ea997ccb19027442b7::$classMap;

        }, null, ClassLoader::class);
    }
}
