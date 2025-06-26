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

class InfoCommand
{
    /** @var string */
    private $pluginFolder;

    /** @var View */
    private $view;

    public function __construct(string $pluginFolder, View $view)
    {
        $this->pluginFolder = $pluginFolder;
        $this->view = $view;
    }

    public function __invoke(): string
    {
        global $title;
        $title = "Fa " . $this->view->esc(Plugin::VERSION);
        return $this->view->render("info", [
            "version" => Plugin::VERSION,
            "checks" => $this->getChecks(),
        ]);
    }

    /**
     * @return object[]
     */
    private function getChecks()
    {
        return array(
            $this->checkPhpVersion('7.1.0'),
            $this->checkXhVersion('1.7.0'),
            $this->checkWritability($this->pluginFolder . "css/"),
            $this->checkWritability($this->pluginFolder . "config/"),
            $this->checkWritability($this->pluginFolder . "languages/")
        );
    }

    /**
     * @param string $version
     * @return object
     */
    private function checkPhpVersion($version)
    {
        $state = $this->compareVersions(PHP_VERSION, $version, 'ge') ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_phpversion", $version);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }

    /**
     * @param string $version
     * @return object
     */
    private function checkXhVersion($version)
    {
        $state = $this->compareVersions(CMSIMPLE_XH_VERSION, "CMSimple_XH $version", 'ge') ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_xhversion", $version);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }

    /**
     * @param string $folder
     * @return object
     */
    private function checkWritability($folder)
    {
        $state = $this->isWritable($folder) ? 'success' : 'warning';
        $label = $this->view->plain("syscheck_writable", $folder);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }

    /** @return int|bool */
    protected function compareVersions(string $version1, string $version2, ?string $operator = null)
    {
        return version_compare($version1, $version2, $operator);
    }

    protected function isWritable(string $filename): bool
    {
        return is_writable($filename);
    }
}
