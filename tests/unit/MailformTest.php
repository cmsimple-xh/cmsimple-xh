<?php

/**
 * Testing the mailform.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case for the mailform.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class MailformTest extends TestCase
{
    private $goodPost;

    protected function setUp(): void
    {
        global $cf;

        $cf = array(
            'mailform' => array(
                'captcha' => 'true',
                'email' => 'devs@cmsimple-xh.org',
                'lf_only' => ''
            )
        );
        $this->goodPost = array(
            'sendername' => 'Christoph Becker',
            'senderphone' => '123456789',
            'sender' => 'devs@cmsimple-xh.org',
            'getlast' => '12345',
            'cap' => '12345',
            'subject' => 'Mailform localhost',
            'mailform' => 'A message.'
        );
    }

    public function testCheckCorrectInput()
    {
        $_POST = $this->goodPost;
        $mail = $this->createMock(Mail::class);
        $mail->expects($this->once())->method('isValidAddress')->willReturn(true);
        $mailform = new Mailform(false, null, $mail);
        $this->assertEquals('', $mailform->check());
    }

    public function testCheckWrongCaptchaCode()
    {
        $this->checkInvalid('captchafalse', 'cap', '54321');
    }

    public function testCheckEmptyMessage()
    {
        $this->checkInvalid('mustwritemessage', 'mailform', '');
    }

    public function testCheckInvalidEmailAddress()
    {
        $this->checkInvalid('notaccepted', 'sender', 'devscmsimple-xh.org');
    }

    public function testCheckEmptySubject()
    {
        $this->checkInvalid('notaccepted', 'subject', '');
    }

    private function checkInvalid($langKey, $postKey, $postValue)
    {
        global $tx;

        $tx['mailform']['notaccepted']="oops";
        $tx['mailform'][$langKey] = 'foo bar';
        $_POST = $this->goodPost;
        $_POST[$postKey] = $postValue;
        $mail = $this->createMock(Mail::class);
        $mail->expects($this->once())->method('isValidAddress')->willReturn(false);
        $mailform = new Mailform(false, null, $mail);
        $this->assertXPathContains(
            '//p[@class="xh_warning"]',
            $tx['mailform'][$langKey],
            $mailform->check()
        );
    }

    public function testSubmitSendsMailSuccess()
    {
        $mail = $this->createMock(Mail::class);
        $mail->expects($this->once())->method('send')->willReturn(true);
        $mailform = new Mailform(false, null, $mail);
        $this->assertTrue($mailform->submit());
    }

    public function testMailFailureIsLogged()
    {
        $mail = $this->createMock(Mail::class);
        $mail->expects($this->once())->method('send')->willReturn(false);
        $mailform = new Mailform(false, null, $mail);
        $logMessageSpy = $this->createFunctionMock('XH_logMessage');
        $logMessageSpy->expects($this->once());
        $this->assertFalse($mailform->submit());
        $logMessageSpy->restore();
    }

    public function testDefaultActionRendersMailform()
    {
        global $action;

        $action = '';
        $mailform = $this->getMockBuilder(Mailform::class)->setMethods(array('render'))->getMock();
        $mailform->expects($this->once())->method('render');
        $mailform->process();
    }

    public function testSendActionSubmitsMailform()
    {
        global $action;

        $action = 'send';
        $mailform = $this->getMockBuilder(Mailform::class)->setMethods(array('check', 'submit'))->getMock();
        $mailform->expects($this->once())->method('check')
            ->willReturn('');
        $mailform->expects($this->once())->method('submit')
            ->willReturn(true);
        $mailform->process();
    }

    public function testSendActionTriggersCheck()
    {
        global $action;

        $action = 'send';
        $mailform = $this->getMockBuilder(Mailform::class)->setMethods(array('check', 'render'))->getMock();
        $mailform->expects($this->once())->method('check')
            ->willReturn('some error message');
        $mailform->method('render');
        $mailform->process();
    }

    public function testSendActionRendersMailformOnFailedSubmission()
    {
        global $action;

        $action = 'send';
        $mailform = $this->getMockBuilder(Mailform::class)->setMethods(array('check', 'submit', 'render'))->getMock();
        $mailform->expects($this->once())->method('check')
            ->willReturn('');
        $mailform->expects($this->once())->method('submit')
            ->willReturn(false);
        $mailform->expects($this->once())->method('render');
        $mailform->process();
    }

    /**
     * @see http://cmsimpleforum.com/viewtopic.php?f=29&t=7568
     */
    public function testNoEmbeddedMailformIfEmailEmpty()
    {
        global $pth, $cf;

        $pth['folder']['classes'] = './cmsimple/classes/';
        $cf['mailform']['email'] = '';
        $this->assertSame('', XH_mailform());
    }

    /**
     * @see http://cmsimpleforum.com/viewtopic.php?f=29&t=7568
     */
    public function testMailformClassCantBeProcessedTwice()
    {
        global $action;

        $action = '';
        $mailform = $this->getMockBuilder(Mailform::class)->setMethods(array('render', 'check'))->getMock();
        $mailform->method('check')
            ->willReturn('some error message');
        $mailform->process();
        $mailform = $this->getMockBuilder(Mailform::class)->setMethods(array('render', 'check'))->getMock();
        $mailform->expects($this->never())->method('check');
        $this->assertSame('', $mailform->process());
    }

    public function testSubjectIsSetFromQueryParameter()
    {
        $subject = 'Foo subject';
        $_GET['xh_mailform_subject'] = $subject;
        $mailform = new Mailform();
        // TODO: don't test for *protected* property
        $this->assertEquals($subject, $mailform->subject);
    }

    public function testSubjectIsSetFromConstructorParameter()
    {
        $subject = 'Foo subject';
        $mailform = new Mailform(true, $subject);
        // TODO: don't test for *protected* property
        $this->assertEquals($subject, $mailform->subject);
    }
}
