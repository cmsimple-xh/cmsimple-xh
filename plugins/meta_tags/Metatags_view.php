<?php

/**
 * Meta-Tags - module meta_tags_view
 *
 * Creates the menu for the user to change meta-tags
 * (description, keywords, title and robots) per page.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Metatags
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/* utf8-marker = äöüß */

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
 * @global string The URL of the requested page.
 * @global array  The localization of the plugins.
 * @global array  The paths of system files and folders.
 * @global string The JavaScript for the onload attribute of the body element.
 * @global string The (X)HTML fragment to insert at the bottom of the body element.
 */
function Metatags_view($page)
{
    global $sn, $su, $plugin_tx, $pth, $onload, $bjs;

    $lang = $plugin_tx['meta_tags'];

    $my_fields = array('title', 'description', 'keywords', 'robots');

    $bjs .= <<<EOT
<script type="text/javascript">/* <![CDATA[ */
var META_TAGS = {};

META_TAGS.init = function () {
    var description = document.getElementById("meta_tags").elements.description,
        indicator = document.getElementById("mt_description_length");

    if (description && indicator) {
        XH.displayTextLength(description, indicator);
    }
};
/* ]]> */</script>
EOT;
    $onload .= 'META_TAGS.init();';

    $view ="\n" . '<form action="' . $sn . '?' . $su
        . '" method="post" id="meta_tags">'
        . "\n\t" . '<p><b>' . $lang['form_title'] . '</b></p>';
    foreach ($my_fields as $field) {
        $element = $field == 'description' || $field == 'keywords'
            ? '<textarea name="' . $field . '" rows="3" cols="30"'
                . ' class="xh_setting">'
                . XH_hsc($page[$field])
                . '</textarea>'
            : tag(
                'input type="text" class="xh_setting" size="50"'
                . ' name="' . $field . '" value="'
                . XH_hsc($page[$field]) . '"'
            );
        $view .= "\n\t" . XH_helpIcon($lang['hint_' . $field])
            . "\n\t" . '<label><span class = "mt_label">'
            . $lang[$field] . '</span>';
        if ($field == 'description') {
            $view .= '<span id="mt_description_length">['
                . utf8_strlen($page[$field]). ']</span>';
        }
        $view .= tag('br')
            . "\n\t\t" . $element . '</label>' . tag('hr');
    }
    $view .= "\n\t" . tag('input name="save_page_data" type="hidden"')
        . "\n\t" . '<div style="text-align: right;">'
        . "\n\t\t" . tag('input type="submit" value="' . $lang['submit'].'"')
        . tag('br')
        . "\n\t" . '</div>'
        . "\n" . '</form>';
    return $view;
}

?>
