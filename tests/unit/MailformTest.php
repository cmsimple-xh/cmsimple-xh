<?php

/**
 * Testing the mailform.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

require_once './cmsimple/functions.php';

/**
 * The file under test.
 */
require_once './cmsimple/classes/Mailform.php';

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

        $cf = array(
            'mailform' => array(
                'captcha' => 'true',
                'email' => 'devs@cmsimple-xh.org'
            )
        );
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

    public function testSubmitSendsMailForValidInput()
    {
        $mailform = $this->getMock('XH_Mailform', array('check', 'sendMail'));
        $mailform->expects($this->once())->method('check')
            ->will($this->returnValue(true));
        $mailform->expects($this->once())->method('sendMail')
            ->will($this->returnValue(true));
        $this->assertTrue($mailform->submit());
    }

    public function testMailFailureIsLogged()
    {
        $mailform = $this->getMock('XH_Mailform', array('check', 'sendMail'));
        $mailform->expects($this->once())->method('check')
            ->will($this->returnValue(true));
        $mailform->expects($this->once())->method('sendMail')
            ->will($this->returnValue(false));
        $logMessageSpy = new PHPUnit_Extensions_MockFunction(
            'XH_logMessage', $mailform
        );
        $logMessageSpy->expects($this->once());
        $this->assertFalse($mailform->submit());
    }

    public function testSubmitReturnsFalseForInvalidInput()
    {
        $mailform = $this->getMock('XH_Mailform', array('check'));
        $mailform->expects($this->once())->method('check')
            ->will($this->returnValue(false));
        $this->assertFalse($mailform->submit());
    }

    public function testDefaultActionRendersMailform()
    {
        global $action;

        $action = '';
        $mailform = $this->getMock('XH_Mailform', array('render'));
        $mailform->expects($this->once())->method('render');
        $mailform->process();
    }

    public function testSendActionSubmitsMailform()
    {
        global $action;

        $action = 'send';
        $mailform = $this->getMock('XH_Mailform', array('submit'));
        $mailform->expects($this->once())->method('submit')
            ->will($this->returnValue(true));
        $mailform->process();
    }

    public function testSendActionRendersMailformOnFailedSubmission()
    {
        global $action;

        $action = 'send';
        $mailform = $this->getMock('XH_Mailform', array('submit', 'render'));
        $mailform->expects($this->once())->method('submit')
            ->will($this->returnValue(false));
        $mailform->expects($this->once())->method('render');
        $mailform->process();
    }

    public function testSendMailCallsMailOnce()
    {
        $mailform = new XH_Mailform();
        $mailSpy = new PHPUnit_Extensions_MockFunction('mail', $mailform);
        $mailSpy->expects($this->once());
        $mailform->sendMail('devs@cmsimple-xh.org');
    }

    public function dataForIsValidEmail()
    {
        return array(
            array('post@example.com', '127.0.0.1', true),
            array('post.master@example.com', '127.0.0.1', true),
            array('post-master@example.com', '127.0.0.1', true),
            array('post,master@example.com', '127.0.0.1', false),
            array('post@master@example.com', '127.0.0.1', false),
            array("me@\xC3\xA4rger.de", '127.0.0.1', true),
            array("hacker\r\n\r\n@example.com", '127.0.0.1', false),
            array("j\xC3\xBCrgen@example.com", '127.0.0.1', false),
            array('foo@bar.invalid', 'bar.invalid', false)
        );
    }

    /**
     * @dataProvider dataForIsValidEmail
     */
    public function testIsValidEmail($address, $ip, $expected)
    {
        $mailform = new XH_Mailform();
        $getHostByNameStub = new PHPUnit_Extensions_MockFunction(
            'gethostbyname', $mailform
        );
        $getHostByNameStub->expects($this->any())
            ->will($this->returnValue($ip));
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
