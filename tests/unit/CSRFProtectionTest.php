<?php

/**
 * Testing the CSRF protection.
 *
 * If CMSimple_XH is not installed directly in the web root,
 * the environment variable CMSIMPLEDIR has to be set to the installation folder
 * (e.g. cmsimple_xh/).
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

require_once './cmsimple/functions.php';

/**
 * The file under test.
 */
require_once './cmsimple/classes/CSRFProtection.php';

const CMSIMPLE_ROOT = '/test/';

/**
 * A test case to simulate the CSRF protection.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class CSRFProtectionTest extends PHPUnit_Framework_TestCase
{
    public function testGetFollowedByPost()
    {
        $protection = new XH_CSRFProtection();
        $input = $protection->tokenInput();
        preg_match('/value="(.*)"/', $input, $matches);
        $protection->store();
        $_POST['xh_csrf_token'] = $matches[1];
        $protection->check();
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCSRFAttack()
    {
        $protection = new XH_CSRFProtection();
        $_SESSION['xh_csrf_token'][CMSIMPLE_ROOT] = '5dff45ce0e8db5e4ea2bf59cf0cb96dd';
        $_POST['xh_csrf_token'] = 'fd97a436f658ecc2178561898f8a6c9e';
        $protection->check();

    }
}

if (session_id() === '') {
    session_start();
}

?>
