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

        $this->_tagStub = $this->getFunctionMock('tag', $this->subject);
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
        $matcher = array(
            'tag' => 'form',
            'id' => 'xh_config_form',
            'attributes' => array(
                'action' => '/xh/',
                'method' => 'post',
                'accept-charset' => 'UTF-8'
            )
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsSubmitButton()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'submit',
                'class' => 'submit'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsMetaRobotsField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'text',
                'name' => 'meta_robots',
                'value' => 'index, follow',
                'class' => 'xh_setting'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsMenuLevelcatchField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => 'menu_levelcatch',
                'value' => '10'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsForBarField()
    {
        $matcher = array(
            'tag' => 'select',
            'attributes' => array('name' => 'foo_bar'),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsForBarFieldWithoutOptions()
    {
        $matcher = array(
            'tag' => 'select',
            'attributes' => array('name' => 'foo_bar'),
            'child' => array('tag' => 'option'),
            'ancestor' => array('tag' => 'form')
        );
        @$this->assertNotTag($matcher, $this->subject->form());
    }

    public function testFormContainsLanguageDefaultField()
    {
        $matcher = array(
            'tag' => 'select',
            'attributes' => array('name' => 'language_default'),
            'children' => array(
                'only' => array('tag' => 'option'),
                'count' => 2
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsLanguageOtherField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'text',
                'name' => 'language_other',
                'list' => 'language_other_DATA'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsLanguageOtherDatalist()
    {
        $matcher = array(
            'tag' => 'datalist',
            'id' => 'language_other_DATA',
            'children' => array(
                'only' => array('tag' => 'option'),
                'count' => 2
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsLocatorShowHomepageField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'checkbox',
                'name' => 'locator_show_homepage',
                'checked' => 'checked'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsMenuLevelsField()
    {
        $matcher = array(
            'tag' => 'select',
            'attributes' => array(
                'name' => 'menu_levels'
            ),
            'children' => array(
                'count' => 6,
                'only' => array('tag' => 'option')
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsMenuSdocField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'text',
                'name' => 'menu_sdoc',
                'list' => 'menu_sdoc_DATA'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsMenuSdocDatalist()
    {
        $matcher = array(
            'tag' => 'datalist',
            'id' => 'menu_sdoc_DATA',
            'children' => array(
                'only' => array('tag' => 'option'),
                'count' => 1
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormContainsSecuritySecretField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => 'security_secret',
                'value' => 'foo'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->assertFormMatches($matcher);
    }

    public function testFormDoesNotContainScriptingRegexpField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array('name' => 'scripting_regexp'),
            'ancestor' => array('tag' => 'form')
        );
        @$this->assertNotTag($matcher, $this->subject->form());
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = '';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => 'xh_success')
        );
        $this->assertFormMatches($matcher);
    }

    public function testSubmit()
    {
        $writeFileSpy = $this->getFunctionMock('XH_writeFile', $this->subject);
        $writeFileSpy->expects($this->once())->will($this->returnValue(true));
        $headerSpy = $this->getFunctionMock('header', $this->subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL
                . '?file=config&action=array&xh_success=config'
            )
        );
        $exitSpy = $this->getFunctionMock('XH_exit', $this->subject);
        $exitSpy->expects($this->once());
        $this->subject->submit();
    }

    public function testSubmitSaveFailure()
    {
        $writeFileSpy = $this->getFunctionMock('XH_writeFile', $this->subject);
        $writeFileSpy->expects($this->once())->will($this->returnValue(false));
        $eSpy = $this->getFunctionMock('e', $this->subject);
        $eSpy->expects($this->once());
        $this->subject->submit();
    }

    private function assertFormMatches($matcher)
    {
        @$this->assertTag($matcher, $this->subject->form());
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
