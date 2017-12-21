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

use XH\Pages;

class Plugin
{
    const VERSION = '3.2';

    public function run()
    {
        global $f, $cf, $o;

        if (function_exists('XH_registerStandardPluginMenuItems')) {
            XH_registerStandardPluginMenuItems(false);
        }
        if ($f === 'xhpages' && in_array($cf['pagemanager']['external'], array('', 'pagemanager'))) {
            $o .= $this->handleMainAdministration('plugin_text');
        } elseif (XH_wantsPluginAdministration('pagemanager')) {
            $o .= $this->handleAdministration();
        }
    }

    /**
     * @return string
     */
    private function handleAdministration()
    {
        global $plugin, $admin, $action;

        $o = print_plugin_admin('on');
        switch ($admin) {
            case '':
                $controller = new PluginInfoController;
                ob_start();
                $controller->indexAction();
                $o .= ob_get_clean();
                break;
            case 'plugin_main':
                $o .= $this->handleMainAdministration($action);
                break;
            default:
                $o .= plugin_admin_common($action, $admin, $plugin);
        }
        return $o;
    }

    /**
     * @param string $action
     * @return string
     */
    private function handleMainAdministration($action)
    {
        $controller = new MainAdminController;
        switch ($action) {
            case 'plugin_data':
                $controller->dataAction();
                exit;
            case 'plugin_save':
                $controller->saveAction();
                exit;
            default:
                ob_start();
                $controller->indexAction();
                return ob_get_clean();
        }
    }
}
