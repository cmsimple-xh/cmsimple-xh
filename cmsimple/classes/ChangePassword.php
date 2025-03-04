<?php

namespace XH;

/**
 * Changing the password.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2023 The CMSimple_XH developers <https://www.cmsimple-xh.org/?About-CMSimple_XH/The-XH-Team>
 * @copyright GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.en.html>
 * @since     1.7
 */
class ChangePassword
{
    /**
     * The old password.
     *
     * @var string
     */
    private $passwordOld;

    /**
     * The new password.
     *
     * @var string
     */
    private $passwordNew;

    /**
     * The password confirmation.
     *
     * @var string
     */
    private $passwordConfirmation;

    /**
     * The configuration of the core.
     *
     * @var array
     */
    private $config;

    /**
     * The localization of the core.
     *
     * @var array
     */
    private $lang;

    /**
     * The CSRF protector.
     *
     * @var CSRFProtection
     */
    private $csrfProtector;

    /**
     * Initializes a new instance.
     */
    public function __construct()
    {
        global $cf, $tx, $_XH_csrfProtection;

        $this->passwordOld = isset($_POST['xh_password_old'])
            ? $_POST['xh_password_old'] : '';
        $this->passwordNew = isset($_POST['xh_password_new'])
            ? $_POST['xh_password_new'] : '';
        $this->passwordConfirmation = isset($_POST['xh_password_confirmation'])
            ? $_POST['xh_password_confirmation'] : '';
        $this->config = $cf;
        $this->lang = $tx;
        $this->csrfProtector = $_XH_csrfProtection;
    }

    /**
     * Process the default action.
     *
     * @return void
     */
    public function defaultAction()
    {
        global $o, $cf;

        $minPWlength = $cf['password']['min_length'];
        $o .= $this->render();
        $o .= '<p class="xh_info">' . $this->lang['password']['invalid']
            . '<br>'
            . sprintf($this->lang['password']['too_short'], $minPWlength)
            . '</p>'
            . PHP_EOL;
    }

    /**
     * Renders the change password form.
     *
     * @return string
     */
    private function render()
    {
        global $sn;

        return '<form id="xh_change_password" action="' . $sn
            . '?&xh_change_password" method="post">'
            . $this->csrfProtector->tokenInput()
            . $this->renderField('old', $this->passwordOld)
            . $this->renderField('new', $this->passwordNew)
            . $this->renderField('confirmation', $this->passwordConfirmation)
            . $this->renderSubmit()
            . '</form>';
    }

    /**
     * Renders a field.
     *
     * @param string $which A field id/name postfix.
     * @param string $value A field value.
     *
     * @return string
     */
    private function renderField($which, $value)
    {
        $id = "xh_password_$which";
        $html = '<p>'
            . '<label for="' . $id . '">' . $this->lang['password'][$which]
            . '</label> '
            . '<input id="' . $id . '" type="password" name="' . $id
                . '" value="' . XH_hsc($value) . '">';
        if (in_array($which, array('old', 'new'))) {
            $html .= ' <span class="xh_password_score"></span>';
        }
        $html .= '</p>';
        return $html;
    }

    /**
     * Renders the submit element.
     *
     * @return string
     */
    private function renderSubmit()
    {
        return '<p><button name="action" value="save">'
            . utf8_ucfirst($this->lang['action']['save']) . '</button></p>';
    }

    /**
     * Process the save action.
     *
     * @return void
     */
    public function saveAction()
    {
        global $o, $pth;

        $this->csrfProtector->check();
        if ($hash = $this->validate($error)) {
            $this->config['security']['password'] = $hash;
            $this->savePassword();
            $written = XH_logMessage('info', 'XH', 'login', 'password was changed');
            if (!$written) {
                e('cntwriteto', 'log', $pth['file']['log']);
            }
            header('Location: ' . CMSIMPLE_URL);
            exit;
        } else {
            $o .= XH_message('fail', $error);
            $o .= $this->render();
        }
    }

    /**
     * Validates the posted input and returns the new password hash.
     *
     * @param string $error An error message.
     *
     * @return ?string
     */
    private function validate(&$error)
    {
        global $cf;

        $result = null;
        $minPWlength = (int)$cf['password']['min_length'];
        if ($this->passwordOld && $this->passwordNew
            && $this->passwordConfirmation
        ) {
            $hash = password_verify($this->passwordOld, $this->config['security']['password']);
            if (!$hash) {
                $error = $this->lang['password']['wrong'];
            } else {
                if (mb_strlen($this->passwordNew, 'UTF-8') < $minPWlength) {
                    $error = sprintf($this->lang['password']['too_short'], $minPWlength);
                } elseif (!preg_match('/^[!-~]+$/u', $this->passwordNew)) {
                    $error = $this->lang['password']['invalid'];
                } elseif ($this->passwordNew != $this->passwordConfirmation) {
                    $error = $this->lang['password']['mismatch'];
                } else {
                    $result = password_hash($this->passwordNew, PASSWORD_BCRYPT);
                }
            }
        } else {
            $error = $this->lang['password']['fields_missing'];
        }
        return $result;
    }

    /**
     * Saves the configuration with the new password hash.
     *
     * @return bool
     */
    private function savePassword()
    {
        global $pth;

        $o = "<?php\n\n";
        foreach ($this->config as $cat => $opts) {
            foreach ($opts as $name => $opt) {
                // The following are there for backwards compatibility,
                // and have to be suppressed in the config form.
                if ($cat == 'security' && $name == 'type'
                    || $cat == 'scripting' && $name == 'regexp'
                    || $cat == 'site' && $name == 'title'
                    || $cat == 'xhtml'
                ) {
                    continue;
                }
                $opt = addcslashes($opt, "\0..\37\"\$\\");
                $o .= "\$cf['$cat']['$name']=\"$opt\";\n";
            }
        }
        $o .= "\n?>\n";
        $res = (bool) XH_writeFile($pth['file']['config'], $o, true);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($pth['file']['config']);
        }
        return $res;
    }
}
