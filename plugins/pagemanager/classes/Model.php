<?php

/**
 * Copyright 2011-2021 Christoph M. Becker
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

class Model
{
    /**
     * @var string[]
     */
    private $headings;

    /**
     * @var bool[]
     */
    private $mayRename;

    /**
     * @param int $index
     * @return string
     */
    public function getHeading($index)
    {
        return $this->headings[$index];
    }

    /**
     * @return string[]
     */
    public function getHeadings()
    {
        return $this->headings;
    }

    /**
     * @param int $index
     * @return bool
     */
    public function getMayRename($index)
    {
        return $this->mayRename[$index];
    }

    /**
     * @param string $heading
     * @return bool
     */
    private function mayRename($heading)
    {
        return strip_tags($heading) === $heading;
    }

    /**
     * @param string $heading
     * @return string
     */
    private function cleanedHeading($heading)
    {
        $heading = trim(strip_tags($heading));
        $heading = str_replace("\xC2\xAD", "|-|", $heading);
        $heading = html_entity_decode($heading, ENT_COMPAT, 'UTF-8');
        return $heading;
    }

    /**
     * @return void
     */
    public function calculateHeadings()
    {
        global $c, $tx;

        $empty = 0;
        foreach ($c as $i => $page) {
            preg_match('/<!--XH_ml[0-9]:(.*?)-->/su', $page, $matches);
            $heading = $this->cleanedHeading($matches[1]);
            if ($heading === '') {
                $empty += 1;
                $this->headings[$i] = $tx['toc']['empty'] . ' ' . $empty;
            } else {
                $this->headings[$i] = $heading;
            }
            $this->mayRename[$i] = $this->mayRename($matches[1]);
        }
    }

    /**
     * @return bool
     */
    public function isIrregular()
    {
        global $l, $cl;

        for ($i = 1; $i < $cl; $i++) {
            $delta = $l[$i] - $l[$i - 1];
            if ($delta > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string[]
     */
    public static function getThemes()
    {
        global $pth;

        $themes = array();
        $path = "{$pth['folder']['plugins']}pagemanager/jstree/themes/";
        $dir = opendir($path);
        if ($dir !== false) {
            while (($entry = readdir($dir)) !== false) {
                if ($entry[0] !== '.' && is_dir($path . $entry)) {
                    $themes[] = $entry;
                }
            }
        }
        natcasesort($themes);
        return $themes;
    }

    /**
     * @param string $json
     * @return bool
     */
    public function save($json)
    {
        global $c, $plugin_cf, $pd_router;

        $parser = new JSONProcessor(
            $c,
            $plugin_cf['pagemanager']['pagedata_attribute'],
            time()
        );
        $parser->process($json);
        $c = $parser->getContents();
        return $pd_router->refresh($parser->getPageData());
    }
}
