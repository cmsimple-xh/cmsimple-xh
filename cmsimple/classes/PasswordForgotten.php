<?php

// TODO: i18n
// TODO: code file has to be writable
// TODO: further error handling
// TODO: use another email address than $cf[mailform][email]
// TODO: use XH_Mailform::sendMail() instead of plain mail()
// TODO: improve code uniquity

class XH_PasswordForgotten
{
    var $status;

    var $codeFile;

    function XH_PasswordForgotten()
    {
        global $pth;

        $this->codeFile = $pth['folder']['cmsimple'] . 'password.code';
    }

    function dispatch()
    {
        if (isset($_POST['xh_email'])) {
            $this->submit();
        } elseif (isset($_GET['xh_code'])
                  && time() <= filemtime($this->codeFile) + 1800
                  && $_GET['xh_code'] == $this->readCode()
        ) {
            $this->reset();
        }
        $this->render();
    }

    function saveCode($code)
    {
        return XH_writeFile($this->codeFile, $code);
    }

    function readCode()
    {
        return file_get_contents($this->codeFile);
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

    function submit()
    {
        global $cf, $e;

        if ($_POST['xh_email'] == $cf['mailform']['email']) {
            $code = uniqid();
            $ok = $this->saveCode($code) && mail(
                $cf['mailform']['email'], 'Password forgotten',
                'click the following link to reset your password:'
                . '<' . CMSIMPLE_URL . '?&function=forgotten&xh_code=' . $code . '>'
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

        $password = uniqid();
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
