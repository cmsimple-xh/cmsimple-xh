<!-- Pagemanager_XH: widget -->
<h1>Pagemanager &ndash; <?php echo $this->lang('menu_main')?></h1>
<form id="pagemanager-form" action="<?php echo XH_hsc($this->submissionURL());?>"
      method="post" accept-charset="UTF-8">
<?php if ($this->model->isIrregular()):?>
    <div id="pagemanager-structure-warning" class="cmsimplecore_warning">
        <p><?php echo $this->lang('error_structure_warning');?></p>
        <p>
            <button type="button">
                <?php echo $this->lang('error_structure_confirmation');?>
            </button>
        </p>
    </div>
<?php endif;?>
    <p class="pagemanager-status" style="display:none">
        <img src="<?php echo $this->ajaxLoaderPath();?>" alt="Loading">
    </p>
    <!-- toolbar -->
<?php if ($this->hasToolbar()):?>
    <div id="pagemanager-toolbar" class="<?php echo $this->toolbarClass();?>">
<?php foreach ($this->tools() as $tool):?>
        <?php echo $this->tool($tool);?>
<?php endforeach;?>
        <div style="clear: both"></div>
    </div>
<?php endif;?>
    <div id="pagemanager" class="<?php echo $this->toolbarClass();?>"></div>
    <input type="hidden" name="admin" value="plugin_main">
    <input type="hidden" name="action" value="plugin_save">
    <input type="hidden" name="json" id="pagemanager-json" value="">
    <input id="pagemanager-submit" type="submit" class="submit"
           value="<?php echo $this->lang('button_save');?>" style="display: none">
    <?php global $_XH_csrfProtection; echo $_XH_csrfProtection->tokenInput();?>
    <p class="pagemanager-status" style="display:none">
        <img src="<?php echo $this->ajaxLoaderPath();?>" alt="Loading">
    </p>
</form>
<div id="pagemanager-footer"></div>
<div id="pagemanager-confirmation" title="<?php echo $this->lang('message_confirm');?>"></div>
<div id="pagemanager-alert" title="<?php echo $this->lang('message_information');?>"></div>
<script type="text/javascript">var PAGEMANAGER = <?php echo $this->jsConfig();?>;</script>
<script type="text/javascript" src="<?php echo $this->jsScriptPath();?>"></script>
