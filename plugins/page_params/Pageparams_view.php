<?php
/**
 * Page-Parameters - module page_params_view
 *
 * Creates the menu for the user to change
 * page-parameters per page.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Pageparams
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    Jerry Jakobsfeld <mail@simplesolutions.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * Returns a document fragment to be inserted to the HEAD element.
 *
 * @return (X)HTML.
 *
 * @since 1.6
 */
function Pageparams_hjs()
{
    global $plugin_tx;

    $message = addcslashes(
        $plugin_tx['page_params']['error_date_format'], "\t\n\r\\/\""
    );
    return <<<HTM
<script type="text/javascript">
/* <![CDATA[ */
var PAGEPARAMS = PAGEPARAMS || {};

function page_params_date_check(field) {
    var datearr = field.value.split(" ");
    var dateformat = /^\d{4}[-](0?[1-9]|1[012])[-](0?[1-9]|[12][0-9]|3[01])$/;
    var timeformat = /^([01]?[0-9]|2[0-3)])[:]([0-5]?[0-9])$/;

    if (datearr[0] == "" || datearr[0] == undefined) {
        datearr[0] = "2099-12-31";
    }
    if (datearr[1] == "" || datearr[1] == undefined) {
        datearr[1] = "00:00";
    }
    if (dateformat.test(datearr[0]) && timeformat.test(datearr[1])) {
        field.style.backgroundColor = "";
        field.style.color = "";
    } else {
        field.style.backgroundColor ="#ffe4e1";
        field.style.color = "#000";
        alert("$message");
    }
}

PAGEPARAMS.onLinkListChange = function(that) {
    var input = document.forms["page_params"].elements["header_location"];

    input.value = that.value ? "?" + that.value : "";
}
/* ]]> */
</script>

HTM;
}

 /**
  * Returns the caption of a page param section.
  *
  * @param string $label A label.
  * @param string $hint  A help tooltip text.
  *
  * @return string (X)HTML
  *
  * @since 1.6
  */
function Pageparams_caption($label, $hint)
{
    return "\n\t" . XH_helpIcon($hint)
        . "\n\t" . '<span class="pp_label">' . $label . '</span>';
}

/**
 * Returns a checkbox.
 *
 * @param string $name    Name of the checkbox.
 * @param bool   $checked Whether the checkbox is checked.
 * @param array  $toggles An array of elements to en-/disable.
 *
 * @return string (X)HTML
 *
 * @since 1.6
 */
function Pageparams_checkbox($name, $checked, $toggles)
{
    $checkedAttr = $checked ? ' checked="checked"' : '';
    $onclick = '';
    foreach ($toggles as $toggle) {
        $onclick .= 'document.forms[\'page_params\'].elements[\'' . $toggle
            . '\'].disabled=!document.forms[\'page_params\'].elements[\'' . $toggle
            . '\'].disabled;';
    }
    if ($onclick != '') {
        $onclick = ' onclick="' . $onclick . '"';
    }
    $o = "\n\t\t" . tag('input type="hidden" name="' . $name . '" value="0"')
        . tag(
            'input type="checkbox" name="' . $name . '" value="1"'
            . $checkedAttr . $onclick
        );
    return $o;
}

/**
 * Returns the last edit radio group.
 *
 * @param int $value The current value.
 *
 * @return string (X)HTML
 *
 * @global array The localization of the plugins.
 *
 * @since 1.6
 */
function Pageparams_lastEditRadiogroup($value)
{
    global $plugin_tx;

    $o = '';
    foreach (array('top' => 2, 'bottom' => 1, 'no' => 0) as $string => $number) {
        $checked = $value == $number ? ' checked="checked"' : '';
        $radio = tag(
            'input type="radio" name="show_last_edit"'
            . ' value="' . $number . '"' . $checked
        );
        $o .= "\n\t\t" . '<label>' . $radio
            . $plugin_tx['page_params'][$string] . '</label>';
    }
    $o .= tag('br');
    return $o;
}

/**
 * Returns the redirect radio group.
 *
 * @param int $value The current value.
 *
 * @return string (X)HTML
 *
 * @global array The localization of the plugins.
 *
 * @since 1.6
 */
function Pageparams_redirectRadiogroup($value)
{
    global $plugin_tx;

    $o = '';
    $onclick = '';
    $options = array('yes_new' => 2, 'yes_same' => 1, 'no' => 0);
    foreach ($options as $string => $number) {
        $checked = $value == $number ? ' checked="checked"' : '';
        foreach (array('header_location', 'pageparams_linklist') as $toggle) {
            $onclick .= 'document.forms[\'page_params\'].elements[\'' . $toggle
                . '\'].disabled=' . ($number > 0 ? 'false' : 'true') . ';';
        }
        $radio = tag(
            'input type="radio" name="use_header_location"'
            . ' value="' . $number . '"' . $checked . ' onclick="' . $onclick . '"'
        );
        $o .= "\n\t\t" . '<label>' . $radio
            . $plugin_tx['page_params'][$string] . '</label>';
    }
    $o .= tag('br');
    return $o;
}

/**
 * Returns an INPUT element.
 *
 * @param string $name     A name.
 * @param string $value    A value.
 * @param bool   $disabled Whether the element is disabled.
 *
 * @return string (X)HTML
 *
 * @since 1.6
 */
function Pageparams_input($name, $value, $disabled)
{
    $input = tag(
        'input type="text" size="50" name="' . $name . '"'
        . ' value="' . XH_hsc($value) . '"'
        . ($disabled ? ' disabled="disabled"' : '')
    );
    return "\n\t\t" . $input;
}

/**
 * Returns a text INPUT element for the scheduling.
 *
 * @param string $name     An element name.
 * @param string $value    An element value.
 * @param bool   $disabled Whether the input is disabled.
 *
 * @return string (X)HTML
 *
 * @since 1.6
 */
function Pageparams_scheduleInput($name, $value, $disabled)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    $js = 'page_params_date_check(document.forms[\'page_params\'].elements[\''
        . $name . '\'])';
    return tag(
        'input type="text" size="16" maxlength="16" name="' . $name . '"'
        . ' value="' . $value . '"' . $disabled
        . ' onchange="' . $js .'"'
    );
}

/**
 * Returns a template selectbox.
 *
 * @param array $page Page data of the current page.
 *
 * @return string (X)HTML
 *
 * @global array The localization of the plugins.
 *
 * @since 1.6
 */
function Pageparams_templateSelectbox($page)
{
    global $plugin_tx;

    if (isset($page['template']) && trim($page['template']) !== '') {
        $template = $page['template'];
        $selected = '';
    } else {
        $template = '';
        $selected = ' selected="selected"';
    }
    $o = "\n" . '<select name="template">';
    $o .= "\n\t" . '<option value="0"' . $selected . '>'
        . $plugin_tx['page_params']['use_default_template'] . '</option>';
    $templates = XH_templates();
    foreach ($templates as $file) {
        $selected = ($file == $template) ? ' selected="selected"' : '';
        $o .= "\n\t" . '<option value="' . $file . '"' . $selected
            . '>' . $file . '</option>';
    }
    $o .= "\n" . '</select>';
    return $o;
}

/**
 * Returns a quick select for site internal links.
 *
 * @param string $default  Default value of the redirect.
 * @param bool   $disabled Whether the SELECT element is initially disabled.
 *
 * @return string (X)HTML
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the plugins.
 *
 * @since 1.6
 */
function Pageparams_linkList($default, $disabled)
{
    global $pth, $plugin_tx;

    include_once $pth['folder']['classes'] . 'Pages.php';
    $pages = new XH_Pages();
    $disabled = $disabled ? ' disabled="disabled"' : '';
    $onchange = ' onchange="PAGEPARAMS.onLinkListChange(this)"';
    $o = '<select name="pageparams_linklist"' . $disabled . $onchange . '>';
    $links = $pages->linkList();
    array_unshift($links, array($plugin_tx['page_params']['quick_select'], ''));
    foreach ($links as $link) {
        list($heading, $url) = $link;
        $selected = '?' . $url == $default ? ' selected="selected"' : '';
        $o .= '<option value="' . $url . '"' . $selected . '>'
            . $heading . '</option>';
    }
    $o .= '</select>';
    return $o;
}

/**
 * Returns the editor tab view.
 *
 * @param array $page Page data of the current page.
 *
 * @return string (X)HTML
 *
 * @global string The script name.
 * @global string The URL of the current page.
 * @global string Document fragment to insert into the HEAD element.
 * @global array  The localization of the core.
 * @global array  The localization of the plugins.
 */
function Pageparams_view($page)
{
    global $sn, $su, $hjs, $tx, $plugin_tx;

    $hjs .= Pageparams_hjs();

    $lang = $plugin_tx['page_params'];

    $view = "\n" . '<form action="' . $sn . '?' . $su
        . '" method="post" id="page_params" name="page_params">';
    $view .= "\n\t" . '<p><b>' . $lang['form_title'] . '</b></p>';

    /*
     * heading
     */
    $view .= Pageparams_caption($lang['heading'], $lang['hint_heading']);
    $view .= Pageparams_checkbox(
        'show_heading', $page['show_heading'] == '1', array('heading')
    );
    $view .= tag('br');
    $view .= Pageparams_input(
        'heading', $page['heading'], $page['show_heading'] !== '1'
    );
    $view .= tag('br') . "\n\t" . tag('hr');

    /*
     * published
     */
    $view .= Pageparams_caption($lang['published'], $lang['hint_published']);
    $view .= Pageparams_checkbox(
        'published', $page['published'] != '0',
        array('expires', 'publication_date')
    );
    $view .= tag('br');
    $view .= "\n\t" . XH_helpIcon($lang['hint_publication_period']);
    $view .= "\n\t\t" . $plugin_tx['page_params']['publication_period'];
    $view .= Pageparams_scheduleInput(
        'publication_date', $page['publication_date'], $page['published'] == '0'
    );
    $view .= ' - ';
    $view .= Pageparams_scheduleInput(
        'expires', $page['expires'], $page['published'] == '0'
    );
    $view .= tag('br');
    $view .= "\n\t" . tag('hr');

    /*
     * linked to menu
     */
    $view .= Pageparams_caption(
        $lang['linked_to_menu'], $lang['hint_linked_to_menu']
    );
    $view .= Pageparams_checkbox(
        'linked_to_menu', $page['linked_to_menu'] !== '0', array()
    );
    $view .= tag('br');
    $view .= "\n\t" . tag('hr');

    /*
     * template chooser
     */
    $view .= Pageparams_caption($lang['template'], $lang['hint_template'])
        . Pageparams_templateSelectbox($page) . tag('br');
    $view .= "\n\t" . tag('hr');

    /*
     * last edit
     */
    $view .= Pageparams_caption($lang['show_last_edit'], $lang['hint_last_edit']);
    $view .= Pageparams_lastEditRadiogroup($page['show_last_edit']);
    if ($page['last_edit'] !== '') {
        $view .= "\n\t\t" . '&nbsp;&nbsp;(' . $lang['last_edit'] . ' '
            . date($tx['lastupdate']['dateformat'], $page['last_edit']) . ')';
    }
    $view .= "\n\t" . tag('hr');

    /*
     * header_location
     */
    $view .= Pageparams_caption(
        $lang['header_location'], $lang['hint_header_location']
    );
    $view .= Pageparams_redirectRadiogroup($page['use_header_location']);
    $view .= Pageparams_input(
        'header_location', $page['header_location'],
        (int) $page['use_header_location'] === 0
    );
    $view .= tag('br');
    $view .= Pageparams_linkList(
        $page['header_location'], (int) $page['use_header_location'] === 0
    );
    $view .= tag('br') . "\n\t";

    $view .= "\n\t" . tag('input name="save_page_data" type="hidden"')
        . "\n\t" . '<div style="text-align: right">'
        . "\n\t\t" . tag('input type="submit" value="' . $lang['submit'] . '"')
        . tag('br')
        . "\n\t" . '</div>'
        . "\n" . '</form>';
    return $view;
}

?>
