<?php

/**
 * Handling of password forgotten functionality.
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
 * The password forgotten handling class.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 *
 * @todo i18n
 * @todo further error handling
 * @todo use another email address than $cf[mailform][email]
 * @todo use XH_Mailform::sendMail() instead of plain mail()
 */
class XH_PasswordForgotten
{
    /**
     * The status of the password forgotten procedure.
     *
     * @var string
     */
    var $status = '';

    /**
     * Dispatches according to the request.
     *
     * @return void
     */
    function dispatch()
    {
        if (isset($_POST['xh_email'])) {
            $this->submit();
        } elseif (isset($_GET['xh_code']) && $this->checkMac($_GET['xh_code'])) {
            $this->reset();
        }
        $this->render();
    }

    /**
     * Renders the view.
     *
     * @return void
     *
     * @global string The page title.
     * @global string The generated (X)HTML.
     * @global string The script name.
     */
    function render()
    {
        global $title, $o, $sn;

        $title = 'Password forgotten';
        $o .= '<h1>' . 'Password forgotten' . '</h1>';
        switch ($this->status) {
        case 'sent':
            $o .= '<p>' . 'Email sent' . '</p>';
            break;
        case 'reset':
            $o .= '<p>' . 'Password reset; email sent' . '</p>';
            break;
        default:
            $o .= '<p>' . 'You can request...' . '</p>'
            . '<form action="' . $sn . '?&function=forgotten" method="post">'
            . tag('input type="text" name="xh_email"')
            . tag('input type="submit" value="Send Reminder"')
            . '</form>';
        }
    }

    /**
     * Returns a MAC for the current or previous hour.
     *
     * @param bool $previous Whether to generate the MAC for the previous hour.
     *
     * @return string
     *
     * @global array The configuration of the core.
     */
    function mac($previous = false)
    {
        global $cf;

        $email = $cf['mailform']['email'];
        $date = date('Y-m-d h:00:00') . ($previous ? ' -1hour' : '');
        $timestamp = strtotime($date);
        $secret = $cf['security']['secret'];
        $mac = md5($email . $timestamp . $secret);
        return $mac;
    }

    /**
     * Returns whether a MAC is valid.
     *
     * @param string $mac A MAC.
     *
     * @return bool
     */
    function checkMac($mac)
    {
        return $mac == $this->mac() || $mac == $this->mac(true);
    }

    /**
     * Handles the submission of the email address. If valid, sends an email
     * with a link to reset the password.
     *
     * @return void
     *
     * @global array  The configuration of the core.
     * @global string The LI elements containing error messages.
     */
    function submit()
    {
        global $cf, $e;

        if ($_POST['xh_email'] == $cf['mailform']['email']) {
            $ok = mail(
                $cf['mailform']['email'], 'Password forgotten',
                'click the following link to reset your password:'
                . '<' . CMSIMPLE_URL . '?&function=forgotten&xh_code='
                . $this->mac() . '>'
            );
            $this->status = $ok ? 'sent' : '';
        } else {
            $this->status = '';
            $e .= '<li>' . 'Invalid email' . '</li>';
        }
    }

    /**
     * Resets the password to a randomly generated one and sends an appropriate
     * info email.
     *
     * @return void.
     *
     * @global object The password hasher.
     */
    function reset()
    {
        global $xh_hasher;

        $password = bin2hex($xh_hasher->get_random_bytes(8));
        $hash = $xh_hasher->HashPassword($password);
        $sent = mail(
            $cf['mailform']['email'], 'Password forgotten',
            'Your new password is: ' . $password
        );
        if ($sent) {
            $this->saveNewPassword($hash);
            $this->status = 'reset';
        } else {
            $this->status = '';
        }
    }

    /**
     * Saves the new password in the configuration file, and returns
     * whether that succeeded.
     *
     * @param string $hash A password hash.
     *
     * @return bool
     *
     * @global array The paths of system files and folders.
     */
    function saveNewPassword($hash)
    {
        global $pth;

        // TODO: write config in usual format
        include $pth['file']['config'];
        $cf['security']['password'] = $hash;
        $config = '<?php' . PHP_EOL . '$cf = ' . var_export($cf, true) . PHP_EOL
            . '?>';
        return XH_writeFile($pth['file']['config'], $config);
    }
}

?>
