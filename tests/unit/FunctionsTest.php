<?php

/**
 * Testing the functions in functions.php.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2021 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Error\Deprecated as Deprecated;

/**
 * A test case for the functions in functions.php.
 *
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see      http://cmsimple-xh.org/
 * @since    1.6
 */
class FunctionsTest extends TestCase
{
    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp(): void
    {
        global $cf, $tx;

        include './cmsimple/config.php';
        include './cmsimple/languages/en.php';
        $_SERVER['SERVER_NAME'] = 'example.com';
    }

    public function testHIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        h(0);
    }

    public function testLIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        l(0);
    }

    public function testAutogalleryIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        autogallery('');
    }

    public function testAmpIsDeprecated()
    {
        $this->expectException(Deprecated::class);
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

    public function testRpIsDeprecated()
    {
        $this->expectException(Deprecated::class);
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

    public function testInitvarIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        initvar('foo');
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

    public function testChkdlIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        chkdl('dummy');
    }

    public function testRfIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        rf('dummy');
    }

    public function dataForTestTag()
    {
        return array(
            array('', 'br', '<br>'),
            array('true', 'br', '<br>')
        );
    }

    /**
     * @dataProvider dataForTestTag
     */
    public function testTag($xhtmlEndtags, $str, $expected)
    {
        global $cf;

        $cf['xhtml']['endtags'] = $xhtmlEndtags;
        $errorReporting = error_reporting();
        error_reporting(0);
        $actual = tag($str);
        error_reporting($errorReporting);
        $this->assertEquals($expected, $actual);
    }

    public function testWritelogIsDeprecated()
    {
        $this->expectException(Deprecated::class);
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
        $_SESSION = ['xh_default_password' => ''];
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

    public function testAfterFinalCleanUp()
    {
        XH_afterFinalCleanUp(function () {
            return 'foo';
        });
        $this->assertEquals('foo', XH_afterFinalCleanUp('bar'));
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

    public function testAWithEmptyX()
    {
        global $sn, $u, $xh_publisher;

        $sn = '/xh/';
        $u = [];
        $xh_publisher = $this->createMock(Publisher::class);
        $xh_publisher->method('getFirstPublishedPage')->willReturn(0);
        $this->setConstant('XH_ADM', false);
        $this->assertEquals('<a href="/xh/">', a(0, ''));
    }

    public function testMeta()
    {
        $actual = meta('robots');
        $this->assertXPath(
            '//meta[@name="robots" and @content="index, follow"]',
            $actual
        );
    }

    /**
     * @dataProvider dataForUenc
     */
    public function testUenc($uricharSep, $uricharOrg, $uricharNew, $wordSep, $expected)
    {
        global $cf, $tx;

        $this->setConstant('XH_URICHAR_SEPARATOR', $uricharSep);
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

    public function testUencTrimsSeparators()
    {
        global $cf, $tx;

        $this->setConstant('XH_URICHAR_SEPARATOR', '/');
        $cf['uri']['word_separator'] = '-';
        $tx['urichar']['org'] = '';
        $tx['urichar']['new'] = '';
        $this->assertEquals('This-That', uenc('- This - That -'));
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
     * @see http://cmsimpleforum.com/viewtopic.php?f=17&t=7679#p41533
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
        $actual = rename($oldname, $newname);
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
        $this->assertEquals(['ckeditor', 'tinymce'], XH_registerPluginType('editor'));
        $this->assertEquals(['filebrowser'], XH_registerPluginType('filebrowser'));
    }

    /**
     * Tests XH_formatDate().
     *
     * @return void
     */
    public function testFormatDate()
    {
        global $tx;

        if (class_exists('IntlDateFormatter', false)) {
            $oldLocale = $tx['locale']['all'];
            $tx['locale']['all'] = 'en_US';
            $this->assertStringMatchesFormat('January 2, 1970%s10:17 AM', XH_formatDate('123456'));
            $tx['locale']['all'] = $oldLocale;
        } else {
            $this->assertEquals('January 02, 1970, 10:17', XH_formatDate('123456'));
        }
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
        $flockMock = $this->createFunctionMock('flock');
        $flockMock->expects($this->once())->with($handle, $operation);
        XH_lockFile($handle, $operation);
        $flockMock->restore();
    }

    /**
     * @dataProvider dataForHightlightSearchWords
     */
    public function testHighlightSearchWords($words, $text, $expected)
    {
        $this->assertEquals($expected, XH_highlightSearchWords($words, $text));
    }

    public function dataForHightlightSearchWords()
    {
        return [
            // A simple case.
            [
                ['word'], 'blah word blah',
                'blah <span class="xh_find">word</span> blah'
            ],
            // Don't highlight inside an HTML tag.
            [
                ['word'], 'blah <img src="word.jpg"> blah',
                'blah <img src="word.jpg"> blah'
            ],
            // Escape meta characters.
            [['w.rd'], 'blah word blah', 'blah word blah'],
            // Searching for two words of which one is contained in the other.
            // The result isn't beautiful, but it works.
            [
                ['or', 'word'], 'blah word blah',
                'blah <span class="xh_find">w<span class="xh_find">or</span>'
                . 'd</span> blah'
            ],
            // A sanscrit example that points out problems wrt. combining chars.
            // See <http://cmsimpleforum.com/viewtopic.php?f=29&t=8077>.
            [
                [
                    "\xE0\xA4\xB0\xE0\xA5\x87\xE0\xA4\xA6\xE0\xA4\xBE\xE0\xA4\xA4"
                    . "\xE0\xA5\x8D\xE0\xA4\xAE\xE0\xA4\xA8\xE0\xA4\xBE\xE0\xA4"
                    . "\xA4\xE0\xA5\x8D\xE0\xA4\xAE\xE0\xA4\xBE\xE0\xA4\xA8\xE0"
                    . "\xA4\x82",
                    "\xE0\xA4\xA8\xE0\xA4\xBE\xE0\xA4\xA4\xE0\xA5"
                    . "\x8D\xE0\xA4\xAE\xE0\xA4\xBE\xE0\xA4\xA8"
                ],
                "\xE0\xA4\x89\xE0\xA4\xA6\xE0\xA5\x8D\xE0\xA4\xA7\xE0\xA4\xB0\xE0"
                . "\xA5\x87\xE0\xA4\xA6\xE0\xA4\xBE\xE0\xA4\xA4\xE0\xA5\x8D\xE0"
                . "\xA4\xAE\xE0\xA4\xA8\xE0\xA4\xBE\xE0\xA4\xA4\xE0\xA5\x8D\xE0"
                . "\xA4\xAE\xE0\xA4\xBE\xE0\xA4\xA8\xE0\xA4\x82\x20\xE0\xA4\xA8"
                . "\xE0\xA4\xBE\xE0\xA4\xA4\xE0\xA5\x8D\xE0\xA4\xAE\xE0\xA4\xBE"
                . "\xE0\xA4\xA8\xE0\xA4\xAE\xE0\xA4\xB5\xE0\xA4\xB8\xE0\xA4\xBE"
                . "\xE0\xA4\xA6\xE0\xA4\xAF\xE0\xA5\x87\xE0\xA4\xA4\xE0\xA5\x8D",
                "\xE0\xA4\x89\xE0\xA4\xA6\xE0\xA5\x8D\xE0\xA4\xA7"
                . '<span class="xh_find">'
                . "\xE0\xA4\xB0\xE0\xA5\x87\xE0\xA4\xA6\xE0\xA4\xBE\xE0\xA4\xA4"
                . "\xE0\xA5\x8D\xE0\xA4\xAE"
                . '<span class="xh_find">'
                . "\xE0\xA4\xA8\xE0\xA4\xBE\xE0\xA4\xA4\xE0\xA5\x8D\xE0\xA4\xAE"
                . "\xE0\xA4\xBE\xE0\xA4\xA8"
                . '</span>' . "\xE0\xA4\x82" . '</span> <span class="xh_find">'
                . "\xE0\xA4\xA8\xE0\xA4\xBE\xE0\xA4\xA4\xE0\xA5\x8D\xE0\xA4\xAE"
                . "\xE0\xA4\xBE\xE0\xA4\xA8"
                . '</span>'
                . "\xE0\xA4\xAE\xE0\xA4\xB5\xE0\xA4\xB8\xE0\xA4\xBE\xE0\xA4\xA6"
                . "\xE0\xA4\xAF\xE0\xA5\x87\xE0\xA4\xA4\xE0\xA5\x8D"
            ],
            // empty search string (see http://cmsimpleforum.com/viewtopic.php?f=10&t=8789)
            [[''], 'foo bar baz', 'foo bar baz'],
            // searching for a space doesn't "highlight" spaces
            [[' '], 'foo bar baz', 'foo bar baz'],
            // search for same word twice
            [
                ['word', 'word'], 'foo word bar',
                'foo <span class="xh_find">word</span> bar'
            ],
            // searching for entity names must not highlight
            [
                ['lt'], '<div>Standard text, paragraphs, heading &lt;h2&gt;</div>',
                '<div>Standard text, paragraphs, heading &lt;h2&gt;</div>'
            ],
            // searching must not highlight inside of tags
            // cf. <https://cmsimpleforum.com/viewtopic.php?f=10&t=14178>
            [
                ['emil'],
                '<a href="userfiles/images/billedudlaan/k47_emil_nielsen/wtrmrk/2.jpg"'
                . ' rel="prettyPhoto[imgalbum0]" title="566 - 50x60 cm<br />">'
                . '<img src="userfiles/images/billedudlaan/k47_emil_nielsen/thumb/imgalbum_2.jpg"'
                . ' class="thumb" alt="" title="" width="135" height="113"></a>',
                '<a href="userfiles/images/billedudlaan/k47_emil_nielsen/wtrmrk/2.jpg"'
                . ' rel="prettyPhoto[imgalbum0]" title="566 - 50x60 cm<br />">'
                . '<img src="userfiles/images/billedudlaan/k47_emil_nielsen/thumb/imgalbum_2.jpg"'
                . ' class="thumb" alt="" title="" width="135" height="113"></a>'
            ],
            // searching for "<" and ">"
            [
                ['&lt;code&gt;'],
                'blah blah <code>&lt;code&gt;some code&lt;/code&gt;</code>'
                . ' yada yada',
                'blah blah <code><span class="xh_find">&lt;code&gt;</span>some code&lt;/code&gt;</code>'
                . ' yada yada'
            ],
        ];
    }

    /**
     * @dataProvider dataForRedirectSelectedUrl
     * @param string $queryString
     * @param string $expected
     */
    public function testRedirectSelectedUrl($queryString, $selected, $expected)
    {
        $this->setConstant('CMSIMPLE_URL', 'http://example.com/');
        $GLOBALS['selected'] = $selected;
        $_SERVER['QUERY_STRING'] = $queryString;
        $this->assertSame($expected, XH_redirectSelectedUrl());
    }

    /**
     * @return array[]
     */
    public function dataForRedirectSelectedUrl()
    {
        return array(
            ['selected=foo', 'foo', 'http://example.com/?foo'],
            ['selected=foo&bar=baz', 'foo', 'http://example.com/?foo&bar=baz'],
            ['foo=bar&selected=baz', 'baz', 'http://example.com/?baz&foo=bar'],
            ['foo=bar&selected=baz&bar=foo', 'baz', 'http://example.com/?baz&foo=bar&bar=foo']
        );
    }
}
