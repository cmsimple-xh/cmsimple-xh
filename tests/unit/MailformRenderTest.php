<?php

/**
 * Testing the mailform view.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Extensions_MockFunction;

class MailformRenderTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        $this->subject = new Mailform();
        $tagStub = new PHPUnit_Extensions_MockFunction('tag', $this->subject);
        $tagStub->expects($this->any())->will($this->returnCallback(
            function ($string) {
                return "<$string>";
            }
        ));
    }

    public function testForm()
    {
        $this->assertMatches(
            array(
                'tag' => 'form',
                'attributes' => array(
                    'class' => 'xh_mailform',
                    'method' => 'post'
                )
            )
        );
    }

    public function testFunctionInput()
    {
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'hidden',
                    'name' => 'function',
                    'value' => 'mailform'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testNoFunctionInputInEmbeddedMailform()
    {
        $this->subject = new Mailform(true);
        $this->assertNotMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'name' => 'function',
                    'value' => 'mailform'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testActionInput()
    {
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'hidden',
                    'name' => 'action',
                    'value' => 'send'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testSendernameInput()
    {
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'text',
                    'name' => 'sendername',
                    'class' => 'text'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testSenderphoneInput()
    {
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'tel',
                    'name' => 'senderphone',
                    'class' => 'text'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testSenderInput()
    {
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'email',
                    'name' => 'sender',
                    'class' => 'text',
                    'required' => 'required'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testSubjectInput()
    {
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'text',
                    'name' => 'subject',
                    'class' => 'text',
                    'required' => 'required'
                ),
                'ancestor' => array('tag' => 'form')
            )
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
        $this->assertMatches(
            array(
                'tag' => 'textarea',
                'attributes' => array(
                    'name' => $name,
                    'required' => 'required'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testSubmit()
    {
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'submit',
                    'class' => 'submit'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testCaptcha()
    {
        global $cf;

        $cf['mailform']['captcha'] = 'true';
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'hidden',
                    'name' => 'getlast'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
        $this->assertMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'type' => 'text',
                    'name' => 'cap',
                    'class' => 'xh_captcha_input',
                    'required' => 'required'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    public function testNoCaptcha()
    {
        global $cf;

        $cf['mailform']['captcha'] = '';
        $this->assertNotMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'name' => 'getlast'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
        $this->assertNotMatches(
            array(
                'tag' => 'input',
                'attributes' => array(
                    'name' => 'cap'
                ),
                'ancestor' => array('tag' => 'form')
            )
        );
    }

    private function assertMatches($matcher)
    {
        @$this->assertTag($matcher, $this->subject->render());
    }

    private function assertNotMatches($matcher)
    {
        @$this->assertNotTag($matcher, $this->subject->render());
    }
}
