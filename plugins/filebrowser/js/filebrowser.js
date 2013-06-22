/**
 * @version $Id$
 */

function confirmFileDelete(string)
{
    return confirm(string);
}

function confirmFolderDelete(string)
{
    return confirm(string);
}

function togglexhfbForm(id)
{
    var isOpen = document.getElementById(id).style.display == "block";
    var forms = document.getElementsByTagName('fieldset');
    for(var i=0; i<forms.length; i++){
        var form = forms[i];
        if(form.className == "xhfbform"){
            form.style.display='none';
        }
    }
    if (!isOpen) {
        document.getElementById(id).style.display='block';
        document.getElementById(id).getElementsByTagName('input')[0].focus();
    }
}


function showRenameForm(id, message)
{  var oldName = document.getElementById("rename_" + id).renameFile.value;
    var newName = prompt(message, oldName);

    if(newName){
 //   document.getElementById("rename_" + id).style.display='inline';
    document.getElementById("rename_" + id).renameFile.value=newName;
    document.getElementById("rename_" + id).submit();

}

}

function hideRenameForm(id)
{
    document.getElementById("rename_" + id).style.display='none';
    document.getElementById("file_" + id).style.display='inline';
}