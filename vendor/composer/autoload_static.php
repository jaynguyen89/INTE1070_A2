<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit029faa2a77ba6ecf4cbfa5fc03e4d763
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit029faa2a77ba6ecf4cbfa5fc03e4d763::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit029faa2a77ba6ecf4cbfa5fc03e4d763::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}