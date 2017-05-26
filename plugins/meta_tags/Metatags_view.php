<?php

/**
 * Meta-Tags - module meta_tags_view
 *
 * Creates the menu for the user to change meta-tags
 * (description, keywords, title and robots) per page.
 *
 * @category  CMSimple_XH
 * @package   Metatags
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * Returns the Meta pagedata view.
 *
 * @param array $page The pagedata of the requested page.
 *                    Gets cleaned of unallowed doublequotes,
 *                    that will destroy input-fields.
 *
 * @return string
 *
 * @global string The site name.
 * @global array  The paths of system files and folders.
 * @global string The URL of the requested page.
 * @global array  The localization of the plugins.
 * @global string The HTML fragment to insert at the bottom of the body element.
 */
function Metatags_view(array $page)
{
    global $sn, $pth, $su, $plugin_tx, $bjs;

    $lang = $plugin_tx['meta_tags'];

    $my_fields = array('title', 'description', 'keywords', 'robots');

    $bjs .= '<script type="text/javascript" src="' . $pth['folder']['plugins']
        . 'meta_tags/metatags.min.js"></script>';

    $view ="\n" . '<form action="' . $sn . '?' . $su
        . '" method="post" id="meta_tags">'
        . "\n\t" . '<p><b>' . $lang['form_title'] . '</b></p>';
    foreach ($my_fields as $field) {
        $element = $field == 'description' || $field == 'keywords'
            ? '<textarea name="' . $field . '" rows="3" cols="30"'
                . ' class="xh_setting">'
                . XH_hsc($page[$field])
                . '</textarea>'
            : '<input type="text" class="xh_setting" size="50"'
                . ' name="' . $field . '" value="'
                . XH_hsc($page[$field]) . '">';
        $view .= "\n\t" . XH_helpIcon($lang['hint_' . $field])
            . "\n\t" . '<label><span class = "mt_label">'
            . $lang[$field] . '</span>';
        if ($field == 'title') {
            $view .= '<span id="mt_title_length">['
                . utf8_strlen($page[$field]). ']</span>';
        } elseif ($field == 'description') {
            $view .= '<span id="mt_description_length">['
                . utf8_strlen($page[$field]). ']</span>';
        }
        $view .= '<br>'
            . "\n\t\t" . $element . '</label>' . '<hr>';
    }
    $view .= "\n\t" . '<input name="save_page_data" type="hidden">'
        . "\n\t" . '<div style="text-align: right;">'
        . "\n\t\t" . '<input type="submit" value="' . $lang['submit'].'">'
        . '<br>'
        . "\n\t" . '</div>'
        . "\n" . '</form>';
    return $view;
}
