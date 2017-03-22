<h1>Pagemanager – <?php echo $this->text('menu_info')?></h1>
<img src="<?php echo $this->logoPath()?>" style="float: left; margin-right: 10px" alt="Plugin Icon">
<p>
    Version: <?php echo $this->version()?>
</p>
<p>
    Copyright &copy; 2011-2017 <a href="http://3-magi.net">Christoph M. Becker</a>
</p>
<p>
    Pagemanager_XH is powered by <a
    href="http://www.cmsimple-xh.org/wiki/doku.php/extend:jquery4cmsimple">
    jQuery4CMSimple</a> and <a href="http://www.jstree.com/">jsTree</a>.
</p>
<p style="text-align: justify">
    Pagemanager_XH is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as published by the Free
    Software Foundation, either version 3 of the License, or (at your option)
    any later version.
</p>
<p style="text-align: justify">
    Pagemanager_XH is distributed in the hope that it will be useful, but
    <em>without any warranty</em>; without even the implied warranty of
    <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
    the GNU General Public License for more details.
</p>
<p style="text-align: justify">
    You should have received a copy of the GNU General Public License along with
    this program. If not, see <a
    href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.
</p>
<div style="clear: both"></div>
<h4><?php echo $this->text('syscheck_title')?></h4>
<ul style="list-style: none">
<?php foreach ($this->checks as $check):?>
    <li>
        <img src="<?php echo $this->escape($check->icon)?>" alt="<?php echo $this->escape($check->state)?>"
            style="margin: 0; height: 1em; padding-right: 1em">
        <span><?php echo $this->escape($check->check)?></span>
    </li>
<?php endforeach?>
</ul>
