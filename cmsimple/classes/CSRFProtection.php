<?php

/**
 * Handling of CSRF protection.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
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
 * @tutorial XH/XH_CSRFProtection.cls
 */
class XH_CSRFProtection
{
    /**
     * The name of the session key and input name of the CSRF token.
     *
     * @var string
     */
    protected $keyName;

    /**
     * The CSRF token for the following request.
     *
     * @var string $token
     */
    protected $token = null;

    /**
     * Initializes a new object.
     *
     * @param string $keyName    A key name.
     * @param bool   $perRequest Whether a new token shall be generated for each
     *                           request (otherwise once per session).
     */
    public function __construct($keyName = 'xh_csrf_token', $perRequest = false)
    {
        $this->keyName = $keyName;
        if (!$perRequest) {
            if (session_id() == '') {
                session_start();
            }
            if (isset($_SESSION[$this->keyName][CMSIMPLE_ROOT])) {
                $this->token = $_SESSION[$this->keyName][CMSIMPLE_ROOT];
            }
        }
    }

    /**
     * Returns a hidden input field with the CSRF token
     * for inclusion in an (X)HTML form.
     *
     * @return string (X)HTML
     *
     * @todo Use cryptographically stronger token?
     */
    public function tokenInput()
    {
        if (!isset($this->token)) {
            $this->token = md5(uniqid(rand()));
        }
        $o = tag(
            'input type="hidden" name="' . $this->keyName . '" value="'
            . $this->token . '"'
        );
        return $o;
    }

    /**
     * Checks whether the submitted CSRF token matches the one stored in the
     * session. Responds with "403 Forbidden" if not.
     *
     * @return void
     */
    public function check()
    {
        $submittedToken = isset($_POST[$this->keyName])
            ? $_POST[$this->keyName]
            : (isset($_GET[$this->keyName]) ? $_GET[$this->keyName] : '');
        if (session_id() == '') {
            session_start();
        }
        if (!isset($_SESSION[$this->keyName][CMSIMPLE_ROOT])
            || $submittedToken != $_SESSION[$this->keyName][CMSIMPLE_ROOT]
        ) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Invalid CSRF token!';
            // the following should be exit/die, but that would break unit tests
            trigger_error('Invalid CSRF token!', E_USER_ERROR);
        }
    }

    /**
     * Stores the CSRF token in the session, if a self::tokenInput() was called.
     *
     * @return void
     */
    public function store()
    {
        if (isset($this->token)) {
            if (session_id() == '') {
                session_start();
            }
            $_SESSION[$this->keyName][CMSIMPLE_ROOT] = $this->token;
        }
    }
}

?>
