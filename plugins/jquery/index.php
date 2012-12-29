<?php

/**
 * jQuery for CMSimple
 *
 * @author Holger Irmler
 * @link http://cmsimple.holgerirmler.de
 * @version 1.3.3 - 2012-12-28
 * @build 2012122801
 * @package jQuery
 * */
/* utf8-marker = äöüß */

if ($plugin_cf['jquery']['autoload'] == '1') {
    include_once($pth['folder']['plugins'] . 'jquery/jquery.inc.php');
    include_jQuery();
    include_jQueryUI();
}
?>