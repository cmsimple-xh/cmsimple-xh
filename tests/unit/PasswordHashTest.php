<?php

/**
 * Testing the password hashing class.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2015-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

/**
 * Testing the password hashing class.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.7
 */
class PasswordHashTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests that a hash passes the check.
     *
     * @return void
     */
    public function testHashPassesCheck()
    {
        $subject = new XH\PasswordHash(4, false);
        $this->assertTrue(
            $subject->checkPassword('test', $subject->hashPassword('test'))
        );
    }

    /**
     * Tests that a portable hash passes the check.
     *
     * @return void
     */
    public function testPortableHashPassesCheck()
    {
        $subject = new XH\PasswordHash(4, true);
        $this->assertTrue(
            $subject->checkPassword('test', $subject->hashPassword('test'))
        );
    }

    /**
     * Tests that random bytes are random.
     *
     * Actually, this test is merely wishful thinking.
     *
     * @return void
     */
    public function testRandomBytesAreRandom()
    {
        $subject = new XH\PasswordHash(4, true);
        $this->assertNotEquals(
            $subject->getRandomBytes(2), $subject->getRandomBytes(2)
        );
    }
}

?>
