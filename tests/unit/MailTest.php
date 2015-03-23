<?php

/**
 * Testing the mails.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2015 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

/**
 * Testing the mails.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class MailTest extends PHPUnit_Framework_TestCase
{
    protected $subject;

    protected $getHostByNameMock;

    protected $mailMock;

    public function setUp()
    {
        $this->subject = new XH_Mail();
        $this->getHostByNameMock = new PHPUnit_Extensions_MockFunction(
            'gethostbyname', $this->subject
        );
        $this->mailMock = new PHPUnit_Extensions_MockFunction(
            'mail', $this->subject
        );
    }

    /**
     * @dataProvider dataForAddressValidity
     */
    public function testAddressValidity($address, $ip, $expected)
    {
        $this->getHostByNameMock
            ->expects($this->any())
            ->will($this->returnValue($ip));
        $actual = $this->subject->isValidAddress($address);
        $this->assertEquals($expected, $actual);
    }

    public function dataForAddressValidity()
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
     * @dataProvider dataForSubjectIsProperlyEncoded
     */
    public function testSubjectIsProperlyEncoded($subject, $expected)
    {
        $this->subject->setSubject($subject);
        $this->assertEquals($expected, $this->subject->getSubject());
    }

    public function dataForSubjectIsProperlyEncoded()
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

    public function testNoMailSentWhenSubjectMissing()
    {
        $this->mailMock->expects($this->never());
        $this->subject->setTo('devs@cmsimple-xh.org');
        $this->subject->setMessage('bar');
        $this->assertFalse($this->subject->send());
    }

    public function testSendCallsMailOnce()
    {
        $this->mailMock->expects($this->once());
        $this->subject->addHeader('From', 'cmbecker69@gmx.de');
        $this->subject->setTo('devs@cmsimple-xh.org');
        $this->subject->setSubject('foo');
        $this->subject->setMessage('bar');
        $this->subject->send();
    }
}

?>
