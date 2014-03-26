<?php

define('APPDIR', realpath(__DIR__.'/..'));

// Autoload our application and tests
spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';

    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    if (file_exists(__DIR__.'/../src/'.$fileName)) {
        require 'src/'.$fileName;
    }

    if (file_exists(__DIR__.'/'.$fileName)) {
        require 'tests/'.$fileName;
    }
});
