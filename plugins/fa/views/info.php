<?php

use Fa\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $version
 * @var list<object{state:string,label:string,stateLabel:string}> $checks
 */
?>

<article class="fa_pluginfo">
    <h1>Fa <?=$this->esc($version)?></h1>
    <section class="fa_syscheck">
        <h2><?=$this->text('syscheck_title')?></h2>
<?php foreach ($checks as $check):?>
        <p class="xh_<?=$this->esc($check->state)?>"><?=$this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
    </section>
</article>
