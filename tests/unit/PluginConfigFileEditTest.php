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

use PHPUnit_Extensions_MockFunction;
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

        $this->_tagStub = new PHPUnit_Extensions_MockFunction('tag', $this->subject);
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
        $matcher = array(
            'tag' => 'form',
            'id' => 'xh_config_form',
            'attributes' => array(
                'action' => '/xh/?&pagemanager',
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
            )
        );
        $this->assertFormMatches($matcher);
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
        $this->assertFormMatches($matcher);
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
        $this->assertFormMatches($matcher);
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
        $this->assertFormMatches($matcher);
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
        $this->assertFormMatches($matcher);
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
        $this->assertFormMatches($matcher);
    }

    public function testFormDoesNotContainScriptingRegexpField()
    {
        $matcher = array(
            'tag' => 'input',
            'attributes' => array('name' => 'scripting_regexp'),
            'ancestor' => 'form'
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
        $writeFileSpy = new PHPUnit_Extensions_MockFunction('XH_writeFile', $this->subject);
        $writeFileSpy->expects($this->once())->will($this->returnValue(true));
        $headerSpy = new PHPUnit_Extensions_MockFunction('header', $this->subject);
        $headerSpy->expects($this->once())->with(
            $this->equalTo(
                'Location: ' . CMSIMPLE_URL . '?&pagemanager&admin=plugin_config'
                . '&action=plugin_edit&xh_success=config'
            )
        );
        $exitSpy = new PHPUnit_Extensions_MockFunction('XH_exit', $this->subject);
        $exitSpy->expects($this->once());
        $this->subject->submit();
    }

    public function testSubmitSaveFailure()
    {
        $writeFileSpy = new PHPUnit_Extensions_MockFunction('XH_writeFile', $this->subject);
        $writeFileSpy->expects($this->once())->will($this->returnValue(false));
        $eSpy = new PHPUnit_Extensions_MockFunction('e', $this->subject);
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
