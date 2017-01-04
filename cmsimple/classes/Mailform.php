<?php

/**
 * Handling of the mailform.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2016 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * The mailform class.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class XH_Mailform
{
    /**
     * Whether the mailform is embedded on a CMSimple_XH page.
     *
     * @var bool
     */
    var $embedded;

    /**
     * The linebreak characters (either CRLF or LF).
     *
     * Serves as workaround for broken mailers which don't handle CRLF correctly.
     *
     * @var bool
     */
    var $_linebreak;

    /**
     * The name of the sender.
     *
     * @var string
     *
     * @access protected
     */
    var $sendername;

    /**
     * The phone number of the sender.
     *
     * @var string
     *
     * @access protected
     */
    var $senderphone;

    /**
     * The email address of the sender.
     *
     * @var string
     *
     * @access protected
     */
    var $sender;

    /**
     * The expected CAPTCHA value.
     *
     * @var string
     *
     * @access protected
     */
    var $getlast;

    /**
     * The actual CAPTCHA value.
     *
     * @var string
     *
     * @access protected
     */
    var $cap;

    /**
     * The subject of the mail.
     *
     * @var string
     *
     * @access protected
     */
    var $subject;

    /**
     * The message.
     *
     * @var string
     *
     * @access protected
     */
    var $mailform;

    /**
     * Constructs an instance.
     *
     * @param bool   $embedded Whether the mailform is embedded on a CMSimple_XH
     *                         page.
     * @param string $subject  An alternative subject field preset text instead of 
     *                         the subject default in localization.
     *
     * @global array   The configuration of the core.
     * @global array   The localization of the core.
     *
     * @return void
     *
     * @access public
     */
    function __construct($embedded = false, $subject=null)
    {
        global $cf, $tx;
        $this->embedded = $embedded;
        $this->_linebreak = ($cf['mailform']['lf_only'] ? "\n" : "\r\n");
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
            $this->subject = sprintf(
                $tx['mailform']['subject_default'], sv('SERVER_NAME')
            );
        }
            
        if ($embedded) {
            $this->mailform = isset($_POST['xh_mailform'])
                ? stsl($_POST['xh_mailform']) : '';
        } else {
            $this->mailform = isset($_POST['mailform'])
                ? stsl($_POST['mailform']) : '';
        }
    }

    /**
     * Fallback constructor for PHP 4
     *
     * @param bool   $embedded Whether the mailform is embedded on a CMSimple_XH
     *                         page.
     * @param string $subject  An alternative subject field preset text instead of 
     *                         the subject default in localization.
     *
     * @return void
     *
     * @access public
     */
    function XH_Mailform($embedded = false, $subject=null)
    {
        XH_Mailform::__construct($embedded, $subject);
    }

    /**
     * Returns error messages resp. an empty string if everything is okay.
     *
     * @return string (X)HTML.
     *
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     *
     * @access protected
     */
    function check()
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
        if (!$this->isValidEmail($this->sender) || $this->subject == '') {
            $o .= XH_message('warning', $tx['mailform']['notaccepted']);
        }
        return $o;
    }

    /**
     * Submits the mailform and returns whether that succeeded.
     *
     * @return bool
     *
     * @global array The configuration of the core.
     * @global array The localization of the core.
     *
     * @access protected
     */
    function submit()
    {
        global $cf, $tx;

        $body = $tx['mailform']['sendername'] . $this->sendername . "\n"
            . $tx['mailform']['senderphone'] . $this->senderphone . "\n\n"
            . $this->mailform;
        $sent = $this->sendMail(
            $cf['mailform']['email'], $this->subject, $body,
            "From: " . $this->sender . $this->_linebreak
            . "X-Remote: " . sv('REMOTE_ADDR')
        );
        if (!$sent) {
            XH_logMessage('error', 'XH', 'mailform', $this->sender);
        }
        return $sent;
    }

    /**
     * Processes the mailform request and returns the resulting view.
     *
     * @return string (X)HTML
     *
     * @global string The requested action.
     * @global array  The localization of the core.
     *
     * @staticvar bool Whether any mailform is processed more than once.
     *
     * @access public
     *
     * @todo Remove static variable for better testability.
     */
    function process()
    {
        global $action, $tx;
        static $again = false;

        if ($again) {
            return false;
        }
        $again = true;

        $anchor = '<div id="xh_mailform"></div>';
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
     * @return string (X)HTML
     *
     * @global string The script name.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     * @global string The current page URL.
     *
     * @access protected
     */
    function render()
    {
        global $sn, $cf, $tx, $su;

        $random = rand(10000, 99999);
        $url = $sn . ($this->embedded ? '?' . $su : '');
        $o = '<form class="xh_mailform" action="' . $url
            . '#xh_mailform" method="post">' . "\n";
        if (!$this->embedded) {
            $o .= tag('input type="hidden" name="function" value="mailform"') . "\n";
        }
        if (isset($cf['mailform']['captcha'])
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $o .= tag('input type="hidden" name="getlast" value="' . $random . '"')
                . "\n";
        }
        $o .= tag('input type="hidden" name="action" value="send"') . "\n";

        // fields before textarea
        $o .= '<div>' . "\n" . '<label for="xh_mailform_sendername">'
            . $tx['mailform']['sendername'] . '</label>' . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="sendername"'
                . ' id="xh_mailform_sendername" value="'
                . XH_hsc($this->sendername).'"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" . '<label for="xh_mailform_senderphone">'
            . $tx['mailform']['senderphone'] . '</label>' . tag('br') . "\n"
            . tag(
                'input type="tel" class="text" size="35" name="senderphone"'
                . ' id="xh_mailform_senderphone" value="'
                . XH_hsc($this->senderphone).'"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" . '<label for="xh_mailform_sender">'
            . $tx['mailform']['sender'] . '</label>' . tag('br') . "\n"
            . tag(
                'input type="email" class="text" size="35" name="sender"'
                . ' id="xh_mailform_sender" value="'
                . XH_hsc($this->sender).'" required="required"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" .  '<label for="xh_mailform_subject">'
            . $tx['mailform']['subject'] . '</label>'. tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="subject"'
                . ' id="xh_mailform_subject" value="'
                . XH_hsc($this->subject).'" required="required"'
            ) . "\n"
            . '</div>' . "\n"
            . tag('br') . "\n";

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
                .  tag(
                    'input type="text" name="cap" class="xh_captcha_input"'
                    . ' required="required"'
                )
                . "\n" .  '<span class="xh_captcha_code">' . "\n"
                .  $random . '</span>' . "\n";
        }

        // send button
        $o .= '<div class="xh_break">' . "\n"
            . tag(
                'input type="submit" class="submit" value="'
                .  $tx['mailform']['sendbutton'] . '"'
            )
            . "\n" . '</div>' . "\n" . '</form>' . "\n";

        return $o;
    }

    /**
     * Sends a UTF-8 encoded mail.
     *
     * @param string $to      Receiver(s) of the mail.
     * @param string $subject Subject of the email to be sent.
     * @param string $message Message to be sent.
     * @param string $header  String to be inserted at the end of the email header.
     *
     * @return bool Whether the mail was accepted for delivery.
     *
     * @access protected
     */
    function sendMail($to, $subject = '(No Subject)', $message = '', $header = '')
    {
        $header = 'MIME-Version: 1.0' . $this->_linebreak
            . 'Content-Type: text/plain; charset=UTF-8; format=flowed'
            . $this->_linebreak
            . 'Content-Transfer-Encoding: base64' . $this->_linebreak
            . $header;
        $subject = $this->encodeMIMEFieldBody($subject);

        $message = preg_replace(
            '/(?:\r\n|\r|\n)/', $this->_linebreak, trim($message)
        );
        $message = chunk_split(base64_encode($message));

        return mail($to, $subject, $message, $header);
    }

    /**
     * Returns the body of an email header field as "encoded word" (RFC 2047)
     * with "folding" (RFC 5322), if necessary.
     *
     * @param string $text The body of the MIME field.
     *
     * @return string
     *
     * @access protected
     *
     * @todo Don't we have to fold overlong pure ASCII texts also?
     */
    function encodeMIMEFieldBody($text)
    {
        if (!preg_match('/(?:[^\x00-\x7F])/', $text)) { // ASCII only
            return $text;
        } else {
            $lines = array();
            do {
                $i = 45;
                if (strlen($text) > $i) {
                    while ((ord($text[$i]) & 0xc0) == 0x80) {
                        $i--;
                    }
                    $lines[] = substr($text, 0, $i);
                    $text = substr($text, $i);
                } else {
                    $lines[] = $text;
                    $text = '';
                }
            } while ($text != '');
            $body = 'return \'=?UTF-8?B?\' . base64_encode($l) . \'?=\';';
            $func = create_function('$l', $body);
            return implode($this->_linebreak . ' ', array_map($func, $lines));
        }
    }

    /**
     * Returns whether an email address is valid.
     *
     * For simplicity we are not aiming for full compliance with RFC 5322.
     * The local-part must be a dot-atom-text. The domain is checked with
     * gethostbyname() after applying idn_to_ascii(), if the latter is available.
     *
     * @param string $address An email address.
     *
     * @return bool
     *
     * @access protected
     */
    function isValidEmail($address)
    {
        $atext = '[!#-\'*+\-\/-9=?A-Z^-~]';
        $dotAtomText = $atext . '(?:' . $atext . '|\.)*';
        $pattern = '/^(' . $dotAtomText . ')@([^@]+)$/u';
        if (!preg_match($pattern, $address, $matches)) {
            return false;
        }
        $local = $matches[1];
        $domain = $matches[2];
        if (function_exists('idn_to_ascii')) {
            $domain = defined('INTL_IDNA_VARIANT_UTS46')
                ? idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46)
                : idn_to_ascii($domain);
        }
        if ($domain
            && (strlen($domain) > 255 || gethostbyname($domain) == $domain)
        ) {
            return false;
        }
        return true;
    }
}

?>
