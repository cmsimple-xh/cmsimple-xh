/*!
 * Meta_tags_XH
 *
 * @author    Martin Damken <kontakt@zeichenkombinat.de>
 * @author    The CMSimple_XH developers <devs@cmsimple-xh.org>
 * @copyright 2009-2017 The CMSimple_XH developers
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://cmsimple-xh.org/
 */

(function () {
    "use strict";

    /**
     * Registers an event listener.
     *
     * @param {EventTarget} target
     * @param {string}      event
     * @param {Function}    listener
     *
     * @returns {undefined}
     */
    function on(target, event, listener) {
        if (typeof target.addEventListener !== "undefined") {
            target.addEventListener(event, listener, false);
        } else if (typeof target.attachEvent !== "undefined") {
            target.attachEvent("on" + event, listener);
        }
    }

    /*
     * Initalization.
     */
    on(window, "load", function () {
        var form = document.getElementById("meta_tags"),
            title = form.elements.title,
            titleIndicator = document.getElementById("mt_title_length"),
            description = form.elements.description,
            descIndicator = document.getElementById("mt_description_length");

        if (title && titleIndicator) {
            XH.displayTextLength(title, titleIndicator);
        }
        if (description && descIndicator) {
            XH.displayTextLength(description, descIndicator);
        }
    });
}());