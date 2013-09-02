/* Torsten Behrens Webtemplatedesign */
/*
This file is part of a template, which was created by Torsten Behrens.
Take a modern CMSimple XH version. www.cmsimple-xh.org www.cmsimple.name www.cmsimple.me www.cmsimple.eu

Version 08.08.2013. Update for jQuery4CMSimple. 
##################################################################################
# Dies ist ein GPL3 Template von Torsten Behrens.                                #
# Torsten Behrens                                                                #
# Dorfstraße 2                                                                   #
# D-24619 Tarbek                                                                 #
# USt.ID-Nr. DE214080613                                                         #
# http://torsten-behrens.de                                                      #
# http://tbis.info                                                               #
# http://tbis.net                                                                #
# http://cmsimple-templates.de                                                   #
# http://cmsimple-templates.com                                                  #
##################################################################################
*/

/*jshint forin:true, noarg:true, noempty:true, eqeqeq:true, bitwise:true, strict:true, undef:true, curly:false, browser:true, jquery:false */
/*global jQuery BackgroundHelper */

// css helper
(function ($) {
    'use strict';
    var data = [
        { str: navigator.userAgent, sub: 'Chrome', ver: 'Chrome', name: 'chrome' },
        { str: navigator.vendor, sub: 'Apple', ver: 'Version', name: 'safari' },
        { prop: window.opera, ver: 'Opera', name: 'opera' },
        { str: navigator.userAgent, sub: 'Firefox', ver: 'Firefox', name: 'firefox' },
        { str: navigator.userAgent, sub: 'MSIE', ver: 'MSIE', name: 'ie' }
    ];
    var v = function (s, n) {
        var i = s.indexOf(data[n].ver);
        return (i !== -1) ? parseInt(s.substring(i + data[n].ver.length + 1), 10) : '';
    };
    var html = $('html');
    for (var n = 0; n < data.length; n++) {
        if ((data[n].str && (data[n].str.indexOf(data[n].sub) !== -1)) || data[n].prop) {
            html.addClass(data[n].name + ' ' + data[n].name + v(navigator.userAgent, n) || v(navigator.appVersion, n));
            break;
        }
    }

    // 'desktop' class is used as responsive design initial value
    html.addClass('desktop');
})(jQuery);

jQuery(function ($) {
    'use strict';
    var i, j, k, l, m;
    if (!$.browser.msie || parseInt($.browser.version, 10) !== 9) {
        return;
    }

    var splitByTokens = function (str, startToken, endToken, last) {
        if (!last) {
            last = false;
        }
        var startPos = str.indexOf(startToken);
        if (startPos !== -1) {
            startPos += startToken.length;
            var endPos = last ? str.lastIndexOf(endToken) : str.indexOf(endToken, startPos);

            if (endPos !== -1 && endPos > startPos) {
                return str.substr(startPos, endPos - startPos);
            }
        }
        return '';
    };

    var splitWithBrackets = function (str, token, brackets) {
        /*jshint nonstandard:true */
        if (!token) {
            token = ',';
        }
        if (!brackets) {
            brackets = '()';
        }
        var bracket = 0;
        var startPos = 0;
        var result = [];
        if (brackets.lenght < 2) {
            return result;
        }
        var pos = 0;
        while (pos < str.length) {
            var ch = str[pos];
            if (ch === brackets[0]) {
                bracket++;
            }
            if (ch === brackets[1]) {
                bracket--;
            }
            if (ch === token && bracket < 1) {
                result.push(str.substr(startPos, pos - startPos));
                startPos = pos + token.length;
            }
            pos++;
        }
        result.push(str.substr(startPos, pos - startPos));
        return result;
    };

    var byteToHex = function (d) {
        var hex = Number(d).toString(16);
        while (hex.length < 2) {
            hex = "0" + hex;
        }
        return hex;
    };

    for (i = 0; i < document.styleSheets.length; i++) {
        var s = document.styleSheets[i];
        var r = [s];
        for (j = 0; j < s.imports.length; j++) {
            r.push(s.imports[j]);
        }
        for (j = 0; j < r.length; j++) {
            s = r[j];
            var n = [];
            for (k = 0; k < s.rules.length; k++) {
                var css = s.rules[k].cssText || s.rules[k].style.cssText;
                if (!css) {
                    continue;
                }
                var value = splitByTokens(css, '-svg-background:', ';');
                if (value === '') {
                    continue;
                }
                var values = splitWithBrackets(value);
                for (l = 0; l < values.length; l++) {
                    var g = splitByTokens(values[l], 'linear-gradient(', ')', true);
                    if (g === '') {
                        continue;
                    }
                    var args = splitWithBrackets(g);
                    if (args.length < 3) {
                        continue;
                    }
                    var maxOffset = 0;
                    var stops = [];
                    for (m = 1; m < args.length; m++) {
                        var stopValues = splitWithBrackets($.trim(args[m]), ' ');
                        if (stopValues.length < 2) {
                            continue;
                        }
                        var stopColor = $.trim(stopValues[0]);
                        var stopOpacity = 1;
                        var colorRgba = splitByTokens(stopColor, 'rgba(', ')', true);
                        var stopOffset = $.trim(stopValues[1]);
                        if (colorRgba !== "") {
                            var rgba = colorRgba.split(',');
                            if (rgba.length < 4) {
                                continue;
                            }
                            stopColor = '#' + byteToHex(rgba[0]) + byteToHex(rgba[1]) + byteToHex(rgba[2]);
                            stopOpacity = rgba[3];
                        }
                        var isPx = stopOffset.indexOf('px') !== -1;
                        if (isPx) {
                            maxOffset = Math.max(maxOffset, parseInt(stopOffset, 10) || 0);
                        }
                        stops.push({ offset: stopOffset, color: stopColor, opacity: stopOpacity, isPx: isPx });
                    }
                    var stopsXML = '';
                    var lastStop = null;
                    for (m = 0; m < stops.length; m++) {
                        if (stops[m].isPx) {
                            stops[m].offset = ((parseInt(stops[m].offset, 10) || 0) / (maxOffset / 100)) + '%';
                        }
                        stopsXML += '<stop offset="' + stops[m].offset + '" stop-color="' + stops[m].color + '" stop-opacity="' + stops[m].opacity + '"/>';
                        if (m === stops.length - 1) {
                            lastStop = stops[m];
                        }
                    }
                    var isLeft = $.trim(args[0]) === 'left';
                    var direction = 'x1="0%" y1="0%" ' + (isLeft ? 'x2="100%" y2="0%"' : 'x2="0%" y2="100%"');
                    var gradientLength = '100%';
                    if (maxOffset > 0) {
                        gradientLength = maxOffset + 'px';
                    }
                    var size = (isLeft ? 'width="' + gradientLength + '" height="100%"' : 'width="100%" height="' + gradientLength + '"');
                    var last = "";
                    if (lastStop !== null && maxOffset > 0) {
                        last = '<rect ' +
                            (isLeft ?
                                'x="' + maxOffset + '" y="0"' :
                                'x="0" y="' + maxOffset + '"') +
                            ' width="100%" height="100%" style="fill:' + lastStop.color + ';opacity:' + lastStop.opacity + ';"/>';

                    }
                    var svgGradient = '<svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><linearGradient id="g" gradientUnits="objectBoundingBox" ' + direction + '>' + stopsXML + '</linearGradient><rect x="0" y="0" ' + size + ' fill="url(#g)" />' + last + '</svg>';
                    values[l] = values[l].replace('linear-gradient(' + g + ')', 'url(data:image/svg+xml,' + escape(svgGradient) + ')');
                }
                n.push({ s: s.rules[k].selectorText, v: 'background: ' + values.join(",") });
            }
            for (k = 0; k < n.length; k++) {
                s.addRule(n[k].s, n[k].v);
            }
        }
    }
});

jQuery(function ($) {
    'use strict';
    // ie < 9 slider multiple background fix
    if (!$.browser.msie || $.browser.version > 8) return;
    
    function split(str) {
        str = str.replace(/"/g, '').replace(/%20/g, '');
        return  str.split(/\s*,\s*/);
    }

    $('.tbisgpl3-slider .tbisgpl3-slide-item').each(function () {
        var bgs = split($(this).css('background-image'));
        // needs to use the last image
        if (bgs.length > 1) {
            $(this).css("background-image", bgs[bgs.length - 1]);
        }
    });
});

jQuery(function ($) {
    "use strict";
    // ie8
    if (!$.browser.msie || $.browser.version > 8) return;
    $('.tbisgpl3-shapes').css('z-index', 1);
    
    // ie7
    if (!$.browser.msie || $.browser.version > 7) return;
    var textblockTexts = $('.tbisgpl3-textblock > div');
    textblockTexts.each(function () {
        var tbText = $(this);
        var valign = tbText.css('vertical-align') ? tbText.css('vertical-align') : 'top';
        if (valign === 'middle') {
            var wrapper = tbText.wrap('<div/>').parent();
            tbText.css({
                'position': 'relative',
                'top': '-50%',
                'height': 'auto'
            });
            wrapper.css({
                'position': 'absolute',
                'top': '50%'
            });
        } else if (valign === 'bottom') {
            tbText.css({
                'position': 'absolute',
                'height': 'auto',
                'bottom': 0
            });
        }
    });
});

/* Set wmode=transparent for iframes to show it under the menus, lightboxes etc. */
jQuery(function ($) {
    "use strict";
    $("iframe[src]").each(function () {
        var iframe = $(this);
        var src = iframe.attr("src");
        if (src == "") {
            return;
        }
        if (src.lastIndexOf("?") !== -1) {
            src += "&amp;wmode=transparent";
        } else {
            src += "?wmode=transparent";
        }
        iframe.attr("src", src);
    });
});

jQuery(function ($) {
    "use strict";
    $(window).bind("resize", function () { navigatorResizeHandler($("html").hasClass("responsive")); });
});

var navigatorResizeHandler = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        if (responsiveDesign) return;
        $(".tbisgpl3-slider").each(function () {
            var slider = $(this);
            var sliderWidth = slider.width();
            var nav = slider.siblings(".tbisgpl3-slidenavigator");
            if (nav.length) {
                // left offset
                var left = nav.attr("data-left");
                // (margin = containerWidth - (objectPosition + objectWidth)) < 0
                var margin = sliderWidth - sliderWidth * parseFloat(left) / 100 - nav.outerWidth(false);
                if (margin < 0) {
                    nav.css("margin-left", margin);
                }
            }
        });
    };
})(jQuery);
jQuery(function($) {
    "use strict";
    $('nav.tbisgpl3-nav').addClass("desktop-nav");
});


jQuery(function ($) {
    "use strict";
    if (!$.browser.msie || parseInt($.browser.version, 10) > 7) {
        return;
    }
    $('ul.tbisgpl3-hmenu>li:not(:first-child)').each(function () { $(this).prepend('<span class="tbisgpl3-hmenu-separator"> </span>'); });
});

jQuery(function ($) {
    "use strict";
    $("ul.tbisgpl3-hmenu a:not([href])").attr('href', '#').click(function (e) { e.preventDefault(); });
});


jQuery(function ($) {
    "use strict";
    if (!$.browser.msie) {
        return;
    }
    var ieVersion = parseInt($.browser.version, 10);
    if (ieVersion > 7) {
        return;
    }

    /* Fix width of submenu items.
    * The width of submenu item calculated incorrectly in IE6-7. IE6 has wider items, IE7 display items like stairs.
    */
    $.each($("ul.tbisgpl3-hmenu ul"), function () {
        var maxSubitemWidth = 0;
        var submenu = $(this);
        var subitem = null;
        $.each(submenu.children("li").children("a"), function () {
            subitem = $(this);
            var subitemWidth = subitem.outerWidth(false);
            if (maxSubitemWidth < subitemWidth) {
                maxSubitemWidth = subitemWidth;
            }
        });
        if (subitem !== null) {
            var subitemBorderLeft = parseInt(subitem.css("border-left-width"), 10) || 0;
            var subitemBorderRight = parseInt(subitem.css("border-right-width"), 10) || 0;
            var subitemPaddingLeft = parseInt(subitem.css("padding-left"), 10) || 0;
            var subitemPaddingRight = parseInt(subitem.css("padding-right"), 10) || 0;
            maxSubitemWidth -= subitemBorderLeft + subitemBorderRight + subitemPaddingLeft + subitemPaddingRight;
            submenu.children("li").children("a").css("width", maxSubitemWidth + "px");
        }
    });
});
jQuery(function () {
    "use strict";
    setHMenuOpenDirection({
        container: "div.tbisgpl3-sheet",
        defaultContainer: "#tbisgpl3-main",
        menuClass: "tbisgpl3-hmenu",
        leftToRightClass: "tbisgpl3-hmenu-left-to-right",
        rightToLeftClass: "tbisgpl3-hmenu-right-to-left"
    });
});

var setHMenuOpenDirection = (function ($) {
    "use strict";
    return (function(menuInfo) {
        var defaultContainer = $(menuInfo.defaultContainer);
        defaultContainer = defaultContainer.length > 0 ? defaultContainer = $(defaultContainer[0]) : null;

        $("ul." + menuInfo.menuClass + ">li>ul").each(function () {
            var submenu = $(this);

            var submenuWidth = submenu.outerWidth(false);
            var submenuLeft = submenu.offset().left;

            var mainContainer = submenu.parents(menuInfo.container);
            mainContainer = mainContainer.length > 0 ? mainContainer = $(mainContainer[0]) : null;

            var container = mainContainer || defaultContainer;
            if (container !== null) {
                var containerLeft = container.offset().left;
                var containerWidth = container.outerWidth(false);

                if (submenuLeft + submenuWidth >= containerLeft + containerWidth) {
                    /* right to left */
                    submenu.addClass(menuInfo.rightToLeftClass).find("ul").addClass(menuInfo.rightToLeftClass);
                } else if (submenuLeft <= containerLeft) {
                    /* left to right */
                    submenu.addClass(menuInfo.leftToRightClass).find("ul").addClass(menuInfo.leftToRightClass);
                }
            }
        });
    });
})(jQuery);

var megaMenuCreate = (function ($) {
    "use strict";
    return function () {
        var sheet = $(".tbisgpl3-sheet");
        var sheetLeft = sheet.offset().left;
        var sheetWidth = sheet.width();
        sheetWidth -= (parseInt($(".tbisgpl3-hmenu").parents("nav").css("padding-left").replace("px", ""), 10) || 0) * 2;

        // reset
        $(".tbisgpl3-hmenu .tbisgpl3-hmenu-mega-menu").removeClass("tbisgpl3-hmenu-mega-menu").css("width", "").css("left", "").css("right", "")
            .removeAttr("data-ext-l").removeAttr("data-ext-r").children("li").css("width", "").css("margin-left", "").css("margin-right", "")
            .siblings("li.cleared").remove();

        $(".tbisgpl3-hmenu>li>ul").each(function() {
            var ul = $(this);
            ul.addClass("tbisgpl3-hmenu-mega-menu");

            var ltr = !ul.hasClass("tbisgpl3-hmenu-right-to-left");
            var widths = [];
            var horizontalMargin = 7;
            ul.children("li").each(function() {
                widths.push($(this).width());
            });
            var maxWidth = Math.max.apply(window, widths);

            var submenusWidth = maxWidth * widths.length;
            var minMegaMenuWidth = Math.min(sheetWidth, submenusWidth);
            var colsPerRow = parseInt(minMegaMenuWidth / maxWidth, 10);

            var megaMenuWidth = (maxWidth * colsPerRow) + (horizontalMargin > 0 ? horizontalMargin * colsPerRow - 1 : colsPerRow - 1);
            var parentWidth = ul.parent("li").children("a").outerWidth(false);

            if (submenusWidth > ul.parent("li").children("a").outerWidth(false)) {
                ul.children("li").css("width", Math.round(parseFloat(maxWidth, 10)) + "px");
                ul.css("width", Math.round(parseFloat(megaMenuWidth, 10)) + "px");
            }

            ul.children("li").each(function(index) {
                if (index % colsPerRow !== 0) 
                    $(this).css("margin-left", horizontalMargin + "px");
            });

            if (submenusWidth > sheetWidth) {
                var newlineLis = [];
                ul.children("li").each(function(index) {
                    if ((index + 1) % colsPerRow === 0) {
                        newlineLis.push($(this));
                    }
                });
                $(newlineLis).each(function(index, value) {
                    $("<li class=\"cleared\" style=\"padding-top:7px;float:none;\"></div>").insertAfter(value);
                });
            }

            sheetWidth = sheet.outerWidth(false);
            var li = ul.parent("li");
            var ulLeft = li.offset().left;
            var ulWidth = ul.outerWidth(false) - 60;
            if (ltr) {
                var leftOffset = 0;
                if (ulLeft + ulWidth > sheetLeft + sheetWidth) {
                    leftOffset = (ulLeft + ulWidth) - (sheetLeft + sheetWidth);
                    ul.css("left", "-" + Math.round(parseFloat(leftOffset, 10)) + "px");
                    ul.attr("data-ext-l", Math.round(parseFloat(leftOffset, 10)));
                    ul.attr("data-ext-r", Math.round(parseFloat(ulWidth - leftOffset - li.outerWidth(false), 10)));
                }
            } else {
                var rightOffset = 0;
                ulLeft = ulLeft + li.outerWidth(false) - ulWidth;
                if (ulLeft < sheetLeft) {
                    rightOffset = sheetLeft - ulLeft;
                    ul.css("right", "-" + Math.round(parseFloat(rightOffset, 10)) + "px");
                    ul.attr("data-ext-r", Math.round(parseFloat(rightOffset, 10)));
                    ul.attr("data-ext-l", Math.round(parseFloat(ulWidth - rightOffset - li.outerWidth(false), 10)));
                }
            }
        });
    };
})(jQuery);
jQuery(window).load(megaMenuCreate);

jQuery(window).bind("resize", (function ($) {
    /*global responsiveDesign */
    "use strict";
    return function () {
        if (typeof responsiveDesign !== "undefined" && responsiveDesign.isResponsive)
            return;
        var sheetLeft = $(".tbisgpl3-sheet").offset().left;
        $("header.tbisgpl3-header #tbisgpl3-flash-area").each(function () {
            var object = $(this);
            object.css("left", sheetLeft + "px");
        });
    };
})(jQuery));

jQuery(function ($) {
    'use strict';
    $(window).bind('resize', function () {
        var bh = $('body').height();
        var mh = 0;
        var c = $('div.tbisgpl3-content');
        c.removeAttr('style');

        $('#tbisgpl3-main').children().each(function() {
            if ($(this).css('position') !== 'absolute') {
                mh += $(this).outerHeight(true);
            }
        });
        
        if (mh < bh) {
            var r = bh - mh;
            c.css('height', (c.outerHeight(true) + r) + 'px');
        }
    });

    if ($.browser.msie && parseInt($.browser.version, 10) < 8) {
        $(window).bind('resize', function() {
            var c = $('div.tbisgpl3-content');
            var s = c.parent().children('.tbisgpl3-layout-cell:not(.tbisgpl3-content)');
            var w = 0;
            c.hide();
            s.each(function() { w += $(this).outerWidth(true); });
            c.w = c.parent().width(); c.css('width', c.w - w + 'px');
            c.show();
        });
    }

    $(window).trigger('resize');
});

jQuery(function($) {
    "use strict";
    if (!$('html').hasClass('ie7')) {
        return;
    }
    $('ul.tbisgpl3-vmenu li:not(:first-child),ul.tbisgpl3-vmenu li li li:first-child,ul.tbisgpl3-vmenu>li>ul').each(function () { $(this).append('<div class="tbisgpl3-vmenu-separator"> </div><div class="tbisgpl3-vmenu-separator-bg"> </div>'); });
});



var artButtonSetup = (function ($) {
    'use strict';
    return (function (className) {
        $.each($("a." + className + ", button." + className + ", input." + className), function (i, val) {
            var b = $(val);
            if (!b.hasClass('tbisgpl3-button')) {
                b.addClass('tbisgpl3-button');
            }
            if (b.is('input')) {
                b.val(b.val().replace(/^\s*/, '')).css('zoom', '1');
            }
            b.mousedown(function () {
                var b = $(this);
                b.addClass("active");
            });
            b.mouseup(function () {
                var b = $(this);
                if (b.hasClass('active')) {
                    b.removeClass('active');
                }
            });
            b.mouseleave(function () {
                var b = $(this);
                if (b.hasClass('active')) {
                    b.removeClass('active');
                }
            });
        });
    });
})(jQuery);
jQuery(function () {
    'use strict';
    artButtonSetup("tbisgpl3-button");
});

jQuery(function($) {
    'use strict';
    $('input.tbisgpl3-search-button, form.tbisgpl3-search input[type="submit"]').attr('value', '');
});

var Control = (function ($) {
    'use strict';
    return (function () {
        this.init = function(label, type, callback) {
            var chAttr = label.find('input[type="' +type + '"]').attr('checked');
            if (chAttr === 'checked') {
              label.addClass('tbisgpl3-checked');
            }

            label.mouseleave(function () {
              $(this).removeClass('hovered').removeClass('active');
            });
            label.mouseover(function () {
              $(this).addClass('hovered').removeClass('active');
            });
            label.mousedown(function (event) {
              if (event.which !== 1) {
                  return;
              }
              $(this).addClass('active').removeClass('hovered');
            });
            label.mouseup(function (event) {
              if (event.which !== 1) {
                  return;
              }
              callback.apply(this);
              $(this).removeClass('active').addClass('hovered');
            });
        };
    });
})(jQuery);


jQuery(function ($) {
    'use strict';
    $('.tbisgpl3-pager').contents().filter(
        function () {
            return this.nodeType === this.TEXT_NODE;
        }
    ).remove();
});
var fixRssIconLineHeight = (function ($) {
    "use strict";
    return function (className) {
        $("." + className).css("line-height", $("." + className).height() + "px");
    };
})(jQuery);

jQuery(function ($) {
    "use strict";
    var rssIcons = $(".tbisgpl3-rss-tag-icon");
    if (rssIcons.length){
        fixRssIconLineHeight("tbisgpl3-rss-tag-icon");
        if ($.browser.msie && parseInt($.browser.version, 10) < 9) {
            rssIcons.each(function () {
                if ($.trim($(this).html()) === "") {
                    $(this).css("vertical-align", "middle");
                }
            });
        }
    }
});
/**
* @license 
* jQuery Tools 1.2.6 Mousewheel
* 
* NO COPYRIGHTS OR LICENSES. DO WHAT YOU LIKE.
* 
* http://flowplayer.org/tools/toolbox/mousewheel.html
* 
* based on jquery.event.wheel.js ~ rev 1 ~ 
* Copyright (c) 2008, Three Dub Media
* http://threedubmedia.com 
*
* Since: Mar 2010
* Date:  
*/
(function ($) {
    'use strict';
    $.fn.mousewheel = function (fn) {
        return this[fn ? "bind" : "trigger"]("wheel", fn);
    };

    // special event config
    $.event.special.wheel = {
        setup: function () {
            $.event.add(this, wheelEvents, wheelHandler, {});
        },
        teardown: function () {
            $.event.remove(this, wheelEvents, wheelHandler);
        }
    };

    // events to bind ( browser sniffed... )
    var wheelEvents = !$.browser.mozilla ? "mousewheel" : // IE, opera, safari
        "DOMMouseScroll" + ($.browser.version < "1.9" ? " mousemove" : ""); // firefox

    // shared event handler
    function wheelHandler(event) {
        /*jshint validthis:true*/
        
        switch (event.type) {

            // FF2 has incorrect event positions
            case "mousemove":
                return $.extend(event.data, { // store the correct properties
                    clientX: event.clientX, clientY: event.clientY,
                    pageX: event.pageX, pageY: event.pageY
                });

                // firefox
            case "DOMMouseScroll":
                $.extend(event, event.data); // fix event properties in FF2
                event.delta = -event.detail / 3; // normalize delta
                break;

            // IE, opera, safari
            case "mousewheel":
                event.delta = event.wheelDelta / 120;
                break;
        }

        event.type = "wheel"; // hijack the event
        return $.event.handle.call(this, event, event.delta);
    }

})(jQuery);


var ThemeLightbox = (function ($) {
    'use strict';
    return (function () {
        var current;
        var images = $(".tbisgpl3-lightbox");

        this.init = function (ctrl) {
            $(".tbisgpl3-lightbox").live("click", { _ctrl: ctrl }, function (e) {

                if (e.data._ctrl === true && !e.ctrlKey) {
                    return;
                }
                
                reload();
                current = images.index(this);
                show(this);
            });

            $(".tbisgpl3-lightbox-wrapper .arrow.left:not(.disabled)").live("click", function () {
                move(current - 1);
            });

            $(".tbisgpl3-lightbox-wrapper .arrow.right:not(.disabled)").live("click", function () {
                move(current + 1);
            });

            $(".tbisgpl3-lightbox-wrapper .active").live("click", function () {
                move(current + 1);
            });

            $(".tbisgpl3-lightbox-wrapper .close").live("click", function () {
                close();
            });
        };

        function show(src) {
            var closeBtn = $('<div class="close"><div class="cw"> </div><div class="ccw"> </div><div class="close-alt">&#10007;</div></div>')
                .click(close);

            var imgContainer = $('.tbisgpl3-lightbox-wrapper');
            if (imgContainer.length === 0) {
                imgContainer = $('<div class="tbisgpl3-lightbox-wrapper">').css('line-height', $(window).height() + "px")
                    .appendTo($("body"));
            }
            
            var img = $('<img class="tbisgpl3-lightbox-image active" src="' + getFullImgSrc($(src).attr("src")) + '">');
            img.appendTo(imgContainer);

            showArrows();
            closeBtn.appendTo(imgContainer);
            showLoader(true);

            img.load(function () {
                showLoader(false);
            });

            img.error(function () {
                showLoader(false);
                img.attr("src", $(src).attr("src"));
            });

            bindMouse($(".tbisgpl3-lightbox-wrapper .arrow").add(img).add(imgContainer));
        }

        function reload() {
            images = $(".tbisgpl3-lightbox");
        }

        function move(index) {
            if (index < 0 || index >= images.length) {
                return;
            }

            showError(false);

            current = index;

            $(".tbisgpl3-lightbox-wrapper .tbisgpl3-lightbox-image:not(.active)").remove();

            var active = $(".tbisgpl3-lightbox-wrapper .active");
            var target = $('<img class="tbisgpl3-lightbox-image" alt="" src="' + getFullImgSrc($(images[current]).attr("src")) + '" />');

            active.after(target);

            showArrows();
            showLoader(true);

            bindMouse($(".tbisgpl3-lightbox-wrapper").add(target));

            target.load(function () {
                showLoader(false);

                active.removeClass("active");
                target.addClass("active");
            });

            target.error(function () {
                showLoader(false);
                active.removeClass("active");
                target.addClass("active");
                target.attr("src", $(images[current]).attr("src"));
            });
        }

        function showArrows() {
            if ($(".tbisgpl3-lightbox-wrapper .arrow").length === 0) {
                $(".tbisgpl3-lightbox-wrapper").append($('<div class="arrow left"><div class="arrow-t ccw"> </div><div class="arrow-b cw"> </div><div class="arrow-left-alt">&#8592;</div></div>').css("top", $(window).height() / 2 - 40));
                $(".tbisgpl3-lightbox-wrapper").append($('<div class="arrow right"><div class="arrow-t cw"> </div><div class="arrow-b ccw"> </div><div class="arrow-right-alt">&#8594;</div></div>').css("top", $(window).height() / 2 - 40));
            }

            if (current === 0) {
                $(".tbisgpl3-lightbox-wrapper .arrow.left").addClass("disabled");
            } else {
                $(".tbisgpl3-lightbox-wrapper .arrow.left").removeClass("disabled");
            }

            if (current === images.length - 1) {
                $(".tbisgpl3-lightbox-wrapper .arrow.right").addClass("disabled");
            } else {
                $(".tbisgpl3-lightbox-wrapper .arrow.right").removeClass("disabled");
            }
        }

        function showError(enable) {
            if (enable) {
                $(".tbisgpl3-lightbox-wrapper").append($('<div class="lightbox-error">Der angeforderte Inhalt kann nicht geladen werden. <br/>Bitte später noch einmal versuchen.</div>')
                        .css({ "top": $(window).height() / 2 - 60, "left": $(window).width() / 2 - 170 }));
            } else {
                $(".tbisgpl3-lightbox-wrapper .lightbox-error").remove();
            }
        }

        function showLoader(enable) {
            if (!enable) {
                $(".tbisgpl3-lightbox-wrapper .loading").remove();
            }
            else {
                $('<div class="loading"> </div>').css({ "top": $(window).height() / 2 - 16, "left": $(window).width() / 2 - 16 }).appendTo($(".tbisgpl3-lightbox-wrapper"));
            }
        }

        var close = function () {
            $(".tbisgpl3-lightbox-wrapper").remove();
        };

        function bindMouse(img) {
            img.unbind("wheel").mousewheel(function (event, delta) {
                delta = delta > 0 ? 1 : -1;
                move(current + delta);
                event.preventDefault();
            });

            img.mousedown(function (e) {
                // close on middle button click
                if (e.which === 2) {
                    close();
                }
                e.preventDefault();
            });
        }

        function getFullImgSrc(src) {
            var fileName = src.substring(0, src.lastIndexOf('.'));
            var ext = src.substring(src.lastIndexOf('.'));
            src = fileName + "-large" + ext;

            return src;
        }

    });
})(jQuery);

jQuery(function () {
    'use strict';
    new ThemeLightbox().init();
});

(function($) {
    'use strict';
    // transition && transitionEnd && browser prefix
    $.support.transition = (function() {
        var thisBody = document.body || document.documentElement,
            thisStyle = thisBody.style,
            support = thisStyle.transition !== undefined ||
                thisStyle.WebkitTransition !== undefined ||
                thisStyle.MozTransition !== undefined ||
                thisStyle.MsTransition !== undefined ||
                thisStyle.OTransition !== undefined;
        return support && {
            event: (function() {
                var e = "transitionend";
                if ($.browser.opera) {
                    var version = parseFloat($.browser.version);
                    e = version >= 12 ? (version < 12.50 ? "otransitionend" : "transitionend") : "oTransitionEnd";
                } else if ($.browser.webkit) {
                    e = "webkitTransitionEnd";
                }
                return e;
            })(),
            prefix: (function() {
                var result;
                $.each($.browser, function(key, value) {
                    if (key === "version") {
                        return true;
                    }
                    return (result = {
                        opera: "-o-",
                        mozilla: "-moz-",
                        webkit: "-webkit-",
                        msie: "-ms-"
                    }[key]) ? false : true;
                });
                return result || "";
            })()
        };
    })();

    window.BackgroundHelper = function () {
        var slides = [];
        var direction = "next";
        var motion = "horizontal";
        var width = 0;
        var height = 0;
        var multiplier = 1;
        var transitionDuration = "";

        this.init = function(motionType, dir, duration) {
            direction = dir;
            motion = motionType;
            slides = [];
            width = 0;
            height = 0;
            multiplier = 1;
            transitionDuration = duration;
        };

        this.processSlide = function(element, modify) {
            this.updateSize(element, null);
            var pos = [];

            var bgPosition = element.css("background-position");
            var positions = bgPosition.split(",");
            $.each(positions, function (i) {
                var position = $.trim(this);
                var point = position.split(" ");
                if (point.length > 1) {
                    var x = parseInt(point[0], 10);
                    var y = parseInt(point[1], 10);
                    pos.push({ x: x, y: y });
                }
            });

            slides.push({
                "images": element.css("background-image"),
                "sizes": element.css("background-size"),
                "positions": pos
            });
            
            if (modify)
                element.css("background-image", "none");
        };
        
        this.updateSize = function (element, initialSize) {
            width = element.outerWidth(false);
            height = element.outerHeight();
            if (initialSize && parseInt(initialSize.width, 10) !== 0) {
                multiplier = width / initialSize.width;
                if (motion === "fade") {
                    $.each(element.children(), function (i) {
                        $(this).css("background-position", getCssPositions(slides[i].positions, { x: 0, y: 0 }));
                    });
                }
            }
        };

        this.setBackground = function(element, items) {
            var bg = [];
            var sizes = [];
            $.each(items, function (i, o) {
                bg.push(o.images);
                sizes.push(o.sizes);
            });
            element.css({
                "background-image": bg.join(", "),
                "background-size": sizes.join(", "),
                "background-repeat": "no-repeat"
            });
        };

        this.setPosition = function(element, items) {
            var pos = [];
            $.each(items, function(i, o) {
                pos.push(o.positions);
            });
            element.css({
                "background-position": pos.join(", ")
            });
        };

        this.current = function(index) {
            return slides[index] || null;
        };

        this.next = function(index) {
            var next;
            if (direction === "next") {
                next = (index + 1) % slides.length;
            } else {
                next = index - 1;
                if (next < 0) {
                    next = slides.length - 1;
                }
            }
            return slides[next];
        };

        this.items = function(prev, next, move) {
            var prevItem = { x: 0, y: 0 };
            var nextItem = { x: 0, y: 0 };
            var isDirectionNext = direction === "next";
            if (motion === "horizontal") {
                nextItem.x = isDirectionNext ? width : -width;
                nextItem.y = 0;
                if (move) {
                    prevItem.x += isDirectionNext ? -width : width;
                    nextItem.x += isDirectionNext ? -width : width;
                }
            } else if (motion === "vertical") {
                nextItem.x = 0;
                nextItem.y = isDirectionNext ? height : -height;
                if (move) {
                    prevItem.y += isDirectionNext ? -height : height;
                    nextItem.y += isDirectionNext ? -height : height;
                }
            }
            var result = [ ];
            if (!!prev) {
                result.push({ images: prev.images, positions: getCssPositions(prev.positions, prevItem), sizes: prev.sizes });
            }
            if (!!next) {
                result.push({ images: next.images, positions: getCssPositions(next.positions, nextItem), sizes: next.sizes });
            }
            
            if (direction === "next") {
                result.reverse();
            }

            return result;
        };

        this.transition = function(container, on) {
            container.css($.support.transition.prefix + "transition", on ? transitionDuration + " ease-in-out background-position" : "");
        };
        
        function getCssPositions(positions, offset) {
            var result = [];
            if (positions === undefined) {
                return "";
            }
            offset.x = offset.x || 0;
            offset.y = offset.y || 0;
            for (var i = 0; i < positions.length; i++) {
                result.push((positions[i].x * multiplier + offset.x) + "px " + (positions[i].y * multiplier + offset.y) + "px");
            }
            return result.join(", ");
        }
    };


    var Slider = function (element, settings) {

        var interval = null;
        var active = false;
        var children = element.find(".active").parent().children();
        var last = false;
        var running = false;

        this.settings = $.extend({ }, {
            "animation": "horizontal",
            "direction": "next",
            "speed": 600,
            "pause": 2500,
            "auto": true,
            "repeat": true,
            "navigator": null,
            "clickevents": true,
            "hover": true,
            "helper": null
        }, settings);

        this.move = function (direction, next) {
            var activeItem = element.find(".active"),
                nextItem = next || activeItem[direction](),
                innerDirection = this.settings.direction === "next" ? "forward" : "back",
                reset = direction === "next" ? "first" : "last",
                moving = interval,
                slider = this, tmp;

            active = true;

            if (moving) { this.stop(true); }

            if (!nextItem.length) {
                nextItem = element.find(".tbisgpl3-slide-item")[reset]();
                if (!this.settings.repeat) { last = true; active = false; return; }
            }

            if ($.support.transition) {
                nextItem.addClass(this.settings.direction);
                tmp = nextItem.get(0).offsetHeight;
                
                activeItem.addClass(innerDirection);
                nextItem.addClass(innerDirection);
                
                element.trigger("beforeSlide", children.length);
                
                element.one($.support.transition.event, function () {
                    nextItem.removeClass(slider.settings.direction)
                        .removeClass(innerDirection)
                        .addClass("active");
                    activeItem.removeClass("active")
                        .removeClass(innerDirection);
                    active = false;
                    setTimeout(function () {
                        element.trigger("afterSlide", children.length);
                    }, 0);
                });
            } else {
                element.trigger("beforeSlide", children.length);
                
                activeItem.removeClass("active");
                nextItem.addClass("active");
                active = false;
                
                element.trigger("afterSlide", children.length);
            }

            this.navigate(nextItem);

            if (moving) { this.start(); }
        };

        this.navigate = function (position) {
            var index = children.index(position);
            $(this.settings.navigator).children().removeClass("active").eq(index).addClass("active");
        };

        this.to = function (index) {
            var activeItem = element.find(".active"),
                children = activeItem.parent().children(),
                activeIndex = children.index(activeItem),
                slider = this;

            if (index > (children.length - 1) || index < 0) {
                return;
            }

            if (active) {
                return element.one("afterSlide", function () {
                    slider.to(index);
                });
            }
            
            if (activeIndex === index) {
                return;
            }

            this.move(index > activeIndex ? "next" : "prev", $(children[index]));
        };

        this.next = function () {
            if (!active) {
                if (last) { this.stop(); return;  }
                this.move("next");
            }
        };

        this.prev = function () {
            if (!active) {
                if (last) { this.stop(); return; }
                this.move("prev");
            }
        };

        this.start = function (force) {
            if (!!force) {
                setTimeout($.proxy(this.next, this), 10);
            }
            interval = setInterval($.proxy(this.next, this), this.settings.pause);
            running = true;
        };

        this.stop = function (pause) {
            clearInterval(interval);
            interval = null;
            running = !!pause;
            active = false;
        };

        this.active = function () {
            return running;
        };

        this.moving = function () {
            return active;
        };
        
        this.navigate(children.filter(".active"));

        if (this.settings.clickevents) {
            $(this.settings.navigator).on("click", "a", { slider: this }, function (event) {
                var activeIndex = children.index(children.filter(".active"));
                var index = $(this).parent().children().index($(this));
                if (activeIndex !== index) {
                    event.data.slider.to(index);
                }
                event.preventDefault();
            });
        }
        
        if (this.settings.hover) {
            var slider = this;
            element.add(this.settings.navigator)
                   .add(element.siblings(".tbisgpl3-shapes")).hover(function () {
                if (element.is(":visible") && !last) { slider.stop(true); }
            }, function () {
                if (element.is(":visible") && !last) { slider.start(); }
            });
        }
    };

    $.fn.slider = function (arg) {
        return this.each(function () {
            var element = $(this),
                data = element.data("slider"),
                options = typeof arg === "object" && arg;

            if (!data) {
                data = new Slider(element, options);
                element.data("slider", data);
            }
            
            if (typeof arg === "string" && data[arg]) {
                data[arg]();
            } else if (data.settings.auto && element.is(":visible")) {
                data.start();
            }
        });
    };

})(jQuery);




jQuery(function ($) {
    "use strict";
    if (!$.browser.msie || parseInt($.browser.version, 10) > 8)
        return;
    var path = "";
    var scripts = $("script[src*='script.js']");
    if (scripts.length > 0) {
        var src = scripts.last().attr('src');
        path = src.substr(0, src.indexOf("script.js"));
    }
    processHeaderMultipleBg(path);
});

var processHeaderMultipleBg = (function ($) {
    "use strict";
    return (function (path) {
        var header = $(".tbisgpl3-header");
        var bgimages = "".split(",");
        var bgpositions = "".split(",");
        for (var i = bgimages.length - 1; i >= 0; i--) {
            var bgimage = $.trim(bgimages[i]);
            if (bgimage === "")
                continue;
            if (path !== "") {
                bgimage = bgimage.replace(/(url\(['"]?)/i, "$1" + path);
            }
            header.append("<div style=\"position:absolute;top:0;left:0;width:100%;height:100%;background:" + bgimage + " " + bgpositions[i] + " no-repeat\">");
        }
        header.css('background-image', "url('images/header.png')".replace(/(url\(['"]?)/i, "$1" + path));
        header.css('background-position', "0 0");
    });
})(jQuery);