/**
 * The XH namespace.
 *
 * @namespace
 *
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2013 The CMSimple_XH developers (http://cmsimple-xh.org/?The_Team)
 * @license   GNU GPLv3 (http://www.gnu.org/licenses/gpl-3.0.en.html)
 * @version   $Id$
 * @since     1.6
 */
var XH = {}

/**
 * Toggles the visibility of a page data tab.
 *
 * @param {string} tabId
 *
 * @returns {undefined}
 */
XH.toggleTab = function (tabId) {
    var currView = document.getElementById("xh_view_" + tabId);
    var currTab = document.getElementById("xh_tab_" + tabId);
    var views, view, tabs, i, n, status;

    if (currTab.className == "xh_active_tab") {
        currView.className = "xh_inactive_view";
        currTab.className = "xh_inactive_tab";
        return;
    }

    views = document.getElementById("xh_pdviews").getElementsByTagName("div");
    for (i = 0, n = views.length; i < n; ++i) {
        view = views[i];
        if (view.id.indexOf("xh_view_") == 0) {
            view.className = "xh_inactive_view";
            status = XH.findViewStatus(view);
            status.getElementsByTagName("div")[0].innerHTML = "";
        }
    }

    tabs = document.getElementById("xh_pdtabs").getElementsByTagName("a");
    for (i = 0, n = tabs.length; i < n; ++i) {
        tabs[i].className = "xh_inactive_tab";
    }

    currTab.className = "xh_active_tab";
    currView.className = "xh_active_view";
};

/**
 * Displays a modal dialog.
 *
 * Requires .xh_modal_dialog_overlay to overlay the whole viewport.
 *
 * @param {HTMLElement} contentElement
 * @param {string}      width          The width of the dialog as CSS width.
 * @param {Function}    func
 *
 * @returns {undefined}
 *
 * @since 1.6
 */
XH.modalDialog = function (contentElement, width, func) {
    var overlay, center, dialog, contentClone, error, buttons, okButton,
        cancelButton, text;

    overlay = document.createElement("div");
    overlay.className = "xh_modal_dialog_overlay";
    overlay.onclick = function () {
        document.body.removeChild(overlay);
    }

    center = document.createElement("div");
    center.className = "xh_modal_dialog_center";

    dialog = document.createElement("div");
    dialog.className = "xh_modal_dialog";
    dialog.style.width = width;
    dialog.onclick = function (event) {
        if (event) {
            event.stopPropagation();
        } else {
            window.event.cancelBubble = true;
        }
    }

    contentClone = contentElement.cloneNode(true);
    contentClone.style.display = "block";
    dialog.appendChild(contentClone);

    error = document.createElement("div");
    error.className = "xh_modal_dialog_error";
    error.appendChild(document.createTextNode(""));
    dialog.appendChild(error);

    buttons = document.createElement("div");
    buttons.className = "xh_modal_dialog_buttons";

    okButton = document.createElement("button");
    text = document.createTextNode(XH.i18n["action"]["ok"]);
    okButton.appendChild(text);
    okButton.onclick = function () {
        var result = func(contentClone);

        if (result === true) {
            contentElement.parentNode.replaceChild(contentClone,
                    contentElement);
            contentClone.style.display = "none";
            document.body.removeChild(overlay);
        } else {
            error.firstChild.nodeValue = result;
        }
    }
    buttons.appendChild(okButton);

    cancelButton = document.createElement("button");
    text = document.createTextNode(XH.i18n["action"]["cancel"]);
    cancelButton.appendChild(text);
    cancelButton.onclick = function () {
        document.body.removeChild(overlay);
    }
    buttons.appendChild(cancelButton);

    dialog.appendChild(buttons);
    center.appendChild(dialog);
    overlay.appendChild(center);
    document.body.appendChild(overlay);
};

/**
 * Validates the `change password' dialog.
 * Returns `true', if everything is okay; an error message otherwise.
 *
 * @param {HTMLElement} dialog
 *
 * @returns {mixed}
 *
 * @since 1.6
 */
XH.validatePassword = function (dialog) {
    var inputs = dialog.getElementsByTagName("input"),
        oldPassword = inputs[0].value,
        newPassword = inputs[1].value,
        confirmation = inputs[2].value,
        request;

    if (oldPassword == "" || newPassword == "" || confirmation == "") {
        return XH.i18n["password"]["fields_missing"];
    }
    if (newPassword != confirmation) {
        return XH.i18n["password"]["mismatch"];
    }
    request = new XMLHttpRequest();
    request.open("GET", "?xh_check=" + encodeURIComponent(oldPassword), false);
    request.send(null);
    if (request.status != 200) {
        return XH.i18n["error"]["server"].replace("%s",
                request.status + " " + request.statusText);
    }
    if (request.responseText != 1) {
        return XH.i18n["password"]["wrong"];
    }
    return true;
};

/**
 * Returns the x-www-form-urlencoded data of a form.
 *
 * @param {HTMLFormElement} form
 *
 * @returns {string}
 *
 * @since 1.6
 */
XH.serializeForm = function (form) {
    var params = [],
        els = form.elements,
        n, i, el, name, value, checked;

    for (i = 0, n = els.length; i < n; ++i) {
        el = els[i];
        checked = !(el.type == "checkbox" || el.type == "radio") || el.checked;
        if (el.name && checked) {
            name = encodeURIComponent(el.name);
            value = encodeURIComponent(el.value);
            params.push(name + "=" + value);
        }
    }
    return params.join("&");
};

/**
 * Returns the status element of a page data form resp. tab.
 *
 * @params {HTMLElement} formOrTab
 *
 * @returns {HTMLElement}
 *
 * @since 1.6
 */
XH.findViewStatus = function (formOrTab) {
    var node;

    if (formOrTab.nodeName.toLowerCase() == "form") {
        node = formOrTab.parentNode;
        while (typeof node.id == "undefined" ||
                node.id.indexOf("xh_view_") !== 0) {
            node = node.parentNode;
        }
    } else {
        node = formOrTab;
    }
    node = node.lastChild;
    while (typeof node.className == "undefined" ||
            node.className != "xh_view_status") {
        node = node.previousSibling;
    }
    return node;
};

/**
 * Submits a page data form via AJAX.
 *
 * @param {HTMLFormElement}
 *
 * @returns {undefined}
 *
 * @since 1.6
 */
XH.quickSubmit = function (form) {
    var request = new XMLHttpRequest,
        status, img, message;

    request.open("POST", form.action + "&xh_pagedata_ajax");
    request.setRequestHeader("Content-Type",
            "application/x-www-form-urlencoded");
    status = XH.findViewStatus(form);
    img = status.getElementsByTagName("img")[0];
    message = status.getElementsByTagName("div")[0];
    message.innerHTML = '';
    img.style.display = "inline";
    request.onreadystatechange = function () {
        if (request.readyState == 4) {
            img.style.display = "none";
            message.innerHTML = request.responseText;
            if (request.status != 200) {
                form.onsubmit = null;
            }
        }
    }
    request.send(XH.serializeForm(form));
};

/**
 * Initialize the quick submit of page data forms.
 *
 * @returns {undefined}
 *
 * @since 1.6
 */
XH.initQuickSubmit = function () {
    var views, forms, i, n, form;

    views = document.getElementById("xh_pdviews");
    if (views) {
        forms = views.getElementsByTagName("form");
        for (i = 0, n = forms.length; i < n; ++i) {
            form = forms[i];
            if (!form.onsubmit) {
                form.onsubmit = function () {
                    XH.quickSubmit(this);
                    return false;
                }
            }
        }
    }
};

/**
 * Makes a focused textarea autosizing according to its content.
 *
 * @param {HTMLTextareaElement} textarea A textarea.
 *
 * @returns {undefined}
 *
 * @since 1.6
 */
XH.makeAutosize = function (textarea) {
    function resize(textarea) {
        var border = textarea.offsetHeight - textarea.clientHeight;
        var h0 = textarea.scrollHeight, h1;

        // Several layout engines increase the scrollHeight
        // after the following. Temporarily setting style.height="auto"
        // seems to work around this issue.
        textarea.style.height = (textarea.scrollHeight  + border ) + "px";
        h1 = textarea.scrollHeight;
        if (h0 != h1) {
            textarea.style.height = "auto";
            textarea.style.height = (textarea.scrollHeight  + border ) + "px";
        }
    }

    function onResize(event) {
        var ev = event || window.event;
        var textarea = ev.target || ev.srcElement;

        resize(textarea);
    }

    function onPropertyChange(event) {
        var ev = event || window.event;
        var textarea = ev.target || ev.srcElement;

        if (ev.propertyName == "value") {
            resize(textarea);
        }
    }

    function onBlur(event) {
        var ev = event || window.event;
        var textarea = ev.target || ev.srcElement;

        textarea.style.height = null;
    }

    if (typeof textarea.addEventListener != "undefined") {
        textarea.addEventListener("focus", onResize, false);
        //textarea.addEventListener("blur", onBlur, false);
        if (typeof textarea.oninput != "undefined") {
            textarea.addEventListener("input", onResize, false);
        } else if (typeof textarea.onpropertychange != "undefined") {
            textarea.addEventListener("onpropertychange", onPropertyChange,
                    false);
        } else {
            textarea.addEventListener("keypress", onResize, false);
        }
    } else {
        textarea.attachEvent("onfocus", onResize);
        //textarea.attachEvent("onblur", onBlur);
        textarea.attachEvent("onpropertychange", onPropertyChange);
    }
    // the following would be nice, but it's very slow for many textareas
    //resize(textarea);
};

/**
 * Makes all textareas which are descendends of a node autosizing according to
 * their content, when they got the focus.
 *
 * @param {Node} node A DOM node.
 *
 * @returns {undefined}
 *
 * @since 1.6
 */
XH.makeTextareasAutosize = function (node) {
    var textareas = node.getElementsByTagName("textarea");
    var i, count;

    for (i = 0, count = textareas.length; i < count; i++) {
        XH.makeAutosize(textareas[i]);
    }
};

/**
 * Prompts for a valid backup suffix. Returns whether to continue.
 *
 * @param {HTMLFormElement} form
 *
 * @returns {bool}
 *
 * @since 1.6
 */
XH.promptBackupName = function (form) {
    var suffix, field;

    field = form.elements["xh_suffix"];
    suffix = field.value;
    do {
        suffix = prompt(XH.i18n["settings"]["backupsuffix"], suffix);
        if (suffix === null) {
            return false;
        }
    } while (!/^[a-z_0-9-]{1,20}$/i.test(suffix));
    field.value = suffix;
    return true;
};

/*
 * Initialize the quick submit of page data forms.
 */
XH.initQuickSubmit();
