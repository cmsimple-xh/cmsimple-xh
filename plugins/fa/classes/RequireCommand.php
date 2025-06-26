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

    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $conf;

    /**
     * @api
     * @param ?array<string,string> $conf
     */
    public function __construct(?string $pluginFolder = null, ?array $conf = null)
    {
        global $pth, $plugin_cf;
        $this->pluginFolder = $pluginFolder ?? $pth["folder"]["plugins"] . "fa/";
        $this->conf = $conf ?? $plugin_cf["fa"];
    }

    /**
     * @api
     * @return void
     */
    public function execute()
    {
        global $hjs;

        if (self::$isEmitted) {
            return;
        }
        self::$isEmitted = true;

        switch ($this->conf['fontawesome_version']) {
            case "5":
                $fa_css_pth = 'css/v5/all.min.css';
                break;
            case "6":
                $fa_css_pth = 'css/v6/all.min.css';
                break;
            default:
                $fa_css_pth = 'css/font-awesome.min.css';
        }
        $hjs .= '<link rel="stylesheet" type="text/css" href="' . $this->pluginFolder . $fa_css_pth . '">';
        if ($this->conf['fontawesome_shim']) {
            switch ($this->conf['fontawesome_version']) {
                case "5":
                    $hjs .= '<link rel="stylesheet" type="text/css" href="' . $this->pluginFolder
                        . 'css/v5/v4-shims.min.css">';
                    break;
                case "6":
                    $hjs .= '<link rel="stylesheet" type="text/css" href="' . $this->pluginFolder
                        . 'css/v6/v4-shims.min.css">';
                    break;
            }
        }
    }
}
