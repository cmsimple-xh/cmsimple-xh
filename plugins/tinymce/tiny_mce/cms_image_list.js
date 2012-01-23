var tinyMCEImageList;

if(window.opener){

    tinyMCEImageList = window.opener.myImageList;
}
else{
    tinyMCEImageList = window.parent.myImageList;
}
