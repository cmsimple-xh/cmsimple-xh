<?php

/**
 * Copyright 2017-2019 The CMSimple_XH developers.
 *
 * This file is part of CMSimple_XH.
 *
 * CMSimple_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CMSimple_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CMSimple_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Xh;

class ErrorHandlerTest extends TestCase
{
    /**
     * @var int
     */
    private $errorHandling;

    protected function setUp(): void
    {
        $this->errorHandling = error_reporting();
        error_reporting(-1);
    }

    protected function tearDown(): void
    {
        error_reporting($this->errorHandling);
    }

    /**
     * @dataProvider provideDataForErrorTest
     */
    public function testError($errtype, $errname)
    {
        global $errors;

        $exitMock = $this->createFunctionMock('XH_exit');
        $exitMock->expects($this->once());
        $this->assertTrue(XH_debug($errtype, 'foo', 'bar', 42));
        $this->assertEquals("<b>$errname:</b> foo<br>bar:42<br>\n", $errors[0]);
    }

    public function provideDataForErrorTest()
    {
        return array(
            [E_RECOVERABLE_ERROR, 'ERROR'],
            [E_USER_ERROR, 'XH-ERROR']
        );
    }

    /**
     * @dataProvider provideDataForWarningTest
     */
    public function testWarning($errtype, $errname)
    {
        global $errors;

        $this->assertTrue(XH_debug($errtype, 'foo', 'bar', 42));
        $this->assertEquals("<b>$errname:</b> foo<br>bar:42<br>\n", $errors[0]);
    }

    public function provideDataForWarningTest()
    {
        return array(
            [E_USER_WARNING, 'XH-WARNING'],
            [E_USER_NOTICE, 'XH-NOTICE'],
            [E_WARNING, 'WARNING'],
            [E_NOTICE, 'NOTICE'],
            [E_STRICT, 'STRICT'],
            [E_DEPRECATED, 'DEPRECATED'],
        );
    }

    public function testUserDeprecation()
    {
        global $errors;

        $this->assertTrue(XH_debug(E_USER_DEPRECATED, 'foo', 'bar', 42));
        $this->assertStringMatchesFormat("<b>XH-DEPRECATED:</b> foo<br>%s:%d<br>\n", $errors[0]);
    }

    public function testUnknownError()
    {
        global $errors;

        $this->assertTrue(XH_debug(1 << 30, 'foo', 'bar', 42));
        $this->assertEquals("<b>Unknown error type [1073741824]:</b> foo<br>bar:42<br>\n", $errors[0]);
    }
}
