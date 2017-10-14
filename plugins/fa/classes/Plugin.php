<?php

/**
 * Copyright 2017 Christoph M. Becker
 *
 * This file is part of Fa_XH.
 *
 * Fa_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Fa_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fa_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fa;

class Plugin
{
    const VERSION = '1.2';

    public function run()
    {
        global $plugin_cf;

        if ($plugin_cf['fa']['require_auto']) {
            $command = new RequireCommand;
            $command->execute();
        }
        if (XH_ADM) {
            XH_registerStandardPluginMenuItems(false);
            if (XH_wantsPluginAdministration('fa')) {
                $this->handlePluginAdministration();
            }
        }
    }

    private function handlePluginAdministration()
    {
        global $o, $action, $admin;

        $o .= print_plugin_admin('off');
        switch ($admin) {
            case '':
                $o .= $this->handlePluginInfo();
                break;
            default:
                $o .= plugin_admin_common($action, $admin, 'fa');
        }
    }

    private function handlePluginInfo()
    {
        global $title, $pth;

        $title = 'Fa';
        $view = new View('info');
        $view->logo = "{$pth['folder']['plugins']}fa/fa.png";
        $view->version = self::VERSION;
        $checkService = new SystemCheckService;
        $view->checks = $checkService->getChecks();
        return $view;
    }
}
