<?php

/**
 * Testing the PluginConfigFileEdit class.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2013-2016 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
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
class PluginConfigFileEditTest extends PHPUnit_Framework_TestCase
{
    private $_subject;

    public function setUp()
    {
        global $sn, $pth, $file, $plugin;

        $this->_setConstant('CMSIMPLE_URL', 'http://example.com/xh/');
        $this->_setConstant('XH_FORM_NAMESPACE', '');
        $plugin = 'pagemanager';
        $this->_setUpConfiguration();
        $this->_setUpMockery();
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
        $this->_setUpMetaConfig();
        $this->_subject = new XH_PluginConfigFileEdit();
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
        $matcher = array(
            'tag' => 'form',
            'id' => 'xh_config_form',
            'attributes' => array(
                'action' => '/xh/?&pagemanager',
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
            )
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsStringField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'text',
                'name' => 'test_string',
                'value' => 'foo',
                'class' => 'xh_setting'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsHiddenField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => 'test_hidden',
                'value' => 'foo'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsBoolField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'checkbox',
                'name' => 'test_bool',
                'checked' => 'checked'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsEnumField()
    {
        $matcher = array(
            'tag' => 'select',
            'attributes' => array(
                'name' => 'test_enum'
            ),
            'children' => array(
                'count' => 3,
                'only' => array('tag' => 'option')
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsRandomField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array(
                'type' => 'hidden',
                'name' => 'test_random',
                'value' => 'foo'
            ),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsPasswordField()
    {
        $matcher = array(
            'tag' => 'button',
            'attributes' => array('type' => 'button'),
            'ancestor' => array('tag' => 'form')
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormContainsPasswordDialog()
    {
        $matcher = array(
            'tag' => 'table',
            'parent' => array(
                'tag' => 'div',
                'id' => 'test_password_DLG'
            ),
            'children' => array(
                'count' => 3,
                'only' => array('tag' => 'tr')
            )
        );
        $this->_assertFormMatches($matcher);
    }

    public function testFormDoesNotContainScriptingRegexpField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array('name' => 'scripting_regexp'),
            'ancestor' => 'form'
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
            array('admin', 'plugin_config'),
            array('action', 'plugin_save')
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
                'Location: ' . CMSIMPLE_URL . '?&pagemanager&admin=plugin_config'
                . '&action=plugin_edit&xh_success=config'
            )
        );
        $exitSpy = new PHPUnit_Extensions_MockFunction('XH_exit', $this->_subject);
        $exitSpy->expects($this->once());
        $_POST = array(
            'test_password_OLD' => 'foo',
            'test_password_NEW' => 'bar',
            'test_password_CONFIRM' => 'bar',
        );
        $this->_subject->submit();
    }

    public function testSubmitWrongPasswordFailure()
    {
        global $xh_hasher, $e;

        $xh_hasher->expects($this->once())->method('CheckPassword')
            ->will($this->returnValue(false));
        $_POST = array(
            'test_password_OLD' => 'bar',
            'test_password_NEW' => 'bar',
            'test_password_CONFIRM' => 'bar',
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
            'test_password_OLD' => 'foo',
            'test_password_NEW' => "\xC3\xA4",
            'test_password_CONFIRM' => "\xC3\xA4",
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
            'test_password_OLD' => 'foo',
            'test_password_NEW' => 'bar',
            'test_password_CONFIRM' => 'foo',
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
