<h1>Pagemanager – <?php echo $this->text('menu_info')?></h1>
<img src="<?php echo $this->logoPath()?>" class="pagemanager_logo" alt="<?php echo $this->text('alt_logo')?>">
<p>
    Version <?php echo $this->version()?>
</p>
<p>
    Copyright &copy; 2011-2017 Christoph M. Becker
</p>
<p>
    Pagemanager_XH is powered by <a href="http://www.jstree.com/"
    target="_blank">jsTree</a>.
</p>
<p class="pagemanager_license">
    Pagemanager_XH is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as published by the Free
    Software Foundation, either version 3 of the License, or (at your option)
    any later version.
</p>
<p class="pagemanager_license">
    Pagemanager_XH is distributed in the hope that it will be useful, but
    <em>without any warranty</em>; without even the implied warranty of
    <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
    the GNU General Public License for more details.
</p>
<p class="pagemanager_license">
    You should have received a copy of the GNU General Public License along with
    this program. If not, see <a href="http://www.gnu.org/licenses/"
    target="_blank">http://www.gnu.org/licenses/</a>.
</p>
<div class="pagemanager_syscheck">
    <h2><?php echo $this->text('syscheck_title')?></h2>
<?php foreach ($this->checks as $check):?>
    <p class="xh_<?php echo $this->escape($check->state)?>"><?php echo $this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
</div>
