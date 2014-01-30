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
 * @copyright 1999-2009 <http://cmsimple.org/>
 * @copyright 2009-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
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
     * @param bool $embedded Whether the mailform is embedded on a CMSimple_XH page.
     *
     * @access public
     */
    function XH_Mailform($embedded = false)
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
        $this->subject = isset($_POST['subject'])
            ? stsl($_POST['subject'])
            : sprintf($tx['mailform']['subject_default'], sv('SERVER_NAME'));
        if ($embedded) {
            $this->mailform = isset($_POST['xh_mailform'])
                ? stsl($_POST['xh_mailform']) : '';
        } else {
            $this->mailform = isset($_POST['mailform'])
                ? stsl($_POST['mailform']) : '';
        }
    }

    /**
     * Returns whether the submitted mailform is valid.
     * Errors are reported to <var>$e</var>
     *
     * @return bool
     *
     * @global string (X)HTML fragment of LI elements with error messages.
     * @global array  The configuration of the core.
     * @global array  The localization of the core.
     *
     * @access protected
     */
    function check()
    {
        global $e, $cf, $tx;

        if ($this->getlast != $this->cap
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $e .= '<li>' . $tx['mailform']['captchafalse'] . '</li>';
        }
        if ($this->mailform == '') {
            $e .= '<li>' . $tx['mailform']['mustwritemessage'] . '</li>';
        }
        if (!$this->isValidEmail($this->sender) || $this->subject == '') {
            $e .= '<li>' . $tx['mailform']['notaccepted'] . '</li>';
        }
        return $e == '';
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

        if ($this->check()) {
            $body = $tx['mailform']['sendername'] . $this->sendername . "\n"
                . $tx['mailform']['senderphone'] . $this->senderphone . "\n\n"
                . $this->mailform;
            $sent = $this->sendMail(
                $cf['mailform']['email'], $this->subject, $body,
                "From: " . $this->sender . "\r\n"
                . "X-Remote: " . sv('REMOTE_ADDR') . "\r\n"
            );
            if (!$sent) {
                XH_logMessage('error', 'XH', 'mailform', $this->sender);
            }
        } else {
            $sent = false;
        }
        return $sent;
    }

    /**
     * Processes the mailform request and returns the resulting view.
     *
     * @return string (X)HTML
     *
     * @global string The requested action.
     * @global string (X)HTML fragment of LI elements with error messages.
     * @global array  The localization of the core.
     *
     * @access public
     */
    function process()
    {
        global $action, $e, $tx;

        if ($action == 'send') {
            if ($this->submit()) {
                $o = '<p>' . $tx['mailform']['send'] . '</p>' . "\n";
            } else {
                $e .= '<li>' . $tx['mailform']['notsend'] . '</li>' . "\n";
                $o = $this->render();
            }
        } else {
            $o = $this->render();
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
        $o = '<form class="xh_mailform" action="' . $url . '" method="post">' . "\n";
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
        $o .= '<div>' . "\n" . $tx['mailform']['sendername'] . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="sendername" value="'
                . XH_hsc($this->sendername).'"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" . $tx['mailform']['senderphone'] . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="senderphone" value="'
                . XH_hsc($this->senderphone).'"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" . $tx['mailform']['sender'] . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="sender" value="'
                . XH_hsc($this->sender).'"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" .  $tx['mailform']['subject'] . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="subject" value="'
                . XH_hsc($this->subject).'"'
            ) . "\n"
            . '</div>' . "\n"
            . tag('br') . "\n";

        // textarea
        $name = $this->embedded ? 'xh_mailform' : 'mailform';
        $o .= '<textarea rows="12" cols="40" name="' . $name . '">' . "\n";
        $o .= XH_hsc($this->mailform) . "\n";
        $o .= '</textarea>' . "\n";

        // captcha
        if (isset($cf['mailform']['captcha'])
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $o .= '<p>' .  $tx['mailform']['captcha'] . '</p>' . "\n"
                .  tag('input type="text" name="cap" class="xh_captcha_input"')
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
        $header = 'MIME-Version: 1.0' . "\r\n"
            . 'Content-Type: text/plain; charset=UTF-8; format=flowed' . "\r\n"
            . 'Content-Transfer-Encoding: base64' . "\r\n"
            . $header;
        $subject = $this->encodeMIMEFieldBody($subject);

        $message = preg_replace('/(?:\r\n|\r|\n)/', "\r\n", trim($message));
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
            return implode("\r\n ", array_map($func, $lines));
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
        if (gethostbyname($domain) == $domain) {
            return false;
        }
        return true;
    }

}

?>
