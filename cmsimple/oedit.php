<?php
/*
======================================
CMSimple version 3.3 - December 31. 2009
Small - simple - smart
© 1999-2009 Peter Andreas Harteg - peter@harteg.dk

This file is part of CMSimple
For licence see notice in /cmsimple/cms.php and http://www.cmsimple.org/?Licence
======================================
OEdit - The build-in editor in CMSimple.

*/

if (eregi('oedit.php',sv('PHP_SELF')))die('Access Denied');

// Making array strings for images, links & downloads

if (@$tx['editor']['buttons']!='') {
    if (@is_dir($pth['folder']['images'])) {
        $fs=sortdir($pth['folder']['images']);
        foreach($fs as $p){
            if (preg_match("/\.gif$|\.jpg$|\.jpeg$|\.png$/i",$p)) {
                if ($iimage!='') $iimage.=',';
                $iimage.='["'.$pth['folder']['images'].$p.'","'.substr($p,0,30).'"]';
            }
        }
    } else $iimage.='["","'.$tx['error']['cntopen'].' '.$pth['folder']['images'].'"]';
    if ($iimage=='') $iimage.='["","'.$tx['editor']['noimages'].' '.$pth['folder']['images'].'"]';
    $ilink='';
    for ($i=0; $i<$cl; $i++) {
        if ($ilink!='') $ilink.=',';
        $ilink.='["'.$sn.'?'.$u[$i].'","'.substr(str_replace('"','&quot;',rmanl($h[$i])),0,30).'"]';
    }
    if (@is_dir($pth['folder']['downloads'])) {
        $fs=sortdir($pth['folder']['downloads']);
        foreach($fs as $p){
            if (preg_match("/.+\..+$/",$p)) {
                if ($ilink!='') $ilink.=',';
                $ilink.='["'.$sn.'?download='.$p.'","(File '.(round((filesize($pth['folder']['downloads'].'/'.$p))/102.4)/10).' KB)'.' '.substr($p,0,25).'"]';
            }
        }
    }
    if (@is_dir($pth['folder']['editbuttons'])) {
        $getimage='"'.$pth['folder']['editbuttons'].'"+image+".gif"';
    } else $getimage='"'.$sn.'?image="+image';
    $onload.=' onload="init()"';


// Javascript printed to head section
if(!$retrieve)$hjs.='
<script type="text/javascript">
// OEdit Ver. 3.7 - © 2007 Peter Andreas Harteg - http://www.harteg.dk
var copyright="CMSimple - http://www.cmsimple.dk";
var changemode="'.$tx['editor']['changemode'].'";
var btns='.$tx['editor']['buttons'].';
var iimage=['.$iimage.'];
var ilink=['.$ilink.'];

function getimage(image){return '.$getimage.'}

var format="HTML";
var isNav=(navigator.appName=="Netscape"||navigator.appName=="Opera");

function init(){ // turns iframe editable
    document.getElementById("f").contentWindow.document.designMode="on";
    document.getElementById("f").contentWindow.focus();
    window.status=copyright
}

function chmode(){ // changing between WYSIWYG and HTML
    if(format=="HTML"){
        if(isNav){
            var html=document.createTextNode(document.getElementById("f").contentWindow.document.body.innerHTML);
            with(document.getElementById("f").contentWindow.document.body){
                innerHTML="";
                appendChild(html)
            }

        }
        else{
            with(document.getElementById("f").contentWindow){
                with(document.body){
                    style.fontFamily="Courier";
                    style.fontSize="10pt";
                    innerText=innerHTML
                }
                focus();
                document.body.createTextRange().collapse(false)
            }

        }
        document.getElementById("html").src=img2.src;format="Text"
    }
    else{
        if(isNav){
            var html=document.getElementById("f").contentWindow.document.body.ownerDocument.createRange();
            html.selectNodeContents(document.getElementById("f").contentWindow.document.body);
            document.getElementById("f").contentWindow.document.body.innerHTML=html.toString()
        }
        else{
            with(document.getElementById("f").contentWindow){
                with(document.body){
                    innerHTML=innerText;
                    style.fontFamily="";
                    style.fontSize=""
                }
                focus();
                document.body.createTextRange().collapse(false)
            }

        }
        document.getElementById("html").src=img1.src;format="HTML"
    }

}

function cmd(c){ // button commands
    if(c=="save"){
        if(format=="HTML"){
            document.getElementById("text").value=document.getElementById("f").contentWindow.document.body.innerHTML;
            document.getElementById("ta").submit()
        }
        else if(confirm(changemode))chmode()
    }
    else if(c=="selectall")document.getElementById("f").contentWindow.document.execCommand(c,false,null);
    else if(c=="html")chmode();
    else{
        if(format=="HTML"||(c=="cut"||c=="copy"||c=="paste"||c=="undo"||c=="redo")){
            var t=null;
            if(c=="iimage"){
                t=document.forms[c].iimage.value;c="insertimage"
            }
            if(c=="ilink"){
                t=document.forms[c].ilink.value;c="createlink"
            }
            if((c.search(/h\d/)!=-1)||c=="p"){
                t="<"+c+">";c="formatblock"
            }
            document.getElementById("f").contentWindow.focus();
            if(t==null&&c=="createlink"){
                if(isNav){
                    t=prompt("Enter URL:","");
                    document.getElementById("f").contentWindow.document.execCommand("CreateLink",false,t)
                }
                else document.getElementById("f").contentWindow.document.selection.createRange().execCommand(c,true,t)
            }
            else if(c=="cut"||c=="copy"||c=="paste")document.getElementById("f").contentWindow.document.selection.createRange().execCommand(c,false,null);
            else document.getElementById("f").contentWindow.document.execCommand(c,false,t);
            document.getElementById("f").contentWindow.focus();

        }

    }

}

function tables(){ // Used for editbuttons
	for(var i=0;i<btns.length;i++){
		if(btns[i][0]=="ilink")sb(i,ilink);
		if(btns[i][0]=="iimage")sb(i,iimage);
		if(btns[i][0]=="tr")document.write("</td></tr></table></td></tr><tr><td><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>");
		else{
			if(btns[i][0]!="")document.write("<img src=\""+getimage(btns[i][0])+"\" id=\""+btns[i][0]+"\" alt=\""+btns[i][1]+"\" title=\""+btns[i][1]+"\" onclick=\"cmd(\'"+btns[i][0]+"\')\" width=\"23\" height=\"22\" style=\"border:outset 1px;\" onmouseover=\"this.style.border=\'inset 1px\';window.status=\'"+btns[i][2]+"\'\" onmouseout=\"this.style.border=\'outset 1px\';window.status=\'"+copyright+"\'\">");
			else document.write("<img src=\""+getimage("space")+"\" alt=\"\" width=\"3\" height=\"22\">")
		}
	}
}


function sb(i,t){ // selectbox
    document.write("</td><td><form id=\""+btns[i][0]+"\"><select id=\""+btns[i][0]+"\">");for(var j=0;j<t.length;j++)document.write("<option value=\""+ t[j][0]+"\">"+t[j][1]+"</option>");document.write("</select></form></td><td>")
}

function bloker(){ // Blocks dragndrop of editbuttons
    return false
}

document.ondragstart=bloker;
img1=new Image();
img1.src=getimage("html");
img2=new Image();
img2.src=getimage("layout");

</script>';

// HTML output to content

$o.='<table class="edit" width=100% border="1" cellpadding="0" cellspacing="0"><tr><td><table border="0" cellpadding="0" cellspacing="0"><tr><td>
<script type="text/javascript">tables();</script>
</td></tr></table></td></tr><tr><td><script type="text/javascript">
document.write(\'<iframe id="f" src="'.$sn.'?'.$su.'&retrieve=\'+((new Date()).getTime())+\'" width="100%" height="\'+('.$cf['editor']['height'].')+\'"></iframe>\');
</script>
</td></tr></table>
<p style="visibility:hidden;position:absolute;left:0;top:0">
	<form method="post" id="ta" action="'.$sn.'">'.tag('input type="hidden" name="selected" value="'.$u[$s].'"').tag('input type="hidden" name="function" value="save"').'<textarea name="text" id="text" style="width: 0;height: 0;visibility: hidden;"></textarea></form>
</p>';
}

?>