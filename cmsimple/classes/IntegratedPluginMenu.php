<?php

/**
 * The builders for the integrated plugin menus.
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
 * The menu builder for a plugin menu that is integrated in the admin menu.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.2
 */
class IntegratedPluginMenu extends PluginMenu
{
    /**
     * Makes the main menu item.
     *
     * @return void
     */
    protected function makeMainItem()
    {
        XH_registerPluginMenuItem($this->plugin, $this->mainLabel, $this->mainUrl);
    }

    /**
     * Makes the stylesheet menu item.
     *
     * @return void
     */
    protected function makeStylesheetItem()
    {
        XH_registerPluginMenuItem($this->plugin, $this->cssLabel, $this->cssUrl);
    }

    /**
     * Makes the configuration menu item.
     *
     * @return void
     */
    protected function makeConfigItem()
    {
        XH_registerPluginMenuItem($this->plugin, $this->configLabel, $this->configUrl);
    }

    /**
     * Makes the language menu item.
     *
     * @return void
     */
    protected function makeLanguageItem()
    {
        XH_registerPluginMenuItem($this->plugin, $this->languageLabel, $this->languageUrl);
    }

    /**
     * Makes the help menu item.
     *
     * @return void
     *
     * @todo target=_blank
     */
    protected function makeHelpItem()
    {
        XH_registerPluginMenuItem($this->plugin, $this->helpLabel, $this->helpUrl, '_blank');
    }
}
