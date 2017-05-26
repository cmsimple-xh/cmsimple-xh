<?php

/**
 * Top-level functionality.
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
 * Top-level functionality.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   Peter Harteg <peter@harteg.dk>
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6.3
 */
class Controller
{
    /**
     * Initializes the paths related to the template.
     *
     * @return void
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
     * @global array The localization of the core.
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
     *
     * @global array  The paths of system files and folders.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The HTML of the contents area.
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
     *
     * @global string The search string.
     */
    public function makeSearch()
    {
        global $search;

        return new Search(stsl($search));
    }

    /**
     * Handles mailform requests.
     *
     * @return void
     *
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The HTML of the contents area.
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
     * @global int    The number of pages.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The HTML of the content area.
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
     *
     * @global string Whether admin mode is active.
     * @global string Whether login is requested.
     * @global string Whether logout is requested.
     * @global string The admin password.
     * @global string The requested function.
     */
    public function handleLoginAndLogout()
    {
        global $adm, $login, $logout, $keycut, $f;

        $adm = gc('status') == 'adm' && logincheck();
        $keycut = stsl($keycut);
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
     *
     * @global string       The requested function.
     * @global array        The paths of system files and folders.
     * @global string       The admin password.
     * @global string       Whether login is requested.
     * @global array        The configuration of the core.
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
     *
     * @global string Whether admin mode is active.
     * @global string The requested function.
     * @global string Whether logout is requested.
     * @global array  The localization of the core.
     * @global string The HTML for the contents area.
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
     *
     * @global string The requested function.
     * @global string The URL of the current page.
     * @global string Whether the mailform is requested.
     * @global string Whether the sitemap is requested.
     * @global string Whether the page manager is requested.
     * @global string The requested function.
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
     * @global string The requested function.
     * @global string Whether the link check is requested.
     * @global string Whether the actual link check is requested.
     * @global string Whether the settings page is requested.
     * @global string Whether the backup page is requested.
     * @global string Whether the pagedata editor is requested.
     * @global string Whether the system info is requested.
     * @global string Whether the PHP info is requested.
     * @global string The name of a special file to be handled.
     * @global string Whether the file browser is requested to show the
     *                userfiles folder.
     * @global string Whether the file browser is requested to show the image
     *                folder.
     * @global string Whether the file browser is requested to show the download
     *                folder.
     * @global string The requested function.
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
     *
     * @global int The number of the current page.
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
     *
     * @global array          The paths of system files and folders.
     * @global int            The index of the currently selected page.
     * @global PageDataRouter The page data router.
     * @global array          The localization of the core.
     * @global CSRFProtection The CSRF protector.
     */
    public function handleSavePageData()
    {
        global $pth, $s, $pd_router, $tx, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        $postData = $_POST;
        unset($postData['save_page_data'], $postData['xh_csrf_token']);
        $postData = array_map('stsl', $postData);
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
     * @global string The HTML for the contents area.
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
     *
     * @global array  The paths of system files and folders.
     * @global string The name of a special file to be handled.
     * @global string The HTML for the contents area.
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
     *
     * @global string         The name of a special file to be handled.
     * @global CSRFProtection The CRSF protector.
     */
    public function handleFileBackup()
    {
        global $file, $_XH_csrfProtection;

        $_XH_csrfProtection->check();
        if ($file == 'content') {
            $suffix = stsl($_POST['xh_suffix']);
            if (preg_match('/^[a-z_0-9-]{1,20}$/i', $suffix)) {
                XH_extraBackup($suffix);
            }
        }
    }

    /**
     * Handles file edit requests.
     *
     * @return void
     *
     * @global string The name of a special file to be handled.
     * @global string The requested action.
     * @global string The HTML for the contents area.
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
     *
     * @global array  The localization of the core.
     * @global string The HTML for the contents area.
     */
    public function outputAdminScripts()
    {
        global $tx, $o;

        $interval = 1000 * (ini_get('session.gc_maxlifetime') - 1);
        $o .= <<<EOT
<script type="text/javascript">
if (document.cookie.indexOf('status=adm') == -1) {
    document.write('<div class="xh_warning">{$tx['error']['nocookies']}<\/div>');
}
</script>
<noscript><div class="xh_warning">{$tx['error']['nojs']}</div></noscript>
<script type="text/javascript">
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
     * @global string Whether edit mode is requested.
     * @global string Whether normal mode is requested.
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
     *
     * @global string         The text of the editor on save.
     * @global CSRFProtection The CSRF protector.
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
     * @global string Whether edit mode is requested.
     * @global string The requested function.
     * @global string The filename requested for download.
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
     *
     * @global int    The index of the currently selected page.
     * @global array  The localization of the core.
     * @global string The HTML for the contents area.
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
     *
     * @global string The requested function.
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
     * @global string Whether the file browser is requested to show the image folder.
     * @global string Whether the file browser is requested to show the download
     *                folder.
     * @global string Whether the file browser is requested to show the userfiles
     *                folder.
     * @global string Whether the file browser is requested to show the media folder.
     * @global string Whether edit mode is requested.
     * @global string The requested function.
     * @global string The filename requested for download.
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
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
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
     * @return bool
     *
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The HTML for the <li>s holding error messages.
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
     *
     * @global bool   Whether we're logged in as administrator.
     * @global bool   Whether we're in edit mode.
     * @global array  The localization of the core.
     * @global int    The current page.
     * @global string The HTML fragment for insertion in the contents area.
     * @global string The current special function.
     * @global string The title of the page.
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
     *
     * @global string The HTML for the <li>s holding error messages.
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
     * @global string The ISO 659-1 code of the current language.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
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
