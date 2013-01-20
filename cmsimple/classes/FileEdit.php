<?php

/**
 * Classes for online editing of text and config files.
 *
 * @package	XH
 * @copyright	1999-2009 <http://cmsimple.org/>
 * @copyright	2009-2012 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license	http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version	$CMSIMPLE_XH_VERSION$, $CMSIMPLE_XH_BUILD$
 * @version 	$Id$
 * @link	http://cmsimple-xh.org/
 */


/**
 * The abstract base class for editing of text and config files.
 *
 * @package	XH
 * @abstract
 */
class XH_FileEdit
{
    /**
     * Additional POST parameters.
     *
     * @access protected
     * @var    array
     */
    var $params = array();

    /**
     * The name of the plugin.
     *
     * @access protected
     * @var    string
     */
    var $plugin = null;

    /**
     * @var string
     */
    var $caption = null;

    /**
     * The name of the file to edit.
     *
     * @access protected
     * @var    string
     */
    var $filename = null;

    /**
     * URL for redirecting after successful submission (PRG pattern).
     *
     * @access protected
     * @var    string
     */
    var $redir = null;

    /**
     * Saves the file. Returns whether that succeeded.
     *
     * @access protected
     * @return bool
     */
    function save()
    {
	// TODO: use XH_writeFile()
        $ok = is_writable($this->filename)
	    && ($fh = fopen($this->filename, 'w'))
	    && fwrite($fh, $this->asString()) !== false;
        if (!empty($fh)) {
	    fclose($fh);
        }
	return $ok;
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @abstract
     * @access public
     * @return string  (X)HTML.
     */
    function form() {}

    /**
     * Handles the form submission.
     *
     * If file could be successfully saved, triggers a redirect.
     * Otherwise writes error message to $e, and returns the edit form.
     *
     * @abstract
     * @access public
     * @return mixed  The (X)HTML resp. void.
     */
    function submit() {}

    /**
     * Returns the the file contents as string for saving.
     *
     * @abstract
     * @access protected
     * @return string
     */
    function asString() {}
}


/**
 * The abstract base class for editing of text files.
 *
 * @package	XH
 * @abstract
 */
class XH_TextFileEdit extends XH_FileEdit
{
    /**
     * The name of the textarea.
     *
     * @var string
     */
    var $textareaName = null;

    /**
     * The contents of the file.
     *
     * @var string
     */
    var $text = null;

    /**
     *
     */
    function XH_TextFileEdit()
    {
	// TODO: error handling
	$this->text = file_get_contents($this->filename);
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @access public
     * @return string  (X)HTML.
     */
    function form()
    {
        global $tx;

	$action = isset($this->plugin) ? '?&amp;' . $this->plugin : '.';
	$o = '<h1>' . ucfirst($this->caption) . '</h1>'
	    . '<form action="' . $action . '" method="POST">'
	    . '<textarea rows="25" cols="80" name="' . $this->textareaName
            . '" class="cmsimplecore_file_edit">'
	    . htmlspecialchars($this->text, ENT_NOQUOTES, 'UTF-8')
	    . '</textarea>';
	foreach ($this->params as $param => $value) {
	    $o .= tag('input type="hidden" name="' . $param . '" value="' . $value . '"');
	}
	$o .= tag('input type="submit" class="submit" value="'
                  . ucfirst($tx['action']['save']) . '"')
	    . '</form>';
	return $o;
    }

    /**
     * Handles the form submission.
     *
     * If file could be successfully saved, triggers a redirect.
     * Otherwise writes error message to $e, and returns the edit form.
     *
     * @access public
     * @return mixed
     */
    function submit()
    {
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
     * @access protected
     * @return string
     */
    function asString()
    {
	return $this->text;
    }
}


/**
 * Editing of core text files.
 *
 * @package	XH
 */
class XH_CoreTextFileEdit extends XH_TextFileEdit
{
    /**
     *
     */
    function XH_CoreTextFileEdit()
    {
        global $pth, $file;

        $this->filename = $pth['file'][$file];
        $this->params = array('file' => $file, 'action' => 'save');
	$this->redir = "?file=$file&action=edit";
        $this->textareaName = 'text';
	parent::XH_TextFileEdit();
    }
}


/**
 * @package	XH
 */
class XH_PluginTextFileEdit extends XH_TextFileEdit
{
    function XH_PluginTextFileEdit()
    {
        global $pth, $plugin;

        $this->plugin = $plugin;
        $this->filename = $pth['file']['plugin_stylesheet'];
        $this->params = array('admin' => 'plugin_stylesheet',
                              'action' => 'plugin_textsave');
	$this->redir = "?&$plugin&admin=plugin_stylesheet&action=plugin_text";
        $this->textareaName = 'plugin_text';
        $this->caption = $plugin;
	parent::XH_TextFileEdit();
    }
}


/**
 * The abstract base class for editing of config files.
 *
 * @package	XH
 * @abstract
 */
class XH_ArrayFileEdit extends XH_FileEdit
{
    var $cfg = null;
    //var $file = null;
    //var $admin = null;
    //var $action = null;
    //var $plugin = null;

    // TODO: constructor should probably be abstract
    function XH_ArrayFileEdit()
    {
        $this->cfg = array(
            'general' => array(
                'one' => array(
                    'val' => 'hugo',
                    'hint' => 'This is the first option',
                    'type' => 'string'
                ),
                'two' => array(
                    'val' => 'Peter, Paul and Mary ...',
                    'hint' => 'This is the second option',
                    'type' => 'text'
                )
            ),
            'special' => array(
                'three' => array(
                    'val' => TRUE,
                    'hint' => 'This is the third option',
                    'type' => 'bool'
                ),
                'four' => array(
                    'val' => 'two',
                    'hint' => 'This is the fourth option',
                    'type' => 'enum',
                    'vals' => array('one', 'two', 'three')
                )
            )
        );
    }

    /**
     * Returns a key split to category and rest.
     *
     * @access protected
     * @param  string $key
     * @return array
     */
    function splitKey($key)
    {
	// TODO: use explode()'s $limit
        $parts = explode('_', $key);
        $first = array_shift($parts);
        return array($first, implode('_', $parts));
    }

    function hasVisibleFields($options)
    {
	foreach ($options as $opt) {
	    if ($opt['type'] != 'hidden') {
		return true;
	    }
	}
	return false;
    }

    /**
     * Returns the "change password" dialog.
     *
     * @todo: finish up
     * @todo: i18n
     *
     * @access private
     *
     * @global array  The pathes of system files and folders.
     * @global string  Scripts to insert before the closing body tag.
     * @param  string $name  The name of the password config option.
     * @return string  The (X)HTML.
     */
    function passwordDialog($name)
    {
	global $pth, $bjs;

	include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php';
	include_jQuery();
	include_jQueryUI();
	$id = "xh_${name}_dialog";
        $iname = XH_FORM_NAMESPACE . $name;
	$o = '<div id="' . $id . '" title="Change Password" style="display:none">'
	    . '<table>'
	    . '<tr><td>Old Password</td><td>' . tag('input type="password"') . '</td></tr>'
	    . '<tr><td>New Password</td><td>' . tag('input type="password"') . '</td></tr>'
	    . '<tr><td>Confirmation</td><td>' . tag('input type="password"') . '</td></tr>'
	    . '</table>'
	    . '</div>';
	$o .= '<button onclick="jQuery(\'#' . $id . '\').dialog(\'open\');return false">Change Password</button>';
	$bjs .= <<<EOS
<script type="text/javascript">
jQuery("#$id").dialog({
    autoOpen: false,
    modal: true,
    width: 400,
    buttons: {
	"Change Password": function() {
	    var inputs = jQuery(this).find("td:nth-child(2) input");
	    var oldPW = inputs.get(0).value;
	    var newPW = inputs.get(1).value;
	    var confirm = inputs.get(2).value;
	    // TODO: check old password!!!
	    if (confirm != newPW) {
		alert('New Passwords do not match!');
	    } else {
		var form = window.document.getElementById("xh_config_form");
		form.elements["$iname"].value = newPW;
		jQuery(this).dialog("close");
	    }
	},
	"Cancel": function() {
	    jQuery(this).dialog("close");
	}
    }
})
</script>
EOS;
	return $o;
    }

    /**
     * Returns a form field.
     *
     * @access private
     *
     * @param  string $cat  The category.
     * @param  string $name  The name.
     * @param  array $opt  The field options.
     * @return string  The (X)HTML.
     */
    function formField($cat, $name, $opt)
    {
        $iname = XH_FORM_NAMESPACE . $cat . '_' . $name;
        switch ($opt['type']) {
        case 'password':
            return $this->passwordDialog($cat . '_' . $name)
		. tag('input type="hidden" name="' . $iname . '" value="'
                       . htmlspecialchars($opt['val'], ENT_QUOTES, 'UTF-8')
                       . '" class="cmsimplecore_settings"')
		. tag('input type="hidden" name="' . $iname . '_OLD" value="'
		      . htmlspecialchars($opt['val'], ENT_QUOTES, 'UTF-8')
		      . '"');
        case 'text':
	    $class = 'cmsimplecore_settings';
	    if (utf8_strlen($opt['val']) < 30) {
		$class .= ' cmsimplecore_settings_short';
	    }
            return '<textarea name="' . $iname . '" rows="3" cols="30"'
		. ' class="' . $class . '">'
                . htmlspecialchars($opt['val'], ENT_NOQUOTES, 'UTF-8')
                . '</textarea>';
        case 'bool':
            return tag('input type="checkbox" name="' . $iname . '"'
                       . ($opt['val'] ? ' checked="checked"' : ''));
        case 'enum':
            $o = '<select name="' . $iname . '">';
            foreach ($opt['vals'] as $val) {
                $sel = $val == $opt['val'] ? ' selected="selected"' : '';
                $o .= '<option' . $sel . '>' . $val . '</option>';
            }
            $o .= '</select>';
            return $o;
	case 'hidden':
            return tag('input type="hidden" name="' . $iname . '" value="'
                       . htmlspecialchars($opt['val'], ENT_QUOTES, 'UTF-8') . '"');
        default:
            return tag('input type="text" name="' . $iname . '" value="'
                       . htmlspecialchars($opt['val'], ENT_QUOTES, 'UTF-8')
                       . '" class="cmsimplecore_settings"');
        }
    }

    /**
     * Returns the form to edit the file contents.
     *
     * @access public
     * @global array
     * @global array
     * @return string  (X)HTML.
     */
    function form()
    {
        global $pth, $tx;

        $action = isset($this->plugin) ? '?&amp;' . $this->plugin : '.';
	$o = '<h1>' . ucfirst($this->caption) . '</h1>'
	    . '<form id="xh_config_form" action="' . $action . '" method="POST" accept-charset="UTF-8">'
            . '<table style="width: 100%">';
        foreach ($this->cfg as $category => $options) {
	    if ($this->hasVisibleFields($options)) {
                $o .= '<tr><td colspan="2"><h4>' . ucfirst($category)
		    . '</h4></td></tr>';
	    }
            foreach ($options as $name => $opt) {
                $info = isset($opt['hint'])
                    ? '<div class="pl_tooltip">'
                        . tag('img src="' . $pth['folder']['flags'] . 'help_icon.png" alt=""')
                        . '<div>' . $opt['hint'] . '</div></div> '
                    : '';
		if ($opt['type'] == 'hidden') {
		    $o .= '<tr><td colspan="2">'
			. $this->formField($category, $name, $opt);
		} else {
		    $o .= '<tr><td>' . $info . ucfirst($name) . '</td><td>'
			. $this->formField($category, $name, $opt);
		}
		$o .= '</td></tr>';
            }
        }
        $o .= '</table>';
        foreach ($this->params as $param => $value) {
            $o .= tag('input type="hidden" name="' . $param . '" value="' . $value . '"');
        }
        $o .= tag('input type="submit" class="submit" value="'
                  . ucfirst($tx['action']['save']) . '"')
            . '</form>';
        return $o;
    }

    /**
     * Handles the form submission.
     *
     * If file could be successfully saved, triggers a redirect.
     * Otherwise writes error message to $e, and returns the edit form.
     *
     * @access public
     * @return mixed
     */
    function submit()
    {
	global $xh_hasher;

        foreach ($this->cfg as $cat => $opts) {
            foreach ($opts as $name => $opt) {
		$iname = XH_FORM_NAMESPACE . $cat . '_' . $name;
		$val = stsl($_POST[$iname]);
		if ($opt['type'] == 'bool') {
		    $val = isset($_POST[$iname]) ? 'true' : ''; // TODO: which values should be written back?
		} elseif ($opt['type'] == 'password'
		    && $_POST[$iname] != $_POST[$iname . '_OLD'])
		{
		    $val = $xh_hasher->HashPassword($val);
		}
                $this->cfg[$cat][$name]['val'] = $val;
            }
        }
	if ($this->save()) {
	    header('Location: ' . $this->redir, true, 303);
	    exit;
	} else {
	    e('cntsave', 'file', $this->filename);
	    return $this->form();
	}
    }
}


/**
 * The abstract base class for editing of core config and text files.
 *
 * @package	XH
 * @abstract
 */
class XH_CoreArrayFileEdit extends XH_ArrayFileEdit
{
    /**
     * @global string
     * @global string
     * @global array
     */
    function XH_CoreArrayFileEdit()
    {
	global $pth, $file, $tx;

	$this->filename = $pth['file'][$file];
	$this->caption = utf8_ucfirst($tx['action']['edit']) . ' '
	    . (isset($tx['filetype'][$file]) ? $tx['filetype'][$file] : $file);
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @access protected
     * @return string
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
 * @package	XH
 *
 */
class XH_CoreConfigFileEdit extends XH_CoreArrayFileEdit
{
    /**
     * @global array
     * @global array
     * @global array
     */
    function XH_CoreConfigFileEdit()
    {
        global $cf, $txc, $tx;

	parent::XH_CoreArrayFileEdit();
	$this->varName = 'cf';
	$this->params = array('form' => 'array', 'file' => 'config', 'action' => 'save');
	$this->redir = '?file=config&action=array';
        $this->cfg = array();
        foreach ($cf as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
		if (!isset($txc[$cat][$name])) {
		    $co = array('val' => $val, 'type' => 'string');
		    if (isset($tx['help']["${cat}_$name"])) {
			$co['hint'] = $tx['help']["${cat}_$name"];
		    }
		    if ($cat == 'language' && $name == 'default') {
			$co['type'] = 'enum';
			$co['vals'] = $this->selectOptions('language', '/^([a-z]{2})\.php$/i');
		    } elseif ($cat == 'site' && $name == 'template') {
			$co['type'] = 'enum';
			$co['vals'] = $this->selectOptions('templates', '/^([^\.]*)$/i');
		    } elseif ($cat == 'security' && $name == 'password') {
			$co['type'] = 'password';
		    }
		    $this->cfg[$cat][$name] = $co;
		}
            }
	    if (empty($this->cfg[$cat])) {
		unset($this->cfg[$cat]);
	    }
        }
    }
}


/**
 * Editing of core langconfig files.
 *
 * @package	XH
 */
class XH_CoreLangconfigFileEdit extends XH_CoreArrayFileEdit
{
    /**
     * @global array
     * @global array
     * @global array
     * @global string
     */
    function XH_CoreLangconfigFileEdit()
    {
        global $cf, $txc, $tx, $sl;

	parent::XH_CoreArrayFileEdit();
	$this->varName = 'txc';
	$this->params = array('form' => 'array', 'file' => 'langconfig', 'action' => 'save');
	$this->redir = '?file=langconfig&action=array';
        $this->cfg = array();
        foreach ($txc as $cat => $opts) {
	    if ($cat != 'subsite' || $sl != $cf['language']['default']) {
		$this->cfg[$cat] = array();
		foreach ($opts as $name => $val) {
		    $co = array('val' => $val, 'type' => 'text');
		    if (isset($tx['help']["${cat}_$name"])) {
			$co['hint'] = $tx['help']["${cat}_$name"];
		    }
		    if ($cat == 'subsite' && $name == 'template') {
			$co['type'] = 'enum';
			$co['vals'] = $this->selectOptions('templates', '/^([^\.]*)$/i');
			array_unshift($co['vals'], $tx['template']['default']);
		    } elseif ($cat == 'subsite' && $name == 'password') {
			$co['type'] = 'password';
		    }
		    $this->cfg[$cat][$name] = $co;
		}
            }
        }
    }
}


/**
 * Editing of core language files.
 *
 * @package	XH
 */
class XH_CoreLangFileEdit extends XH_CoreArrayFileEdit
{
    /**
     * @global array
     */
    function XH_CoreLangFileEdit()
    {
        global $tx;

	parent::XH_CoreArrayFileEdit();
	$this->varName = 'tx';
	$this->params = array('form' => 'array', 'file' => 'language', 'action' => 'save');
	$this->redir = '?file=language&action=array';
        $this->cfg = array();
        // TODO: sort?
        foreach ($tx as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
		// don't show or save the following
		if ($cat == 'meta' && $name =='codepage') {
		    continue;
		}
                $co = array('val' => $val, 'type' => 'text');
                $this->cfg[$cat][$name] = $co;
            }
        }
    }
}


/**
 * The abstract base class for plugin config file editing.
 *
 * @package	XH
 * @abstract
 */
class XH_PluginArrayFileEdit extends XH_ArrayFileEdit
{
    /**
     * The name of the config array variable.
     *
     * @access protected
     * @var    string
     */
    var $varName = null;

    /**
     * @global array
     * @global string
     */
    function XH_PluginArrayFileEdit()
    {
	global $pth, $plugin;

        $this->plugin = $plugin;
	$this->caption = $plugin;
    }

    /**
     * Returns the the file contents as string for saving.
     *
     * @access protected
     * @return string
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
 * @package	XH
 */
class XH_PluginConfigFileEdit extends XH_PluginArrayFileEdit
{
    /**
     * @global array
     * @global string
     * @global array
     * @global array
     */
    function XH_PluginConfigFileEdit()
    {
        global $pth, $plugin, $plugin_cf, $plugin_tx;

	parent::XH_PluginArrayFileEdit();
	$fn = $pth['folder']['plugins'] . $plugin . '/config/metaconfig.php';
	if (is_readable($fn)) {
	    include $fn;
	}
	$mcf = isset($plugin_mcf[$plugin]) ? $plugin_mcf[$plugin] : array();
	$this->filename = $pth['file']['plugin_config'];
	$this->params = array('admin' => 'plugin_config',
			      'action' => 'plugin_save');
	$this->redir = "?&$plugin&admin=plugin_config&action=plugin_edit";
	$this->varName = 'plugin_cf';
        $this->cfg = array();
        foreach ($plugin_cf[$plugin] as $key => $val) {
            list($cat, $name) = $this->splitKey($key);
	    $type = isset($mcf[$key]) ? $mcf[$key] : 'string';
	    if (strpos($type, 'enum:') === 0) {
		$vals = explode(',', substr($type, strlen('enum:')));
		$type = 'enum';
	    } else {
		$vals = null;
	    }
            $co = array('val' => $val, 'type' => $type,  'vals' => $vals);
            if (isset($plugin_tx[$plugin]["cf_$key"])) {
                $co['hint'] = $plugin_tx[$plugin]["cf_$key"];
            }
            $this->cfg[$cat][$name] = $co;
        }
    }
}


/**
 * Editing of plugin language files.
 *
 * @package	XH
 */
class XH_PluginLanguageFileEdit extends XH_PluginArrayFileEdit
{
    /**
     * @global array
     * @global string
     * @global array
     */
    function XH_PluginLanguageFileEdit()
    {
        global $pth, $plugin, $plugin_tx;

	parent::XH_PluginArrayFileEdit();
	$this->filename = $pth['file']['plugin_language'];
	$this->params = array('admin' => 'plugin_language',
			      'action' => 'plugin_save');
	$this->redir = "?&$plugin&admin=plugin_language&action=plugin_edit";
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
