<?php

/**
 * @version SVN: $Id$
 */

require_once '../cmsimple/classes/CSRFProtection.php';

class CSRFProtectionTest extends PHPUnit_Framework_TestCase
{
    public function testGetFollowedByPost()
    {
        $protection = new XH_CSRFProtection();
        $protection->tokenInput();
        $protection->store();

        $_POST['xh_csrf_token'] = $protection->token;
        $protection->check();
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testCSRFAttack()
    {
        $protection = new XH_CSRFProtection();
        $_SESSION['xh_csrf_token'] = '5dff45ce0e8db5e4ea2bf59cf0cb96dd';
        $_POST['xh_csrf_token'] = 'fd97a436f658ecc2178561898f8a6c9e';
        $protection->check();

    }
}
