<h1>Fa</h1>
<img src="<?php echo $this->logo()?>" class="fa_logo" alt="<?php echo $this->text('alt_logo')?>">
<p>Version: <?php echo $this->version()?></p>
<p>
    Copyright 2017 <a href="http://3-magi.net/" target="_blank">Christoph M.
    Becker</a>
</p>
<p>
    Powered by <a href="http://fontawesome.io" target="_blank">Font Awesome by
    Dave Gandy</a>.
</p>
<p class="fa_license">
    Fa_XH is free software: you can redistribute it and/or modify it under the
    terms of the GNU General Public License as published by the Free Software
    Foundation, either version 3 of the License, or (at your option) any later
    version.
</p>
<p class="fa_license">
    Fa_XH is distributed in the hope that it will be useful, but <em>without any
    warranty</em>; without even the implied warranty of <em>merchantability</em>
    or <em>fitness for a particular purpose</em>. See the GNU General Public
    License for more details.
</p>
<p class="fa_license">
    You should have received a copy of the GNU General Public License along with
    Fa_XH. If not, see <a href="http://www.gnu.org/licenses/"
    target="_blank">http://www.gnu.org/licenses/</a>.
</p>
<div class="fa_syscheck">
    <h2><?php echo $this->text('syscheck_title')?></h2>
<?php foreach ($this->checks as $check):?>
    <p class="xh_<?php echo $this->escape($check->state)?>"><?php echo $this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
</div>
