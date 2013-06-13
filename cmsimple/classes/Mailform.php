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
 * @copyright 2009-2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
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
     * @access public
     */
    function XH_Mailform()
    {
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
        $this->mailform = isset($_POST['mailform'])
            ? stsl($_POST['mailform']) : '';
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
        if (!$this->isValidEmail($this->sender)) {
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
            $body = $tx['mailform']['sendername'] . ": "
                . $this->sendername . "\n"
                . $tx['mailform']['senderphone'] . ": "
                . $this->senderphone . "\n\n" . $this->mailform;
            $sent = $this->sendMail(
                $cf['mailform']['email'],
                $tx['menu']['mailform'] . ' ' . sv('SERVER_NAME'), $body,
                "From: " . $this->sender . "\r\n"
                . "X-Remote: " . sv('REMOTE_ADDR') . "\r\n"
            );
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
     * @global array The configuration of the core.
     * @global array The localization of the core.
     *
     * @access protected
     */
    function render()
    {
        global $cf, $tx;

        $random = rand(10000, 99999);

        $o .= '<form action="' . $sn . '" method="post">' . "\n";
        $o .= tag('input type="hidden" name="function" value="mailform"') . "\n";
        if (isset($cf['mailform']['captcha'])
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $o .= tag('input type="hidden" name="getlast" value="' . $random . '"')
                . "\n";
        }
        $o .= tag('input type="hidden" name="action" value="send"') . "\n";

        // fields before textarea
        $o .= '<div>' . "\n" . $tx['mailform']['sendername'].': ' . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="sendername" value="'
                . htmlspecialchars($this->sendername, ENT_COMPAT, 'UTF-8').'"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" . $tx['mailform']['senderphone'].': ' . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35"name="senderphone" value="'
                . htmlspecialchars($this->senderphone, ENT_COMPAT, 'UTF-8').'"'
            ) . "\n"
            . '</div>' . "\n"
            . '<div>' . "\n" .  $tx['mailform']['sender'].': ' . tag('br') . "\n"
            . tag(
                'input type="text" class="text" size="35" name="sender" value="'
                . htmlspecialchars($this->sender, ENT_COMPAT, 'UTF-8').'"'
            ) . "\n"
            . '</div>' . "\n" . tag('br') . "\n";

        // textarea
        $o .= '<textarea rows="12" cols="40" name="mailform">' . "\n";
        $o .= htmlspecialchars($this->mailform, ENT_COMPAT, 'UTF-8') . "\n";
        $o .= '</textarea>' . "\n";

        // captcha
        if (isset($cf['mailform']['captcha'])
            && trim($cf['mailform']['captcha']) == 'true'
        ) {
            $o .= '<p>' .  $tx['mailform']['captcha'] . '</p>' . "\n"
                .  tag('input type="text" name="cap" class="captchainput"') . "\n"
                .  '<span class="captcha_code">' . "\n"
                .  $random . '</span>' . "\n";
        }

        // send button
        $o .= '<div style="clear: both;">' . "\n"
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
     * For simplicity we are not aiming to validate according to RFC 5322,
     * but rather to make a minimal check, if the email address <i>may</i> be valid.
     * Furthermore, we make sure, that email header injection is not possible.
     *
     * @param string $address An email address.
     *
     * @return bool
     *
     * @access protected
     */
    function isValidEmail($address)
    {
        return !preg_match('/[^\x00-\x7F]/', $address)
            && preg_match('!^[^\r\n]+@[^\s]+$!', $address);
    }

}

?>
