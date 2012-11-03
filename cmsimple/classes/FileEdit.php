<?php

class XH_FileEdit
{
    var $params = array();
    var $plugin = null;
    var $caption = null;
    var $filename = null;
    var $redir = null;
    
    
    function save()
    {
        $ok = is_writable($this->filename)
	    && ($fh = fopen($this->filename, 'w'))
	    && fwrite($fh, $this->asString()) !== false;
        !empty($fh) and fclose($fh);
	return $ok;
    }
}


class XH_TextFileEdit extends XH_FileEdit
{
    var $textareaName = null;
    var $text = null;
    
    
    function XH_TextFileEdit()
    {
	$this->text = file_get_contents($this->filename);
    }
    
    
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
    
    
    function asString()
    {
	return $this->text;
    }
    
}


class XH_CoreTextFileEdit extends XH_TextFileEdit
{
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



class XH_ArrayFileEdit extends XH_FileEdit
{
    var $cfg = null;
    //var $file = null;
    //var $admin = null;
    //var $action = null;
    //var $plugin = null;
    
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
    
    
    function splitKey($key)
    {
        $parts = explode('_', $key);
        $first = array_shift($parts);
        return array($first, implode('_', $parts));
    }
    
    
    function formField($cat, $name, $opt)
    {
        $iname = XH_FORM_NAMESPACE . $cat . '_' . $name;
        switch ($opt['type']) {
        case 'string':
            return tag('input type="text" name="' . $iname . '" value="'
                       . htmlspecialchars($opt['val'], ENT_QUOTES, 'UTF-8')
                       . '" class="cmsimplecore_settings"');
        case 'password':
            return tag('input type="text" name="' . $iname . '" value="'
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
        }
    }
    
    
    function form()
    {
        global $tx, $pth;
    
        $action = isset($this->plugin) ? '?&amp;' . $this->plugin : '.';
	$o = '<h1>' . ucfirst($this->caption) . '</h1>'
	    . '<form action="' . $action . '" method="POST" accept-charset="UTF-8">'
            . '<table style="width: 100%">';
        foreach ($this->cfg as $category => $options) {
            $o .= '<tr><td colspan="2"><h4>' . ucfirst($category) . '</h4></td></tr>';
            foreach ($options as $name => $opt) {
                $info = isset($opt['hint'])
                    ? '<a href="#" class="pl_tooltip" onclick="return false">'
                        . tag('img src="' . $pth['folder']['flags'] . 'help_icon.png" alt=""')
                        . '<span>' . $opt['hint'] . '</span></a> '
                    : '';
                $o .= '<tr><td>' . $info . ucfirst($name) . '</td><td>'
                    . $this->formField($category, $name, $opt);
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
    
    
    function submit()
    {
	global $xh_hasher;
	
        foreach ($this->cfg as $cat => $opts) {
            foreach ($opts as $name => $opt) {
		$iname = XH_FORM_NAMESPACE . $cat . '_' . $name;
		$val = stsl($_POST[$iname]);
		if ($opt['type'] == 'password'
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

class XH_CoreArrayFileEdit extends XH_ArrayFileEdit
{
    function XH_CoreArrayFileEdit()
    {
	global $pth, $file, $tx;
	
	$this->filename = $pth['file'][$file];
	$this->caption = utf8_ucfirst($tx['action']['edit']) . ' '
	    . (isset($tx['filetype'][$file]) ? $tx['filetype'][$file] : $file);
    }


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


class XH_CoreConfigFileEdit extends XH_CoreArrayFileEdit
{
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


class XH_CoreLangconfigFileEdit extends XH_CoreArrayFileEdit
{
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


class XH_CoreLangFileEdit extends XH_CoreArrayFileEdit
{
    function XH_CoreLangFileEdit()
    {
        global $tx;
    
	parent::XH_CoreArrayFileEdit();
	$this->varName = 'tx';
	$this->params = array('form' => 'array', 'file' => 'language', 'action' => 'save');
	$this->redir = '?file=langconfig&action=array';
        $this->cfg = array();
        // TODO: sort?
        foreach ($tx as $cat => $opts) {
            $this->cfg[$cat] = array();
            foreach ($opts as $name => $val) {
                $co = array('val' => $val, 'type' => 'text');
                $this->cfg[$cat][$name] = $co;
            }
        }
    }
}


class XH_PluginArrayFileEdit extends XH_ArrayFileEdit
{
    var $varName = null;
    
    function XH_PluginArrayFileEdit()
    {
	global $pth, $plugin;
	
        $this->plugin = $plugin;
	$this->caption = $plugin;
    }


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

class XH_PluginConfigFileEdit extends XH_PluginArrayFileEdit
{
    function XH_PluginConfigFileEdit()
    {
        global $pth, $plugin, $plugin_cf, $plugin_tx;
        
	parent::XH_PluginArrayFileEdit();
	$this->filename = $pth['file']['plugin_config'];
	$this->params = array('admin' => 'plugin_config',
			      'action' => 'plugin_save');
	$this->redir = "?&$plugin&admin=plugin_config&action=plugin_edit";
	$this->varName = 'plugin_cf';
        $this->cfg = array();
        foreach ($plugin_cf[$plugin] as $key => $val) {
            list($cat, $name) = $this->splitKey($key);
            $co = array('val' => $val, 'type' => 'string');
            if (isset($plugin_tx[$plugin]["cf_$key"])) {
                $co['hint'] = $plugin_tx[$plugin]["cf_$key"];
            }
            $this->cfg[$cat][$name] = $co;
        }
    }
}

class XH_PluginLanguageFileEdit extends XH_PluginArrayFileEdit
{
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
