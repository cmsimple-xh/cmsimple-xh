/**
 * JS for CMSimple_XH's admin mode
 *
 * @version $Id$
 * @since   1.6
 */


/**
 * The namespace object.
 */
var xh = {}


/**
 * Activates a tab above the contents editor.
 *
 * @param {String} tabID
 * @returns {undefined}
 */
xh.toggleTab = function(tabID) {
    var currView = document.getElementById("PLTab_" + tabID);
    var currTab = document.getElementById("tab_" + tabID);
    if (currTab.className == "active_tab") {
        currView.className = "inactive_view";
        currTab.className = "inactive_tab";
        return;
    }
    var views = document.getElementById("pd_views").getElementsByTagName("div");
    var tabs = document.getElementById("pd_tabs").getElementsByTagName("a");
    for (i = 0; i < views.length; i++) {
        if (views[i].id.indexOf("PLTab_") == 0) {
            views[i].className = "inactive_view";
            var status = xh.findPDTabStatus(views[i]);
            status.getElementsByTagName("DIV")[0].innerHTML = "";
        }
    }
    for (i = 0; i < tabs.length; i++) {
        tabs[i].className = "inactive_tab";
    }
    currTab.className = "active_tab";
    currView.className = "active_view";
    return;
}


/**
 * Displays a modal dialog.
 *
 * Requires .xh_modal_dialog_overlay to overlay the whole viewport.
 *
 * @param {HTMLElement} contentElement
 * @param {String} width  The width of the dialog as CSS width.
 * @param {Function} func
 * @returns {undefined}
 */
xh.modalDialog = function(contentElement, width, func) {
    // TODO: i18n
    var overlay = document.createElement("div");
    overlay.className = "xh_modal_dialog_overlay";
    overlay.onclick = function() {
        document.body.removeChild(overlay);
    }

    var center = document.createElement("div");
    center.className = "xh_modal_dialog_center";

    var dialog = document.createElement("div");
    dialog.className = "xh_modal_dialog";
    dialog.style.width = width;
    dialog.onclick = function(event) {
        if (event) {
            event.stopPropagation();
        } else {
            window.event.cancelBubble = true;
        }
    }

    var contentClone = contentElement.cloneNode(true);
    contentClone.style.display = "block";
    dialog.appendChild(contentClone);

    var error = document.createElement("div");
    error.className = "xh_modal_dialog_error";
    error.appendChild(document.createTextNode(""));
    dialog.appendChild(error);

    var buttons = document.createElement("div");
    buttons.className = "xh_modal_dialog_buttons";

    var okButton = document.createElement("button");
    okButton.appendChild(document.createTextNode("OK"));
    okButton.onclick = function() {
        var result = func(contentClone);
        if (result === true) {
            contentElement.parentNode.replaceChild(contentClone, contentElement);
            contentClone.style.display = "none";
            document.body.removeChild(overlay);
        } else {
            error.firstChild.nodeValue = result;
        }
    }
    buttons.appendChild(okButton);

    var cancelButton = document.createElement("button");
    cancelButton.appendChild(document.createTextNode("Cancel"));
    cancelButton.onclick = function() {
        document.body.removeChild(overlay);
    }
    buttons.appendChild(cancelButton);

    dialog.appendChild(buttons);

    center.appendChild(dialog);

    overlay.appendChild(center);

    document.body.appendChild(overlay);
}


/**
 * Validates the `change password' dialog.
 * Returns `true', if everything is okay; an error message otherwise.
 *
 * @param {HTMLElement} dialog
 * @returns {mixed}
 */
xh.validatePassword = function(dialog) {
    // TODO: i18n
    var inputs = dialog.getElementsByTagName("input"),
        oldPassword = inputs[0].value,
        newPassword = inputs[1].value,
        confirmation = inputs[2].value,
        request;

    if (oldPassword == "" || newPassword == "" || confirmation == "") {
        return "Fill out all fields!";
    }
    if (newPassword != confirmation) {
        return "New password and confirmation must match!";
    }
    request = new XMLHttpRequest();
    request.open("GET", "?xh_check=" + encodeURIComponent(oldPassword), false);
    request.send(null);
    if (request.status != 200) {
        return "Server error: " + request.statusText;
    }
    if (request.responseText != 1) {
        return "Wrong password!";
    }
    return true;
}

/**
 * Returns the x-www-form-urlencoded data of a form.
 */
xh.serializeForm = function(form) {
    var params = [],
        els = form.elements, n = els.length, i, el,
        name, value;

    for (i = 0; i < n; ++i) {
        el = els[i];
        if (el.name && (!(el.type == "checkbox" || el.type == "radio") || el.checked)) {
            name = encodeURIComponent(el.name);
            value = encodeURIComponent(el.value);
            params.push(name + "=" + value);
        }
    }
    return params.join("&");
}

/**
 * Returns the status element of a page data form resp. tab.
 */
xh.findPDTabStatus = function(formOrTab) {
    var node;

    if (formOrTab.nodeName == "FORM") {
        node = formOrTab.parentNode;
        while (typeof node.id == "undefined" || node.id.indexOf("PLTab_") !== 0) {
            node = node.parentNode;
        }
    } else {
        node = formOrTab;
    }
    node = node.lastChild;
    while (typeof node.className == "undefined" || node.className != "pltab_status") {
        node = node.previousSibling;
    }
    return node;
}

/**
 * Submits a page data form via AJAX.
 */
xh.quickSubmit = function(form) {
    var request = new XMLHttpRequest,
        status, img, message;

    request.open("POST", form.action + "&xh_pagedata_ajax");
    request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    status = xh.findPDTabStatus(form);
    img = status.getElementsByTagName("IMG")[0];
    message = status.getElementsByTagName("DIV")[0];
    message.innerHTML = '';
    img.style.display = "inline";
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            img.style.display = "none";
            message.innerHTML = request.responseText;
            if (request.status != 200) {
                form.onsubmit = null;
            }
        }
    }
    request.send(xh.serializeForm(form));
}

/**
 * Initialize the quick submit of page data forms.
 */
xh.initQuickSubmit = function() {
    var views, forms, i, n, form;

    views = document.getElementById("pd_views");
    if (views) {
        forms = views.getElementsByTagName("FORM");
        for (i = 0, n = forms.length; i < n; ++i) {
            form = forms[i];
            if (!form.onsubmit) {
                form.onsubmit = function() {
                    xh.quickSubmit(this);
                    return false;
                }
            }
        }
    }
}

/*
 * Initialize the quick submit of page data forms.
 */
xh.initQuickSubmit();
