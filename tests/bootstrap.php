<?php

const CMSIMPLE_XH_VERSION = 'CMSimple_XH 1.8';
const CMSIMPLE_XH_DATE = '2024-12-11';
const CMSIMPLE_URL = 'http://example.com/';
const XH_URICHAR_SEPARATOR = '|';

require_once './vendor/autoload.php';

require_once './cmsimple/utf8.php';
require_once './cmsimple/functions.php';
require_once './cmsimple/adminfuncs.php';
require_once './cmsimple/tplfuncs.php';
require_once './cmsimple/compat.php';

require_once './tests/unit/FunctionMock.php';
require_once './tests/unit/UopzFunctionMock.php';
require_once './tests/TestCase.php';
require_once './tests/unit/ControllerLogInOutTestCase.php';

spl_autoload_register(function ($className) {
    $className = str_replace('_', '\\', $className);
    // set $package, $subpackages and $class
    $subpackages = explode('\\', $className);
    $packages = array_splice($subpackages, 0, 1);
    if (empty($packages)) {
        return;
    }
    $package = $packages[0];
    $classes = array_splice($subpackages, -1);
    if (empty($classes)) {
        return;
    }
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

    if (file_exists($filename)) {
        include_once $filename;
    }
});

date_default_timezone_set('UTC');
