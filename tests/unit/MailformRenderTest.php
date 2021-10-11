<?php

/**
 * Testing the mailform view.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

class MailformRenderTest extends TestCase
{
    private $subject;

    protected function setUp(): void
    {
        global $cf;

        $cf['mailform']['lf_only'] = '';
        $this->subject = new Mailform();
    }

    public function testForm()
    {
        $this->assertXPath(
            '//form[@class="xh_mailform" and @method="post"]',
            $this->subject->render()
        );
    }

    public function testFunctionInput()
    {
        $this->assertXPath(
            '//form//input[@type="hidden" and @name="function" and @value="mailform"]',
            $this->subject->render()
        );
    }

    public function testNoFunctionInputInEmbeddedMailform()
    {
        $this->subject = new Mailform(true);
        $this->assertNotXPath(
            '//form//input[@name="function" and @value="mailform"]',
            $this->subject->render()
        );
    }

    public function testActionInput()
    {
        $this->assertXPath(
            '//form//input[@type="hidden" and @name="action" and @value="send"]',
            $this->subject->render()
        );
    }

    public function testSendernameInput()
    {
        $this->assertXPath(
            '//form//input[@type="text" and @name="sendername" and @class="text"]',
            $this->subject->render()
        );
    }

    public function testSenderphoneInput()
    {
        $this->assertXPath(
            '//form//input[@type="tel" and @name="senderphone" and @class="text"]',
            $this->subject->render()
        );
    }

    public function testSenderInput()
    {
        $this->assertXPath(
            '//form//input[@type="email" and @name="sender" and @class="text" and @required]',
            $this->subject->render()
        );
    }

    public function testSubjectInput()
    {
        $this->assertXPath(
            '//form//input[@type="text" and @name="subject" and @class="text" and @required]',
            $this->subject->render()
        );
    }

    public function testTextareaOfNormalMailform()
    {
        $this->checkTextarea('mailform');
    }

    public function testTextareaOfEmbeddedMailform()
    {
        $this->subject = new Mailform(true);
        $this->checkTextarea('xh_mailform');
    }

    private function checkTextarea($name)
    {
        $this->assertXPath(
            sprintf('//form//textarea[@name="%s" and @required]', $name),
            $this->subject->render()
        );
    }

    public function testSubmit()
    {
        $this->assertXPath(
            '//form//input[@type="submit" and @class="submit"]',
            $this->subject->render()
        );
    }

    public function testCaptcha()
    {
        global $cf;

        $cf['mailform']['captcha'] = 'true';
        $this->assertXPath(
            '//form//input[@type="hidden" and @name="getlast"]',
            $this->subject->render()
        );
        $this->assertXPath(
            '//form//input[@type="text" and @name="cap" and @class="xh_captcha_input" and @required]',
            $this->subject->render()
        );
    }

    public function testNoCaptcha()
    {
        global $cf;

        $cf['mailform']['captcha'] = '';
        $this->assertNotXPath(
            '//form//input[@name="getlast"]',
            $this->subject->render()
        );
        $this->assertNotXPath(
            '//form//input[@name="cap"]',
            $this->subject->render()
        );
    }
}
