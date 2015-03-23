<?php

/**
 * The abstract base class for editing of config files.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The abstract base class for editing of config files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
abstract class XH_ArrayFileEdit extends XH_FileEdit
{

    /**
     * The configuration.
     *
     * @var array
     */
    protected $cfg = null;

    /**
     * A dictionary which maps from config and languages keys
     * to their localization.
     *
     * @var array
     */
    protected $lang = null;

    /**
     * The path of the meta language file,
     * which contains localization of config and language keys.
     *
     * @var string
     */
    protected $metaLangFile;

    /**
     * Construct an instance
     */
    public function __construct()
    {
        if (is_readable($this->metaLangFile)) {
            include $this->metaLangFile;
            $this->lang = $mtx;
        } else {
            $this->lang = array();
        }
    }

    /**
     * Saves the file and returns whether that succeeded.
     * Invalidates the cached file, if OPcache is enabled.
     *
     * @return bool
     */
    protected function save()
    {
        $ok = parent::save();
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->filename);
        }
        return $ok;
    }

    /**
     * Returns the localization of the given config or language key.
     *
     * @param string $key A config or language key.
     *
     * @return string
     */
    protected function translate($key)
    {
        $altKey = str_replace(' ', '_', $key);
        if (isset($this->lang[$key])) {
            $result = $this->lang[$key];
        } elseif (isset($this->lang[$altKey])) {
            $result = $this->lang[$altKey];
        } else {
            $result = utf8_ucfirst($key);
        }
        return $result;
    }

    /**
     * Returns a key split to category and rest.
     *
     * @param string $key The original key.
     *
     * @return array
     */
    protected function splitKey($key)
    {
        if (strpos($key, '_') !== false) {
            list($first, $rest) = explode('_', $key, 2);
        } else {
            $first = $key;
            $rest = '';
        }
        return array($first, $rest);
    }

    /**
     * Returns whether all options are hidden.
     *
     * @param array $options The list of options.
     *
     * @return bool
     */
    protected function hasVisibleFields($options)
    {
        foreach ($options as $opt) {
            if ($opt['type'] != 'hidden' && $opt['type'] != 'random') {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the "change password" dialog.
     *
     * @param string $iname The base name of the password input.
     *
     * @return string  The (X)HTML.
     *
     * @global array The localization of the core.
     */
    protected function passwordDialog($iname)
    {
        global $tx;

        $id = $iname . '_DLG';
        $o = '<div id="' . $id . '" style="display:none">'
            . '<table style="width: 100%">'
            . '<tr><td>' . $tx['password']['old'] . '</td><td>'
            . tag(
                'input type="password" name="' . $iname . '_OLD" value=""'
                . ' autocomplete="off" class="xh_setting"'
            )
            . '</td></tr>'
            . '<tr><td>' . $tx['password']['new'] . '</td><td>'
            . tag(
                'input type="password" name="' . $iname . '_NEW" value=""'
                . ' autocomplete="off" class="xh_setting"'
            )
            . '</td></tr>'
            . '<tr><td>' . $tx['password']['confirmation'] . '</td><td>'
            . tag(
                'input type="password" name="' . $iname . '_CONFIRM" value=""'
                . ' autocomplete="off" class="xh_setting"'
            )
            . '</td></tr>'
            . '</table>'
            . '</div>';
        $onclick = 'var dlg = document.getElementById(\'' . $id
            . '\'); XH.modalDialog(dlg, \'350px\', XH.validatePassword)';
        $o .= '<button type="button" onclick="' . $onclick
            . '">' . $tx['password']['change'] . '</button>';
        return $o;
    }

    /**
     * Returns a form field.
     *
     * @param string $cat  The category.
     * @param string $name The name.
     * @param array  $opt  The field options.
     *
     * @return string The (X)HTML.
     *
     * @global array The localization of the core.
     */
    protected function formField($cat, $name, $opt)
    {
        global $tx;

        $iname = XH_FORM_NAMESPACE . $cat . '_' . $name;
        switch ($opt['type']) {
        case 'password':
            return $this->passwordDialog($iname);
        case 'text':
            $class = 'xh_setting';
            if (utf8_strlen($opt['val']) < 50) {
                $class .= ' xh_setting_short';
            }
            return '<textarea name="' . $iname . '" rows="1" cols="50"'
                . ' class="' . $class . '">'
                . XH_hsc($opt['val'])
                . '</textarea>';
        case 'bool':
            return tag(
                'input type="checkbox" name="' . $iname . '"'
                . ($opt['val'] ? ' checked="checked"' : '')
            );
        case 'enum':
            $o = '<select name="' . $iname . '">';
            foreach ($opt['vals'] as $val) {
                $sel = ($val == $opt['val']) ? ' selected="selected"' : '';
                $label = ($val == '')
                    ? ' label="' . $tx['label']['empty'] . '"'
                    : '';
                $o .= '<option' . $sel . $label . '>' . XH_hsc($val) . '</option>';
            }
            $o .= '</select>';
            return $o;
        case 'xenum':
            $o = tag(
                'input type="text" name="' . $iname . '" value="'
                . XH_hsc($opt['val']) . '" class="xh_setting" list="'
                . $iname . '_DATA"'
            );
            $o .= '<datalist id="' . $iname . '_DATA">';
            foreach ($opt['vals'] as $val) {
                $label = ($val == '')
                    ? ' label="' . $tx['label']['empty'] . '"'
                    : '';
                $o .= tag('option' . $label . ' value="' . XH_hsc($val) . '"');
            }
            $o .= '</datalist>';
            return $o;
        case 'hidden':
        case 'random':
            return tag(
                'input type="hidden" name="' . $iname . '" value="'
                . XH_hsc($opt['val']) . '"'
            );
        default:
            return tag(
                'input type="text" name="' . $iname . '" value="'
                . XH_hsc($opt['val'])
                . '" class="xh_setting"'
            );
        }
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @return string  (X)HTML.
     *
     * @global string The script name.
     * @global array  The paths of system files and folders.
     * @global array  The localization of the core.
     * @global string JS for the onload attribute of the body element.
     * @global string The title of the current page.
     * @global object The CSRF protection object.
     */
    public function form()
    {
        global $sn, $pth, $tx, $onload, $title, $_XH_csrfProtection;

        $title = $this->caption;
        $action = isset($this->plugin) ? $sn . '?&amp;' . $this->plugin : $sn;
        $value = utf8_ucfirst($tx['action']['save']);
        $button = tag('input type="submit" class="submit" value="' . $value . '"');
        if (isset($_GET['xh_success'])) {
            $filetype = utf8_ucfirst($tx['filetype'][stsl($_GET['xh_success'])]);
            $message = XH_message('success', $tx['message']['saved'], $filetype);
        } else {
            $message = '';
        }
        $o = '<h1>' . $this->caption . '</h1>' . $message
            . '<form id="xh_config_form" action="' . $action
            . '" method="post" accept-charset="UTF-8">'
            . $button;
        foreach ($this->cfg as $category => $options) {
            $hasVisibleFields = $this->hasVisibleFields($options);
            if ($hasVisibleFields) {
                $o .= '<fieldset><legend>' . $this->translate($category)
                    . '</legend>';
            }
            foreach ($options as $name => $opt) {
                $info = isset($opt['hint']) ? XH_helpIcon($opt['hint']) . ' ' : '';
                if ($opt['type'] == 'hidden' || $opt['type'] == 'random') {
                    $o .= $this->formField($category, $name, $opt);
                } else {
                    $displayName = $name != ''
                        ? str_replace('_', ' ', $name)
                        : $category;
                    $o .= '<div class="xh_label">'
                        . $info . '<span class="xh_label">'
                        . $this->translate($displayName) . '</span>';
                    if ($category == 'meta' && $name == 'description') {
                        $o .= ' <span id="xh_description_length">['
                            . utf8_strlen($opt['val']) . ']</span>';
                    }
                    $o .= '</div>'
                        . '<div class="xh_field">'
                        . $this->formField($category, $name, $opt) . '</div>'
                        . tag('br');
                }
            }
            if ($hasVisibleFields) {
                $o .= '</fieldset>';
            }
        }
        foreach ($this->params as $param => $value) {
            $o .= tag(
                'input type="hidden" name="' . $param . '" value="' . $value . '"'
            );
        }
        $o .= $_XH_csrfProtection->tokenInput();
        $o .= $button . '</form>';
        $onload .= 'XH.makeTextareasAutosize(document.getElementById('
            . '\'xh_config_form\'));';

        return $o;
    }

    /**
     * Handles the submission of a password field and returns the new password
     * hash on success, <var>false</var> on failure to change the password.
     *
     * @param array  $opt    An option record.
     * @param string $iname  The name of the INPUT element.
     * @param array  $errors LI elements with an error message.
     *
     * @return string
     *
     * @global array   The localization of the core.
     * @global object  The password hasher.
     */
    protected function submitPassword($opt, $iname, &$errors)
    {
        global $tx, $xh_hasher;

        if (!isset($_POST[$iname . '_OLD']) || $_POST[$iname . '_OLD'] == '') {
            $val = $opt['val'];
        } else {
            $val = false;
            $old = isset($_POST[$iname . '_OLD'])
                ? stsl($_POST[$iname . '_OLD']) : '';
            $new = isset($_POST[$iname . '_NEW'])
                ? stsl($_POST[$iname . '_NEW']) : '';
            $confirm = isset($_POST[$iname . '_CONFIRM'])
                ? stsl($_POST[$iname . '_CONFIRM']) : '';
            if (!$xh_hasher->checkPassword($old, $opt['val'])) {
                $errors[] = '<li>' . $tx['password']['wrong'] . '</li>';
            } else {
                if (!preg_match('/^[!-~]+$/u', $new)) {
                    $errors[] = '<li>' . $tx['password']['invalid'] . '</li>';
                } elseif ($new != $confirm) {
                    $errors[] = '<li>' . $tx['password']['mismatch'] . '</li>';
                } else {
                    $val = $xh_hasher->hashPassword($new);
                }
            }
        }
        return $val;
    }

    /**
     * Handles the form submission.
     *
     * Triggers a redirect, if the submission was valid
     * and the file could be successfully saved.
     * Otherwise writes an error message to $e, and returns the edit form.
     *
     * @return string  The (X)HTML.
     *
     * @global string Error messages.
     * @global object The CSRF protection object.
     * @global object The password hasher.
     */
    public function submit()
    {
        global $e, $_XH_csrfProtection, $xh_hasher;

        $_XH_csrfProtection->check();
        $errors = array();
        foreach ($this->cfg as $cat => $opts) {
            foreach ($opts as $name => $opt) {
                $iname = XH_FORM_NAMESPACE . $cat . '_' . $name;
                $val = isset($_POST[$iname]) ? stsl($_POST[$iname]) : '';
                if ($opt['type'] == 'bool') {
                    $val = isset($_POST[$iname]) ? 'true' : '';
                } elseif ($opt['type'] == 'password') {
                    $val = $this->submitPassword($opt, $iname, $errors);
                } elseif ($opt['type'] == 'random') {
                    $val = bin2hex($xh_hasher->getRandomBytes(12));
                }
                $this->cfg[$cat][$name]['val'] = $val;
            }
        }
        if (!empty($errors)) {
            $e .= implode('', $errors);
            return $this->form();
        } elseif ($this->save()) {
            header('Location: ' . CMSIMPLE_URL . $this->redir, true, 303);
            XH_exit();
        } else {
            e('cntsave', 'file', $this->filename);
            return $this->form();
        }
    }

    /**
     * Returns an option array.
     *
     * @param string $mcf  The meta config of the option.
     * @param mixed  $val  The current value of the option.
     * @param string $hint A hint for the option usage.
     *
     * @return array
     */
    protected function option($mcf, $val, $hint)
    {
        $type = isset($mcf) ? $mcf : 'string';
        list($typeTag) = explode(':', $type);
        switch ($typeTag) {
        case 'enum':
        case 'xenum':
            $vals = explode(',', substr($type, strlen($typeTag) + 1));
            $type = $typeTag;
            break;
        case 'function':
        case 'xfunction':
            $func = substr($type, strlen($typeTag) + 1);
            if (function_exists($func)) {
                $vals = call_user_func($func);
            } else {
                $vals = array();
            }
            $type = ($typeTag == 'function') ? 'enum' : 'xenum';
            break;
        default:
            $vals = null;
        }
        $co = compact('val', 'type', 'vals');
        if (isset($hint)) {
            $co['hint'] = $hint;
        }
        return $co;
    }
}

?>
