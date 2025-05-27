<?php

/**
 * Testing the mailform.
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2025 The CMSimple_XH developers <http://cmsimple-xh.org/?The_Team>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @see       http://cmsimple-xh.org/
 */

namespace XH;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;

class SystemInfoTest extends TestCase
{
    protected function setUp(): void
    {
        global $pth, $cf, $tx;

        $pth = [
            'folder' => [
                'cmsimple' => '',
                'content' => '',
                'corestyle' => '',
                'images' => '',
                'downloads' => '',
                'userfiles' => '',
                'media' => '',
            ],
            'file' => [
                'config' => '',
                'log' => '',
                'debug-log' => '',
                'language' => '',
                'content' => '',
                'template' => '',
                'stylesheet' => ''
            ],
        ];
        $cf = XH_includeVar('./cmsimple/config.php', 'cf');
        $tx = XH_includeVar('./cmsimple/languages/en.php', 'tx');
    }

    public function testRendersSystemInfo(): void
    {
        $systemInfo = $this->getMockBuilder(SystemInfo::class)->onlyMethods([
            'plugins', 'isAccessProtected', 'isExtensionLoaded', 'getIni', 'defaultTimezone', 'setLocale',
        ])->getMock();
        $systemInfo->expects($this->any())->method('plugins')->willReturn(['filebrowser']);
        $systemInfo->expects($this->any())->method('isAccessProtected')->willReturn(true);
        $systemInfo->expects($this->any())->method('isExtensionLoaded')->willReturn(true);
        $systemInfo->expects($this->any())->method('getIni')->willReturnMap([
            ['safe_mode', ''],
            ['session.use_trans_sid', ''],
            ['session.use_only_cookies', '1'],
            ['session.cookie_lifetime', ''],
        ]);
        $systemInfo->expects($this->any())->method('defaultTimezone')->willReturn('Europe/Berlin');
        $systemInfo->expects($this->any())->method('setLocale')->willReturnArgument(1);
        Approvals::verifyHtml($systemInfo->render());
    }
}
