<?php

/**
 * Handling of CSRF protection.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

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
class CSRFProtection
{
    /**
     * The name of the session key and input name of the CSRF token.
     *
     * @var string
     */
    private $keyName;

    /**
     * The CSRF token for the following request.
     *
     * @var string $token
     */
    private $token = null;

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
            XH_startSession();
            if (isset($_SESSION[$this->keyName])) {
                $this->token = $_SESSION[$this->keyName];
            }
        }
    }

    /**
     * Returns a hidden input field with the CSRF token
     * for inclusion in an HTML form.
     *
     * @return string HTML
     */
    public function tokenInput()
    {
        if (!isset($this->token)) {
            $this->token = bin2hex(random_bytes(16));
        }
        $o = '<input type="hidden" name="' . $this->keyName . '" value="'
            . $this->token . '">';
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
        XH_startSession();
        if (!isset($_SESSION[$this->keyName])
            || $submittedToken != $_SESSION[$this->keyName]
        ) {
            header('HTTP/1.0 403 Forbidden');
            XH_exit('Invalid CSRF token!');
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
            XH_startSession();
            $_SESSION[$this->keyName] = $this->token;
        }
    }
}
