<?php

/**
 * Testing the functions in functions.php.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2019 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use PHPUnit_Framework_Error_Warning as Warning;

/**
 * A helper to test multiple evaluation of a function with side effects.
 */
eval(<<<EOS
function counter()
{
    static \$count = 0;

    return ++\$count;
}
EOS
);

/**
 * A test case for the functions in functions.php.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class ScriptEvaluationTest extends TestCase
{
    protected function setUp()
    {
        global $tx, $var;

        $tx['error']['plugincall'] = 'Function %s() is not defined!';
        $var = 'baz';
    }

    public function testNestedScriptingIsNotEvaluated()
    {
        $oldErrorReporting = error_reporting(0); // suppress warning
        evaluate_scripting('{{{PLUGIN:trim(\'#CMSimple die("failed");#\');}}}');
        error_reporting($oldErrorReporting);
    }

    public function testNestedScriptingTriggersWarning()
    {
        $this->expectException(Warning::class);
        evaluate_scripting('{{{PLUGIN:trim(\'#CMSimple die("failed");#\');}}}');
    }

    /**
     * @dataProvider dataForTestEvaluateCmsimpleScripting
     */
    public function testEvaluateCmsimpleScripting($str, $compat, $expected)
    {
        $actual = evaluate_cmsimple_scripting($str, $compat);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @todo add more tests
     */
    public function dataForTestEvaluateCmsimpleScripting()
    {
        return array(
            array('foo bar', true, 'foo bar'),
            array('foo #CMSimple $output .= \'baz\';# bar', true, 'foo  barbaz'),
            array('foo #CMSimple $output .= $var;# bar', true, 'foo  barbaz'),
            array('foo #CMSimple hide# bar', true, 'foo #CMSimple hide# bar')
        );
    }

    public function testEvaluateCmsimpleScriptingNoKeywords()
    {
        $str = 'foo #CMSimple $keywords = \'foo, bar\';# bar';
        $expected = 'foo  bar';
        $actual = evaluate_cmsimple_scripting($str, true);
        $this->assertEquals($expected, $actual);
        $this->assertArrayNotHasKey('keywords', $GLOBALS);
    }

    public function testEvaluateCmsimpleScriptingParseError()
    {
        if (PHP_MAJOR_VERSION !== 5) {
            $this->markTestSkipped();
        }
        $this->expectOutputRegex('/^\s*Parse error:/s');
        evaluate_cmsimple_scripting('#CMSimple trim(\');#');
    }

    /**
     * @dataProvider dataForSpliceString
     */
    public function testSpliceString($string, $offset, $length, $replacement, $expectedResult, $expectedString)
    {
        $actual = XH_spliceString($string, $offset, $length, $replacement);
        $this->assertEquals($expectedResult, $actual);
        $this->assertEquals($expectedString, $string);
    }

    public function dataForSpliceString()
    {
        return array(
            array('foobarbaz', 3, 3, 'test', 'bar', 'footestbaz'),
            array('foobarbaz', 3, 3, '', 'bar', 'foobaz'),
            array('foobaz', 3, 0, 'bar', '', 'foobarbaz')
        );
    }

    /**
     * @dataProvider dataForTestEvaluatePluginCall
     */
    public function testEvaluatePluginCall($str, $expected)
    {
        $actual = evaluate_plugincall($str);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestEvaluatePluginCall()
    {
        return array(
            array('foo bar','foo bar'),
            array('foo {{{PLUGIN:trim(\'baz\');}}} bar', 'foo baz bar'),
            array('foo {{{PLUGIN:trim(trim(\'baz\'));}}} bar', 'foo baz bar'),
            array('foo {{{PLUGIN:trim($var);}}} bar', 'foo baz bar'),
            array( // evaluation of plugin calls in order of their appearance
                'foo {{{PLUGIN:counter();}}} bar {{{PLUGIN:counter();}}} baz',
                'foo 1 bar 2 baz'
            ),
            array( // function does not exist
                'foo {{{PLUGIN:doesnotexist();}}} bar',
                'foo <span class="xh_fail">Function doesnotexist() is not defined!</span> bar'
            ),
            array('foo {{{PLUGIN:trim(\':\');}}} bar', 'foo : bar'),
            array( // without trailing semicolon
                'foo {{{trim(\'baz\');}}} bar', 'foo baz bar'
            ),
            array( // with whitespace before the opening parenthesis
                'foo {{{trim(\'baz\');}}} bar', 'foo baz bar'
            ),
            array( // without parentheses
                'foo {{{trim \'baz\';}}} bar', 'foo baz bar'
            )
        );
    }

    public function testEvaluatePluginCallKeywords()
    {
        $str = 'foo {{{PLUGIN:sscanf(\'baz\', \'%s\', $keywords);}}} bar';
        $expected = 'foo 1 bar';
        $actual = evaluate_plugincall($str, true);
        $this->assertEquals($expected, $actual);
        $this->assertFalse(isset($GLOBALS['keywords']));
    }

    public function testEvaluatePluginCallParseError()
    {
        if (PHP_MAJOR_VERSION !== 5) {
            $this->markTestSkipped();
        }
        $this->expectOutputRegex('/^\s*Parse error:/s');
        evaluate_plugincall('{{{trim(\')}}}');
    }
}
