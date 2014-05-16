<?php

/**
 * The plugin menu builder.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The plugin menu builder.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Peter Harteg <peter@harteg.dk>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.2
 */
class XH_PluginMenu
{
    /**
     * The script name.
     *
     * @var string
     *
     * @access private
     */
    var $_scriptName;

    /**
     * The name of current plugin.
     *
     * @var string
     *
     * @access private
     */
    var $_plugin;

    /**
     * The label of the main item for the current plugin.
     *
     * @var string
     *
     * @access private
     */
    var $_mainLabel;

    /**
     * The label of the main item for the current plugin.
     *
     * @var string
     *
     * @access private
     */
    var $_cssLabel;

    /**
     * The label of the main item for the current plugin.
     *
     * @var string
     *
     * @access private
     */
    var $_configLabel;

    /**
     * The label of the main item for the current plugin.
     *
     * @var string
     *
     * @access private
     */
    var $_languageLabel;

    /**
     * The label of the main item for the current plugin.
     *
     * @var string
     *
     * @access private
     */
    var $_helpLabel;

    /**
     * The menu built so far.
     *
     * @var string (X)HTML.
     *
     * @access private
     */
    var $_menu;

    /**
     * Initializes a new instance.
     *
     * @return void
     *
     * @global string The script name.
     */
    function __construct()
    {
        global $sn;

        $this->_scriptName = $sn;
        $this->_menu = '';
    }

    /**
     * Renders the default plugin menu.
     *
     * @param bool $showMain Whether to show the main menu item.
     *
     * @return string (X)HTML.
     *
     * @global string The name of the current plugin.
     * @global array  The paths of system files and folders.
     */
    function render($showMain)
    {
        global $plugin, $pth;

        $this->_plugin = $plugin;
        pluginFiles($this->_plugin);
        $this->_initLabels();
        $this->makeRow();
        if ($showMain) {
            $this->_makeMainItem();
        }
        if (is_readable($pth['file']['plugin_stylesheet'])) {
            $this->_makeStylesheetItem();
        }
        if (is_readable($pth['file']['plugin_config'])) {
            $this->_makeConfigItem();
        }
        if (is_readable($pth['file']['plugin_language'])) {
            $this->_makeLanguageItem();
        }
        if (is_readable($pth['file']['plugin_help'])) {
            $this->_makeHelpItem($pth['file']['plugin_help']);
        }
        return $this->show();
    }

    /**
     * Initializes the menu item labels.
     *
     * @return void
     *
     * @access private
     *
     * @global array The localization of the core.
     * @global array The localization of the plugins.
     */
    function _initLabels()
    {
        global $tx, $plugin_tx;

        $this->_mainLabel = empty($plugin_tx[$this->_plugin]['menu_main'])
            ? $tx['menu']['tab_main']
            : $plugin_tx[$this->_plugin]['menu_main'];
        $this->_cssLabel = empty($plugin_tx[$this->_plugin]['menu_css'])
            ? $tx['menu']['tab_css']
            : $plugin_tx[$this->_plugin]['menu_css'];
        $this->_configLabel = empty($plugin_tx[$this->_plugin]['menu_config'])
            ? $tx['menu']['tab_config']
            : $plugin_tx[$this->_plugin]['menu_config'];
        $this->_languageLabel = empty($plugin_tx[$this->_plugin]['menu_language'])
            ? $tx['menu']['tab_language']
            : $plugin_tx[$this->_plugin]['menu_language'];
        $this->_helpLabel = empty($plugin_tx[$this->_plugin]['menu_help'])
            ? $tx['menu']['tab_help']
            : $plugin_tx[$this->_plugin]['menu_help'];
    }

    /**
     * Makes the main menu item.
     *
     * @return void
     *
     * @access private
     */
    function _makeMainItem()
    {
        $link = $this->_scriptName . '?&amp;' . $this->_plugin
            . '&amp;admin=plugin_main&amp;action=plugin_text';
        $this->makeTab($link, '', $this->_mainLabel);
    }

    /**
     * Makes the stylesheet menu item.
     *
     * @return void
     *
     * @access private
     */
    function _makeStylesheetItem()
    {
        $link = $this->_scriptName . '?&amp;' . $this->_plugin
            . '&amp;admin=plugin_stylesheet&amp;action=plugin_text';
        $this->makeTab($link, '', $this->_cssLabel);
    }

    /**
     * Makes the configuration menu item.
     *
     * @return void
     *
     * @access private
     */
    function _makeConfigItem()
    {
        $link = $this->_scriptName . '?&amp;' . $this->_plugin
            . '&amp;admin=plugin_config&amp;action=plugin_edit';
        $this->makeTab($link, '', $this->_configLabel);
    }

    /**
     * Makes the language menu item.
     *
     * @return void
     *
     * @access private
     */
    function _makeLanguageItem()
    {
        $link = $this->_scriptName . '?&amp;' . $this->_plugin
            . '&amp;admin=plugin_language&amp;action=plugin_edit';
        $this->makeTab($link, '', $this->_languageLabel);
    }

    /**
     * Makes the help menu item.
     *
     * @param string $filename A help filename.
     *
     * @return void
     *
     * @access private
     */
    function _makeHelpItem($filename)
    {
        $this->makeTab($filename, 'target="_blank"', $this->_helpLabel);
    }

    /**
     * Makes a new menu item row.
     *
     * @param array $style Attributes of the table element.
     *
     * @return void
     */
    function makeRow($style = array())
    {
        if (!isset($style['row'])) {
            $style['row'] = 'class="edit" style="width: 100%;"';
        }
        $template = '<table {{STYLE_ROW}}>' . "\n"
            . '<tr>' . "\n" . '{{TAB}}</tr>' . "\n" . '</table>' . "\n" . "\n";

        $this->_menu .= str_replace('{{STYLE_ROW}}', $style['row'], $template);
    }

    /**
     * Makes a new menu item tab.
     *
     * @param string $link   href attribute value of the anchor element.
     * @param string $target target attribute of the anchor element.
     * @param string $text   Content of the anchor element.
     * @param array  $style  Attributes of the td resp. anchor element.
     *
     * @return void
     */
    function makeTab($link, $target, $text, $style = array())
    {
        if (!isset($style['tab'])) {
            $style['tab'] = '';
        }
        if (!isset($style['link'])) {
            $style['link'] = '';
        }
        $tab = strtr(
            '<td {{STYLE_TAB}}><a {{STYLE_LINK}} href="{{LINK}}"'
            . ' {{TARGET}}>{{TEXT}}</a></td>' . "\n",
            array(
                '{{STYLE_TAB}}' => $style['tab'],
                '{{STYLE_LINK}}' => $style['link'],
                '{{LINK}}' => $link,
                '{{TARGET}}' => $target,
                '{{TEXT}}' => $text
            )
        );
        $this->_menu = str_replace('{{TAB}}', $tab . '{{TAB}}', $this->_menu);
    }

    /**
     * Makes a new data menu item.
     *
     * @param string $text  Content of the td element.
     * @param array  $style Attributes of the td element.
     *
     * @return void
     */
    function makeData($text, $style = array())
    {
        if (!isset($style['data'])) {
            $style['data'] = '';
        }
        $data = strtr(
            '<td {{STYLE_DATA}}>{{TEXT}}</td>' . "\n",
            array(
                '{{STYLE_DATA}}' => $style['data'],
                '{{TEXT}}' => $text
            )
        );
        $this->_menu = str_replace('{{TAB}}', $data . '{{TAB}}', $this->_menu);
    }

    /**
     * Renders the built plugin menu.
     *
     * @return string (X)HTML.
     */
    function show()
    {
        $this->_menu = str_replace('{{TAB}}', '', $this->_menu);
        $result = $this->_menu;
        $this->_menu = '';
        return $result;
    }
}

?>
