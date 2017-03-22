<?php

/**
 * Copyright 2011-2017 Christoph M. Becker
 *
 * This file is part of Pagemanager_XH.
 *
 * Pagemanager_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Pagemanager_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Pagemanager_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Pagemanager;

class PluginInfoController extends Controller
{
    public function indexAction()
    {
        global $title;

        $title = "Pagemanager – {$this->lang['menu_info']}";
        $view = new View('info');
        $view->logoPath = "{$this->pluginFolder}pagemanager.png";
        $view->version = Plugin::VERSION;
        $checks = array();
        foreach ($this->systemChecks() as $check => $state) {
            $checks[] = (object) array(
                'check' => $check,
                'state' => $state,
                'icon' => "{$this->pluginFolder}images/$state.png"
            );
        }
        $view->checks = $checks;
        $view->render();
    }

    /**
     * @return array
     */
    private function systemChecks()
    {
        global $pth;

        $phpVersion = '5.3.0';
        $checks = array();
        $key = sprintf($this->lang['syscheck_phpversion'], $phpVersion);
        $ok = version_compare(PHP_VERSION, $phpVersion) >= 0;
        $checks[$key] = $ok ? 'ok' : 'fail';
        foreach (array('json') as $ext) {
            $key = sprintf($this->lang['syscheck_extension'], $ext);
            $checks[$key] = extension_loaded($ext) ? 'ok' : 'fail';
        }
        $xhVersion = 'CMSimple_XH 1.7dev';
        $ok = strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') === 0
            && version_compare(CMSIMPLE_XH_VERSION, $xhVersion) >= 0;
        $xhVersion = substr($xhVersion, 12);
        $key = sprintf($this->lang['syscheck_xhversion'], $xhVersion);
        $checks[$key] = $ok ? 'ok' : 'fail';
        $ok = file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php');
        $checks[$this->lang['syscheck_jquery']] = $ok ? 'ok' : 'fail';
        $folders = array();
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = "{$this->pluginFolder}{$folder}";
        }
        foreach ($folders as $folder) {
            $key = sprintf($this->lang['syscheck_writable'], $folder);
            $checks[$key] = is_writable($folder) ? 'ok' : 'warn';
        }
        return $checks;
    }
}
