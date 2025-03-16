<?php

use Beam\Beam\Beam as LaravelBeam;
use Beam\BeamCore\Beam;

if (!function_exists('appBasePath')) {
    function appBasePath(): string
    {
        $basePath = rtrim(strval(getcwd()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach (['public', 'pub', 'wp-admin'] as $dir) {
            if (str_ends_with($basePath, $dir . DIRECTORY_SEPARATOR)) {
                $basePath = substr($basePath, 0, -strlen($dir . DIRECTORY_SEPARATOR));

                break;
            }
        }

        return $basePath;
    }
}

if (!function_exists('ds')) {
    function ds(mixed ...$args): Beam|LaravelBeam
    {
        $sendRequest = function ($args, Beam $instance) {
            if ($args) {
                foreach ($args as $arg) {
                    $instance->write($arg);
                }
            }
        };

        if (class_exists(LaravelBeam::class) && function_exists('app')) {
            $instance = app(LaravelBeam::class);

            $sendRequest($args, $instance);

            return $instance;
        }

        $instance = new Beam();

        $sendRequest($args, $instance);

        return $instance;
    }
}

if (!function_exists('phpinfo')) {
    function phpinfo(): Beam
    {
        return ds()->phpinfo();
    }
}

if (!function_exists('dsd')) {
    function dsd(mixed ...$args): void
    {
        $instance = new Beam();

        foreach ($args as $arg) {
            $instance->write($arg);
        }

        die();
    }
}

if (!function_exists('dsq')) {
    function dsq(mixed ...$args): void
    {
        $instance = new Beam();

        if ($args) {
            foreach ($args as $arg) {
                $instance->write($arg, autoInvokeApp: false);
            }
        }
    }
}

if (!function_exists('runningInTest')) {
    function runningInTest(): bool
    {
        if (PHP_SAPI != 'cli') {
            return false;
        }

        if (str_contains($_SERVER['argv'][0], 'phpunit')) {
            return true;
        }

        if (str_contains($_SERVER['argv'][0], 'pest')) {
            return true;
        }

        return false;
    }
}
