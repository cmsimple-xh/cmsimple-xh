<?php

/**
 * Testing the password forgotten class.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

/**
 * A test case for the password forgotten class.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class PasswordForgottenTest extends TestCase
{
    protected $passwordForgotten;

    protected function setUp(): void
    {
        global $cf;

        $cf = array(
            'security' => array(
                'email' => 'devs@cmsimple-xh.org',
                'secret' => '0123456789abcdef'
            )
        );
        $this->passwordForgotten = new PasswordForgotten();
    }

    protected function currentMac()
    {
        global $cf;

        return md5(
            $cf['security']['email'] . strtotime(date('Y-m-d h:00:00')) . $cf['security']['secret']
        );
    }

    public function testMac()
    {
        $actual = $this->passwordForgotten->mac();
        $expected = $this->currentMac();
        $this->assertEquals($expected, $actual);
    }

    public function testCheckMac()
    {
        $this->assertTrue($this->passwordForgotten->checkMac($this->currentMac()));
    }
}
