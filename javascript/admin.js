/**
 * JS for CMSimple_XH's admin mode
 *
 * @version $Id$
 * @since   1.6
 */

var xh = {
    toggleTab: function(tabID) {
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
                if (views[i].id.indexOf("PLTab_" == 0)) {
                        views[i].className = "inactive_view";
                }
        }
        for (i = 0; i < tabs.length; i++) {
                tabs[i].className = "inactive_tab";
        }
        currTab.className = "active_tab";
        currView.className = "active_view";
        return;
    }
}
