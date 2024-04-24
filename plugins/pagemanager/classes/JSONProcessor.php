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

use XH\PageDataRouter;

class JSONProcessor
{
    /**
     * @var string[]
     */
    private $contents;

    /**
     * @var string[]
     */
    private $newContents;

    /**
     * @var list<array<string,mixed>>
     */
    private $pageData;

    /**
     * @var int
     */
    private $level;

    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $pdattrName;

    /**
     * @var int
     */
    private $now;

    /**
     * @var string
     */
    private $pdattr;

    /**
     * @var bool
     */
    private $mayRename;

    /**
     * @var PageDataRouter
     */
    private $pdRouter;

    /**
     * @param string[] $contents
     * @param string $pdattrName
     * @param int $now
     */
    public function __construct(array $contents, $pdattrName, $now)
    {
        global $pd_router;

        $this->contents = $contents;
        $this->pdattrName = $pdattrName;
        $this->now = $now;
        $this->pdRouter = $pd_router;
    }

    /**
     * @param string $json
     * @return void
     */
    public function process($json)
    {
        $this->level = 0;
        $this->newContents = array();
        $this->pageData = array();
        $this->processPages(json_decode($json, true));
    }

    /**
     * @param list<array<string,mixed>> $pages
     * @return void
     */
    private function processPages(array $pages)
    {
        $this->level++;
        foreach ($pages as $page) {
            $this->processPage($page);
        }
        $this->level--;
    }

    /**
     * @param array<string,mixed> $page
     * @return void
     */
    private function processPage(array $page)
    {
        if (strpos($page['id'], 'pagemanager_') !== 0) {
            $this->id = null;
        } else {
            $pattern = '/^pagemanager_([0-9]*)(?:_copy_[0-9]+)?$/';
            $id = preg_replace($pattern, '$1', $page['id']);
            assert(!is_array($id));
            $this->id = (int) $id;
        }
        $title = str_replace("|-|", "\xC2\xAD", $page['text']);
        $this->title = htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8');
        $this->pdattr = $page['state']['checked'] ? '1' : '0';
        $this->mayRename = !preg_match('/unrenameable$/', $page['type']);

        if (isset($this->contents[$this->id])) {
            $this->appendExistingPageContent();
        } else {
            $this->appendNewPageContent();
        }
        $this->appendPageData();

        $this->processPages($page['children']);
    }

    /**
     * @return void
     */
    private function appendExistingPageContent()
    {
        $content = $this->contents[$this->id];
        if ($this->mayRename) {
            $content = $this->replaceHeading($content);
        }
        $this->newContents[] = $content;
    }

    /**
     * @param string $content
     * @return string
     */
    private function replaceHeading($content)
    {
        $pattern = "/<!--XH_ml[0-9]:.*?-->/";
        $replacement = "<!--XH_ml{$this->level}:"
            . addcslashes($this->title, '$\\') . '-->';
        return (string) preg_replace($pattern, $replacement, $content, 1);
    }

    /**
     * @return void
     */
    private function appendNewPageContent()
    {
        $this->newContents[] = "<!--XH_ml{$this->level}:{$this->title}-->";
    }

    /**
     * @return void
     */
    private function appendPageData()
    {
        if (isset($this->id)) {
            $pageData = $this->pdRouter->find_page($this->id);
        } else {
            $pageData = $this->pdRouter->new_page();
            $pageData['last_edit'] = $this->now;
        }
        if ($this->mayRename) {
            $pageData['url'] = uenc($this->title);
        }
        if ($this->pdattrName !== '') {
            $pageData[$this->pdattrName] = $this->pdattr;
        }
        $this->pageData[] = $pageData;
    }

    /**
     * @return string[]
     */
    public function getContents()
    {
        return $this->newContents;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getPageData()
    {
        return $this->pageData;
    }
}
