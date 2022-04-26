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

class RequireCommand
{
    /**
     * @var bool
     */
    private static $isEmitted = false;

    /**
     * @var string
     */
    private $pluginFolder;

    public function __construct()
    {
        global $pth;

        $this->pluginFolder = "{$pth['folder']['plugins']}fa/";
    }

    /**
     * @return void
     */
    public function execute()
    {
        global $hjs, $plugin_cf;

        if (self::$isEmitted) {
            return;
        }
        self::$isEmitted = true;

        switch ($plugin_cf['fa']['fontawesome_version']) {
            case '5':
                $fa_css_pth = 'css/v5/all.min.css';
                break;
            default:
                $fa_css_pth = 'css/font-awesome.min.css';
        }
        $hjs .= '<link rel="stylesheet" type="text/css" href="' . $this->pluginFolder . $fa_css_pth . '">';
        if ($plugin_cf['fa']['fontawesome_version'] === '5' && $plugin_cf['fa']['fontawesome_shim']) {
            $hjs .= '<link rel="stylesheet" type"text/css" href="' . $this->pluginFolder . 'css/v5/v4-shims.min.css">';
        }
    }
}
