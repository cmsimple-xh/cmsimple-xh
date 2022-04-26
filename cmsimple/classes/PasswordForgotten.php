<?php

namespace XH;

/**
 * Handling of password forgotten functionality.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.6
 */
class PasswordForgotten
{
    /**
     * The status of the password forgotten procedure.
     *
     * @var string
     */
    private $status = '';

    /**
     * Dispatches according to the request.
     *
     * @return void
     */
    public function dispatch()
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
     */
    private function render()
    {
        global $title, $o, $sn, $tx, $onload;

        $title = $tx['title']['password_forgotten'];
        $o .= '<div class="xh_login">'
            . '<h1>' . $title . '</h1>';
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
                . '<input type="text" name="xh_email">'
                . '<input type="submit" class="submit" value="Send Reminder">'
                . '</form>';
                $onload .= 'document.forms[\'xh_forgotten\'].elements[\'xh_email\']'
                    . '.focus();';
        }
        $o .= '</div>';
    }

    /**
     * Returns a MAC for the current or previous hour.
     *
     * @param bool $previous Whether to generate the MAC for the previous hour.
     *
     * @return string
     */
    public function mac($previous = false)
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
    public function checkMac($mac)
    {
        return $mac == $this->mac() || $mac == $this->mac(true);
    }

    /**
     * Handles the submission of the email address. If valid, sends an email
     * with a link to reset the password.
     *
     * @return void
     */
    private function submit()
    {
        global $cf, $tx, $e;

        if ($_POST['xh_email'] == $cf['security']['email']) {
            $to = $cf['security']['email'];
            $message = $tx['password_forgotten']['email1_text'] . "\r\n"
                . '<' . CMSIMPLE_URL . '?&function=forgotten&xh_code='
                . $this->mac() . '>';
            $mail = new Mail();
            $mail->setTo($to);
            $mail->setSubject($tx['title']['password_forgotten']);
            $mail->setMessage($message);
            $mail->addHeader('From', $to);
            $ok = $mail->send();
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
     * @return void
     */
    private function reset()
    {
        global $pth, $cf, $tx, $e;

        $password = bin2hex(random_bytes(8));
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if (($hash !== false) && ($password != '')) {
            $to = $cf['security']['email'];
            $message = $tx['password_forgotten']['email2_text'] . ' ' . $password;
            $mail = new Mail();
            $mail->setTo($to);
            $mail->setSubject($tx['title']['password_forgotten']);
            $mail->setMessage($message);
            $mail->addHeader('From', $to);
            $sent = $mail->send();
            if ($sent) {
                if (!$this->saveNewPassword($hash)) {
                    e('cntsave', 'config', $pth['file']['config']);
                }
                $this->status = 'reset';
            } else {
                $this->status = '';
                $e .= '<li>' . $tx['mailform']['notsend'] . '</li>';
            }
        } else {
            $e .= XH_message('fail', $tx['mailform']['reset_pw_error']);
        }
    }

    /**
     * Saves the new password in the configuration file, and returns
     * whether that succeeded.
     *
     * @param string $hash A password hash.
     *
     * @return bool
     */
    private function saveNewPassword($hash)
    {
        global $pth;

        $cf = XH_includeVar($pth['file']['config'], 'cf');
        $cf['security']['password'] = $hash;
        $o = '<?php' . PHP_EOL . PHP_EOL;
        foreach ($cf as $cat => $opts) {
            foreach ($opts as $name => $opt) {
                $opt = addcslashes($opt, "\0..\37\"\$\\");
                $o .= "\$cf['$cat']['$name']=\"$opt\";" . PHP_EOL;
            }
        }
        $o .= PHP_EOL . '?>' . PHP_EOL;
        $res = (bool) XH_writeFile($pth['file']['config'], $o);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($pth['file']['config']);
        }
        return $res;
    }
}
