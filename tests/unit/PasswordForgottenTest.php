<?php

/**
 * Testing the password forgotten class.
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

/**
 * The file under test.
 */
require_once './cmsimple/classes/PasswordForgotten.php';

/**
 * A test case for the password forgotten class.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PasswordForgottenTest extends PHPUnit_Framework_TestCase
{
    protected $passwordForgotten;

    public function setUp()
    {
        global $cf;

        $cf = array(
            'security' => array(
                'email' => 'devs@cmsimple-xh.org',
                'secret' => '0123456789abcdef'
            )
        );
        $this->passwordForgotten = new XH_PasswordForgotten();
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
        global $cf;

        $actual = $this->passwordForgotten->mac();
        $expected = $this->currentMac();
        $this->assertEquals($expected, $actual);
    }

    public function testCheckMac()
    {
        $this->assertTrue($this->passwordForgotten->checkMac($this->currentMac()));
    }

}

?>
