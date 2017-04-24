<?php

/**
 * The plugin menu builder.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

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
abstract class PluginMenu
{
    /**
     * The script name.
     *
     * @var string
     */
    protected $scriptName;

    /**
     * The name of current plugin.
     *
     * @var string
     */
    protected $plugin;

    /**
     * The label of the main item of the current plugin.
     *
     * @var string
     */
    protected $mainLabel;

    /**
     * The label of the stylesheet item of the current plugin.
     *
     * @var string
     */
    protected $cssLabel;

    /**
     * The label of the configuration item of the current plugin.
     *
     * @var string
     */
    protected $configLabel;

    /**
     * The label of the language item of the current plugin.
     *
     * @var string
     */
    protected $languageLabel;

    /**
     * The label of the help item of the current plugin.
     *
     * @var string
     */
    protected $helpLabel;

    /**
     * The URL of the main item of the current plugin.
     *
     * @var string
     */
    protected $mainUrl;

    /**
     * The URL of the stylesheet item of the current plugin.
     *
     * @var string
     */
    protected $cssUrl;

    /**
     * The URL of the configuration item of the current plugin.
     *
     * @var string
     */
    protected $configUrl;

    /**
     * The URL of the language item of the current plugin.
     *
     * @var string
     */
    protected $languageUrl;

    /**
     * The URL of the help item of the current plugin.
     *
     * @var string
     */
    protected $helpUrl;

    /**
     * Initializes a new instance.
     *
     * @global string The script name.
     */
    public function __construct()
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
    public function render($showMain)
    {
        global $plugin, $pth;

        $this->plugin = $plugin;
        pluginFiles($this->plugin);
        $this->initLabels();
        $this->initUrls();
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
     * @global array The localization of the core.
     * @global array The localization of the plugins.
     */
    private function initLabels()
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
     * @global array The paths of system files and folders.
     */
    private function initUrls()
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
