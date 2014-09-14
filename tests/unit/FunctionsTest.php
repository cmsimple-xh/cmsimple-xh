<?php

/**
 * Testing the functions in functions.php.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2014 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

/**
 * The file under test.
 */
require_once './cmsimple/functions.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * A helper to test multiple evaluation of a function with side effects.
 */
function counter()
{
    static $count = 0;

    return ++$count;
}

/**
 * A test case for the functions in functions.php.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class FunctionsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $var, $cf, $tx;

        include './cmsimple/config.php';
        include './cmsimple/languages/en.php';
        $_SERVER['SERVER_NAME'] = 'example.com';
        $var = 'baz';
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testAutogalleryIsDeprecated()
    {
        autogallery('');
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

    /**
     * @dataProvider dataForTestEvaluateCmsimpleScripting
     */
    public function testEvaluateCmsimpleScripting($str, $compat, $expected)
    {
        $actual = evaluate_cmsimple_scripting($str, $compat);
        $this->assertEquals($expected, $actual);
    }

    public function testEvaluateCmsimpleScriptingKeywords()
    {
        $str = 'foo #CMSimple $keywords = \'foo, bar\';# bar';
        $expected = 'foo  bar';
        $actual = evaluate_cmsimple_scripting($str, true);
        $this->assertEquals($expected, $actual);
        $this->assertEquals('foo, bar', $GLOBALS['keywords']);
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
     * @dataProvider dataForSpliceString
     */
    public function testSpliceString($string, $offset, $length, $replacement, $expectedResult, $expectedString)
    {
        $actual = XH_spliceString($string,$offset, $length, $replacement);
        $this->assertEquals($expectedResult, $actual);
        $this->assertEquals($expectedString, $string);
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
            array('foo {{{PLUGIN:trim(\':\');}}} bar', 'foo : bar')
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

    public function testEvaluatePluginCallKeywords()
    {
        $str = 'foo {{{PLUGIN:sscanf(\'baz\', \'%s\', $keywords);}}} bar';
        $expected = 'foo 1 bar';
        $actual = evaluate_plugincall($str, true);
        $this->assertEquals($expected, $actual);
        $this->assertFalse(isset($GLOBALS['keywords']));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testAmpIsDeprecated()
    {
        amp();
    }

    public function dataForTestAmp()
    {
        return array(
            array('true', '&amp;'),
            array('', '&')
        );
    }

    /**
     * @dataProvider dataForTestAmp()
     */
    public function testAmp($xhtmlAmp, $expected)
    {
        global $cf;

        $cf['xhtml']['amp'] = $xhtmlAmp;
        $actual = @amp(); // suppress deprecated warning
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestRmanl()
    {
        return array(
            array("\r\nFoo\r\n\n\rBar\rBaz\n", "FooBarBaz"),
            array('Foo Bar', 'Foo Bar')
        );
    }

    /**
     * @dataProvider dataForTestRmanl
     */
    public function testRmanl($str, $expected)
    {
        $actual = rmanl($str);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestRmws()
    {
        return array(
            array('  Foo  Bar ', ' Foo Bar '),
            array("\t Foo \t Bar \t", ' Foo Bar ')
        );
    }

    /**
     * @dataProvider dataForTestRmws
     */
    public function testRmws($str, $expected)
    {
        $actual = xh_rmws($str);
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestRmnl()
    {
        return array(
            array("\r\nFoo\r\n\n\rBar\rBaz\n", "\nFoo\nBar\nBaz\n"),
            array('Foo Bar', 'Foo Bar')
        );
    }

    /**
     * @dataProvider dataForTestRmnl
     */
    public function testRmnl($str, $expected)
    {
        $actual = rmnl($str);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testRpIsDeprecated()
    {
        rp('');
    }

    public function dataForTestRp()
    {
        return array(
            array('./tests/unit/FunctionsTest.php', __FILE__),
            array('./DoesNotExist', './DoesNotExist')
        );
    }

    /**
     * @dataProvider dataForTestRp
     */
    public function testRp($filename, $expected)
    {
        $actual = @rp($filename); // suppress deprecated warning
        $this->assertEquals($expected, $actual);
    }

    public function dataForTestSv()
    {
        return array(
            array('', ''),
            array('SERVER_NAME', 'example.com')
        );
    }

    /**
     * @dataProvider dataForTestSv
     */
    public function testSv($key, $expected)
    {
        $actual = sv($key);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testChkdlIsDeprecated()
    {
        chkdl('dummy');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testRfIsDeprecated()
    {
        rf('dummy');
    }

    public function dataForTestTag()
    {
        return array(
            array('', 'br', '<br>'),
            array('true', 'br', '<br />')
        );
    }

    /**
     * @dataProvider dataForTestTag
     */
    public function testTag($xhtmlEndtags, $str, $expected)
    {
        global $cf;

        $cf['xhtml']['endtags'] = $xhtmlEndtags;
        $actual = tag($str);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testWritelogIsDeprecated()
    {
        writelog('foo');
    }

    public function testLogMessage()
    {
        global $pth;

        $filename = './tests/unit/data/log.txt';
        if (file_exists($filename)) {
            unlink($filename);
        }
        $pth['file']['log'] = $filename;
        XH_logMessage('info', 'XH', 'test', 'testing');
        $string = file_get_contents($filename);
        $suffix = "info\tXH\ttest\ttesting" . PHP_EOL;
        $this->assertStringEndsWith($suffix, $string);
        unlink($filename);
    }

    public function testWriteFile()
    {
        $filename = './tests/unit/data/temp';
        $expected = 'foo';
        XH_writeFile($filename, $expected);
        $actual = file_get_contents($filename);
        $this->assertEquals($expected, $actual);
        $expected = 'bar';
        XH_writeFile($filename, $expected);
        $actual = file_get_contents($filename);
        $this->assertEquals($expected, $actual);
        unlink($filename);
    }

    public function dataForTestAdjustStylesheetURLs()
    {
        return array(
            array(
                'pagemanager',
                '#pagemanager {background-url: url(images/bg.jpg)}',
                '#pagemanager {background-url: url(../../plugins/pagemanager/css/'
                . 'images/bg.jpg)}'
            ),
            array(
                'filebrowser',
                '.filebrowser {background-url: url("http://www.example.com/img.png")',
                '.filebrowser {background-url: url("http://www.example.com/img.png")'
            ),
            array(
                'plugin',
                'div {whatever: url( \'/images/anim.gif\' )}',
                'div {whatever: url( \'/images/anim.gif\' )}'
            ),
            array(
                'test',
                'body {background: url("./images/bg.jpg)}',
                'body {background: url("../../plugins/test/css/./images/bg.jpg)}'
            ),
            array( // invalid UTF-8
                'test',
                "body {background: url(\"./doppelg\xE4nger.jpg\")}",
                "body {background: url(\"../../plugins/test/css/./doppelg\xE4nger.jpg\")}"
            )
        );
    }

    /**
     * @dataProvider dataForTestAdjustStylesheetURLs
     */
    public function testAdjustStylesheetURLs($plugin, $css, $expected)
    {
        $actual = XH_adjustStylesheetURLs($plugin, $css);
        $this->assertEquals($expected, $actual);
    }

    public function testMeta()
    {
        $matcher = array(
            'tag' => 'meta',
            'attributes' => array('name' => 'robots', 'content' => 'index, follow')
        );
        $actual = meta('robots');
        @$this->assertTag($matcher, $actual);
    }

    /**
     * @dataProvider dataForUenc
     */
    public function testUenc($uricharSep, $uricharOrg, $uricharNew, $wordSep, $expected)
    {
        global $cf, $tx;

        if (!defined('XH_URICHAR_SEPARATOR')) {
            define('XH_URICHAR_SEPARATOR', $uricharSep);
        } else {
            runkit_constant_redefine('XH_URICHAR_SEPARATOR', $uricharSep);
        }
        $cf['uri']['word_separator'] = $wordSep;
        $tx['urichar']['org'] = $uricharOrg;
        $tx['urichar']['new'] = $uricharNew;
        $this->assertEquals($expected, uenc("\xC3\x9Cber uns"));
    }

    public function dataForUenc()
    {
        return array(
            array('|', "\xC3\x84|\xC3\x96|\xC3\x9C", 'Ae|Oe|Ue', '_', 'Ueber_uns'),
            array(',', "\xC3\x84,\xC3\x96,\xC3\x9C", 'Ae,Oe,Ue', '-', 'Ueber-uns')
        );
    }

    public function testSecondLanguages()
    {
        global $pth;

        $expected = array('de', 'fr');
        $pth['folder']['base'] = './tests/unit/data/';
        $actual = XH_secondLanguages();
        $this->assertEquals($expected, $actual);
    }

    public function dataForIsInternalPath()
    {
        return array(
            array('./', 'en', true),
            array('./fr', 'en', true),
            array('../fr/', 'en', false),
            array('index.php', 'en', true),
            array('./', 'de', true),
            array('./fr', 'de', false),
            array('../fr/', 'de', true),
            array('index.php', 'de', true)
        );
    }

    /**
     * @dataProvider dataForIsInternalPath()
     */
    public function testIsInternalPath($path, $language, $expected)
    {
        global $sl;

        $sl = $language;
        $actual = XH_isInternalPath($path);
        $this->assertEquals($expected, $actual);
    }

    public function dataForIsInternalUrl()
    {
        return array(
            array(
                array(
                    'scheme' => 'http',
                    'host' => 'www.cmsimple-xh.org',
                    'path' => '/'
                ),
                false
            ),
            array( // test for <http://cmsimpleforum.com/viewtopic.php?f=10&t=8053>
                array(
                    'scheme' => 'http',
                    'host' => 'www.cmsimple-xh.org'
                ),
                false
            ),
            array(array('path' =>'./', 'query' => 'Foo'), true),
            array(array('path' => './index.php', 'query' => 'Foo'), true),
            array(array('query' => 'Foo'), true),
            array(array('query' => 'Foo', 'fragment' => 'Bar'), true),
            array(array('path' => './foo.htm'), false)
        );
    }

    /**
     * @dataProvider dataForIsInternalUrl
     */
    public function testIsInternalUrl($url, $expected)
    {
        $actual = XH_isInternalUrl($url);
        $this->assertEquals($expected, $actual);
    }

    public function dataForConvertPrintUrls()
    {
        return array(
            array(
                '<a href="">This Website</a> is powered by <a href="cmsimple-xh.org">CMSimple_XH</a>',
                '<a href="?print">This Website</a> is powered by <a href="cmsimple-xh.org">CMSimple_XH</a>'
            ),
            array(
                '<a href="?Welcome">This Website</a> is powered by <a href="cmsimple-xh.org">CMSimple_XH</a>',
                '<a href="?Welcome&amp;print">This Website</a> is powered by <a href="cmsimple-xh.org">CMSimple_XH</a>'
            )
        );
    }

    /**
     * @dataProvider dataForConvertPrintUrls
     */
    public function testConvertPrintUrls($pageContent, $expected)
    {
        $actual = XH_convertPrintUrls($pageContent);
        $this->assertEquals($expected, $actual);
    }

    public function dataForHsc()
    {
        return array(
            array("Fahrvergn\xC3\xBCgen", "Fahrvergn\xC3\xBCgen"),
            array('<foo> & "bar"', '&lt;foo&gt; &amp; &quot;bar&quot;'),
            array("Fahrverg\xFCgen", "Fahrverg\xEF\xBF\xBDgen")
        );
    }

    /**
     * @dataProvider dataForHsc
     */
    public function testHsc($string, $expected)
    {
        $actual = XH_hsc($string);
        $this->assertEquals($expected, $actual);
    }

    public function testIncludeVar()
    {
        $filename = './cmsimple/languages/de.php';
        $tx = XH_includeVar($filename, 'tx');
        $this->assertTrue(is_array($tx));
    }

    public function dataForNumberSuffix()
    {
        return array(
            array(0, '_5'),
            array(1, '_1'),
            array(2, '_2_4'),
            array(3, '_2_4'),
            array(4, '_2_4'),
            array(5, '_5')
        );
    }

    /**
     * @dataProvider dataForNumberSuffix
     */
    public function testNumberSuffix($count, $expected)
    {
        $actual = XH_numberSuffix($count);
        $this->assertEquals($expected, $actual);
    }

    public function dataForReadConfiguration()
    {
        return array(
            array('cmsimple', 'defaultconfig.php', 'config', 'cf'),
            array('language', 'default.php', 'language', 'tx', false, true),
            array('plugin_config', 'defaultconfig.php', 'plugin_config', 'plugin_cf', true),
            array('plugin_languages', 'default.php', 'plugin_language', 'plugin_tx', true, true)
        );
    }

    /**
     * @dataProvider dataForReadConfiguration()
     */
    public function testReadConfiguration($folderKey, $filename, $fileKey, $varname, $plugin = false, $language = false)
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));

        $config = array(
            'foo' => array('a' => 'b', 'c' => 'c'),
            'bar' => array('a' => 'b', 'c' => 'c')
        );
        $config = var_export($config, true);
        $contents = "<?php \$$varname = $config;?>";
        $filename = vfsStream::url("test/$filename");
        file_put_contents($filename, $contents);

        $config = array(
            'foo' => array('a' => 'a', 'b' => 'b'),
            'baz' => array('a' => 'b', 'c' => 'c')
        );
        $config = var_export($config, true);
        $contents = "<?php \$$varname = $config;?>";
        $filename = vfsStream::url('test/test.php');
        file_put_contents($filename, $contents);

        $pth['folder'][$folderKey] = vfsStream::url('test/');
        $pth['file'][$fileKey] = vfsStream::url('test/test.php');

        $expected = array(
            'foo' => array('a' => 'a', 'b' => 'b', 'c' => 'c'),
            'bar' => array('a' => 'b', 'c' => 'c'),
            'baz' => array('a' => 'b', 'c' => 'c')
        );
        $actual = XH_readConfiguration($plugin, $language);
        $this->assertEquals($expected, $actual);
    }


    /**
     * Tests reading an empty configuration/language file, where a default
     * configuration/language file is there.
     *
     * @dataProvider dataForReadConfiguration()
     */
    public function testReadEmptyConfiguration($folderKey, $filename, $fileKey, $varname, $plugin = false, $language = false)
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));

        $config = array(
            'foo' => array('a' => 'b', 'c' => 'c'),
            'bar' => array('a' => 'b', 'c' => 'c')
        );
        $config = var_export($config, true);
        $contents = "<?php \$$varname = $config;?>";
        $filename = vfsStream::url("test/$filename");
        file_put_contents($filename, $contents);

        $contents = "<?php ?>";
        $filename = vfsStream::url('test/test.php');
        file_put_contents($filename, $contents);

        $pth['folder'][$folderKey] = vfsStream::url('test/');
        $pth['file'][$fileKey] = vfsStream::url('test/test.php');

        $expected = array(
            'foo' => array('a' => 'b', 'c' => 'c'),
            'bar' => array('a' => 'b', 'c' => 'c')
        );
        $actual = XH_readConfiguration($plugin, $language);
        $this->assertEquals($expected, $actual);
    }

    public function testReadConfigWithoutDefaultconfig()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));

        $pth['folder']['cmsimple'] = vfsStream::url('test/');
        $pth['file']['config'] = vfsStream::url('test/config.php');

        $config = array(
            'foo' => array('a' => 'b', 'c' => 'c'),
            'bar' => array('a' => 'b', 'c' => 'c')
        );
        $config = var_export($config, true);
        $contents = "<?php \$cf = $config;?>";
        file_put_contents($pth['file']['config'], $contents);

        $expected = array(
            'foo' => array('a' => 'b', 'c' => 'c'),
            'bar' => array('a' => 'b', 'c' => 'c')
        );
        $this->assertEquals($expected, XH_readConfiguration());
    }

    /**
     * Should also test other include failures, which can't be easily simulated.
     *
     * @link http://cmsimpleforum.com/viewtopic.php?f=17&t=7679#p41533
     */
    public function testReadCorruptConfiguration()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));

        $pth['folder']['cmsimple'] = vfsStream::url('test/');
        $pth['file']['config'] = vfsStream::url('test/config.php');

        $contents = "<?php \$cf = false;?>";
        file_put_contents($pth['file']['config'], $contents);

        $this->assertEquals(array(), XH_readConfiguration());
    }

    public function testRenameFile()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $oldname = vfsStream::url('test/oldname.txt');
        $newname = vfsStream::url('test/newname.txt');
        file_put_contents($oldname, 'foo');
        file_put_contents($newname, 'bar');
        $actual = XH_renameFile($oldname, $newname);
        $this->assertTrue($actual);
    }

    /**
     * @dataProvider getRootFolderData
     */
    public function testRootFolder($scriptName, $language, $expected)
    {
        global $sn, $sl;

        $sn = $scriptName;
        $sl = $language;
        $this->assertEquals($expected, XH_getRootFolder());
    }

    public function getRootFolderData()
    {
        return array(
            array('/xh/', 'en', '/xh/'),
            array('/xh/en/', 'en', '/xh/'),
            array('/xh/index.php', 'en', '/xh/'),
            array('/xh/en/index.php', 'en', '/xh/'),
            array('/en/cms/', 'en', '/en/cms/')
        );
    }

    public function testRegisterPluginType()
    {
        XH_registerPluginType('editor', 'tinymce');
        XH_registerPluginType('filebrowser', 'filebrowser');
        XH_registerPluginType('editor', 'ckeditor');
        $this->assertEmpty(XH_registerPluginType('unknown'));
        $this->assertEquals(
            array('ckeditor', 'tinymce'), XH_registerPluginType('editor')
        );
        $this->assertEquals(
            array('filebrowser'), XH_registerPluginType('filebrowser')
        );
    }

    /**
     * Tests XH_formatDate().
     *
     * @return void
     */
    public function testFormatDate()
    {
        $this->assertEquals('January 02, 1970, 11:17', XH_formatDate('123456'));
    }

    /**
     * Tests XH_lockFile().
     *
     * @return void
     */
    public function testFlock()
    {
        $handle = 'foo';
        $operation = LOCK_EX;
        $flockMock = new PHPUnit_Extensions_MockFunction('flock', null);
        $flockMock->expects($this->once())->with($handle, $operation);
        XH_lockFile($handle, $operation);
        $flockMock->restore();
    }
}

?>
