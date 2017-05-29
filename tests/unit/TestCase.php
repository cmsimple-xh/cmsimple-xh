<?php

/**
 * Our test case base class.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @param object $scopeObject
     * @return PHPUnit_Extension_MockFunction
     */
    protected function getFunctionMock($name, $scopeObject)
    {
        return new FunctionMock($name, $scopeObject);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            runkit_constant_redefine($name, $value);
        }
    }
}
