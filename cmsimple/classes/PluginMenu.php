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
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
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
     * @access protected
     */
    var $scriptName;

    /**
     * The name of current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $plugin;

    /**
     * The label of the main item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $mainLabel;

    /**
     * The label of the stylesheet item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $cssLabel;

    /**
     * The label of the configuration item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $configLabel;

    /**
     * The label of the language item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $languageLabel;

    /**
     * The label of the help item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $helpLabel;

    /**
     * The URL of the main item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $mainUrl;

    /**
     * The URL of the stylesheet item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $cssUrl;

    /**
     * The URL of the configuration item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $configUrl;

    /**
     * The URL of the language item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $languageUrl;

    /**
     * The URL of the help item of the current plugin.
     *
     * @var string
     *
     * @access protected
     */
    var $helpUrl;

    /**
     * Initializes a new instance.
     *
     * @return void
     *
     * @global string The script name.
     */
    function XH_PluginMenu()
    {
        global $sn;

        $this->scriptName = $sn;
    }

    /**
     * Renders the default plugin menu.
     *
     * @param bool $showMain Whether to show the main menu item.
     *
     * @return void
     *
     * @global string The name of the current plugin.
     * @global array  The paths of system files and folders.
     */
    function render($showMain)
    {
        global $plugin, $pth;

        $this->plugin = $plugin;
        pluginFiles($this->plugin);
        $this->_initLabels();
        $this->_initUrls();
        if ($showMain) {
            $this->makeMainItem();
        }
        if (is_readable($pth['file']['plugin_stylesheet'])) {
            $this->makeStylesheetItem();
        }
        if (is_readable($pth['file']['plugin_config'])) {
            $this->makeConfigItem();
        }
        if (is_readable($pth['file']['plugin_language'])) {
            $this->makeLanguageItem();
        }
        if (is_readable($pth['file']['plugin_help'])) {
            $this->makeHelpItem($pth['file']['plugin_help']);
        }
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

        $this->mainLabel = empty($plugin_tx[$this->plugin]['menu_main'])
            ? $tx['menu']['tab_main']
            : $plugin_tx[$this->plugin]['menu_main'];
        $this->cssLabel = empty($plugin_tx[$this->plugin]['menu_css'])
            ? $tx['menu']['tab_css']
            : $plugin_tx[$this->plugin]['menu_css'];
        $this->configLabel = empty($plugin_tx[$this->plugin]['menu_config'])
            ? $tx['menu']['tab_config']
            : $plugin_tx[$this->plugin]['menu_config'];
        $this->languageLabel = empty($plugin_tx[$this->plugin]['menu_language'])
            ? $tx['menu']['tab_language']
            : $plugin_tx[$this->plugin]['menu_language'];
        $this->helpLabel = empty($plugin_tx[$this->plugin]['menu_help'])
            ? $tx['menu']['tab_help']
            : $plugin_tx[$this->plugin]['menu_help'];
    }

    /**
     * Initializes the menu item URLs.
     *
     * @return void
     *
     * @access private
     *
     * @global array The paths of system files and folders.
     */
    function _initUrls()
    {
        global $pth;

        $this->mainUrl = $this->scriptName . '?&' . $this->plugin
            . '&admin=plugin_main&action=plugin_text&normal';
        $this->cssUrl = $this->scriptName . '?&' . $this->plugin
            . '&admin=plugin_stylesheet&action=plugin_text&normal';
        $this->configUrl = $this->scriptName . '?&' . $this->plugin
            . '&admin=plugin_config&action=plugin_edit&normal';
        $this->languageUrl = $this->scriptName . '?&' . $this->plugin
            . '&admin=plugin_language&action=plugin_edit&normal';
        $this->helpUrl = $pth['file']['plugin_help'];
    }
}

/**
 * The plugin menu builder for the classic plugin menu.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Peter Harteg <peter@harteg.dk>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.2
 */
class XH_ClassicPluginMenu extends XH_PluginMenu
{
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
     */
    function XH_ClassicPluginMenu()
    {
        parent::XH_PluginMenu();
        $this->_menu = '';
    }

    /**
     * Renders the plugin menu.
     *
     * Note that this override returns a string!
     *
     * @param bool $showMain Whether to show the main settings menu item.
     *
     * @return (X)HTML.
     */
    function render($showMain)
    {
        $this->makeRow();
        parent::render($showMain);
        return $this->show();
    }

    /**
     * Makes the main menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeMainItem()
    {
        $this->makeTab(XH_hsc($this->mainUrl), '', $this->mainLabel);
    }

    /**
     * Makes the stylesheet menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeStylesheetItem()
    {
        $this->makeTab(XH_hsc($this->cssUrl), '', $this->cssLabel);
    }

    /**
     * Makes the configuration menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeConfigItem()
    {
        $this->makeTab(XH_hsc($this->configUrl), '', $this->configLabel);
    }

    /**
     * Makes the language menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeLanguageItem()
    {
        $this->makeTab(XH_hsc($this->languageUrl), '', $this->languageLabel);
    }

    /**
     * Makes the help menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeHelpItem()
    {
        $this->makeTab(XH_hsc($this->helpUrl), 'target="_blank"', $this->helpLabel);
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

/**
 * The menu builder for a plugin menu that is integrated in the admin menu.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.2
 */
class XH_IntegratedPluginMenu extends XH_PluginMenu
{
    /**
     * Makes the main menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeMainItem()
    {
        XH_registerPluginMenuItem(
            $this->plugin, $this->mainLabel, $this->mainUrl
        );
    }

    /**
     * Makes the stylesheet menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeStylesheetItem()
    {
        XH_registerPluginMenuItem(
            $this->plugin, $this->cssLabel, $this->cssUrl
        );
    }

    /**
     * Makes the configuration menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeConfigItem()
    {
        XH_registerPluginMenuItem(
            $this->plugin, $this->configLabel, $this->configUrl
        );
    }

    /**
     * Makes the language menu item.
     *
     * @return void
     *
     * @access protected
     */
    function makeLanguageItem()
    {
        XH_registerPluginMenuItem(
            $this->plugin, $this->languageLabel, $this->languageUrl
        );
    }

    /**
     * Makes the help menu item.
     *
     * @return void
     *
     * @access protected
     *
     * @todo target=_blank
     */
    function makeHelpItem()
    {
        XH_registerPluginMenuItem(
            $this->plugin, $this->helpLabel, $this->helpUrl, '_blank'
        );
    }
}

?>
