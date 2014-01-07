<?php
/*
 * @version $Id: index.php 207 2013-11-10 16:11:43Z hi $
 *
 */

/**
 * jQuery for CMSimple
 *
 * Version:    1.5
 * Build:      2013111001
 * Copyright:  Holger Irmler
 * Email:      CMSimple@HolgerIrmler.de
 * Website:    http://CMSimple.HolgerIrmler.de
 * */

if ($plugin_cf['jquery']['autoload'] == '1' || $plugin_cf['jquery']['autoload'] == 'true') {
    include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
    include_jQuery();
    include_jQueryUI();
}
?>