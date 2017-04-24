<?php

/**
 * Emails.
 *
 * @category  CMSimple_XH
 * @package   XH
 * @author    Peter Harteg <peter@harteg.dk>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 1999-2009 Peter Harteg
 * @copyright 2009-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

/**
 * Emails.
 *
 * @category CMSimple_XH
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class Mail
{
    /**
     * The To address.
     *
     * @var string
     */
    private $to;

    /**
     * The Subject.
     *
     * @var string
     */
    private $subject;

    /**
     * The message body.
     *
     * @var string
     */
    private $message;

    /**
     * The headers.
     *
     * @var array
     */
    private $headers;

    /**
     * The line ending character(s).
     *
     * @var string
     */
    private $lineEnding;

    /**
     * Initializes a new instance.
     *
     * @global array The configuration of the core.
     */
    public function __construct()
    {
        global $cf;

        $this->headers = array(
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed',
            'Content-Transfer-Encoding' => 'base64'
        );
        $this->lineEnding = $cf['mailform']['lf_only'] ? "\n" : "\r\n";
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
     */
    public function isValidAddress($address)
    {
        $atext = '[!#-\'*+\-\/-9=?A-Z^-~]';
        $dotAtomText = $atext . '(?:' . $atext . '|\.)*';
        $pattern = '/^(' . $dotAtomText . ')@([^@]+)$/u';
        if (!preg_match($pattern, $address, $matches)) {
            return false;
        }
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

    /**
     * Sets the To address.
     *
     * @param string $to A valid email address.
     *
     * @return void
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * Returns the encoded subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the Subject.
     *
     * @param string $subject A subject.
     *
     * @return void
     */
    public function setSubject($subject)
    {
        $this->subject = $this->encodeMIMEFieldBody($subject);
    }

    /**
     * Sets the message.
     *
     * @param string $message A message.
     *
     * @return void
     */
    public function setMessage($message)
    {
        $message = preg_replace('/\r\n|\r|\n/', $this->lineEnding, trim($message));
        $message = chunk_split(base64_encode($message));
        $this->message = $message;
    }

    /**
     * Adds a header field.
     *
     * @param string $name  A header field name.
     * @param string $value A header field value.
     *
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Returns the body of an email header field as "encoded word" (RFC 2047)
     * with "folding" (RFC 5322), if necessary.
     *
     * @param string $text The body of the MIME field.
     *
     * @return string
     *
     * @todo Don't we have to fold overlong pure ASCII texts also?
     */
    private function encodeMIMEFieldBody($text)
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
            $func = function ($line) {
                return '=?UTF-8?B?' . base64_encode($line) . '?=';
            };
            return implode($this->lineEnding . ' ', array_map($func, $lines));
        }
    }

    /**
     * Returns the header string.
     *
     * @return string
     */
    private function getHeaderString()
    {
        $string = '';
        foreach ($this->headers as $name => $value) {
            $string .= $name . ': ' . $value . $this->lineEnding;
        }
        return $string;
    }

    /**
     * Sends the email and return whether that succeeded.
     *
     * @return bool
     */
    public function send()
    {
        if (!isset($this->to, $this->subject, $this->message)) {
            return false;
        } else {
            return mail($this->to, $this->subject, $this->message, $this->getHeaderString());
        }
    }
}
