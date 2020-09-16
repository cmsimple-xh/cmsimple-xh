<?php

namespace XH;

/**
 * Handling of the mailform.
 *
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 * @since     1.6
 */
class Mailform
{
    /**
     * Whether the mailform is embedded on a CMSimple_XH page.
     *
     * @var bool
     */
    private $embedded;

    /**
     * The name of the sender.
     *
     * @var string
     */
    private $sendername;

    /**
     * The phone number of the sender.
     *
     * @var string
     */
    private $senderphone;

    /**
     * The email address of the sender.
     *
     * @var string
     */
    private $sender;

    /**
     * The expected CAPTCHA value.
     *
     * @var string
     */
    private $getlast;

    /**
     * The actual CAPTCHA value.
     *
     * @var string
     */
    private $cap;

    /**
     * The subject of the mail.
     *
     * @var string
     */
    public $subject;

    /**
     * The message.
     *
     * @var string
     */
    private $mailform;

    /**
     * The mail object.
     *
     * @var Mail
     */
    private $mail;

    /**
     * Constructs an instance.
     *
     * @param bool   $embedded Whether the mailform is embedded on a CMSimple_XH
     *                         page.
     * @param string $subject  An alternative subject field preset text instead of
     *                         the subject default in localization.
     * @param Mail   $mail     A mail object.
     */
    public function __construct($embedded = false, $subject = null, $mail = null)
    {
        global $tx;

        $this->embedded = $embedded;
        $this->sendername = isset($_POST['sendername'])
            ? stsl($_POST['sendername']) : '';
        $this->senderphone = isset($_POST['senderphone'])
            ? stsl($_POST['senderphone']) : '';
        $this->sender = isset($_POST['sender'])
            ? stsl($_POST['sender']) : '';
        $this->getlast = isset($_POST['getlast'])
            ? stsl($_POST['getlast']) : '';
        $this->cap = isset($_POST['cap'])
            ? stsl($_POST['cap']) : '';
            
        if (isset($_POST['subject'])) {
            $this->subject = stsl($_POST['subject']);
        } elseif (isset($_GET['xh_mailform_subject'])) {
            $this->subject = stsl($_GET['xh_mailform_subject']);
        } elseif (isset($subject)) {
            $this->subject = $subject;
        } else {
            $this->subject = sprintf($tx['mailform']['subject_default'], sv('SERVER_NAME'));
        }
            
        if ($embedded) {
            $this->mailform = isset($_POST['xh_mailform'])
                ? stsl($_POST['xh_mailform']) : '';
        } else {
            $this->mailform = isset($_POST['mailform'])
                ? stsl($_POST['mailform']) : '';
        }
        $this->mail = isset($mail) ? $mail : new Mail();
    }

    /**
     * Returns error messages resp. an empty string if everything is okay.
     *
     * @return string HTML
     */
    public function check()
    {
        global $cf, $tx;

        $o = '';
        if ($this->getlast != $this->cap
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $o .= XH_message('warning', $tx['mailform']['captchafalse']);
        }
        if ($this->mailform == '') {
            $o .= XH_message('warning', $tx['mailform']['mustwritemessage']);
        }
        if (!$this->mail->isValidAddress($this->sender) || $this->subject == '') {
            $o .= XH_message('warning', $tx['mailform']['notaccepted']);
        }
        return $o;
    }

    /**
     * Submits the mailform and returns whether that succeeded.
     *
     * @return bool
     */
    public function submit()
    {
        global $cf, $tx;

        $cf_email = trim($cf['mailform']['email'], ' ,');
        $cf_email_array = explode(',', $cf_email);
        $cf_email_array = array_map('trim', $cf_email_array);
        $header_from = $cf_email_array[0];
        $header_to = implode(',', $cf_email_array);

        $this->mail->setTo($header_to);
        $this->mail->addHeader('From', $header_from);
        $this->mail->addHeader('Reply-To', $this->sender);
        $this->mail->addHeader('X-Remote', sv('REMOTE_ADDR'));
        $this->mail->setSubject($this->subject);
        $this->mail->setMessage(
            rtrim($tx['mailform']['sendername'] . $this->sendername) . "\n"
            . rtrim($tx['mailform']['senderphone'] . $this->senderphone) . "\n"
            . $tx['mailform']['sendermail'] . $this->sender . "\n\n"
            . $this->mailform
        );
        $sent = $this->mail->send();
        if (!$sent) {
            XH_logMessage('error', 'XH', 'mailform', $this->sender);
        }
        return $sent;
    }

    /**
     * Processes the mailform request and returns the resulting view.
     *
     * @return string HTML
     *
     * @todo Remove static variable for better testability.
     */
    public function process()
    {
        global $action, $tx;
        static $again = false;

        if ($again) {
            return '';
        }
        $again = true;

        $anchor = '';
        if (!isset($_GET['mailform'])) {
            $anchor = '<div id="xh_mailform"></div>';
        }
        if ($action == 'send') {
            $o = $this->check();
            if (!$o && $this->submit()) {
                $o .= $anchor . XH_message('success', $tx['mailform']['send']);
            } else {
                $o .= $anchor . XH_message('fail', $tx['mailform']['notsend'])
                    . $this->render();
            }
        } else {
            $o = $anchor . $this->render();
        }
        return $o;
    }

    /**
     * Returns the mailform view.
     *
     * @return string HTML
     */
    public function render()
    {
        global $sn, $cf, $tx, $su;

        $random = rand(10000, 99999);
        $url = $sn . ($this->embedded ? '?' . $su : '');
        $o = '<form class="xh_mailform" action="' . $url
            . '#xh_mailform" method="post">' . "\n";
        if (!$this->embedded) {
            $o .= '<input type="hidden" name="function" value="mailform">' . "\n";
        }
        if (isset($cf['mailform']['captcha'])
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $o .= '<input type="hidden" name="getlast" value="' . $random . '">'
                . "\n";
        }
        $o .= '<input type="hidden" name="action" value="send">' . "\n";

        // fields before textarea
        $o .= '<div>' . "\n" . '<label for="xh_mailform_sendername">'
            . $tx['mailform']['sendername'] . '</label>' . '<br>' . "\n"
            . '<input type="text" class="text" size="35" name="sendername"'
            . ' id="xh_mailform_sendername" value="'
            . XH_hsc($this->sendername).'">' . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" . '<label for="xh_mailform_senderphone">'
            . $tx['mailform']['senderphone'] . '</label>' . '<br>' . "\n"
            . '<input type="tel" class="text" size="35" name="senderphone"'
            . ' id="xh_mailform_senderphone" value="'
            . XH_hsc($this->senderphone).'">' . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" . '<label for="xh_mailform_sender">'
            . $tx['mailform']['sender'] . '</label>' . '<br>' . "\n"
            . '<input type="email" class="text" size="35" name="sender"'
            . ' id="xh_mailform_sender" value="'
            . XH_hsc($this->sender).'" required="required">' . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" .  '<label for="xh_mailform_subject">'
            . $tx['mailform']['subject'] . '</label>'. '<br>' . "\n"
            . '<input type="text" class="text" size="35" name="subject"'
            . ' id="xh_mailform_subject" value="'
            . XH_hsc($this->subject).'" required="required">' . "\n"
            . '</div>' . "\n"
            . '<br>' . "\n";

        // textarea
        $name = $this->embedded ? 'xh_mailform' : 'mailform';
        $o .= '<textarea rows="12" cols="40" name="' . $name
            . '" required="required" title="' . $tx['mailform']['message'] . '">'
            . XH_hsc($this->mailform) . '</textarea>';

        // captcha
        if (isset($cf['mailform']['captcha'])
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $o .= '<p>' .  $tx['mailform']['captcha'] . '</p>' . "\n"
                .  '<input type="text" name="cap" class="xh_captcha_input"'
                . ' required="required">'
                . "\n" .  '<span class="xh_captcha_code">' . "\n"
                .  $random . '</span>' . "\n";
        }

        // send button
        $o .= '<div class="xh_break">' . "\n"
            . '<input type="submit" class="submit" value="'
            .  $tx['mailform']['sendbutton'] . '">'
            . "\n" . '</div>' . "\n" . '</form>' . "\n";

        return $o;
    }
}
