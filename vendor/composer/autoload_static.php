<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf410908c6c3b1468b0ab8863771f00bb
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Repositories\\' => 13,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
            'PHPMailer\\' => 10,
        ),
        'M' => 
        array (
            'Models\\' => 7,
        ),
        'C' => 
        array (
            'Core\\' => 5,
            'Controllers\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Repositories\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Repositories',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'PHPMailer\\' => 
        array (
            0 => __DIR__ . '/../..' . '/PHPMailer',
        ),
        'Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Models',
        ),
        'Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Core',
        ),
        'Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Controllers',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf410908c6c3b1468b0ab8863771f00bb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf410908c6c3b1468b0ab8863771f00bb::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
