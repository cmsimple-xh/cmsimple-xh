<h1><?=$this->title()?></h1>
<form id="pagemanager_form" action="<?=$this->submissionUrl()?>"
      method="post" accept-charset="UTF-8">
<?php if ($this->isIrregular):?>
    <div id="pagemanager_structure_warning" class="cmsimplecore_warning">
        <p><?=$this->text('error_structure_warning')?></p>
        <p>
            <button type="button">
                <?=$this->text('error_structure_confirmation')?>
            </button>
        </p>
    </div>
<?php endif?>
    <p class="pagemanager_status" style="display:none">
        <img src="<?=$this->ajaxLoaderPath()?>" alt="Loading">
    </p>
<?php if ($this->hasToolbar):?>
    <div id="pagemanager_toolbar">
<?php   foreach ($this->tools as $tool => $class):?>
        <button type="button" id="pagemanager_<?=$this->escape($tool)?>" title="<?=$this->text("op_{$tool}")?>" aria-label="<?=$this->text("op_{$tool}")?>">
            <span class="<?=$this->escape($class)?> fa-lg" aria-hidden="true"></span>
        </button>
<?php   endforeach?>
    </div>
<?php endif?>
    <div id="pagemanager"></div>
    <input type="hidden" name="admin" value="plugin_main">
    <input type="hidden" name="action" value="plugin_save">
    <input type="hidden" name="json" id="pagemanager_json" value="">
    <input type="hidden" name="pagemanager_pdattr" value="<?=$this->pdattr?>">
    <?=$this->csrfTokenInput()?>
    <p class="pagemanager_status" style="display:none">
        <img src="<?=$this->ajaxLoaderPath()?>" alt="Loading">
    </p>
</form>
