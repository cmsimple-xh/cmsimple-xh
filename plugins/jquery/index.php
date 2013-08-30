<?php

/**
 * jQuery for CMSimple
 *
 * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.4 - 2013-03-30
 * @build 2012122801
 * @package jQuery
 * */

if ($plugin_cf['jquery']['autoload'] == '1') {
    include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
    include_jQuery();
    include_jQueryUI();
}
?>