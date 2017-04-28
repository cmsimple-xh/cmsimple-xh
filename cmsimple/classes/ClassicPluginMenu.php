<?php

/**
 * The plugin menu builders for the classic plugin menu.
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
 * The plugin menu builders for the classic plugin menu.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Peter Harteg <peter@harteg.dk>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.2
 */
class ClassicPluginMenu extends PluginMenu
{
    /**
     * The menu built so far.
     *
     * @var string HTML.
     */
    private $menu;

    /**
     * Initializes a new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->menu = '';
    }

    /**
     * Renders the plugin menu.
     *
     * Note that this override returns a string!
     *
     * @param bool $showMain Whether to show the main settings menu item.
     *
     * @return HTML
     */
    public function render($showMain)
    {
        $this->makeRow();
        parent::render($showMain);
        return $this->show();
    }

    /**
     * Makes the main menu item.
     *
     * @return void
     */
    protected function makeMainItem()
    {
        $this->makeTab(XH_hsc($this->mainUrl), '', $this->mainLabel);
    }

    /**
     * Makes the stylesheet menu item.
     *
     * @return void
     */
    protected function makeStylesheetItem()
    {
        $this->makeTab(XH_hsc($this->cssUrl), '', $this->cssLabel);
    }

    /**
     * Makes the configuration menu item.
     *
     * @return void
     */
    protected function makeConfigItem()
    {
        $this->makeTab(XH_hsc($this->configUrl), '', $this->configLabel);
    }

    /**
     * Makes the language menu item.
     *
     * @return void
     */
    protected function makeLanguageItem()
    {
        $this->makeTab(XH_hsc($this->languageUrl), '', $this->languageLabel);
    }

    /**
     * Makes the help menu item.
     *
     * @return void
     */
    protected function makeHelpItem()
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
    public function makeRow(array $style = array())
    {
        if (!isset($style['row'])) {
            $style['row'] = 'class="edit" style="width: 100%;"';
        }
        $template = '<table {{STYLE_ROW}}>' . "\n"
            . '<tr>' . "\n" . '{{TAB}}</tr>' . "\n" . '</table>' . "\n" . "\n";

        $this->menu .= str_replace('{{STYLE_ROW}}', $style['row'], $template);
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
    public function makeTab($link, $target, $text, array $style = array())
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
        $this->menu = str_replace('{{TAB}}', $tab . '{{TAB}}', $this->menu);
    }

    /**
     * Makes a new data menu item.
     *
     * @param string $text  Content of the td element.
     * @param array  $style Attributes of the td element.
     *
     * @return void
     */
    public function makeData($text, array $style = array())
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
        $this->menu = str_replace('{{TAB}}', $data . '{{TAB}}', $this->menu);
    }

    /**
     * Renders the built plugin menu.
     *
     * @return string HTML
     */
    public function show()
    {
        $this->menu = str_replace('{{TAB}}', '', $this->menu);
        $result = $this->menu;
        $this->menu = '';
        return $result;
    }
}
