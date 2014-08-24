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
 * @since    1.6.2
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
     * Handles password check requests.
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
     * Handles menumanager requests.
     *
     * @return void
     *
     * @access public
     *
     * @global array             The paths of system files and folders.
     * @global string            Whether the menumanager is requested.
     * @global string            The requested action.
     * @global string            The menumanager page information.
     * @global XH_PageDataRouter The page data router.
     */
    function handleMenumanager()
    {
        global $pth, $menumanager, $action, $text, $pd_router;

        if (isset($menumanager) && $menumanager == 'true'
            && $action == 'saverearranged' && !empty($text)
        ) {
            if (!$pd_router->refresh_from_menu_manager($text)) {
                e('notwritable', 'content', $pth['file']['content']);
            }
        }
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
     */
    function handleSavePageData()
    {
        global $pth, $s, $pd_router, $tx;

        if ($s > -1 && isset($_POST['save_page_data'])) {
            $temp = $_POST;
            unset($temp['save_page_data']);
            $temp = array_map('stsl', $temp);
            $temp = $pd_router->update($s, $temp);
            if (isset($_GET['xh_pagedata_ajax'])) {
                if ($temp) {
                    echo XH_message('info', $tx['message']['pd_success']);
                } else {
                    header('HTTP/1.0 500 Internal Server Error');
                    echo XH_message('fail', $tx['message']['pd_fail']);
                }
                XH_exit();
            } else {
                if (!$temp) {
                    e('cntsave', 'content', $pth['file']['content']);
                }
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
            $temp = stsl($_POST['xh_suffix']);
            if (preg_match('/^[a-z_0-9-]{1,20}$/i', $temp)) {
                XH_extraBackup($temp);
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
}

?>
