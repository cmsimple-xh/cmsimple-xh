<?php

/**
 * Testing the backup functionality.
 *
 * @category  Testing
 * @package   XH
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2014-2017 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

namespace XH;

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class BackupTest extends TestCase
{
    private $contentFolder;

    private $subject;

    private $utf8UcfirstMock;

    public function setUp()
    {
        global $pth, $cf, $tx;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('content'));
        $this->contentFolder = vfsStream::url('content/');
        $pth = array(
            'folder' => array(
                'classes' => './cmsimple/classes/'
            ),
            'file' => array('content' => "{$this->contentFolder}content.htm")
        );
        $cf = array(
            'backup' => array('numberoffiles' => '3')
        );
        $tx = array(
            'filetype' => array('backup' => 'backup'),
            'result' => array(
                'created' => 'created',
                'deleted' => 'deleted'
            )
        );
        $this->utf8UcfirstMock = $this->getFunctionMock('utf8_ucfirst');
        $this->utf8UcfirstMock->expects($this->any())->will($this->returnArgument(0));
        $this->subject = new Backup(array($this->contentFolder));
    }

    protected function tearDown()
    {
        $this->utf8UcfirstMock->restore();
    }

    /**
     * @dataProvider dataForIsContentBackup
     */
    public function testIsContentBackup($filename, $regularOnly, $expected)
    {
        $this->assertSame($expected, XH_isContentBackup($filename, $regularOnly));
    }

    public function dataForIsContentBackup()
    {
        return array(
            array('20140503_192021_content.htm', true, true),
            array('20140503_1920_content.htm', true, false),
            array('20140503_192021_special.htm', true, false),
            array('20140503_192021_special.html', true, false),
            array('2013-07-11-01-02-03-content.htm', true, false),
            array('20140503_192021_content.htm', false, true),
            array('20140503_1920_content.htm', false, false),
            array('20140503_192021_special.htm', false, true),
            array('20140503_192021_special.html', false, false),
            array('2013-07-11-01-02-03-content.htm', false, false)
        );
    }

    public function testFailedBackupReportsError()
    {
        $eSpy = $this->getFunctionMock('e');
        $eSpy->expects($this->once())->with($this->equalTo('cntsave'), $this->equalTo('backup'));
        file_put_contents("{$this->contentFolder}content.htm", '');
        chmod($this->contentFolder, 0444);
        // HACK: we can't use try-catch here, because that would prevent e()
        // from being called, so we temporarily disable the error_reporting
        $errorReporting = error_reporting(0);
        $this->subject->execute();
        error_reporting($errorReporting);
        $eSpy->restore();
    }

    public function testCreatesBackupWhenNoBackupIsThere()
    {
        touch("{$this->contentFolder}content.htm");
        $this->subject->execute();
        $this->assertCount(4, scandir($this->contentFolder));
    }

    public function testCreatesBackupWhenLatestBackupIsOutdated()
    {
        touch("{$this->contentFolder}19700101_000102_content.htm");
        file_put_contents("{$this->contentFolder}content.htm", 'foo');
        $this->subject->execute();
        $this->assertCount(5, scandir($this->contentFolder));
    }

    public function testDoesntCreateBackupWhenLatestBackupIsUpToDate()
    {
        touch("{$this->contentFolder}19700101_000102_content.htm");
        touch("{$this->contentFolder}content.htm");
        $this->subject->execute();
        $this->assertCount(4, scandir($this->contentFolder));
    }

    public function testReportsCreationOfBackup()
    {
        touch("{$this->contentFolder}content.htm");
        $this->assertXPathContains(
            '//p[@class="xh_info"]',
            'created',
            $this->subject->execute()
        );
    }

    public function testDeletesTooOldBackups()
    {
        file_put_contents("{$this->contentFolder}content.htm", 'foo');
        touch("{$this->contentFolder}19700101_000102_content.htm");
        touch("{$this->contentFolder}19700102_000102_content.htm");
        touch("{$this->contentFolder}19700103_000102_content.htm");
        touch("{$this->contentFolder}19700104_000102_content.htm");
        $this->subject->execute();
        $this->assertFileNotExists(
            "{$this->contentFolder}19700101_000102_content.htm"
        );
        $this->assertFileNotExists(
            "{$this->contentFolder}19700102_000102_content.htm"
        );
    }

    public function testKeepsYoungEnoughBackups()
    {
        file_put_contents("{$this->contentFolder}content.htm", 'foo');
        touch("{$this->contentFolder}19700101_000102_content.htm");
        touch("{$this->contentFolder}19700102_000102_content.htm");
        $this->subject->execute();
        $this->assertFileExists(
            "{$this->contentFolder}19700101_000102_content.htm"
        );
        $this->assertFileExists(
            "{$this->contentFolder}19700102_000102_content.htm"
        );
    }

    public function testReportsDeletionOfBackup()
    {
        file_put_contents("{$this->contentFolder}content.htm", 'foo');
        touch("{$this->contentFolder}19700101_000102_content.htm");
        touch("{$this->contentFolder}19700102_000102_content.htm");
        touch("{$this->contentFolder}19700103_000102_content.htm");
        $this->assertXPath(
            '//p[@class="xh_info"]',
            $this->subject->execute()
        );
    }

    public function testReportsDeletionFailure()
    {
        $eSpy = $this->getFunctionMock('e');
        $eSpy->expects($this->once())->with($this->equalTo('cntdelete'), $this->equalTo('backup'));
        file_put_contents("{$this->contentFolder}content.htm", 'foo');
        touch("{$this->contentFolder}19700101_000102_content.htm");
        touch("{$this->contentFolder}19700102_000102_content.htm");
        touch("{$this->contentFolder}19700103_000102_content.htm");
        $unlinkStub = $this->getFunctionMock('unlink');
        $unlinkStub->expects($this->any())->willReturn(false);
        $this->subject->execute();
        $unlinkStub->restore();
        $eSpy->restore();
    }

    public function testDoesNothingWhenNumberoffilesIsEmpty()
    {
        global $cf;

        $cf['backup']['numberoffiles'] = '0';
        $this->subject = new Backup(array($this->contentFolder));
        touch("{$this->contentFolder}content.htm");
        $eSpy = $this->getFunctionMock('e');
        $eSpy->expects($this->never());
        $this->assertEquals('', $this->subject->execute());
        $eSpy->restore();
    }

    public function testMultipleBackupFolders()
    {
        $subject = $this->getMockBuilder('XH\Backup')
            ->setMethods(array('backupSingleFolder'))
            ->setConstructorArgs(array(array('foo', 'bar')))
            ->getMock();
        $subject->expects($this->exactly(2))->method('backupSingleFolder');
        $subject->execute($subject);
    }
}
