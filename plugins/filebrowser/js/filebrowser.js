/**
 * The FILEBROWSER namespace.
 *
 * @namespace
 *
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 20011-2013 The CMSimple_XH developers (http://cmsimple-xh.org/?The_Team)
 * @license   GNU GPLv3 (http://www.gnu.org/licenses/gpl-3.0.en.html)
 * @version   @version $Id$
 */
var FILEBROWSER = {}

/**
 * Confirms the deletion of a file.
 *
 * @param {string} string
 *
 * @returns {string}
 */
FILEBROWSER.confirmFileDelete = function (string) {
    return confirm(string);
}

/**
 * Confirms the deletion of a file.
 *
 * @param {string} string
 *
 * @returns {string}
 */
FILEBROWSER.confirmFolderDelete = function (string) {
    return confirm(string);
}

/**
 * Toggles the visibility of a form.
 *
 * @param {string} id
 *
 * @returns {undefined}
 */
FILEBROWSER.togglexhfbForm = function (id) {
    var isOpen = document.getElementById(id).style.display == "block";
    var forms = document.getElementsByTagName("fieldset");
    var i, form;

    for(i = 0; i < forms.length; ++i) {
        form = forms[i];
        if (form.className == "xhfbform") {
            form.style.display = "none";
        }
    }
    if (!isOpen) {
        document.getElementById(id).style.display = "block";
        document.getElementById(id).getElementsByTagName("input")[0].focus();
    }
}

/**
 * Shows the rename form.
 *
 * @param {string} id
 * @param {string} messsage
 *
 * @returns {undefined}
 */
FILEBROWSER.showRenameForm = function (id, message) {
    var renameForm = document.getElementById("rename_" + id);
    var oldName = renameForm.renameFile.value;
    var newName = prompt(message, oldName);

    if(newName){
        renameForm.renameFile.value = newName;
        renameForm.submit();
    }
}

/**
 * Hides the rename form.
 *
 * @param {string} id
 *
 * @returns {undefined}
 */
FILEBROWSER.hideRenameForm = function (id) {
    document.getElementById("rename_" + id).style.display = "none";
    document.getElementById("file_" + id).style.display = "inline";
}
