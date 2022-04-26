<h1>Fa</h1>
<img src="<?php echo $this->logo()?>" class="fa_logo" alt="<?php echo $this->text('alt_logo')?>">
<p>Version: <?php echo $this->version()?></p>
<p>
    Copyright 2017-2021 <a href="http://3-magi.net/" target="_blank">Christoph M.
    Becker</a>
</p>
<p>
    Powered by <a href="http://fontawesome.io" target="_blank">Font Awesome by
    Dave Gandy</a>.
</p>
<div style="clear:both"></div>
<div class="fa_syscheck">
    <h2><?php echo $this->text('syscheck_title')?></h2>
<?php foreach ($this->checks as $check):?>
    <p class="xh_<?php echo $this->escape($check->state)?>"><?php echo $this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
</div>
