if (!PAGEMANAGER) {
    /**
     * The pagemanager namespace.
     *
     * @namespace
     *
     * @author    Christoph M. Becker <cmbecker69@gmx.de>
     * @copyright 2011-2014 Christoph M. Becker (http://3-magi.net)
     * @license   GNU GPLv3 (http://www.gnu.org/licenses/gpl-3.0.en.html)
     * @version   $Id: pagemanager.js 184 2014-03-22 12:02:13Z cmb $
     */
    PAGEMANAGER = {};
}

/**
 * The jQuery element.
 */
PAGEMANAGER.element = null;

/**
 * The jstree widget.
 */
PAGEMANAGER.widget = null;

/**
 * Whether the site structure was modified.
 */
PAGEMANAGER.modified = false;

/**
 * Returns the level of the page.
 *
 * @param {Elements} obj
 *
 * @returns {Number}
 */
PAGEMANAGER.level = function (obj) {
    var res = 0;

    while (obj.attr("id") !== "pagemanager") {
	obj = obj.parent().parent();
	res += 1;
    }
    return res;
};

/**
 * Returns the nesting level of the children.
 *
 * @param {Elements} obj
 *
 * @returns {Number}
 */
PAGEMANAGER.childLevels = function (obj) {
    var res = -1;

    while (obj.length > 0) {
	obj = obj.find("li");
	res += 1;
    }
    return res;
};

/**
 * Checks resp. unchecks all child pages of a page.
 *
 * @param {Element} parent
 *
 * @return {undefined}
 */
PAGEMANAGER.checkPages = function (parent) {
    var nodes, i, node;

    nodes = PAGEMANAGER.widget._get_children(parent);
    for (i = 0; i < nodes.length; i += 1) {
	node = PAGEMANAGER.widget._get_node(nodes[i]);
	if (node.attr("data-pdattr") === "1") {
	    PAGEMANAGER.widget.check_node(node);
	}
	PAGEMANAGER.checkPages(node);
    }
};

/**
 * Marks duplicate page headings as such.
 *
 * @param {} node
 * @param {Number} duration
 *
 * @returns {Number} The number of duplicate pages.
 */
PAGEMANAGER.markDuplicates = function (node, duplicates) {
    var children, i, j, iText, jText, heading;

    children = PAGEMANAGER.widget._get_children(node);
    for (i = 0; i < children.length; i += 1) {
	duplicates = PAGEMANAGER.markDuplicates(children[i], duplicates);
	iText = PAGEMANAGER.widget.get_text(children[i]);
	for (j = i + 1; j < children.length; j += 1) {
	    jText = PAGEMANAGER.widget.get_text(children[j]);
	    if (iText === jText) {
		duplicates += 1;
		heading = PAGEMANAGER.config.duplicateHeading + " " + duplicates;
		PAGEMANAGER.widget.set_text(children[j], heading);
	    }
	}
    }
    return duplicates;
};

/**
 * Marks new pages as such.
 *
 * @param {Element} node
 *
 * @returns {undefined}
 */
PAGEMANAGER.markNewPages = function (node) {
    var children, i, child;

    children = PAGEMANAGER.widget._get_children(node);
    for (i = 0; i < children.length; i += 1) {
	child = children[i];
	PAGEMANAGER.widget.set_type("new", child);
	PAGEMANAGER.markNewPages(child);
    }
};

/**
 * Marks copied pages as new.
 *
 * @param {Object} event
 * @param {Object} data
 *
 * @returns {undefined}
 */
PAGEMANAGER.markCopiedPages = function (event, data) {
    var result;

    result = data.rslt;
    if (result.cy) {
	PAGEMANAGER.widget.set_type("new", result.oc);
	PAGEMANAGER.markNewPages(result.oc);
    }
};

/**
 * Restores the page headings.
 *
 * @param {Element} node
 *
 * @returns {undefined}
 */
PAGEMANAGER.restorePageHeadings = function (node) {
    var children, i, child;

    children = PAGEMANAGER.widget._get_children(node);
    for (i = 0; i < children.length; i += 1) {
	child = children[i];
	PAGEMANAGER.widget.set_text(child, PAGEMANAGER.widget._get_node(child).attr("title"));
	PAGEMANAGER.restorePageHeadings(child);
    }
};

/**
 * Prepares the form submission.
 *
 * @returns {undefined}
 */
PAGEMANAGER.beforeSubmit = function () {
    var attribs, xml;

    attribs = ["id", "title", "data-pdattr", "class"];
    xml = PAGEMANAGER.widget.get_xml("nest", -1, attribs);
    jQuery("#pagemanager-xml").val(xml);
};

/**
 * Submits the page structure.
 *
 * @returns {undefined}
 */
PAGEMANAGER.submit = function () {
    var form, data, message, status;

    PAGEMANAGER.beforeSubmit();
    form = jQuery("#pagemanager-form");
    url = form.attr("action");
    message = form.children(
	".xh_success, .xh_fail, .cmsimplecore_success, .cmsimplecore_fail"
    );
    message.remove();
    status = jQuery(".pagemanager-status");
    status.css("display", "block");
    data = form.serialize();
    var request = new XMLHttpRequest();
    request.open("POST", url);
    request.setRequestHeader("Content-Type",
	    "application/x-www-form-urlencoded");
    request.onreadystatechange = function () {
	if (request.readyState == 4) {
	    status.css("display", "none");
	    if (request.status == 200) {
		message = request.responseText;
	    } else {
		message = "<p class=\"xh_fail\"><strong>" + request.status +
			" " + request.statusText + "</strong><br>" +
			request.responseText + "</p>";
	    }
	    status.after(message);
	    // TODO: optimization: fix structure instead of reloading
	    PAGEMANAGER.widget.destroy();
	    PAGEMANAGER.init();
	}
    }
    request.send(data);
}

/**
 * Do an operation on the currently selected node.
 *
 * @param {String} operation
 *
 * @returns {undefined}
 */
PAGEMANAGER.doWithSelection = function (operation) {
    var selection;

    selection = PAGEMANAGER.widget.get_selected();
    if (selection.length > 0) {
	switch (operation) {
	case "create_after":
	    PAGEMANAGER.widget.create(selection, "after");
	    break;
	case "delete":
	    PAGEMANAGER.widget.remove(selection);
	    break;
	case "paste_after":
	    PAGEMANAGER.widget.pasteAfter(selection);
	    break;
	default:
	    PAGEMANAGER.widget[operation](selection);
	}
    } else {
	if (PAGEMANAGER.config.verbose) {
	    PAGEMANAGER.alert(PAGEMANAGER.config.noSelectionMessage);
	}
    }
};

/**
 * Execute a tool.
 *
 * @param {String} operation
 *
 * @returns {undefined}
 */
PAGEMANAGER.tool = function (operation) {
    switch (operation) {
    case "expand":
	PAGEMANAGER.widget.open_all();
	break;
    case "collapse":
	PAGEMANAGER.widget.close_all();
	break;
    case "save":
	PAGEMANAGER.submit();
	break;
    default:
	PAGEMANAGER.doWithSelection(operation);
    }
};

/**
 * Hides the irregular page structure warning and shows the save buttons.
 *
 * @returns {undefined}
 */
PAGEMANAGER.confirmStructureWarning = function () {
    jQuery("#pagemanager-structure-warning").hide(500);
    jQuery("#pagemanager-save, #pagemanager-submit").show();
};

/**
 * Initializes the confirmation and the alert dialogs.
 *
 * @returns {undefined}
 */
PAGEMANAGER.initDialogs = function () {
    var buttons = {};

    jQuery("#pagemanager-confirmation").dialog({
	"autoOpen": false,
	"modal": true
    });

    buttons[PAGEMANAGER.config.okButton] = function () {
	jQuery(this).dialog("close");
    };
    jQuery("#pagemanager-alert").dialog({
	"autoOpen": false,
	"modal": true,
	"buttons": buttons
    });
};

/**
 * Displays an alert dialog.
 *
 * @param {String} message
 *
 * @returns {undefined}
 */
PAGEMANAGER.alert = function (message) {
    jQuery("#pagemanager-alert").html(message).dialog("open");
};

/**
 * Prevents creating a page if not allowed.
 *
 * @param {Event}  event
 * @param {Object} data
 *
 * @returns {mixed}
 */
PAGEMANAGER.beforeCreateNode = function (event, data) {
    var node, where, targetLevel;

    node = data.args[0];
    where = data.args[1];
    targetLevel = PAGEMANAGER.level(node) - (where === "after" ? 1 : 0);
    if (targetLevel < PAGEMANAGER.config.menuLevels) {
	return undefined;
    } else {
	if (PAGEMANAGER.config.verbose) {
	    PAGEMANAGER.alert(PAGEMANAGER.config.menuLevelMessage);
	}
	event.stopImmediatePropagation();
	return false;
    }
};

/**
 * Prepares renaming a node.
 *
 * @param {Event}  event
 * @param {Object} data
 *
 * @returns {mixed}
 */
PAGEMANAGER.beforeRename = function (event, data) {
    var node = data.args[0], title;

    if (!node.hasClass("pagemanager-no-rename")) {
	title = node.attr("title");
	PAGEMANAGER.widget.set_text(node, title);
	return undefined;
    } else {
	PAGEMANAGER.alert(PAGEMANAGER.config.cantRenameError);
	event.stopImmediatePropagation();
	return false;
    }
};

/**
 * Prepares deleting a page.
 *
 * @param {Event}  event
 * @param {Object} data
 *
 * @returns {mixed}
 */
PAGEMANAGER.beforeRemove = function (event, data) {
    var node, what, toplevelNodes, buttons;

    node = data.args[0];
    what = data.args[1];
    toplevelNodes = PAGEMANAGER.widget.get_container_ul().children();

    // prevent deletion of last toplevel node
    if (toplevelNodes.length === 1 && node.get(0) === toplevelNodes.get(0)) {
	if (PAGEMANAGER.config.verbose) {
	    PAGEMANAGER.alert(PAGEMANAGER.config.deleteLastMessage);
	}
	event.stopImmediatePropagation();
	return false;
    }

    // confirmation
    if (what !== "confirmed") {
	if (PAGEMANAGER.config.verbose) {
	    buttons = {};
	    buttons[PAGEMANAGER.config.deleteButton] = function () {
		PAGEMANAGER.widget.remove(node, "confirmed");
		jQuery(this).dialog("close");
	    };
	    buttons[PAGEMANAGER.config.cancelButton] = function () {
		jQuery(this).dialog("close");
	    };
	    jQuery("#pagemanager-confirmation")
		.html(PAGEMANAGER.config.confirmDeletionMessage)
		.dialog("option", "buttons", buttons)
		.dialog("open");
	    event.stopImmediatePropagation();
	    return false;
	}
    }
    return undefined;
};

/**
 * Returns whether a move is allowed.
 *
 * @param {Object} move
 *
 * @returns {Boolean}
 */
PAGEMANAGER.isLegalMove = function (move) {
    var sourceLevels, targetLevels, extraLevels, totalLevels;

    if (typeof move.r !== "object") {
	return false;
    }
    sourceLevels = PAGEMANAGER.childLevels(move.o);
    targetLevels = PAGEMANAGER.level(move.r);
    extraLevels = move.p == "last" || move.p == "inside" ? 1 : 0; // paste vs. dnd
    totalLevels = sourceLevels + targetLevels + extraLevels;
    var allowed =  totalLevels <= PAGEMANAGER.config.menuLevels;
    if (!allowed && !move.ot.data.dnd.active && PAGEMANAGER.config.verbose) {
	PAGEMANAGER.alert(PAGEMANAGER.config.menuLevelMessage);
    }
    return allowed;
};

PAGEMANAGER.contextMenuItems = function () {
    return {
	"create": {
	    "label": PAGEMANAGER.config.createOp,
	    "action": function (obj) {this.create(obj);}
	},
	"create-after": {
	    "label": PAGEMANAGER.config.createAfterOp,
	    "action": function(obj) {this.create(obj, "after");}
	},
	"rename": {
	    "label": PAGEMANAGER.config.renameOp,
	    "action": function(obj) {this.rename(obj);}
	},
	"remove" : {
	    "label": PAGEMANAGER.config.deleteOp,
	    "action": function(obj) {this.remove(obj);}
	},
	"cut": {
	    "label": PAGEMANAGER.config.cutOp,
	    "separator_before": true,
	    "action": function(obj) {this.cut(obj);}
	},
	"copy": {
	    "label": PAGEMANAGER.config.copyOp,
	    "action": function(obj) {this.copy(obj);}
	},
	"paste": {
	    "label": PAGEMANAGER.config.pasteOp,
	    "action": function(obj) {this.paste(obj);}
	},
	"paste-after": {
	    "label": PAGEMANAGER.config.pasteAfterOp,
	    "action": function(obj) {this.pasteAfter(obj);}
	}
    }
};

PAGEMANAGER.init = function () {
    if (typeof jQuery.jstree === "undefined") {
	alert(PAGEMANAGER.config.offendingExtensionError);
	return;
    }
    PAGEMANAGER.element = jQuery("#pagemanager");
    jQuery.jstree.plugin("crrm", {
	_fn: {
	    pasteAfter: function(obj) {
		obj = this._get_node(obj);
		if (!obj || !obj.length) {
		    return false;
		}
		var nodes = this.data.crrm.ct_nodes
		    ? this.data.crrm.ct_nodes
		    : this.data.crrm.cp_nodes;
		if (!this.data.crrm.ct_nodes && !this.data.crrm.cp_nodes) {
		    return false;
		}
		if (this.data.crrm.ct_nodes) {
		    this.move_node(this.data.crrm.ct_nodes, obj, "after");
		    this.data.crrm.ct_nodes = false;
		}
		if (this.data.crrm.cp_nodes) {
		    this.move_node(this.data.crrm.cp_nodes, obj, "after", true);
		}
		this.__callback({"obj": obj, "nodes": nodes });
		return undefined;
	    }
	}
    });

    PAGEMANAGER.initDialogs();

    PAGEMANAGER.element.bind("loaded.jstree", function () {
	if (jQuery("#pagemanager-structure-warning").length === 0) {
	    jQuery("#pagemanager-save, #pagemanager-submit").show();
	}
	PAGEMANAGER.markDuplicates(-1, 0);
	if (PAGEMANAGER.config.hasCheckboxes) {
	    PAGEMANAGER.checkPages(-1);
	}
	PAGEMANAGER.element.bind("move_node.jstree create_node.jstree rename_node.jstree remove.jstree change_state.jstree", function () {
	    PAGEMANAGER.modified = true;
	});
	PAGEMANAGER.element.bind("before.jstree", function (e, data) {
	    switch (data.func) {
	    case "create_node":
		return PAGEMANAGER.beforeCreateNode(e, data);
	    case "rename":
		return PAGEMANAGER.beforeRename(e, data);
	    case "remove":
		return PAGEMANAGER.beforeRemove(e, data);
	    default:
		return undefined;
	    }
	});
    });

    if (PAGEMANAGER.config.hasCheckboxes) {
	PAGEMANAGER.element.bind("change_state.jstree", function (e, data) {
	    data.rslt.attr("data-pdattr", data.args[1] ? "0" : "1");
	});
    }

    PAGEMANAGER.element.bind("create_node.jstree", function (e, data) {
	PAGEMANAGER.widget.set_type("new", data.rslt.obj);
	PAGEMANAGER.widget.check_node(data.rslt.obj);
    });

    PAGEMANAGER.element.bind("rename_node.jstree", function (e, data) {
	PAGEMANAGER.widget._get_node(data.rslt.obj).attr("title", PAGEMANAGER.widget.get_text(data.rslt.obj));
    });

    PAGEMANAGER.element.bind("move_node.jstree", PAGEMANAGER.markCopiedPages);

    PAGEMANAGER.element.bind("rename_node.jstree remove.jstree move_node.jstree", function (e, data) {
	PAGEMANAGER.restorePageHeadings(-1);
	PAGEMANAGER.markDuplicates(-1, 0);
    });

    if (!window.opera) {
	window.onbeforeunload = function () {
	    if (PAGEMANAGER.modified && jQuery("#pagemanager-xml").val() === "") {
		return PAGEMANAGER.config.leaveWarning;
	    }
	    return undefined;
	};
    } else {
	jQuery(window).unload(function () {
	    if (PAGEMANAGER.modified && jQuery("#pagemanager-xml").val() === "") {
		if (confirm(PAGEMANAGER.config.leaveConfirmation)) {
		    PAGEMANAGER.submit();
		}
	    }
	});
    }

    /*
     * Initialize jsTree.
     */
    config = {
	"plugins": [
	    "contextmenu", "crrm", "dnd", "themes", "types", "xml_data", "ui"
	],
	"core": {
	    "animation": PAGEMANAGER.config.animation,
	    "strings": {
		loading: PAGEMANAGER.config.loading,
		new_node: PAGEMANAGER.config.newNode
	    }
	},
	"checkbox": {
	    "checked_parent_open": false,
	    "two_state": true
	},
	"contextmenu": {
	    "show_at_node": false,
	    "select_node": true,
	    "items": PAGEMANAGER.contextMenuItems
	},
	"crrm": {
	    "move": {
		"check_move": PAGEMANAGER.isLegalMove
	    }
	},
	"themes": {
	    "theme": PAGEMANAGER.config.theme
	},
	"types": {
	    "types": {
		"new": {
		    "icon": {
			"image": PAGEMANAGER.config.imageDir + "new.png"
		    }
		},
		"default": {}
	    }
	},
	"ui": {
	    "select_limit": 1
	},
	"xml_data": {
	    "ajax": {
		"url": PAGEMANAGER.config.dataURL,
		"error": function (jqXHR, textStatus, errorThrown) {
		    alert(errorThrown);
		}
	    },
	    "xsl": "nest"
	}
    };
    if (PAGEMANAGER.config.hasCheckboxes) {
	config.plugins.push("checkbox");
    }
    PAGEMANAGER.element.jstree(config);
    PAGEMANAGER.widget = jQuery.jstree._reference("#pagemanager");
}

/*
 * Initialize pagemanager.
 */
jQuery(document).ready(PAGEMANAGER.init);
