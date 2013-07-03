<?php

/**
 * Handling of CSRF protection.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

/**
 * The CSRF protection class.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 * @tutorial XH/CSRFProtection.cls
 */
class XH_CSRFProtection
{
    /**
     * The CSRF token for the following request.
     *
     * @var string $token
     *
     * @access protected
     */
    var $token = null;

    /**
     * Returns a hidden input field with the CSRF token
     * for inclusion in an (X)HTML form.
     *
     * @return string (X)HTML
     */
    function tokenInput()
    {
        if (!isset($this->token)) {
            $this->token = md5(uniqid(rand(), true));
        }
        $o = tag(
            'input type="hidden" name="xh_csrf_token" value="' . $this->token . '"'
        );
        return $o;
    }

    /**
     * Checks whether the submitted CSRF token matches the one stored in the
     * session. Responds with "403 Forbidden" if not.
     *
     * @return void
     *
     * @access public
     */
    function check()
    {
        $submittedToken = isset($_POST['xh_csrf_token'])
            ? $_POST['xh_csrf_token']
            : (isset($_GET['xh_csrf_token']) ? $_GET['xh_csrf_token'] : '');
        if (session_id() == '') {
            session_start();
        }
        if (!isset($_SESSION['xh_csrf_token'][CMSIMPLE_ROOT])
            || $submittedToken != $_SESSION['xh_csrf_token'][CMSIMPLE_ROOT]
        ) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Invalid CSRF token!';
            exit;
        }
    }

    /**
     * Stores the CSRF token in the session, if a self::tokenInput() was called.
     *
     * @return void
     *
     * @access public
     */
    function store()
    {
        if (isset($this->token)) {
            if (session_id() == '') {
                session_start();
            }
            $_SESSION['xh_csrf_token'][CMSIMPLE_ROOT] = $this->token;
        }
    }
}

?>
