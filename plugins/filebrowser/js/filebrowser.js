function confirmFileDelete(string)
{
    return confirm(string);
}

function confirmFolderDelete(string)
{
    return confirm(string);
}

function showxhfbForm(id)
{
    forms = document.getElementsByTagName('fieldset');
    for(i=0; i<forms.length; i++){
        form = forms[i];
        if(form.className == "xhfbform"){
            form.style.display='none';
        }
    }
    document.getElementById(id).style.display='block';
    document.getElementById(id).getElementsByTagName('input')[0].focus();
}

function closexhfbForm(id)
{
    document.getElementById(id).style.display='none';
}

function oldshowRenameForm(id)
{
    
    document.getElementById("rename_" + id).style.display='inline';
    document.getElementById("rename_" + id).renameFile.select();
    document.getElementById("file_" + id).style.display='none';
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