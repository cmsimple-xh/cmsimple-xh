<?php

// TODO: i18n
// TODO: further error handling
// TODO: use another email address than $cf[mailform][email]
// TODO: use XH_Mailform::sendMail() instead of plain mail()

class XH_PasswordForgotten
{
    var $status;

    function dispatch()
    {
        if (isset($_POST['xh_email'])) {
            $this->submit();
        } elseif (isset($_GET['xh_code']) && $this->checkMac($_GET['xh_code'])) {
            $this->reset();
        }
        $this->render();
    }

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

    function checkMac($mac)
    {
        return $mac == $this->mac() || $mac == $this->mac(true);
    }

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

    function saveNewPassword($hash)
    {
        global $pth;

        // TODO: write config in usual format
        include $pth['file']['config'];
        $cf['security']['password'] = $hash;
        $config = '<?php' . PHP_EOL . '$cf = ' . var_export($cf, true) . PHP_EOL . '?>';
        XH_writeFile($pth['file']['config'], $config);
    }
}

?>
