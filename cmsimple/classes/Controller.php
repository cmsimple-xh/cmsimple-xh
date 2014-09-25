<?php

/**
 * Top-level functionality.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

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
class XH_Controller
{
    /**
     * Handles search requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array  The paths of system files and folders.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The (X)HTML of the contents area.
     */
    function handleSearch()
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
     * @return XH_Search
     *
     * @access protected
     *
     * @global array  The paths of system files and folders.
     * @global string The search string.
     */
    function makeSearch()
    {
        global $pth, $search;

        include_once $pth['folder']['classes'] . 'Search.php';
        return new XH_Search(stsl($search));
    }

    /**
     * Handles mailform requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The (X)HTML of the contents area.
     */
    function handleMailform()
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
     * @return XH_Mailform
     *
     * @access protected
     *
     * @global array The paths of system files and folders.
     */
    function makeMailform()
    {
        global $pth;

        include_once $pth['folder']['classes'] . 'Mailform.php';
        return new XH_Mailform();
    }

    /**
     * Handles sitemap requests.
     *
     * @return void
     *
     * @access public
     *
     * @global int    The number of pages.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The content of the title element.
     * @global string The (X)HTML of the content area.
     */
    function handleSitemap()
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
     *
     * @access public
     */
    function handlePasswordForgotten()
    {
        $passwordForgotten = $this->makePasswordForgotten();
        $passwordForgotten->dispatch();
    }

    /**
     * Makes and returns a password forgotten object.
     *
     * @return XH_PasswordForgotten
     *
     * @access protected
     *
     * @global array The paths of system files and folders.
     */
    function makePasswordForgotten()
    {
        global $pth;

        include_once $pth['folder']['classes'] . 'PasswordForgotten.php';
        return new XH_PasswordForgotten();
    }

    /**
     * Handles login and logout.
     *
     * @return void
     *
     * @access public
     *
     * @global string Whether admin mode is active.
     * @global string Whether login is requested.
     * @global string Whether logout is requested.
     * @global string The admin password.
     * @global string The requested function.
     */
    function handleLoginAndLogout()
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
     * @access public
     *
     * @global string       The requested function.
     * @global array        The paths of system files and folders.
     * @global string       The admin password.
     * @global string       Whether login is requested.
     * @global PasswordHash The password hasher.
     * @global array        The configuration of the core.
     *
     * @todo Make protected.
     */
    function handleLogin()
    {
        global $f, $pth, $keycut, $login, $adm, $edit, $xh_hasher, $cf;

        if ($xh_hasher->CheckPassword($keycut, $cf['security']['password'])) {
            setcookie('status', 'adm', 0, CMSIMPLE_ROOT);
            if (session_id() == '') {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['xh_password'][CMSIMPLE_ROOT] = $cf['security']['password'];
            $_SESSION['xh_user_agent'] = md5($_SERVER['HTTP_USER_AGENT']);
            $adm = true;
            $edit = true;
            $written = XH_logMessage(
                'info', 'XH', 'login', 'login from ' . $_SERVER['REMOTE_ADDR']
            );
            if (!$written) {
                e('cntwriteto', 'log', $pth['file']['log']);
            }
        } else {
            $login = null;
            $f = 'xh_login_failed';
            XH_logMessage(
                'warning', 'XH', 'login',
                'login failed from ' . $_SERVER['REMOTE_ADDR']
            );
        }
    }

    /**
     * Handles logout requests.
     *
     * @return void
     *
     * @access protected
     *
     * @global string Whether admin mode is active.
     * @global string The requested function.
     * @global string Whether logout is requested.
     * @global array  The localization of the core.
     * @global string The (X)HTML for the contents area.
     *
     * @todo Make protected.
     */
    function handleLogout()
    {
        global $adm, $f, $logout, $tx, $o;

        if ($logout != 'no_backup') {
            $o .= XH_backup();
        }
        $adm = false;
        setcookie('status', '', 0, CMSIMPLE_ROOT);
        if (session_id() == '') {
            session_start();
        }
        session_regenerate_id(true);
        unset($_SESSION['xh_password'][CMSIMPLE_ROOT]);
        $o .= XH_message('success', $tx['login']['loggedout']);
        $f = 'xh_loggedout';
    }

    /**
     * Handles Ajax request to keep the admin session alive.
     *
     * @return void
     *
     * @access public
     */
    function handleKeepAlive()
    {
        if (session_id() != '') {
            session_start();
        }
        header('Content-Type: text/plain');
        XH_exit();
    }

    /**
     * Handles password check Ajax requests.
     *
     * @return void
     *
     * @access protected
     *
     * @global PasswordHash The password hasher.
     * @global array        The configuration of the core.
     */
    function handlePasswordCheck()
    {
        global $xh_hasher, $cf;

        header('Content-Type: text/plain');
        echo intval(
            $xh_hasher->CheckPassword(
                stsl($_GET['xh_check']),
                $cf['security']['password']
            )
        );
        XH_exit();
    }

    /**
     * Sets frontend $f.
     *
     * @return void
     *
     * @access public
     *
     * @global string The requested function.
     * @global string The URL of the current page.
     * @global string Whether the mailform is requested.
     * @global string Whether the sitemap is requested.
     * @global string Whether the page manager is requested.
     * @global string The requested function.
     */
    function setFrontendF()
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
     * @access public
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
    function setBackendF()
    {
        global $function, $validate, $xh_do_validate, $settings, $xh_backups,
            $xh_pagedata, $sysinfo, $phpinfo, $file, $userfiles, $images,
            $downloads, $f;

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
        } elseif (isset($phpinfo)) {
            $f = 'phpinfo';
        } elseif (isset($sysinfo)) {
            $f = 'sysinfo';
        } elseif (isset($xh_pagedata)) {
            $f = 'xh_pagedata';
        } elseif (isset($xh_backups)) {
            $f = 'xh_backups';
        } elseif ($settings) {
            $f = 'settings';
        } elseif (isset($xh_do_validate)) {
            $f = 'do_validate';
        } elseif ($validate) {
            $f = 'validate';
        }
    }

    /**
     * Returns whether saving from menumanager is requested.
     *
     * @return bool
     *
     * @access public
     *
     * @global string Whether the menumanager is requested.
     * @global string The requested action.
     */
    function isSavingMenumanager()
    {
        global $menumanager, $action, $text;

        return isset($menumanager) && $menumanager == 'true'
            && $action == 'saverearranged' && !empty($text);
    }

    /**
     * Handles menumanager requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array             The paths of system files and folders.
     * @global string            The menumanager page information.
     * @global XH_PageDataRouter The page data router.
     */
    function handleMenumanager()
    {
        global $pth, $text, $pd_router;

        if (!$pd_router->refresh_from_menu_manager($text)) {
            e('notwritable', 'content', $pth['file']['content']);
        }
    }

    /**
     * Returns whether page data have to be saved.
     *
     * @return bool
     *
     * @access public
     *
     * @global int The number of the current page.
     */
    function wantsSavePageData()
    {
        global $s;

        return $s > -1 && isset($_POST['save_page_data']);
    }

    /**
     * Handles save page data requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array             The paths of system files and folders.
     * @global int               The index of the currently selected page.
     * @global XH_PageDataRouter The page data router.
     * @global array             The localization of the core.
     * @global XH_CSRFProtection The CSRF protector.
     */
    function handleSavePageData()
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
     * @access public
     *
     * @global string The (X)HTML for the contents area.
     */
    function handlePagedataEditor()
    {
        global $o;

        $pageDataEditor = $this->makePageDataEditor();
        $o .= $pageDataEditor->process();
    }

    /**
     * Makes and returns a new page data editor object.
     *
     * @return XH_PageDataEditor
     *
     * @access protected
     *
     * @global array The paths of system files and folders.
     */
    function makePageDataEditor()
    {
        global $pth;

        include_once $pth['folder']['classes'] . 'PageDataEditor.php';
        return new XH_PageDataEditor();
    }

    /**
     * Handles file view requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array  The paths of system files and folders.
     * @global string The name of a special file to be handled.
     * @global string The (X)HTML for the contents area.
     */
    function handleFileView()
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
     * @access public
     *
     * @global string            The name of a special file to be handled.
     * @global XH_CSRFProtection The CRSF protector.
     */
    function handleFileBackup()
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
     * @access public
     *
     * @global string The name of a special file to be handled.
     * @global string The requested action.
     * @global string The (X)HTML for the contents area.
     */
    function handleFileEdit()
    {
        global $file, $action, $o;

        $map = array(
            'config' => 'XH_CoreConfigFileEdit',
            'language' => 'XH_CoreLangFileEdit',
            'content' => 'XH_CoreTextFileEdit',
            'template' => 'XH_CoreTextFileEdit',
            'stylesheet' => 'XH_CoreTextFileEdit'
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
     * @return XH_FileEdit
     *
     * @access protected
     *
     * @global array The paths of system files and folders.
     */
    function makeFileEditor($class)
    {
        global $pth;

        include_once $pth['folder']['classes'] . 'FileEdit.php';
        return new $class;
    }

    /**
     * Outputs administration script elements.
     *
     * @return void
     *
     * @access public
     *
     * @global array  The localization of the core.
     * @global string The (X)HTML for the contents area.
     */
    function outputAdminScripts()
    {
        global $tx, $o;

        $interval = 1000 * (ini_get('session.gc_maxlifetime') - 1);
        $o .= <<<EOT
<script type="text/javascript">/* <![CDATA[ */
if (document.cookie.indexOf('status=adm') == -1) {
    document.write('<div class="xh_warning">{$tx['error']['nocookies']}<\/div>');
}
/* ]]> */</script>
<noscript><div class="xh_warning">{$tx['error']['nojs']}</div></noscript>
<script type="text/javascript">/* <![CDATA[ */
setInterval(function() {
    var request = new XMLHttpRequest();

    request.open("GET", "?xh_keep_alive");
    request.send(null);
}, $interval);
/* ]]> */</script>
EOT;
    }

    /**
     * Sets functions as permitted.
     *
     * @return void
     *
     * @access public
     *
     * @global string Whether edit mode is requested.
     * @global string Whether normal mode is requested.
     *
     * @todo Rename!
     */
    function setFunctionsAsPermitted()
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
     * @access public
     *
     * @global string            The text of the editor on save.
     * @global XH_CSRFProtection The CSRF protector.
     */
    function handleSaveRequest()
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
     * @access public
     *
     * @global string Whether edit mode is requested.
     * @global string The requested function.
     * @global string The filename requested for download.
     *
     * @todo Do we need $f == 'save' && !$download?
     *       IOW: isn't the script already exited in these cases?
     */
    function wantsEditContents()
    {
        global $edit, $f, $download;

        return $edit && (!$f || $f == 'save') && !$download;
    }

    /**
     * Outputs the edit contents (either editor or cntlocateheading).
     *
     * @return void
     *
     * @access public
     *
     * @global int    The index of the currently selected page.
     * @global array  The localization of the core.
     * @global string The (X)HTML for the contents area.
     */
    function outputEditContents()
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
     *
     * @access public
     */
    function isFilebrowserMissing()
    {
        return $this->needsFilebrowser()
            && $this->isExternalMissing('filebrowser');
    }

    /**
     * Returns whether the page manager is missing.
     *
     * @return bool
     *
     * @access public
     *
     * @global string The requested function.
     */
    function isPagemanagerMissing()
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
     * @access protected
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
    function needsFilebrowser()
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
     * @access protected
     *
     * @global array The paths of system files and folders.
     * @global array The configuration of the core.
     */
    function isExternalMissing($name)
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
     * @access public
     *
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The (X)HTML for the <li>s holding error messages.
     */
    function reportMissingExternal($name)
    {
        global $cf, $tx, $e;

        $e .= '<li>' . sprintf($tx['error']['no' . $name], $cf[$name]['external'])
            . '</li>' . "\n";
    }
}

?>
