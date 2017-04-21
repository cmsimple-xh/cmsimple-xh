<?php

/**
 * Testing the CoreConfigFileEdit class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

require_once './vendor/autoload.php';

require_once './cmsimple/functions.php';
require_once './plugins/utf8/utf8.php';
require_once UTF8 . '/ucfirst.php';
require_once './cmsimple/classes/CSRFProtection.php';
require_once './cmsimple/classes/PasswordHash.php';

/**
 * The file under test.
 */
require_once './cmsimple/classes/FileEdit.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

function Test_languages()
{
    return array('de', 'en');
}

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
class CoreConfigFileEditTest extends PHPUnit_Framework_TestCase
{
    private $_subject;

    public function setUp()
    {
        global $sn, $pth, $file;

        $this->_setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $this->_setConstant('XH_FORM_NAMESPACE', '');
        $this->_setUpConfiguration();
        $this->_setUpMockery();
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
        $this->_setUpMetaConfig();
        $this->_subject = new XH_CoreConfigFileEdit();
    }

    private function _setConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            runkit_constant_redefine($name, $value);
        }
    }

    private function _setUpConfiguration()
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

    private function _setUpMockery()
    {
        global $_XH_csrfProtection, $xh_hasher;

        $this->_tagStub = new PHPUnit_Extensions_MockFunction('tag', $this->_subject);
        $this->_tagStub->expects($this->any())->will($this->returnCallback(
            function ($str) {
                return "<$str>";
            }
        ));
        $_XH_csrfProtection = $this->getMockBuilder('XH_CSRFProtection')
            ->disableOriginalConstructor()->getMock();
        $xh_hasher = $this->getMockBuilder('PasswordHash')
            ->disableOriginalConstructor()->getMock();
    }

    private function _setUpMetaConfig()
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsForBarField()
    {
        $matcher = array(
            'tag' => 'select',
            'attributes' => array('name' => 'foo_bar'),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsForBarFieldWithoutOptions()
    {
        $matcher = array(
            'tag' => 'select',
            'attributes' => array('name' => 'foo_bar'),
            'child' => array('tag' => 'option'),
            'ancestor' => array('tag' => 'form')
        );
        @$this->assertNotTag($matcher, $this->_subject->form());
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
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
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsSecurityPasswordField()
    {
        $matcher = array(
            'tag' => 'button',
            'attributes' => array('type' => 'button'),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsSecurityPasswordDialog()
    {
        $matcher = array(
            'tag' => 'table',
            'parent' => array(
                'tag' => 'div',
                'id' => 'security_password_DLG'
            ),
            'children' => array(
                'count' => 3,
                'only' => array('tag' => 'tr')
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormDoesNotContainScriptingRegexpField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array('name' => 'scripting_regexp'),
            'ancestor' => array('tag' => 'form')
        );
        @$this->assertNotTag($matcher, $this->_subject->form());
    }

    /**
     * @dataProvider hiddenInputData
     */
    public function testFormContainsHiddenInput($name, $value)
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => $name,
                'value' => $value
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function hiddenInputData()
    {
        return array(
            array('file', 'config'),
            array('action', 'save')
        );
    }

    public function testSuccessMessage()
    {
        $_GET['xh_success'] = '';
        $matcher = array(
            'tag' => 'p',
            'attributes' => array('class' => 'xh_success')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testSubmit()
    {
        global $xh_hasher;

        $xh_hasher->expects($this->once())->method('CheckPassword')
            ->will($this->returnValue(true));
        $writeFileSpy = new PHPUnit_Extensions_MockFunction(
            'XH_writeFile', $this->_subject
        );
        $writeFileSpy->expects($this->once())->will($this->returnValue(true));
        $headerSpy = new PHPUnit_Extensions_MockFunction('header', $this->_subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL
                . '?file=config&action=array&xh_success=config'
            )
        );
        $exitSpy = new PHPUnit_Extensions_MockFunction('XH_exit', $this->_subject);
        $exitSpy->expects($this->once());
        $_POST = array(
            'security_password_OLD' => 'foo',
            'security_password_NEW' => 'bar',
            'security_password_CONFIRM' => 'bar',
        );
        $this->_subject->submit();
    }

    public function testSubmitWrongPasswordFailure()
    {
        global $xh_hasher, $e;

        $xh_hasher->expects($this->once())->method('CheckPassword')
            ->will($this->returnValue(false));
        $_POST = array(
            'security_password_OLD' => 'foo',
            'security_password_NEW' => 'bar',
            'security_password_CONFIRM' => 'bar',
        );
        $this->_subject->submit();
        $this->assertNotEmpty($e);
    }

    public function testSubmitInvalidPasswordFailure()
    {
        global $xh_hasher, $e;

        $xh_hasher->expects($this->once())->method('CheckPassword')
            ->will($this->returnValue(true));
        $_POST = array(
            'security_password_OLD' => 'foo',
            'security_password_NEW' => "\xC3\xA4",
            'security_password_CONFIRM' => "\xC3\xA4",
        );
        $this->_subject->submit();
        $this->assertNotEmpty($e);
    }

    public function testSubmitPasswordMismatchFailure()
    {
        global $xh_hasher, $e;

        $xh_hasher->expects($this->once())->method('CheckPassword')
            ->will($this->returnValue(true));
        $_POST = array(
            'security_password_OLD' => 'foo',
            'security_password_NEW' => 'bar',
            'security_password_CONFIRM' => 'foo',
        );
        $this->_subject->submit();
        $this->assertNotEmpty($e);
    }

    public function testSubmitSaveFailure()
    {
        $writeFileSpy = new PHPUnit_Extensions_MockFunction(
            'XH_writeFile', $this->_subject
        );
        $writeFileSpy->expects($this->once())->will($this->returnValue(false));
        $eSpy = new PHPUnit_Extensions_MockFunction('e', $this->_subject);
        $eSpy->expects($this->once());
        $this->_subject->submit();
    }

    private function _assertFormMatches($matcher)
    {
        @$this->assertTag($matcher, $this->_subject->form());
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

?>
