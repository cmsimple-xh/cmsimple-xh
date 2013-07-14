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
     * @global array  The localization of the core.
     * @global string JS for the onload attribute of the BODY element.
     */
    function render()
    {
        global $title, $o, $sn, $tx, $onload;

        $title = $tx['title']['password_forgotten'];
        $o .= '<h1>' . $title . '</h1>';
        switch ($this->status) {
        case 'sent':
            $o .= '<p>' . $tx['password_forgotten']['email1_sent'] . '</p>';
            break;
        case 'reset':
            $o .= '<p>' . $tx['password_forgotten']['email2_sent'] . '</p>';
            break;
        default:
            $o .= '<p>' . $tx['password_forgotten']['request'] . '</p>'
            . '<form name="xh_forgotten" action="' . $sn . '?&function=forgotten"'
            . ' method="post">'
            . tag('input type="text" name="xh_email"')
            . tag('input type="submit" class="submit" value="Send Reminder"')
            . '</form>';
            $onload .= 'document.forms[\'xh_forgotten\'].elements[\'xh_email\'].focus();';
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

        $email = $cf['security']['email'];
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
     * @global array  The paths of system files and folders.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string LI elements to be emitted as error messages.
     */
    function submit()
    {
        global $pth, $cf, $tx, $e;

        if ($_POST['xh_email'] == $cf['security']['email']) {
            $to = $cf['security']['email'];
            $subject = $tx['title']['password_forgotten'];
            $message = $tx['password_forgotten']['email1_text'] . "\r\n"
                . '<' . CMSIMPLE_URL . '?&function=forgotten&xh_code='
                . $this->mac() . '>';
            $headers = 'From: ' . $to;
            include_once $pth['folder']['classes'] . 'Mailform.php';
            $mailform = new XH_Mailform();
            $ok = $mailform->sendMail($to, $subject, $message, $headers);
            if ($ok) {
                $this->status = 'sent';
            } else {
                $this->status = '';
                $e .= '<li>' . $tx['mailform']['notsend'] . '</li>';
            }
        } else {
            $this->status = '';
        }
    }

    /**
     * Resets the password to a randomly generated one and sends an appropriate
     * info email.
     *
     * @return void.
     *
     * @global object The password hasher.
     * @global array  The paths of system files and folders.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     */
    function reset()
    {
        global $xh_hasher, $pth, $cf, $tx;

        $password = bin2hex($xh_hasher->get_random_bytes(8));
        $hash = $xh_hasher->HashPassword($password);
        $to = $cf['security']['email'];
        $subject = $tx['title']['password_forgotten'];
        $message = $tx['password_forgotten']['email2_text'] . ' ' . $password;
        $headers = 'From: ' . $to;
        include_once $pth['folder']['classes'] . 'Mailform.php';
        $mailform = new XH_Mailform();
        $sent = $mailform->sendMail($to, $subject, $message, $headers);
        if ($sent) {
            if (!$this->saveNewPassword($hash)) {
                e('cntsave', 'config', $pth['file']['config']);
            }
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

        include $pth['file']['config'];
        $cf['security']['password'] = $hash;
        $o = '<?php' . PHP_EOL . PHP_EOL;
        foreach ($cf as $cat => $opts) {
            foreach ($opts as $name => $opt) {
                $opt = addcslashes($opt, "\0..\37\"\$\\");
                $o .= "\$cf['$cat']['$name']=\"$opt\";" . PHP_EOL;
            }
        }
        $o .= PHP_EOL . '?>' . PHP_EOL;
        return XH_writeFile($pth['file']['config'], $o);
    }
}

?>
