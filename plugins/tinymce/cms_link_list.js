var tinyMCELinkList;

if(window.opener){

    tinyMCELinkList = window.opener.myLinkList;
}
else{
    tinyMCELinkList = window.parent.myLinkList;
}
