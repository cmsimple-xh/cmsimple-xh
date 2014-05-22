<?php

/*
 * @version $Id: index.php 242 2014-05-06 20:20:18Z hi $
 *
 */

/**
 * jQuery for CMSimple
 *
 * Version:    1.5.2
 * Build:      2014050601
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
    include_jQueryUI();
}
?>