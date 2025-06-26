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

// phpcs:disable PSR1.Files.SideEffects

use Fa\Plugin;

if (!defined("CMSIMPLE_XH_VERSION")) {
    http_response_code(403);
    exit;
}

/**
 * @var array<string,array<string,string>> $plugin_cf
 */

if ($plugin_cf["fa"]["require_auto"]) {
    Plugin::requireCommand()->execute();
}

/**
 * @api
 * @return void
 */
function fa_require()
{
    Plugin::requireCommand()->execute();
}
