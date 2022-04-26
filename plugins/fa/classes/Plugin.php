<?php

/**
 * Copyright 2017-2021 Christoph M. Becker
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
    const VERSION = '1.3';

    /**
     * @return void
     */
    public function run()
    {
        global $plugin_cf;

        if ($plugin_cf['fa']['require_auto']) {
            $command = new RequireCommand;
            $command->execute();
        }
        if (XH_ADM) { // @phpstan-ignore-line
            XH_registerStandardPluginMenuItems(false);
            if (XH_wantsPluginAdministration('fa')) {
                $this->handlePluginAdministration();
            }
        }
    }

    /**
     * @return void
     */
    private function handlePluginAdministration()
    {
        global $o, $admin;

        $o .= print_plugin_admin('off');
        switch ($admin) {
            case '':
                $o .= $this->handlePluginInfo();
                break;
            default:
                $o .= plugin_admin_common();
        }
    }

    /**
     * @return string
     */
    private function handlePluginInfo()
    {
        global $title, $pth;

        $title = 'Fa';
        $checkService = new SystemCheckService;
        $view = new View('info');
        $view->data = array(
            'logo' => "{$pth['folder']['plugins']}fa/fa.png",
            'version' => self::VERSION,
            'checks' => $checkService->getChecks(),
        );
        return (string) $view;
    }
}
