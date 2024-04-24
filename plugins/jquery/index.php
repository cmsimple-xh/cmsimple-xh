<?php

/**
 * jQuery for CMSimple
 *
 * Version:    1.6.6
 * Build:      2023071101
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

if ($plugin_cf['jquery']['autoload'] == '1' || $plugin_cf['jquery']['autoload'] == 'true') {
    include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
    include_jQuery();
    if ($plugin_cf['jquery']['autoload_libraries'] == 'jQuery & jQueryUI') {
        include_jQueryUI();
    }
}