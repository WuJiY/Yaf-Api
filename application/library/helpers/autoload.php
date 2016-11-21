<?php
require(__DIR__ . '/../vendor/autoload.php');

spl_autoload_register(function($className) {
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, preg_replace('/^(\\\?app)/', '', $className));
    $classFile = APPLICATION_PATH . '/application/' . $classPath . '.php';
    if (is_file($classFile)) {
        require_once($classFile);
        if (! class_exists($className, false)
            && ! interface_exists($className, false)
            && ! trait_exists($className, false)
        ) {
            throw new \Exception("Unable to find '$className' in file: $classFile. Namespace missing?", E_ERROR);
        }

        return true;
    }

    return false;
}, true, true);