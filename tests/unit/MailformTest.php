<?php

/**
 * Testing the mailform.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './cmsimple/functions.php';

/**
 * The file under test.
 */
require './cmsimple/classes/Mailform.php';

/**
 * A test case for the mailform.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class MailformTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $cf;

        $cf = array('mailform' => array('captcha' => 'true'));
    }

    public function dataForCheck()
    {
        return array(
            // okay
            array(
                array(
                    'sendername' => 'Christoph Becker',
                    'senderphone' => '123456789',
                    'sender' => 'cmbecker69@gmx.de',
                    'getlast' => '12345',
                    'cap' => '12345',
                    'subject' => 'Mailform localhost',
                    'mailform' => 'A message.'
                ),
                true
            ),
            // wrong CAPTCHA code
            array(
                array(
                    'sendername' => 'Christoph Becker',
                    'senderphone' => '123456789',
                    'sender' => 'cmbecker69@gmx.de',
                    'getlast' => '12345',
                    'cap' => '54321',
                    'subject' => 'Mailform localhost',
                    'mailform' => 'A message.'
                ),
                false
            ),
            // empty message
            array(
                array(
                    'sendername' => 'Christoph Becker',
                    'senderphone' => '123456789',
                    'sender' => 'cmbecker69@gmx.de',
                    'getlast' => '12345',
                    'cap' => '12345',
                    'subject' => 'Mailform localhost',
                    'mailform' => ''
                ),
                false
            ),
            // invalid email address
            array(
                array(
                    'sendername' => 'Christoph Becker',
                    'senderphone' => '123456789',
                    'sender' => 'cmbecker69gmx.de',
                    'getlast' => '12345',
                    'cap' => '12345',
                    'subject' => 'Mailform localhost',
                    'mailform' => ''
                ),
                false
            ),
            // empty subject
            array(
                array(
                    'sendername' => 'Christoph Becker',
                    'senderphone' => '123456789',
                    'sender' => 'cmbecker69@gmx.de',
                    'getlast' => '12345',
                    'cap' => '12345',
                    'subject' => '',
                    'mailform' => 'A message.'
                ),
                false
            )
        );
    }

    /**
     * @dataProvider dataForCheck
     */
    public function testCheck($post, $expected)
    {
        $_POST = $post;
        $mailform = new XH_Mailform();
        $actual = $mailform->check();
        $this->assertEquals($expected, $actual);
    }

    public function dataForIsValidEmail()
    {
        return array(
            array('post@example.com', true),
            array('post.master@example.com', true),
            array('post-master@example.com', true),
            array('post,master@example.com', false),
            array('post@master@example.com', false),
            array("me@\xC3\xA4rger.de", function_exists('idn_to_ascii')),
            array("hacker\r\n\r\n@example.com", false),
            array("j\xC3\xBCrgen@example.com", false)
        );
    }

    /**
     * @dataProvider dataForIsValidEmail
     */
    public function testIsValidEmail($address, $expected)
    {
        $mailform = new XH_Mailform();
        $actual = $mailform->isValidEmail($address);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestEncodeMIMEFieldBody()
    {
        return array(
            array('foo bar', 'foo bar'),
            array(str_repeat('foo bar ', 20), str_repeat('foo bar ', 20)),
            array("f\xC3\xB6o", '=?UTF-8?B?ZsO2bw==?='),
            array(
                str_repeat("\xC3\xA4\xC3\xB6\xC3\xBC", 10),
                "=?UTF-8?B?w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6TDtsO8w6Q=?="
                . "\r\n =?UTF-8?B?w7bDvMOkw7bDvMOkw7bDvA==?="
            )
        );
    }

    /**
     * @dataProvider dataForTestEncodeMIMEFieldBody
     */
    public function testEncodeMIMEFieldBody($str, $expected)
    {
        $mailform = new XH_Mailform();
        $actual = $mailform->encodeMIMEFieldBody($str);
        $this->assertEquals($expected, $actual);
    }
}

?>
