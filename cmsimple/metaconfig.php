<?php

$mcf['security']['password']="hidden";
$mcf['security']['secret']="random";
$mcf['security']['frame_options']="enum:DENY,SAMEORIGIN,";
$mcf['site']['template']="function:XH_templates";
$mcf['site']['compat']="hidden";
$mcf['language']['default']="function:XH_availableLocalizations";
$mcf['language']['2nd_lang_names']="hidden";
$mcf['language']['menu']=XH_registeredLanguagemenuPlugins();
$mcf['mailform']['captcha']="bool";
$mcf['mailform']['lf_only']="+bool";
$mcf['locator']['show_homepage']="bool";
$mcf['folders']['content']="+string";
$mcf['folders']['userfiles']="+string";
$mcf['folders']['downloads']="+string";
$mcf['folders']['images']="+string";
$mcf['folders']['media']="+string";
$mcf['show_hidden']['pages_toc']="bool";
$mcf['show_hidden']['pages_search']="bool";
$mcf['show_hidden']['pages_sitemap']="bool";
$mcf['show_hidden']['path_locator']="bool";
$mcf['editor']['external']="xfunction:XH_registeredEditorPlugins";
$mcf['filebrowser']['external']="xfunction:XH_registeredFilebrowserPlugins";
$mcf['pagemanager']['external']="xfunction:XH_registeredPagemanagerPlugins";
$mcf['menu']['color']="hidden";
$mcf['menu']['highlightcolor']="hidden";
$mcf['menu']['levels']="hidden";
$mcf['menu']['levelcatch']="hidden";
$mcf['menu']['sdoc']="enum:,parent";
$mcf['uri']['length']="hidden";
$mcf['editmenu']['scroll']="bool";
$mcf['editmenu']['external']="xfunction:XH_registeredEditmenuPlugins";
$mcf['mode']['advanced']="hidden";
$mcf['format']['date']="enum:none,short,medium,long,full";
$mcf['format']['time']="enum:none,short,medium,long,full";
$mcf['link']['mailto']="+bool";
$mcf['link']['tel']="+bool";
$mcf['link']['redir']="+enum:0,1,2,3";
?>
