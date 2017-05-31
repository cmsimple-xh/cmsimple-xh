<?php

/**
 * Testing the PluginConfigFileEdit class.
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

/**
 * A test case to for the PluginConfigFileEdit classes.
 *
 * @category Testing
 * @package  XH
 * @author   The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://cmsimple-xh.org/
 * @since    1.6
 */
class PluginConfigFileEditTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        global $sn, $pth, $file, $plugin;

        $this->setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $this->setConstant('XH_FORM_NAMESPACE', '');
        $plugin = 'pagemanager';
        $this->setUpConfiguration();
        $this->setUpMockery();
        $sn = '/xh/';
        $file = 'plugin_config';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth = array(
            'folder' => array(
                'cmsimple' => vfsStream::url('test/'),
                'plugins' => vfsStream::url('test/')
            ),
            'file' => array(
                'plugin_config' => vfsStream::url('test/config.php')
            )
        );
        $this->setUpMetaConfig();
        $this->subject = new PluginConfigFileEdit();
    }

    private function setUpConfiguration()
    {
        global $plugin_cf;

        $plugin_cf = array(
            'pagemanager' => array(
                'test_bool' => 'true',
                'test_enum' => 'one',
                'test_hidden' => 'foo',
                'test_string' => 'foo',
                'test_password' => 'foo',
                'test_random' => 'foo'
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
        $_XH_csrfProtection = $this->getMockBuilder('XH\CSRFProtection')
            ->disableOriginalConstructor()->getMock();
    }

    private function setUpMetaConfig()
    {
        $filename = vfsStream::url('test/pagemanager/config/metaconfig.php');
        mkdir(dirname($filename), 0777, true);
        $contents = <<<EOT
<?php
\$plugin_mcf['pagemanager']['test_bool']="bool";
\$plugin_mcf['pagemanager']['test_hidden']="hidden";
\$plugin_mcf['pagemanager']['test_enum']="enum:one,two,three";
\$plugin_mcf['pagemanager']['test_string']="string";
\$plugin_mcf['pagemanager']['test_password']="password";
\$plugin_mcf['pagemanager']['test_random']="random";
?>
EOT;
        file_put_contents($filename, $contents);
    }

    public function tearDown()
    {
        $this->_tagStub->restore();
    }

    public function testFormAttributes()
    {
        $this->assertXPath(
            '//form[@id="xh_config_form" and @action="/xh/?&pagemanager" and @method="post"'
            . ' and @accept-charset="UTF-8"]',
            $this->subject->form()
        );
    }

    public function testFormContainsSubmitButton()
    {
        $this->assertXPath(
            '//input[@type="submit" and @class="submit"]',
            $this->subject->form()
        );
    }

    public function testFormContainsStringField()
    {
        $this->assertXPath(
            '//form//input[@type="text" and @name="test_string" and @value="foo" and @class="xh_setting"]',
            $this->subject->form()
        );
    }

    public function testFormContainsHiddenField()
    {
        $this->assertXPath(
            '//form//input[@type="hidden" and @name="test_hidden" and @value="foo"]',
            $this->subject->form()
        );
    }

    public function testFormContainsBoolField()
    {
        $this->assertXPath(
            '//form//input[@type="checkbox" and @name="test_bool" and @checked]',
            $this->subject->form()
        );
    }

    public function testFormContainsEnumField()
    {
        $this->assertXPathCount(
            '//form//select[@name="test_enum"]/option',
            3,
            $this->subject->form()
        );
    }

    public function testFormContainsRandomField()
    {
        $this->assertXPath(
            '//form//input[@type="hidden" and @name="test_random" and @value="foo"]',
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
        $writeFileSpy->expects($this->once())->will($this->returnValue(true));
        $headerSpy = $this->getFunctionMock('header');
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL . '?&pagemanager&admin=plugin_config'
                . '&action=plugin_edit&xh_success=config'
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
        $writeFileSpy->expects($this->once())->will($this->returnValue(false));
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
