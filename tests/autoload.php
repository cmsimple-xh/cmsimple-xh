<?php

spl_autoload_register(function ($className) {
    // set $package, $subpackages and $class
    $subpackages = explode('_', $className);
    $packages = array_splice($subpackages, 0, 1);
    $package = $packages[0];
    $classes = array_splice($subpackages, -1);
    $class = $classes[0];

    // construct $filename
    if ($package == 'XH') {
        $folder = './cmsimple/classes/';
    } else {
        $folder = './plugins/' . strtolower($package) . '/classes/';
    }
    foreach ($subpackages as $subpackage) {
        $folder .= strtolower($subpackage) . '/';
    }
    $filename = $folder . $class . '.php';

    // error handling
    if (!file_exists($filename)) {
        var_dump($className, $filename);
    }

    // include the class file
    include_once $filename;
});

?>
