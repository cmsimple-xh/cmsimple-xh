<?php
/**
 * tinyMCE Editor - admin module
 *
 * Handles reading and writing of plugin files - init selectors are dynamically loaded from
 * ./inits/ representation of init_.js files
 *
 * PHP version 4 and 5
 *
 * @package tinymce
 * @copyright	1999-2009 <http://cmsimple.org/>
 * @copyright	2009-2012 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license	http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version	@CMSIMPLE_XH_VERSION@, @CMSIMPLE_XH_BUILD@
 * @version $Id$
 * @link	http://cmsimple-xh.org/
 * @since      File available since Release 1.6.0
 * @author     manu <http://www.pixolution.ch/>
 *
 */
/* utf-8 marker: äöü */

if (!XH_ADM) {     return; }

initvar('tinymce');

if ($tinymce) {

    $o .= '<div class="plugintext">';
    $o .= '<div class="plugineditcaption">TinyMCE for CMSimple_XH</div>';
    $o .= '<p>Version for @CMSIMPLE_XH_VERSION@</p>';
    $o .= '<p>TinyMCE version 3.5.10  &ndash; <a href="http://www.tinymce.com/" target="_blank">http://www.tinymce.com/</a>';
    $o .= tag('br');
    $o .= 'Available language packs: cs, da, de, en, et, fr, it, nl, pl, ru, sk tw, zh.</p>';
    $o .= '<p>CMSimple_XH & Filebrowser integration';
    $o .= tag('br');
    $o .= 'up to version 1.5.6 &ndash; <a href="http://www.zeichenkombinat.de/" target="_blank">Zeichenkombinat.de</a>';
    $o .= tag('br');
    $o .= 'from &nbsp;version 1.5.7 &ndash; <a href="http://www.pixolution.ch/" target="_blank">pixolution.ch</a></p>';
    $o .=tag('br');

    include_once $pth['folder']['classes'] . 'FileEdit.php';
/**
 * Editing of tinymce plugin config file.
 *
 * @package	XH
 */
    class XH_TinyMceConfigFileEdit extends XH_PluginConfigFileEdit
    {
/**
* Constructor
*/
        function XH_TinyMceConfigFileEdit()
        {
            parent::XH_PluginConfigFileEdit();
        }
/**
* Controller
* @return string output|nothing parsed output or nothing
*/
        function edit()
        {
            global $action;
            if ($this->setOptions('init'))
            {
                    if ($action!='plugin_save')
                        return $this->form();
                    else
                        return $this->submit();
            }
        }
/**
* Establish option values from ./inits/init_.js files for select field
* and affects cfg property
* @param $field select field name to set the options for
* @global array
* @return true if options available
*/
        function setOptions($field)
        {
            global $pth;

            $inits = glob($pth['folder']['plugins'] . 'tinymce/inits/*.js');
            $options = array();
            foreach ($inits as $init) {
                    $temp = explode('_', basename($init, '.js'));
                    if (isset($temp[1])) {
                            $options[] = $temp[1];
                    }
            }
            (bool) $options &&
                $this->cfg[$field]['']['vals'] = $options;
            return (bool) $options;
        }
    }   // End of class XH_TinyMceConfigFileEdit

    $tiymceConfig = new XH_TinyMceConfigFileEdit();
    $o .= $tiymceConfig->edit();
    $o .= '</div>';
}
/*
 * EOF tinymce/admin.php
 */
