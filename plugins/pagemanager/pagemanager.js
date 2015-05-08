/*!
 * Pagemanager_XH.
 *
 * Copyright 2011-2015 Christoph M. Becker (http://3-magi.net/)
 * Licensed under GNU GPLv3, see LICENSE.
 */
(function($){"use strict";var element=null,widget=null,modified=false,init;function level(obj){var res=0;while(obj.attr("id")!=="pagemanager"){obj=obj.parent().parent();res+=1;}
return res;}
function childLevels(obj){var res=-1;while(obj.length>0){obj=obj.find("li");res+=1;}
return res;}
function checkPages(parent){var nodes,i,node;nodes=widget._get_children(parent);for(i=0;i<nodes.length;i+=1){node=widget._get_node(nodes[i]);if(node.attr("data-pdattr")==="1"){widget.check_node(node);}
checkPages(node);}}
function markNewPages(node){var children,i,child;children=widget._get_children(node);for(i=0;i<children.length;i+=1){child=children[i];widget.set_type("new",child);markNewPages(child);}}
function markCopiedPages(event,data){var result;result=data.rslt;if(result.cy){widget.set_type("new",result.oc);markNewPages(result.oc);}}
function beforeSubmit(){var attribs,json;attribs=["id","title","data-pdattr","class"];json=JSON.stringify(widget.get_json(-1,attribs));$("#pagemanager-json").val(json);}
function submit(){var url,form,data,message,status,request;function onReadyStateChange(){if(request.readyState===4){status.css("display","none");if(request.status===200){message=request.responseText;}else{message="<p class=\"xh_fail\"><strong>"+request.status+" "+request.statusText+"</strong><br>"+
request.responseText+"</p>";}
status.after(message);widget.destroy();init();}}
beforeSubmit();form=$("#pagemanager-form");url=form.attr("action");message=form.children(".xh_success, .xh_fail, .cmsimplecore_success, .cmsimplecore_fail");message.remove();status=$(".pagemanager-status");status.css("display","block");data=form.serialize();request=new XMLHttpRequest();request.open("POST",url);request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");request.onreadystatechange=onReadyStateChange;request.send(data);}
function confirmStructureWarning(){$("#pagemanager-structure-warning").hide(500);$("#pagemanager-save, #pagemanager-submit").show();}
function alert(message){$("#pagemanager-alert").html(message).dialog("open");}
function doWithSelection(operation){var selection;selection=widget.get_selected();if(selection.length>0){switch(operation){case"create_after":widget.create(selection,"after");break;case"delete":widget.remove(selection);break;case"paste_after":widget.pasteAfter(selection);break;default:widget[operation](selection);}}else{if(PAGEMANAGER.verbose){alert(PAGEMANAGER.noSelectionMessage);}}}
function tool(operation){switch(operation){case"expand":widget.open_all();break;case"collapse":widget.close_all();break;case"save":submit();break;default:doWithSelection(operation);}}
function beforeCreateNode(event,data){var node,where,targetLevel,result;node=data.args[0];where=data.args[1];targetLevel=level(node)-(where==="after"?1:0);if(targetLevel<PAGEMANAGER.menuLevels){result=undefined;}else{if(PAGEMANAGER.verbose){alert(PAGEMANAGER.menuLevelMessage);}
event.stopImmediatePropagation();result=false;}
return result;}
function beforeRename(event,data){var node=data.args[0],title,result;if(!node.hasClass("pagemanager-no-rename")){title=node.attr("title");widget.set_text(node,title);result=undefined;}else{alert(PAGEMANAGER.cantRenameError);event.stopImmediatePropagation();result=false;}}
function beforeRemove(event,data){var node,what,toplevelNodes,buttons;node=data.args[0];what=data.args[1];toplevelNodes=widget.get_container_ul().children();if(toplevelNodes.length===1&&node.get(0)===toplevelNodes.get(0)){if(PAGEMANAGER.verbose){alert(PAGEMANAGER.deleteLastMessage);}
event.stopImmediatePropagation();return false;}
if(what!=="confirmed"){if(PAGEMANAGER.verbose){buttons={};buttons[PAGEMANAGER.deleteButton]=function(){widget.remove(node,"confirmed");$(this).dialog("close");};buttons[PAGEMANAGER.cancelButton]=function(){$(this).dialog("close");};$("#pagemanager-confirmation").html(PAGEMANAGER.confirmDeletionMessage).dialog("option","buttons",buttons).dialog("open");event.stopImmediatePropagation();return false;}}
return undefined;}
function isLegalMove(move){var sourceLevels,targetLevels,extraLevels,totalLevels,allowed;if(typeof move.r!=="object"){return false;}
sourceLevels=childLevels(move.o);targetLevels=level(move.r);extraLevels=move.p==="last"||move.p==="inside"?1:0;totalLevels=sourceLevels+targetLevels+extraLevels;allowed=totalLevels<=PAGEMANAGER.menuLevels;if(!allowed&&!move.ot.data.dnd.active&&PAGEMANAGER.verbose){alert(PAGEMANAGER.menuLevelMessage);}
return allowed;}
function contextMenuItems(){return{"create":{"label":PAGEMANAGER.createOp,"action":function(obj){this.create(obj);}},"create-after":{"label":PAGEMANAGER.createAfterOp,"action":function(obj){this.create(obj,"after");}},"rename":{"label":PAGEMANAGER.renameOp,"action":function(obj){this.rename(obj);}},"remove":{"label":PAGEMANAGER.deleteOp,"action":function(obj){this.remove(obj);}},"cut":{"label":PAGEMANAGER.cutOp,"separator_before":true,"action":function(obj){this.cut(obj);}},"copy":{"label":PAGEMANAGER.copyOp,"action":function(obj){this.copy(obj);}},"paste":{"label":PAGEMANAGER.pasteOp,"action":function(obj){this.paste(obj);}},"paste-after":{"label":PAGEMANAGER.pasteAfterOp,"action":function(obj){this.pasteAfter(obj);}}};}
function markDuplicates(node,duplicates){var children,i,j,iText,jText,heading;children=widget._get_children(node);for(i=0;i<children.length;i+=1){duplicates=markDuplicates(children[i],duplicates);iText=widget.get_text(children[i]);for(j=i+1;j<children.length;j+=1){jText=widget.get_text(children[j]);if(iText===jText){duplicates+=1;heading=PAGEMANAGER.duplicateHeading+" "+duplicates;widget.set_text(children[j],heading);}}}
return duplicates;}
function restorePageHeadings(node){var children,i,child;children=widget._get_children(node);for(i=0;i<children.length;i+=1){child=children[i];widget.set_text(child,widget._get_node(child).attr("title"));restorePageHeadings(child);}}
function initDialogs(){var buttons={};$("#pagemanager-confirmation").dialog({"autoOpen":false,"modal":true});buttons[PAGEMANAGER.okButton]=function(){$(this).dialog("close");};$("#pagemanager-alert").dialog({"autoOpen":false,"modal":true,"buttons":buttons});}
function alertAjaxError(jqXHR,textStatus,errorThrown){window.alert(errorThrown);}
init=function(){var config,events,ids;if(typeof $.jstree==="undefined"){window.alert(PAGEMANAGER.offendingExtensionError);return;}
element=$("#pagemanager");$.jstree.plugin("crrm",{_fn:{pasteAfter:function(obj){obj=this._get_node(obj);if(!obj||!obj.length){return false;}
var nodes=this.data.crrm.ct_nodes||this.data.crrm.cp_nodes;if(!this.data.crrm.ct_nodes&&!this.data.crrm.cp_nodes){return false;}
if(this.data.crrm.ct_nodes){this.move_node(this.data.crrm.ct_nodes,obj,"after");this.data.crrm.ct_nodes=false;}
if(this.data.crrm.cp_nodes){this.move_node(this.data.crrm.cp_nodes,obj,"after",true);}
this.__callback({"obj":obj,"nodes":nodes});return undefined;}}});initDialogs();element.bind("loaded.jstree",function(){var events;if($("#pagemanager-structure-warning").length===0){$("#pagemanager-save, #pagemanager-submit").show();}
markDuplicates(-1,0);if(PAGEMANAGER.hasCheckboxes){checkPages(-1);}
events="move_node.jstree create_node.jstree rename_node.jstree"+" remove.jstree change_state.jstree";element.bind(events,function(){modified=true;});element.bind("before.jstree",function(e,data){switch(data.func){case"create_node":return beforeCreateNode(e,data);case"rename":return beforeRename(e,data);case"remove":return beforeRemove(e,data);default:return undefined;}});});if(PAGEMANAGER.hasCheckboxes){element.bind("change_state.jstree",function(e,data){data.rslt.attr("data-pdattr",data.args[1]?"0":"1");});}
element.bind("create_node.jstree",function(e,data){widget.set_type("new",data.rslt.obj);widget.check_node(data.rslt.obj);});element.bind("rename_node.jstree",function(e,data){widget._get_node(data.rslt.obj).attr("title",widget.get_text(data.rslt.obj));});element.bind("move_node.jstree",markCopiedPages);events="rename_node.jstree remove.jstree move_node.jstree";element.bind(events,function(){restorePageHeadings(-1);markDuplicates(-1,0);});if(!window.opera){window.onbeforeunload=function(){if(modified&&$("#pagemanager-json").val()===""){return PAGEMANAGER.leaveWarning;}
return undefined;};}else{$(window).unload(function(){if(modified&&$("#pagemanager-json").val()===""){if(window.confirm(PAGEMANAGER.leaveConfirmation)){submit();}}});}
config={"plugins":["contextmenu","crrm","dnd","themes","types","json_data","ui"],"core":{"animation":PAGEMANAGER.animation,"strings":{loading:PAGEMANAGER.loading,new_node:PAGEMANAGER.newNode}},"checkbox":{"checked_parent_open":false,"two_state":true},"contextmenu":{"show_at_node":false,"select_node":true,"items":contextMenuItems},"crrm":{"move":{"check_move":isLegalMove}},"themes":{"theme":PAGEMANAGER.theme},"types":{"types":{"new":{"icon":{"image":PAGEMANAGER.imageDir+"new.png"}},"default":{}}},"ui":{"select_limit":1},"json_data":{"ajax":{"url":PAGEMANAGER.dataURL,"error":alertAjaxError}}};if(PAGEMANAGER.hasCheckboxes){config.plugins.push("checkbox");}
element.jstree(config);widget=$.jstree._reference("#pagemanager");ids="#pagemanager-save, #pagemanager-expand, #pagemanager-collapse,"+"#pagemanager-create, #pagemanager-create_after,"+"#pagemanager-rename, #pagemanager-delete, #pagemanager-cut,"+"#pagemanager-copy, #pagemanager-paste, #pagemanager-paste_after";$(ids).off("click").click(function(){tool(this.id.substr(12));});$("#pagemanager-form").off("submit").submit(function(event){event.preventDefault();submit();});$("#pagemanager-structure-warning button").click(confirmStructureWarning);element.off("dblclick").dblclick(function(){element.jstree("toggle_node");});};$(init);}(jQuery));