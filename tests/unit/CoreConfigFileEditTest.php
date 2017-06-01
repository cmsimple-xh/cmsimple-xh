<?php

/**
 * Testing the CoreConfigFileEdit class.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

eval(<<<EOS
    function Test_languages()
    {
        return array('de', 'en');
    }
EOS
);

/**
 * A test case to for the CoreConfigFileEdit classes.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class CoreConfigFileEditTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        global $sn, $pth, $file;

        $this->setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $this->setConstant('XH_FORM_NAMESPACE', '');
        $this->setUpConfiguration();
        $this->setUpMockery();
        $sn = '/xh/';
        $file = 'config';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth = array(
            'folder' => array(
                'cmsimple' => vfsStream::url('test/'),
                'language' => vfsStream::url('test/')
            ),
            'file' => array(
                'config' => vfsStream::url('test/config.php')
            )
        );
        $this->setUpMetaConfig();
        $this->subject = new CoreConfigFileEdit();
    }

    private function setUpConfiguration()
    {
        global $cf;

        $cf = array(
            'foo' => array('bar' => 'baz'),
            'language' => array(
                'default' => 'en',
                'other' => 'en'
            ),
            'locator' => array('show_homepage' => 'true'),
            'menu' => array(
                'levelcatch' => '10',
                'levels' => '3',
                'sdoc' => 'parent'
            ),
            'meta' => array('robots' => 'index, follow'),
            'scripting' => array('regexp' => '#CMSimple .*#'),
            'security' => array(
                'password' => 'bar',
                'secret' => 'foo'
            )
        );
    }

    private function setUpMockery()
    {
        global $_XH_csrfProtection;

        $this->_tagStub = $this->getFunctionMock('tag');
        $this->_tagStub->expects($this->any())->will($this->returnCallback(
            function ($str) {
                return "<$str>";
            }
        ));
        $_XH_csrfProtection = $this->createMock('XH\CSRFProtection');
    }

    private function setUpMetaConfig()
    {
        $contents = <<<EOT
<?php
\$mcf['foo']['bar']="function:Test_doesntExist";
\$mcf['language']['default']="function:Test_languages";
\$mcf['language']['other']="xfunction:Test_languages";
\$mcf['locator']['show_homepage']="bool";
\$mcf['menu']['levelcatch']="hidden";
\$mcf['menu']['levels']="enum:1,2,3,4,5,6";
\$mcf['menu']['sdoc']="xenum:parent";
\$mcf['meta']['robots']="string";
\$mcf['security']['password']="password";
\$mcf['security']['secret']="random";
?>
EOT;
        file_put_contents(vfsStream::url('test/metaconfig.php'), $contents);
    }

    public function tearDown()
    {
        $this->_tagStub->restore();
    }

    public function testFormAttributes()
    {
        $this->assertXPath(
            '//form[@id="xh_config_form" and @action="/xh/" and @method="post" and @accept-charset="UTF-8"]',
            $this->subject->form()
        );
    }

    public function testFormContainsSubmitButton()
    {
        $this->assertXPath(
            '//form//input[@type="submit" and @class="submit"]',
            $this->subject->form()
        );
    }

    public function testFormContainsMetaRobotsField()
    {
        $this->assertXPath(
            '//form//input[@type="text" and @name="meta_robots" and @value="index, follow" and @class="xh_setting"]',
            $this->subject->form()
        );
    }

    public function testFormContainsMenuLevelcatchField()
    {
        $this->assertXPath(
            '//form//input[@type="hidden" and @name="menu_levelcatch" and @value="10"]',
            $this->subject->form()
        );
    }

    public function testFormContainsForBarField()
    {
        $this->assertXPath(
            '//form//select[@name="foo_bar"]',
            $this->subject->form()
        );
    }

    public function testFormContainsForBarFieldWithoutOptions()
    {
        $this->assertNotXPath(
            '//form//select[@name="foo_bar"]/option',
            $this->subject->form()
        );
    }

    public function testFormContainsLanguageDefaultField()
    {
        $this->assertXPathCount(
            '//form//select[@name="language_default"]/option',
            2,
            $this->subject->form()
        );
    }

    public function testFormContainsLanguageOtherField()
    {
        $this->assertXPath(
            '//form//input[@type="text" and @name="language_other" and @list="language_other_DATA"]',
            $this->subject->form()
        );
    }

    public function testFormContainsLanguageOtherDatalist()
    {
        $this->assertXPathCount(
            '//form//datalist[@id="language_other_DATA"]/option',
            2,
            $this->subject->form()
        );
    }

    public function testFormContainsLocatorShowHomepageField()
    {
        $this->assertXPath(
            '//form//input[@type="checkbox" and @name="locator_show_homepage" and @checked]',
            $this->subject->form()
        );
    }

    public function testFormContainsMenuLevelsField()
    {
        $this->assertXPathCount(
            '//form//select[@name="menu_levels"]/option',
            6,
            $this->subject->form()
        );
    }

    public function testFormContainsMenuSdocField()
    {
        $this->assertXPath(
            '//form//input[@type="text" and @name="menu_sdoc" and @list="menu_sdoc_DATA"]',
            $this->subject->form()
        );
    }

    public function testFormContainsMenuSdocDatalist()
    {
        $this->assertXPathCount(
            '//form//datalist[@id="menu_sdoc_DATA"]/option',
            1,
            $this->subject->form()
        );
    }

    public function testFormContainsSecuritySecretField()
    {
        $this->assertXPath(
            '//form//input[@type="hidden" and @name="security_secret" and @value="foo"]',
            $this->subject->form()
        );
    }

    public function testFormDoesNotContainScriptingRegexpField()
    {
        $this->assertNotXPath(
            '//form//input[@name="scripting_regexp"]',
            $this->subject->form()
        );
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = '';
        $this->assertXPath(
            '//p[@class="xh_success"]',
            $this->subject->form()
        );
    }

    public function testSubmit()
    {
        $writeFileSpy = $this->getFunctionMock('XH_writeFile');
        $writeFileSpy->expects($this->once())->willReturn(true);
        $headerSpy = $this->getFunctionMock('header');
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL
                . '?file=config&action=array&xh_success=config'
            )
        );
        $exitSpy = $this->getFunctionMock('XH_exit');
        $exitSpy->expects($this->once());
        $this->subject->submit();
        $writeFileSpy->restore();
        $headerSpy->restore();
        $exitSpy->restore();
    }

    public function testSubmitSaveFailure()
    {
        $writeFileSpy = $this->getFunctionMock('XH_writeFile');
        $writeFileSpy->expects($this->once())->willReturn(false);
        $eSpy = $this->getFunctionMock('e');
        $eSpy->expects($this->once());
        $this->subject->submit();
        $writeFileSpy->restore();
        $eSpy->restore();
    }

    //public function dataForFormField()
    //{
    //    return array(
    //        array(
    //            'language', 'default',
    //            array('val' => 'en', 'type' => 'enum', 'vals' => array('de', 'en')),
    //            array(
    //                'tag' => 'select',
    //                'attributes' => array('name' => 'language_default'),
    //                'children' => array('count' => 2)
    //            )
    //        )
    //    );
    //}
}
