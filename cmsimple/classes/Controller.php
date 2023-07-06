<?php

namespace XH;

/**
 * Top-level functionality.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6.3
 */
class Controller
{
    /**
     * Initializes the paths related to the template.
     *
     * @return void
     */
    public function initTemplatePaths()
    {
        global $pth, $cf, $tx;

        $pth['folder']['templates'] = $pth['folder']['base'] . 'templates/';
        $template = $tx['subsite']['template'] == ''
            ? $cf['site']['template']
            : $tx['subsite']['template'];
        $pth['folder']['template'] = $pth['folder']['templates'] . $template . '/';
        $pth['file']['template'] = $pth['folder']['template'] . 'template.htm';
        $pth['file']['stylesheet'] = $pth['folder']['template'] . 'stylesheet.css';
        $pth['folder']['menubuttons'] = $pth['folder']['template'] . 'menu/';
        $pth['folder']['templateimages'] = $pth['folder']['template'] . 'images/';
        $pth['folder']['templateflags'] = $pth['folder']['template'] . 'flags/';
    }

    /**
     * Handles search requests.
     *
     * @return void
     */
    public function handleSearch()
    {
        global $pth, $tx, $title, $o;

        if (file_exists($pth['file']['search'])) {
            // For compatibility with modified search functions and search plugins.
            include $pth['file']['search'];
        } else {
            $title = $tx['title']['search'];
            $search = $this->makeSearch();
            $o .= $search->render();
        }
    }

    /**
     * Makes and returns a search object.
     *
     * @return Search
     */
    public function makeSearch()
    {
        global $search;

        return new Search($search);
    }

    /**
     * Handles mailform requests.
     *
     * @return void
     */
    public function handleMailform()
    {
        global $cf, $tx, $title, $o;

        if ($cf['mailform']['email'] != '') {
            $title = $tx['title']['mailform'];
            $o .= "\n" . '<div id="xh_mailform">' . "\n";
            $o .= '<h1>' . $title . '</h1>' . "\n";
            $mailform = $this->makeMailform();
            $o .= $mailform->process();
            $o .= '</div>' . "\n";
        } else {
            shead(404);
        }
    }

    /**
     * Makes and returns a mailform object.
     *
     * @return Mailform
     */
    public function makeMailform()
    {
        return new Mailform();
    }

    /**
     * Handles sitemap requests.
     *
     * @return void
     *
     * @todo Declare visibility.
     */
    public function handleSitemap()
    {
        global $cl, $cf, $tx, $title, $o;

        $title = $tx['title']['sitemap'];
        $pages = array();
        $o .= '<h1>' . $title . '</h1>' . "\n";
        for ($i = 0; $i < $cl; $i++) {
            if (!hide($i) || $cf['show_hidden']['pages_sitemap'] == 'true') {
                $pages[] = $i;
            }
        }
        $o .= li($pages, 'sitemaplevel');
    }

    /**
     * Handles password forgotten requests.
     *
     * @return void
     */
    public function handlePasswordForgotten()
    {
        $passwordForgotten = $this->makePasswordForgotten();
        $passwordForgotten->dispatch();
    }

    /**
     * Makes and returns a password forgotten object.
     *
     * @return PasswordForgotten
     */
    public function makePasswordForgotten()
    {
        return new PasswordForgotten();
    }

    /**
     * Handles login and logout.
     *
     * @return void
     */
    public function handleLoginAndLogout()
    {
        global $adm, $login, $logout, $keycut, $f;

        $adm = gc('status') == 'adm' && logincheck();
        $keycut = $keycut;
        if ($login && $keycut == '' && !$adm) {
            $login = null;
            $f = 'login';
        }

        if ($login && !$adm) {
            $this->handleLogin();
        } elseif ($logout && $adm) {
            $this->handleLogout();
        }
    }

    /**
     * Handles login requests.
     *
     * @return void
     */
    public function handleLogin()
    {
        global $f, $pth, $keycut, $login, $adm, $edit, $cf;

        if (password_verify($keycut, $cf['security']['password'])) {
            setcookie('status', 'adm', 0, CMSIMPLE_ROOT);
            XH_startSession();
            session_regenerate_id(true);
            $_SESSION['xh_password'] = $cf['security']['password'];
            $_SESSION['xh_user_agent'] = md5($_SERVER['HTTP_USER_AGENT']);
            $adm = true;
            $edit = true;
            $written = XH_logMessage('info', 'XH', 'login', 'login from ' . $_SERVER['REMOTE_ADDR']);
            if (!$written) {
                e('cntwriteto', 'log', $pth['file']['log']);
            }
        } else {
            $login = null;
            $f = 'xh_login_failed';
            XH_logMessage('warning', 'XH', 'login', 'login failed from ' . $_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * Handles logout requests.
     *
     * @return void
     */
    public function handleLogout()
    {
        global $adm, $f, $logout, $tx, $o;

        if ($logout != 'no_backup') {
            $o .= XH_backup();
        }
        $adm = false;
        setcookie('status', '', 0, CMSIMPLE_ROOT);
        XH_startSession();
        session_regenerate_id(true);
        unset($_SESSION['xh_password']);
        $o .= XH_message('success', $tx['login']['loggedout']);
        $f = 'xh_loggedout';
    }

    /**
     * Handles Ajax request to keep the admin session alive.
     *
     * @return void
     */
    public function handleKeepAlive()
    {
        XH_startSession();
        header('Content-Type: text/plain');
        XH_exit();
    }

    /**
     * Sets frontend $f.
     *
     * @return void
     */
    public function setFrontendF()
    {
        global $function, $su, $mailform, $sitemap, $xhpages, $f;

        if ($xhpages) {
            $f = 'xhpages';
        } elseif (($su == '' || $su == 'sitemap') && $sitemap) {
            $f = 'sitemap';
        } elseif (($su == '' || $su == 'mailform')
            && ($mailform || $function == 'mailform')
        ) {
            $f = 'mailform';
        } elseif ($function == 'search') {
            $f = 'search';
        } elseif ($function == 'forgotten') {
            $f = 'forgotten';
        }
    }

    /**
     * Sets backend $f.
     *
     * @return void
     *
     * @todo Handling of userfiles, images and downloads is probably not
     *       necessary, as this should already be handled by the filebrowser.
     *       Otherwise media had to be handled also.
     */
    public function setBackendF()
    {
        global $function, $validate, $xh_do_validate, $settings, $xh_backups,
            $xh_pagedata, $sysinfo, $phpinfo, $file, $userfiles, $images,
            $downloads, $f, $xh_change_password, $xh_plugins;

        if ($function == 'save') {
            $f = 'save';
        } elseif ($downloads || $function == 'downloads') {
            $f = 'downloads';
        } elseif ($images || $function == 'images') {
            $f = 'images';
        } elseif ($userfiles) {
            $f = 'userfiles';
        } elseif ($file) {
            $f = 'file';
        } elseif ($phpinfo) {
            $f = 'phpinfo';
        } elseif ($sysinfo) {
            $f = 'sysinfo';
        } elseif ($xh_pagedata) {
            $f = 'xh_pagedata';
        } elseif ($xh_backups) {
            $f = 'xh_backups';
        } elseif ($settings) {
            $f = 'settings';
        } elseif ($xh_do_validate) {
            $f = 'do_validate';
        } elseif ($validate) {
            $f = 'validate';
        } elseif ($xh_change_password) {
            $f = 'change_password';
        } elseif ($xh_plugins) {
            $f = 'xh_plugins';
        }
    }

    /**
     * Returns whether page data have to be saved.
     *
     * @return bool
     */
    public function wantsSavePageData()
    {
        global $s;

        return $s > -1 && isset($_POST['save_page_data']);
    }

    /**
     * Handles save page data requests.
     *
     * @return void
     */
    public function handleSavePageData()
    {
        global $pth, $s, $pd_router, $tx, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $postData = $_POST;
        unset($postData['save_page_data'], $postData['xh_csrf_token']);
        $successful = $pd_router->update($s, $postData);
        if (isset($_GET['xh_pagedata_ajax'])) {
            if ($successful) {
                echo XH_message('info', $tx['message']['pd_success']);
            } else {
                header('HTTP/1.0 500 Internal Server Error');
                echo XH_message('fail', $tx['message']['pd_fail']);
            }
            XH_exit();
        } else {
            if (!$successful) {
                e('cntsave', 'content', $pth['file']['content']);
            }
        }
    }

    /**
     * Handles page data editor requests.
     *
     * @return void
     *
     * @todo Unused?
     */
    public function handlePagedataEditor()
    {
        global $o;

        $pageDataEditor = $this->makePageDataEditor();
        $o .= $pageDataEditor->process();
    }

    /**
     * Makes and returns a new page data editor object.
     *
     * @return PageDataEditor
     */
    public function makePageDataEditor()
    {
        return new PageDataEditor();
    }

    /**
     * Handles file view requests.
     *
     * @return void
     */
    public function handleFileView()
    {
        global $pth, $file, $o;

        if ($file === 'log') {
            $o .= XH_logFileView();
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            echo rmnl(file_get_contents($pth['file'][$file]));
            XH_exit();
        }
    }

    /**
     * Handles file backup requests.
     *
     * @return void
     */
    public function handleFileBackup()
    {
        global $file, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        if ($file == 'content') {
            $suffix = $_POST['xh_suffix'];
            if (preg_match('/^[a-z_0-9-]{1,20}$/i', $suffix)) {
                XH_extraBackup($suffix);
            }
        }
    }

    /**
     * Handles file edit requests.
     *
     * @return void
     */
    public function handleFileEdit()
    {
        global $file, $action, $o;

        $map = array(
            'config' => 'XH\CoreConfigFileEdit',
            'language' => 'XH\CoreLangFileEdit',
            'content' => 'XH\CoreTextFileEdit',
            'template' => 'XH\CoreTextFileEdit',
            'stylesheet' => 'XH\CoreTextFileEdit'
        );
        $fileEditor = isset($map[$file])
            ? $this->makeFileEditor($map[$file])
            : null;
        if ($action == 'save') {
            $o .= $fileEditor->submit();
        } else {
            $o .= $fileEditor->form();
        }
    }

    /**
     * Makes and returns a file edit object.
     *
     * @param string $class A class name.
     *
     * @return FileEdit
     */
    protected function makeFileEditor($class)
    {
        return new $class;
    }

    /**
     * Outputs administration script elements.
     *
     * @return void
     */
    public function outputAdminScripts()
    {
        global $tx, $o;

        $interval = 1000 * ((int) ini_get('session.gc_maxlifetime') - 1);
        $o .= <<<EOT
<script>
if (document.cookie.indexOf('status=adm') == -1) {
    document.write('<div class="xh_warning">{$tx['error']['nocookies']}<\/div>');
}
</script>
<noscript><div class="xh_warning">{$tx['error']['nojs']}</div></noscript>
<script>
setInterval(function() {
    var request = new XMLHttpRequest();

    request.open("GET", "?xh_keep_alive");
    request.send(null);
}, $interval);
</script>
EOT;
    }

    /**
     * Sets functions as permitted.
     *
     * @return void
     *
     * @todo Rename!
     */
    public function setFunctionsAsPermitted()
    {
        global $edit, $normal;

        if (XH_ADM) {
            if ($edit) {
                setcookie('mode', 'edit', 0, CMSIMPLE_ROOT);
            }
            if ($normal) {
                setcookie('mode', '', 0, CMSIMPLE_ROOT);
            }
            if (gc('mode') == 'edit' && !$normal) {
                $edit = true;
            }
        } else {
            if (gc('status') != '') {
                setcookie('status', '', 0, CMSIMPLE_ROOT);
            }
            if (gc('mode') == 'edit') {
                setcookie('mode', '', 0, CMSIMPLE_ROOT);
            }
        }
    }

    /**
     * Handles save requests.
     *
     * @return void
     */
    public function handleSaveRequest()
    {
        global $text, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        XH_saveEditorContents($text);
    }

    /**
     * Whether edit mode is requested and the edit contents shall be displayed.
     *
     * @return bool
     *
     * @todo Do we need $f == 'save' && !$download?
     *       IOW: isn't the script already exited in these cases?
     */
    public function wantsEditContents()
    {
        global $edit, $f, $download;

        return $edit && (!$f || $f == 'save') && !$download;
    }

    /**
     * Outputs the edit contents (either editor or cntlocateheading).
     *
     * @return void
     */
    public function outputEditContents()
    {
        global $s, $tx, $o;

        if ($s > -1) {
            $o .= XH_contentEditor();
        } else {
            $o .= XH_message('info', $tx['error']['cntlocateheading']) . "\n";
        }
    }

    /**
     * Returns whether the filebrowser is missing.
     *
     * @return bool
     */
    public function isFilebrowserMissing()
    {
        return $this->needsFilebrowser()
            && $this->isExternalMissing('filebrowser');
    }

    /**
     * Returns whether the page manager is missing.
     *
     * @return bool
     */
    public function isPagemanagerMissing()
    {
        global $f;

        return $f == 'xhpages'
            && $this->isExternalMissing('pagemanager');
    }

    /**
     * Returns whether the filebrowser is needed.
     *
     * @return bool
     *
     * @todo Do we need $f == 'save' && !$download?
     *       IOW: isn't the script already exited in these cases?
     */
    private function needsFilebrowser()
    {
        global $images, $downloads, $userfiles, $media, $edit, $f, $download;

        return $images || $downloads || $userfiles || $media
            || $edit && (!$f || $f == 'save') && !$download;
    }

    /**
     * Returns whether an external plugin is missing.
     *
     * @param string $name A plugin name ("filebrowser" or "pagemanager").
     *
     * @return bool
     */
    private function isExternalMissing($name)
    {
        global $pth, $cf;

        return $cf[$name]['external']
            && !file_exists($pth['folder']['plugins'] . $cf[$name]['external']);
    }

    /**
     * Reports a missing external plugin.
     *
     * @param string $name A plugin name ("filebrowser" or "pagemanger").
     *
     * @return void
     */
    public function reportMissingExternal($name)
    {
        global $cf, $tx, $e;

        $e .= '<li>' . sprintf($tx['error']['no' . $name], $cf[$name]['external'])
            . '</li>' . "\n";
    }

    /**
     * Verifies that $adm has not be manipulated.
     *
     * Otherwise we present the login form. Redirecting would be cleaner, but
     * may result in an infinite loop, so we do it this way.
     *
     * @return void
     */
    public function verifyAdm()
    {
        global $adm, $edit, $tx, $s, $o, $f, $title;

        if (!XH_ADM && $adm) {
            $s = -1;
            $adm = $edit = false;
            $o = '';
            $f = 'login';
            $title = utf8_ucfirst($tx['menu']['login']);
            loginforms();
        }
    }

    /**
     * Renders the error messages stored in $e.
     *
     * @return string HTML
     */
    public function renderErrorMessages()
    {
        global $e;

        if ($e) {
            return '<div class="xh_warning">' . "\n"
                . '<ul>' . "\n" . $e . '</ul>' . "\n" . '</div>' . "\n";
        } else {
            return '';
        }
    }

    /**
     * Sends the standard HTTP headers to the client.
     *
     * If that's not possible, a respective error message is send and the script
     * is aborted.
     *
     * @return void
     *
     * @todo Emit error message only in admin mode?
     */
    public function sendStandardHeaders()
    {
        global $sl, $cf, $tx;

        $file = $line = null; // for unit test mocking of headers_sent()
        if (!headers_sent($file, $line)) {
            header('Content-Type: text/html; charset=UTF-8');
            header("Content-Language: $sl");
            if ($cf['security']['frame_options'] != '') {
                header('X-Frame-Options: ' . $cf['security']['frame_options']);
            }
        } else {
            $location = $file . ':' . $line;
            XH_exit(str_replace('{location}', $location, $tx['error']['headers']));
        }
    }
}
