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
        $systemCheckService = new SystemCheckService;
        $view->checks = $systemCheckService->getChecks();
        $view->render();
    }
}
