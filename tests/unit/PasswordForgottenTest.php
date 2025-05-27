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

use org\bovigo\vfs\vfsStream;

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
        global $pth, $cf, $tx;

        vfsStream::setup('root');
        $pth['file']['config'] = vfsStream::url('root/config.php');
        copy('./cmsimple/config.php', vfsStream::url('root/config.php'));
        $cf = XH_includeVar('./cmsimple/config.php', 'cf');
        $cf['security']['email'] = 'devs@cmsimple-xh.org';
        $cf['security']['secret'] = '0123456789abcdef';
        $tx = XH_includeVar('./cmsimple/languages/en.php', 'tx');
        $mail = $this->getMockBuilder(Mail::class)->onlyMethods(["send"])->getMock();
        $mail->expects($this->any())->method("send")->willReturn(true);
        $this->passwordForgotten = new PasswordForgotten($mail);
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

    public function testSavesNewPasswordOnResetAfterSuccessfulMailing(): void
    {
        global $cf;
        $oldPasswordHash = $cf['security']['password'];
        $_GET['xh_code'] = '6fa0cd0c8c1f0ba35d37551b2378459c';
        $this->passwordForgotten->dispatch();
        $cf = XH_includeVar(vfsStream::url('root/config.php'), 'cf');
        $this->assertNotEquals($oldPasswordHash, $cf['security']['password']);
    }
}
