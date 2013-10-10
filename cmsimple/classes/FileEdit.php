<?php

/**
 * Classes for online editing of text and config files.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */


/**
 * The abstract base class for editing of text and config files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6

 * @abstract
 */
class XH_FileEdit
{
    /**
     * Additional POST parameters.
     *
     * @var array
     * @access protected
     */
    var $params = array();

    /**
     * The name of the plugin.
     *
     * @var string
     * @access protected
     */
    var $plugin = null;

    /**
     * The caption of the form.
     *
     * @var string
     * @access protected
     */
    var $caption = null;

    /**
     * The name of the file to edit.
     *
     * @var string
     * @access protected
     */
    var $filename = null;

    /**
     * URL for redirecting after successful submission (PRG pattern).
     *
     * @var string
     * @access protected
     */
    var $redir = null;

    /**
     * Saves the file. Returns whether that succeeded.
     *
     * @return bool
     *
     * @access protected
     */
    function save()
    {
        return XH_writeFile($this->filename, $this->asString());
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @return string  (X)HTML.
     *
     * @abstract
     * @access public
     */
    function form()
    {
    }

    /**
     * Handles the form submission.
     *
     * If file could be successfully saved, triggers a redirect.
     * Otherwise writes error message to $e, and returns the edit form.
     *
     * @return mixed  The (X)HTML resp. void.
     *
     * @abstract
     * @access public
     */
    function submit()
    {
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @return string
     *
     * @abstract
     * @access protected
     */
    function asString()
    {
    }
}


/**
 * The abstract base class for editing of text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 *
 * @abstract
 */
class XH_TextFileEdit extends XH_FileEdit
{
    /**
     * The name of the textarea.
     *
     * @var string
     * @access protected
     */
    var $textareaName = null;

    /**
     * The contents of the file.
     *
     * @var string
     * @access protected
     */
    var $text = null;

    /**
     * Constructs an instance.
     *
     * @access protected
     */
    function XH_TextFileEdit()
    {
        $contents = XH_readFile($this->filename);
        if ($contents !== false) {
            $this->text = $contents;
        } else {
            e('cntopen', 'file', $this->filename);
            $this->text = '';
        }
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @return string  (X)HTML.
     *
     * @global object The CSRF protection object.
     *
     * @access public
     */
    function form()
    {
        global $tx, $_XH_csrfProtection;

        $action = isset($this->plugin) ? '?&amp;' . $this->plugin : '.';
        $value = utf8_ucfirst($tx['action']['save']);
        if (isset($_GET['xh_success'])) {
            $filetype = utf8_ucfirst($tx['filetype'][stsl($_GET['xh_success'])]);
            $message =  XH_message('success', $tx['message']['saved'], $filetype);
        } else {
            $message = '';
        }
        $button = tag('input type="submit" class="submit" value="' . $value . '"');
        $o = '<h1>' . $this->caption . '</h1>' . $message
            . '<form action="' . $action . '" method="POST">'
            . '<textarea rows="25" cols="80" name="' . $this->textareaName
            . '" class="cmsimplecore_file_edit">'
            . XH_hsc($this->text)
            . '</textarea>';
        foreach ($this->params as $param => $value) {
            $o .= tag(
                'input type="hidden" name="' . $param . '" value="' . $value . '"'
            );
        }
        $o .= $_XH_csrfProtection->tokenInput()
            . $button . '</form>';
        return $o;
    }

    /**
     * Handles the form submission.
     *
     * If file could be successfully saved, triggers a redirect.
     * Otherwise writes error message to $e, and returns the edit form.
     *
     * @return mixed
     *
     * @global object The CSRF protection object.
     *
     * @access public
     */
    function submit()
    {
        global $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $this->text = stsl($_POST[$this->textareaName]);
        if ($this->save()) {
            header('Location: ' . $this->redir, true, 303);
            exit;
        } else {
            e('cntsave', 'file', $this->filename);
            return $this->form();
        }
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @return string
     *
     * @access protected
     */
    function asString()
    {
        return $this->text;
    }
}


/**
 * Editing of core text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_CoreTextFileEdit extends XH_TextFileEdit
{
    /**
     * Construct an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The requested special file.
     * @global array  The localization of the core.
     *
     * @access public
     */
    function XH_CoreTextFileEdit()
    {
        global $pth, $file, $tx;

        $this->filename = $pth['file'][$file];
        $this->caption = utf8_ucfirst($tx['filetype'][$file]);
        $this->params = array('file' => $file, 'action' => 'save');
        $this->redir = "?file=$file&action=edit&xh_success=$file";
        $this->textareaName = 'text';
        parent::XH_TextFileEdit();
    }
}


/**
 * Editing of plugin text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_PluginTextFileEdit extends XH_TextFileEdit
{
    /**
     * Construct an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The name of the currently loading plugin.
     * @global array  The localization of the core.
     *
     * @access public
     */
    function XH_PluginTextFileEdit()
    {
        global $pth, $plugin, $tx;

        $this->plugin = $plugin;
        $this->filename = $pth['file']['plugin_stylesheet'];
        $this->params = array('admin' => 'plugin_stylesheet',
                              'action' => 'plugin_textsave');
        $this->redir = '?&' . $plugin
            . '&admin=plugin_stylesheet&action=plugin_text&xh_success=stylesheet';
        $this->textareaName = 'plugin_text';
        $this->caption = utf8_ucfirst($plugin) . ' &ndash; '
            . utf8_ucfirst($tx['filetype']['stylesheet']);
        parent::XH_TextFileEdit();
    }
}


/**
 * The abstract base class for editing of config files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 *
 * @abstract
 */
class XH_ArrayFileEdit extends XH_FileEdit
{

    /**
     *
     */
    var $cfg = null;

    /**
     * A dictionary which maps from config and languages keys
     * to their localization.
     *
     * @var array
     *
     * @access protected
     */
    var $lang = null;

    /**
     * The path of the meta language file,
     * which contains localization of config and language keys.
     *
     * @var string
     *
     * @access protected
     */
    var $metaLangFile;

    /**
     * Construct an instance
     */
    function XH_ArrayFileEdit()
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
    function save()
    {
        $ok = parent::save();
        $func = 'opcache_invalidate';
        if (function_exists($func)) {
            $func($this->filename);
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
    function translate($key)
    {
        return isset($this->lang[$key]) ? $this->lang[$key] : utf8_ucfirst($key);
    }

    /**
     * Returns a key split to category and rest.
     *
     * @param string $key The original key.
     *
     * @return array
     *
     * @access protected
     */
    function splitKey($key)
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
     *
     * @access protected
     */
    function hasVisibleFields($options)
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
     *
     * @access protected
     */
    function passwordDialog($iname)
    {
        global $tx;

        $id = $iname . '_DLG';
        $o = '<div id="' . $id . '" style="display:none">'
            . '<table style="width: 100%">'
            . '<tr><td>' . $tx['password']['old'] . '</td><td>'
            . tag(
                'input type="password" name="' . $iname . '_OLD" value=""'
                . ' autocomplete="off" class="cmsimplecore_settings"'
            )
            . '</td></tr>'
            . '<tr><td>' . $tx['password']['new'] . '</td><td>'
            . tag(
                'input type="password" name="' . $iname . '_NEW" value=""'
                . ' autocomplete="off" class="cmsimplecore_settings"'
            )
            . '</td></tr>'
            . '<tr><td>' . $tx['password']['confirmation'] . '</td><td>'
            . tag(
                'input type="password" name="' . $iname . '_CONFIRM" value=""'
                . ' autocomplete="off" class="cmsimplecore_settings"'
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
     * @access protected
     */
    function formField($cat, $name, $opt)
    {
        $iname = XH_FORM_NAMESPACE . $cat . '_' . $name;
        switch ($opt['type']) {
        case 'password':
            return $this->passwordDialog($iname);
        case 'text':
            $class = 'cmsimplecore_settings';
            if (utf8_strlen($opt['val']) < 50) {
                $class .= ' cmsimplecore_settings_short';
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
                $sel = $val == $opt['val'] ? ' selected="selected"' : '';
                $o .= '<option' . $sel . '>' . $val . '</option>';
            }
            $o .= '</select>';
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
                . '" class="cmsimplecore_settings"'
            );
        }
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @return string  (X)HTML.
     *
     * @global array  The paths of system files and folders.
     * @global array  The localization of the core.
     * @global string JS for the onload attribute of the body element.
     * @global object The CSRF protection object.
     *
     * @access public
     */
    function form()
    {
        global $pth, $tx, $onload, $_XH_csrfProtection;

        $action = isset($this->plugin) ? '?&amp;' . $this->plugin : '.';
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
            . '" method="POST" accept-charset="UTF-8">'
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
                        . $this->translate($displayName) . '</span>'
                        . '</div>'
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
     * @param array  $opt     An option record.
     * @param string $iname   The name of the INPUT element.
     * @param array  &$errors A LI elements with an error message.
     *
     * @return string
     *
     * @global array   The localization of the core.
     * @global object  The password hasher.
     */
    function submitPassword($opt, $iname, &$errors)
    {
        global $tx, $xh_hasher;

        if ($_POST[$iname . '_OLD'] == '') {
            $val = $opt['val'];
        } else {
            $val = false;
            $old = stsl($_POST[$iname . '_OLD']);
            $new = stsl($_POST[$iname . '_NEW']);
            $confirm = stsl($_POST[$iname . '_CONFIRM']);
            if (!$xh_hasher->CheckPassword($old, $opt['val'])) {
                $errors[] = '<li>' . $tx['password']['wrong'] . '</li>';
            } else {
                if ($new == '') {
                    $errors[] = '<li>' . $tx['password']['invalid'] . '</li>';
                } elseif ($new != $confirm) {
                    $errors[] = '<li>' . $tx['password']['mismatch'] . '</li>';
                } else {
                    $val = $xh_hasher->HashPassword($new);
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
     *
     * @access public
     */
    function submit()
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
                    $val = bin2hex($xh_hasher->get_random_bytes(12));
                }
                $this->cfg[$cat][$name]['val'] = $val;
            }
        }
        if (!empty($errors)) {
            $e .= implode('', $errors);
            return $this->form();
        } elseif ($this->save()) {
            header('Location: ' . $this->redir, true, 303);
            exit;
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
    function option($mcf, $val, $hint)
    {
        $type = isset($mcf) ? $mcf : 'string';
        if (strpos($type, 'enum:') === 0) {
            $vals = explode(',', substr($type, strlen('enum:')));
            $type = 'enum';
        } elseif (strpos($type, 'function:') === 0) {
            $func = substr($type, strlen('function:'));
            if (function_exists($func)) {
                $vals = call_user_func($func);
            } else {
                $vals = array();
            }
            $type = 'enum';
        } else {
            $vals = null;
        }
        $co = array('val' => $val, 'type' => $type,  'vals' => $vals);
        if (isset($hint)) {
            $co['hint'] = $hint;
        }
        return $co;
    }
}


/**
 * The abstract base class for editing of core config and text files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 *
 * @abstract
 */
class XH_CoreArrayFileEdit extends XH_ArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The current language.
     * @global string The key of the system file.
     * @global array  The localization of the plugins.
     *
     * @access protected
     */
    function XH_CoreArrayFileEdit()
    {
        global $pth, $sl, $file, $tx;

        $this->filename = $pth['file'][$file];
        $this->caption = utf8_ucfirst($tx['filetype'][$file]);
        $this->metaLangFile = $pth['folder']['language'] . 'meta' . $sl . '.php';
        parent::XH_ArrayFileEdit();
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @return string
     *
     * @access protected
     */
    function asString()
    {
        $o = "<?php\n\n";
        foreach ($this->cfg as $cat => $opts) {
            foreach ($opts as $name => $opt) {
                $opt = addcslashes($opt['val'], "\0..\37\"\$\\");
                $o .= "\$$this->varName['$cat']['$name']=\"$opt\";\n";
            }
        }
        $o .= "\n?>\n";
        return $o;
    }

    /**
     * Returns an array of select options for files.
     *
     * @param string $fn    The key of the system folder.
     * @param string $regex The regex the filename must match.
     *
     * @return array
     *
     * @global array The paths of system files and folders.
     *
     * @access protected
     */
    function selectOptions($fn, $regex)
    {
        global $pth;

        $options = array();
        if ($dh = opendir($pth['folder'][$fn])) {
            while (($p = readdir($dh)) !== false) {
                if (preg_match($regex, $p, $m)) {
                    $options[] = $m[1];
                }
            }
            closedir($dh);
        }
        natcasesort($options);
        return $options;
    }
}


/**
 * Editing of core config files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_CoreConfigFileEdit extends XH_CoreArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     *
     * @access public
     */
    function XH_CoreConfigFileEdit()
    {
        global $pth, $cf, $tx;

        parent::XH_CoreArrayFileEdit();
        $this->varName = 'cf';
        $this->params = array(
            'form' => 'array',
            'file' => 'config',
            'action' => 'save'
        );
        $this->redir = '?file=config&action=array&xh_success=config';
        $this->cfg = array();
        $fn = $pth['folder']['cmsimple'] . 'metaconfig.php';
        if (is_readable($fn)) {
            include $fn;
        }
        foreach ($cf as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                // The following are there for backwards compatibility,
                // and have to be suppressed in the config form.
                if ($cat == 'security' && $name == 'type'
                    || $cat == 'scripting' && $name == 'regexp'
                    || $cat == 'site' && $name == 'title'
                ) {
                    continue;
                }
                $omcf = isset($mcf[$cat][$name]) ? $mcf[$cat][$name] : null;
                $hint = isset($tx['help']["${cat}_$name"])
                    ? $tx['help']["${cat}_$name"] : null;
                $this->cfg[$cat][$name] = $this->option($omcf, $val, $hint);
            }
            if (empty($this->cfg[$cat])) {
                unset($this->cfg[$cat]);
            }
        }
    }
}


/**
 * Editing of core language files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_CoreLangFileEdit extends XH_CoreArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global string The current language.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     *
     * @access public
     */
    function XH_CoreLangFileEdit()
    {
        global $sl, $cf, $tx;

        parent::XH_CoreArrayFileEdit();
        $this->varName = 'tx';
        $this->params = array(
            'form' => 'array',
            'file' => 'language',
            'action' => 'save'
        );
        $this->redir = '?file=language&action=array&xh_success=language';
        $this->cfg = array();
        foreach ($tx as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                // don't show or save the following
                if ($cat == 'meta' && $name =='codepage') {
                    continue;
                }
                $co = array('val' => $val, 'type' => 'text');
                if ($cat == 'subsite' && $name == 'template') {
                    if ($sl === $cf['language']['default']) {
                        $co['type'] = 'hidden';
                    } else {
                        $co['type'] = 'enum';
                        $co['vals'] = $this->selectOptions(
                            'templates', '/^([^\.]*)$/i'
                        );
                        array_unshift($co['vals'], '');
                    }
                }
                $this->cfg[$cat][$name] = $co;
            }
        }
    }
}


/**
 * The abstract base class for plugin config file editing.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 *
 * @abstract
 */
class XH_PluginArrayFileEdit extends XH_ArrayFileEdit
{
    /**
     * The name of the config array variable.
     *
     * @var    string
     *
     * @access protected
     */
    var $varName = null;

    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The current language.
     * @global string The name of the currently loading plugin.
     *
     * @access protected
     */
    function XH_PluginArrayFileEdit()
    {
        global $pth, $sl, $plugin;

        $this->plugin = $plugin;
        $this->metaLangFile = $pth['folder']['plugins'] . $plugin
            . '/languages/meta' . $sl . '.php';
        parent::XH_ArrayFileEdit();
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @return string
     *
     * @access protected
     */
    function asString()
    {
        $o = "<?php\n\n";
        foreach ($this->cfg as $cat => $opts) {
            foreach ($opts as $name => $opt) {
                $key = $cat;
                !empty($name) and $key .= "_$name";
                $opt = addcslashes($opt['val'], "\0..\37\"\$\\");
                $o .= "\$$this->varName['$this->plugin']['$key']=\"$opt\";\n";
            }
        }
        $o .= "\n?>\n";
        return $o;
    }
}


/**
 * Editing of plugin config files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_PluginConfigFileEdit extends XH_PluginArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The name of the currently loading plugin.
     * @global array  The localization of the core.
     * @global array  The configuration of the plugins.
     * @global array  The localization of the plugins.
     *
     * @access public
     */
    function XH_PluginConfigFileEdit()
    {
        global $pth, $plugin, $tx, $plugin_cf, $plugin_tx;

        parent::XH_PluginArrayFileEdit();
        $this->caption = ucfirst($plugin) . ' &ndash; '
            . utf8_ucfirst($tx['filetype']['config']);
        $fn = $pth['folder']['plugins'] . $plugin . '/config/metaconfig.php';
        if (is_readable($fn)) {
            include $fn;
        }
        $mcf = isset($plugin_mcf[$plugin]) ? $plugin_mcf[$plugin] : array();
        $this->filename = $pth['file']['plugin_config'];
        $this->params = array('admin' => 'plugin_config',
                              'action' => 'plugin_save');
        $this->redir = '?&' . $plugin
            . '&admin=plugin_config&action=plugin_edit&xh_success=config';
        $this->varName = 'plugin_cf';
        $this->cfg = array();
        foreach ($plugin_cf[$plugin] as $key => $val) {
            list($cat, $name) = $this->splitKey($key);
            $omcf = isset($mcf[$key])
                ? $mcf[$key]
                : (utf8_strlen($val) <= 50 ? 'string' : 'text');
            $hint = isset($plugin_tx[$plugin]["cf_$key"])
                ? $plugin_tx[$plugin]["cf_$key"] : null;
            $this->cfg[$cat][$name] = $this->option($omcf, $val, $hint);
        }
    }
}


/**
 * Editing of plugin language files.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_PluginLanguageFileEdit extends XH_PluginArrayFileEdit
{
    /**
     * Constructs an instance.
     *
     * @global array  The paths of system files and folders.
     * @global string The name of the currently loading plugin.
     * @global array  The localization of the core.
     * @global array  The localization of the plugins.
     *
     * @access public
     */
    function XH_PluginLanguageFileEdit()
    {
        global $pth, $plugin, $tx, $plugin_tx;

        parent::XH_PluginArrayFileEdit();
        $this->caption = ucfirst($plugin) . ' &ndash; '
            . utf8_ucfirst($tx['filetype']['language']);
        $this->filename = $pth['file']['plugin_language'];
        $this->params = array('admin' => 'plugin_language',
                              'action' => 'plugin_save');
        $this->redir = '?&' . $plugin
            . '&admin=plugin_language&action=plugin_edit&xh_success=language';
        $this->varName = 'plugin_tx';
        $this->cfg = array();
        foreach ($plugin_tx[$plugin] as $key => $val) {
            list($cat, $name) = $this->splitKey($key);
            $co = array('val' => $val, 'type' => 'text');
            $this->cfg[$cat][$name] = $co;
        }
    }
}

?>
