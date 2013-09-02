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
/*global jQuery */

var responsiveDesign = {
    isResponsive: false,
    isDesktop: false,
    isTablet: false,
    isPhone: false,
    windowWidth: 0,
    responsive: (function ($) {
        "use strict";
        return function () {
            var html = $("html");
            this.windowWidth = $(window).width();
            var triggerEvent = false;

            var isRespVisible = $("#tbisgpl3-resp").is(":visible");
            if (isRespVisible && !this.isResponsive) {
                html.addClass("responsive").removeClass("desktop");
                this.isResponsive = true;
                this.isDesktop = false;
                triggerEvent = true;
            } else if (!isRespVisible && !this.isDesktop) {
                html.addClass("desktop").removeClass("responsive responsive-tablet responsive-phone");
                this.isResponsive = this.isTablet = this.isPhone = false;
                this.isDesktop = true;
                triggerEvent = true;
            }

            if (this.isResponsive) {
                if ($("#tbisgpl3-resp-t").is(":visible") && !this.isTablet) {
                    html.addClass("responsive-tablet").removeClass("responsive-phone");
                    this.isTablet = true;
                    this.isPhone = false;
                    triggerEvent = true;
                } else if ($("#tbisgpl3-resp-m").is(":visible") && !this.isPhone) {
                    html.addClass("responsive-phone").removeClass("responsive-tablet");
                    this.isTablet = false;
                    this.isPhone = true;
                    triggerEvent = true;
                }
            }

            if (triggerEvent) {
                $(window).trigger("responsive", this);
            }

            $(window).trigger("responsiveResize", this);
        };
    })(jQuery),
    initialize: (function ($) {
        "use strict";
        return function () {
            $("<div id=\"tbisgpl3-resp\"><div id=\"tbisgpl3-resp-m\"></div><div id=\"tbisgpl3-resp-t\"></div></div>").appendTo("body");
            var resizeTimeout;
            $(window).resize(function () {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function () { responsiveDesign.responsive(); }, 25);
            });
            $(window).trigger("resize");
        };
    })(jQuery)
};

function responsiveAbsBg(responsiveDesign, el, bg) {
    "use strict";
    if (bg.length === 0)
        return;

    var desktopBgTop = bg.attr("data-bg-top");
    var desktopBgHeight = bg.attr("data-bg-height");

    if (responsiveDesign.isResponsive) {
        if (typeof desktopBgTop === "undefined" || desktopBgTop === false) {
            bg.attr("data-bg-top", bg.css("top"));
            bg.attr("data-bg-height", bg.css("height"));
        }

        var elTop = el.offset().top;
        var elHeight = el.outerHeight();
        bg.css("top", elTop + "px");
        bg.css("height", elHeight + "px");
    } else if (typeof desktopBgTop !== "undefined" && desktopBgTop !== false) {
        bg.css("top", desktopBgTop);
        bg.css("height", desktopBgHeight);
        bg.removeAttr("data-bg-top");
        bg.removeAttr("data-bg-height");
    }
}

var responsiveImages = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $("img[width]").each(function () {
            var img = $(this), newWidth = "", newMaxWidth = "", newHeight = "";
            if (responsiveDesign.isResponsive) {
                newWidth = "auto";
                newHeight = "auto";
                newMaxWidth = "100%";

                var widthAttr = img.attr("width");
                if (widthAttr !== null && typeof (widthAttr) === "string" && widthAttr.indexOf("%") === -1) {
                    newWidth = "100%";
                    newMaxWidth = parseInt($.trim(widthAttr), 10) + "px";
                }
            }
            img.css("width", newWidth).css("max-width", newMaxWidth).css("height", newHeight);
        });
    };
})(jQuery);

var responsiveVideos = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $("iframe,object,embed").each(function () {
            var obj = $(this);
            var container = obj.parent(".tbisgpl3-responsive-embed");
            if (responsiveDesign.isResponsive) {
                if (container.length !== 0)
                    return;
                container = $("<div class=\"tbisgpl3-responsive-embed\">").insertBefore(obj);
                obj.appendTo(container);
            } else if (container.length > 0) {
                obj.insertBefore(container);
                container.remove();
            }
        });
    };
})(jQuery);

var responsiveTextblocks = (function ($) {
    "use strict";
    return function (slider, responsiveDesign) {
        slider.find(".tbisgpl3-textblock").each(function () {
            if (parseInt(slider.attr("data-width"), 10) === 0) {
                return true;
            }
            var tb = $(this);
            var c = slider.width() / slider.attr("data-width");
            tb.css({
                "height": "",
                "width": "",
                "top": "",
                "margin-left": ""
            });
            if (responsiveDesign.isResponsive) {
                var tbHeight = parseInt(tb.css("height"), 10);
                var tbWidth = parseInt(tb.css("width"), 10);
                var tbTop = parseInt(tb.css("top"), 10);
                var tbMargin = parseInt(tb.css("margin-left"), 10);
                tb.add(tb.children()).css({
                    "height": tbHeight * c,
                    "width": tbWidth * c
                });
                tb.css("top", tbTop * c);
                tb.attr("style", function (i, s) { return s + "margin-left: " + (tbMargin * c) + "px !important"; });
            }
        });
    };
})(jQuery);

var responsiveSlider = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $(".tbisgpl3-slider").each(function () {
            var s = $(this);

            responsiveTextblocks(s, responsiveDesign);

            if (!responsiveDesign.isResponsive) {
                s.removeAttr("style");
                return;
            }

            // set size
            var initialWidth = s.attr("data-width");
            var initialHeight = s.attr("data-height");
            var c = s.width() / initialWidth;
            var h = c * initialHeight;
            s.css("height", h + "px");

            // set slider
            var obj = s.data("slider");
            if (obj && obj.settings.helper) {
                var inner = s.find(".tbisgpl3-slider-inner");
                obj.settings.helper.updateSize(inner, { width: initialWidth, height: initialHeight });
            }
        });
    };
})(jQuery);

var responsiveCollages = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $(".tbisgpl3-collage").each(function () {
            var collage = $(this);
            var parent = collage.closest(":not(.image-caption-wrapper, .tbisgpl3-collage)");
            var parentWidth = parent.width();
            var collageWidth = collage.width();
            var sliderOriginalWidth = collage.children(".tbisgpl3-slider").attr("data-width");
            if (responsiveDesign.isResponsive && collageWidth > parentWidth) {
                collage
                    .add(collage.find(".tbisgpl3-slider"))
                    .add(collage.closest(".image-caption-wrapper"))
                    .css("width", "100%");
            } else if (!responsiveDesign.isResponsive || collageWidth > sliderOriginalWidth) {
                collage
                    .add(collage.find(".tbisgpl3-slider"))
                    .add(collage.closest(".image-caption-wrapper"))
                    .css("width", "");
            }
        });
    };
})(jQuery);

var responsiveNavigator = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $(".tbisgpl3-slider").each(function () {
            var currentSlider = $(this);
            var currentSliderWidth = currentSlider.width();
            var sliderNavigator = currentSlider.siblings(".tbisgpl3-slidenavigator");
            if (sliderNavigator.length) {
                if (responsiveDesign.isResponsive) {
                    // left offset
                    var left = sliderNavigator.attr("data-left");
                    var margin = currentSliderWidth - currentSliderWidth * parseFloat(left) / 100 - sliderNavigator.outerWidth(false);
                    if (margin < 0) {
                        sliderNavigator.css("margin-left", margin);
                    }
                    // top
                    var sliderHeight = currentSlider.css("height");
                    // reset top to original value
                    sliderNavigator.css("top", "");
                    // newTop = oldTop - (sliderOrinalHeight - sliderCurrentHeight)
                    var offset = parseInt(sliderNavigator.attr("data-offset") || 0, 10);
                    sliderNavigator.css("top", parseInt(sliderNavigator.css("top"), 10) - (currentSlider.attr("data-height") - parseInt(sliderHeight, 10)) + offset);
                } else {
                    sliderNavigator.removeAttr("data-offset");
                    sliderNavigator.removeAttr("style");
                }
            }
        });
    };
})(jQuery);

jQuery(window).bind("responsive", (function ($) {
    "use strict";
    return function (event, responsiveDesign) {
        responsiveImages(responsiveDesign);
        responsiveVideos(responsiveDesign);
    
        if ($.browser.msie && $.browser.version <= 8) return;
    
        if (responsiveDesign.isResponsive) {
            $(window).on("responsiveResize.slider", function () {
                responsiveSlideshow(responsiveDesign);
            });
        } else {
            $(window).trigger("responsiveResize.slider");
            $(window).off("responsiveResize.slider");
        }
    };
})(jQuery));

function responsiveSlideshow(responsiveDesign) {
    "use strict";
    responsiveCollages(responsiveDesign); // must be first
    responsiveSlider(responsiveDesign);
    responsiveNavigator(responsiveDesign);
}






jQuery(window).bind("responsiveResize", (function ($) {
    "use strict";
    return function (event, responsiveDesign) {
        responsiveAbsBg(responsiveDesign, $("nav.tbisgpl3-nav"), $("#tbisgpl3-hmenu-bg"));
    };
})(jQuery));





jQuery(function($) {
    $("<a href=\"#\" class=\"tbisgpl3-menu-btn\"><span></span><span></span><span></span></a>").insertBefore(".tbisgpl3-hmenu").click(function(e) {
        var menu = $(this).next();
        if (menu.is(":visible")) {
            menu.slideUp("fast", function() {
                $(this).removeClass("visible").css("display", "");
            });
        } else {
            menu.slideDown("fast", function() {
                $(this).addClass("visible").css("display", "");
            });
        }
        e.preventDefault();
    });
});

jQuery(window).bind("responsiveNav", (function ($) {
    /*global megaMenuCreate */
    "use strict";
    return function (event, options) {
        if (options.isDesktopNav && $(".tbisgpl3-hmenu-mega-menu").length > 0) {
            megaMenuCreate();
        }
    };
})(jQuery));

var responsiveHeader = (function ($) {
    "use strict";
    return function(responsiveDesign) {
        var header = $("header.tbisgpl3-header");
        var headerSlider = header.find(".tbisgpl3-slider");

        if (headerSlider.length) {
            var firstSlide = headerSlider.find(".tbisgpl3-slide-item").first();
            var slidebg = firstSlide.css("background-image").split(",");
            var previousSibling = headerSlider.prev();
            var sliderNav = headerSlider.siblings(".tbisgpl3-slidenavigator");
            if (slidebg.length && responsiveDesign.isResponsive) {
                header.css("background-image", slidebg[slidebg.length - 1]);
                header.css("min-height", "0");
                // if prev is menu in header
                if (previousSibling.is("nav.tbisgpl3-nav")) {
                    sliderNav.attr("data-offset", previousSibling.height());
                }
            } else {
                sliderNav.removeAttr("data-offset");
                header.removeAttr("style");
            }
        }
    };
})(jQuery);

jQuery(window).bind("responsiveResize", (function ($) {
    "use strict";
    return function (event, responsiveDesign) {
        responsiveAbsBg(responsiveDesign, $(".tbisgpl3-header"), $("#tbisgpl3-header-bg"));
    };
})(jQuery));

jQuery(window).bind("responsive", (function ($) {
    "use strict";
    return function (event, responsiveDesign) {
        if ($.browser.msie && $.browser.version <= 8) return;

        if (responsiveDesign.isResponsive) {
            $(window).on("responsiveResize.header", function () {
                responsiveHeader(responsiveDesign);
            });
        } else {
            $(window).trigger("responsiveResize.header");
            $(window).trigger("resize");
            $(window).off("responsiveResize.header");
        }
    };
})(jQuery));

/*global jQuery, responsiveDesign*/


var responsiveLayoutCell = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $(".tbisgpl3-content .tbisgpl3-content-layout-row,.tbisgpl3-footer .tbisgpl3-content-layout-row").each(function () {
            var row = $(this);
            var rowChildren = row.children(".tbisgpl3-layout-cell");
            if (rowChildren.length > 1) {
                if (responsiveDesign.isTablet) {
                    rowChildren.addClass("responsive-tablet-layout-cell").each(function (i) {
                        if ((i + 1) % 2 === 0) {
                            $(this).after("<div class=\"cleared responsive-cleared\">");
                        }
                    });
                } else {
                    rowChildren.removeClass("responsive-tablet-layout-cell");
                    row.children(".responsive-cleared").remove();
                }
            }
        });
    };
})(jQuery);

jQuery(window).bind("responsive", function (event, responsiveDesign) {
    "use strict";
    responsiveLayoutCell(responsiveDesign);
});


var responsiveLayoutCell = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $(".tbisgpl3-content .tbisgpl3-content-layout-row,.tbisgpl3-footer .tbisgpl3-content-layout-row").each(function () {
            var row = $(this);
            var rowChildren = row.children(".tbisgpl3-layout-cell");
            if (rowChildren.length > 1) {
                if (responsiveDesign.isTablet) {
                    rowChildren.addClass("responsive-tablet-layout-cell").each(function (i) {
                        if ((i + 1) % 2 === 0) {
                            $(this).after("<div class=\"cleared responsive-cleared\">");
                        }
                    });
                } else {
                    rowChildren.removeClass("responsive-tablet-layout-cell");
                    row.children(".responsive-cleared").remove();
                }
            }
        });
    };
})(jQuery);

jQuery(window).bind("responsive", function (event, responsiveDesign) {
    "use strict";
    responsiveLayoutCell(responsiveDesign);
});


var responsiveLayoutCell = (function ($) {
    "use strict";
    return function (responsiveDesign) {
        $(".tbisgpl3-content .tbisgpl3-content-layout-row,.tbisgpl3-footer .tbisgpl3-content-layout-row").each(function () {
            var row = $(this);
            var rowChildren = row.children(".tbisgpl3-layout-cell");
            if (rowChildren.length > 1) {
                if (responsiveDesign.isTablet) {
                    rowChildren.addClass("responsive-tablet-layout-cell").each(function (i) {
                        if ((i + 1) % 2 === 0) {
                            $(this).after("<div class=\"cleared responsive-cleared\">");
                        }
                    });
                } else {
                    rowChildren.removeClass("responsive-tablet-layout-cell");
                    row.children(".responsive-cleared").remove();
                }
            }
        });
    };
})(jQuery);

jQuery(window).bind("responsive", function (event, responsiveDesign) {
    "use strict";
    responsiveLayoutCell(responsiveDesign);
});




if (!jQuery.browser.msie || jQuery.browser.version > 8) {
    jQuery(responsiveDesign.initialize);
}
