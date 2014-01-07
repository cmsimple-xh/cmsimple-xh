/**
 * The FILEBROWSER namespace.
 *
 * @namespace
 *
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 20011-2013 The CMSimple_XH developers (http://cmsimple-xh.org/?The_Team)
 * @license   GNU GPLv3 (http://www.gnu.org/licenses/gpl-3.0.en.html)
 * @version   $Id$
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

    for (i = 0; i < forms.length; ++i) {
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
 * @param {string} renameForm
 * @param {string} messsage
 *
 * @returns {undefined}
 */
FILEBROWSER.promptNewName = function (renameForm, message) {
    var oldNameInput = renameForm["renameFile"];
    var newName = prompt(message, oldNameInput.value);

    if (newName) {
        oldNameInput.value = newName;
    }
    return !!newName;
}

/**
 * Returns whether a file exists in the current directory.
 *
 * @param {string} filename A file name.
 *
 * @returns {bool}
 *
 * @todo Optimize with document.getElementsByClassName if available.
 */
FILEBROWSER.fileExists = function (filename) {
    var els = document.getElementById("filebrowser_files").getElementsByTagName("*");
    var i, el;

    for (i = 0; i < els.length; ++i) {
        el = els[i];
        if (/(^|\s)xhfbfile($|\s)/.test(el.className)) {
            if (el.firstChild.nodeValue === filename) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Returns the basename of a file.
 *
 * @param {string} filename A file name.
 *
 * @returns {string}
 */
FILEBROWSER.basename = function (filename) {
    return /[^\/\\]+$/.exec(filename)[0];
}

/**
 * Obtains information, whether an already existing file should be uploaded.
 *
 * @param {HTMLFormElement} form
 * @param {string}          message
 *
 * @returns {bool}
 */
FILEBROWSER.checkUpload = function (form, message) {
    var filename = FILEBROWSER.basename(form.elements["fbupload"].value);

    if (FILEBROWSER.fileExists(filename)) {
        return confirm(message);
    }
    return true;
}
