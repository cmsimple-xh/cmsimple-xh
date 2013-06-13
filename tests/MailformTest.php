<?php

/**
 * @version SVN: $Id$
 */

require '../cmsimple/classes/Mailform.php';

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
                    'mailform' => ''
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
}

?>
