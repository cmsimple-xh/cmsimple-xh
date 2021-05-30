<?php

/**
 * Copyright 2016-2021 Christoph M. Becker
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

class Url
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array<string,mixed>
     */
    private $params;

    /**
     * @param string $path
     * @param array<string,mixed> $params
     */
    public function __construct($path, array $params)
    {
        $this->path = $path;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->relative();
    }

    /**
     * @return string
     */
    public function relative()
    {
        $result = $this->path;
        if (!empty($this->params)) {
            $result .= '?' . $this->queryString();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function absolute()
    {
        $result = CMSIMPLE_URL;
        if (!empty($this->params)) {
            $result .= '?' . $this->queryString();
        }
        return $result;
    }

    /**
     * @return string
     */
    private function queryString()
    {
        return (string) preg_replace('/=(?=&|$)/', '', http_build_query($this->params, "", '&'));
    }

    /**
     * @param string $param
     * @param string $value
     * @return self
     */
    public function with($param, $value)
    {
        $params = $this->params;
        $params[$param] = (string) $value;
        return new self($this->path, $params);
    }

    /**
     * @param string $param
     * @return self
     */
    public function without($param)
    {
        $params = $this->params;
        unset($params[$param]);
        return new self($this->path, $params);
    }
}
