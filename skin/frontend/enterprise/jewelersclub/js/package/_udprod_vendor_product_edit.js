/* ======================================================================================= */
/* merged js/calendar/calendar.js                                                          */
/* ======================================================================================= */
/*  Copyright Mihai Bazon, 2002-2005 | www.bazon.net/mishoo
 * -----------------------------------------------------------
 *
 * The DHTML Calendar, version 1.0 "It is happening again"
 *
 * Details and latest version at:
 * www.dynarch.com/projects/calendar
 *
 * This script is developed by Dynarch.com.  Visit us at www.dynarch.com.
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 */

// $Id: calendar.js,v 1.51 2005/03/07 16:44:31 mishoo Exp $

/** The Calendar object constructor. */
Calendar = function (firstDayOfWeek, dateStr, onSelected, onClose) {
    // member variables
    this.activeDiv = null;
    this.currentDateEl = null;
    this.getDateStatus = null;
    this.getDateToolTip = null;
    this.getDateText = null;
    this.timeout = null;
    this.onSelected = onSelected || null;
    this.onClose = onClose || null;
    this.dragging = false;
    this.hidden = false;
    this.minYear = 1970;
    this.maxYear = 2050;
    this.dateFormat = Calendar._TT["DEF_DATE_FORMAT"];
    this.ttDateFormat = Calendar._TT["TT_DATE_FORMAT"];
    this.isPopup = true;
    this.weekNumbers = true;
    this.firstDayOfWeek = typeof firstDayOfWeek == "number" ? firstDayOfWeek : Calendar._FD; // 0 for Sunday, 1 for Monday, etc.
    this.showsOtherMonths = false;
    this.dateStr = dateStr;
    this.ar_days = null;
    this.showsTime = false;
    this.time24 = true;
    this.yearStep = 2;
    this.hiliteToday = true;
    this.multiple = null;
    // HTML elements
    this.table = null;
    this.element = null;
    this.tbody = null;
    this.firstdayname = null;
    // Combo boxes
    this.monthsCombo = null;
    this.yearsCombo = null;
    this.hilitedMonth = null;
    this.activeMonth = null;
    this.hilitedYear = null;
    this.activeYear = null;
    // Information
    this.dateClicked = false;

    // one-time initializations
    if (typeof Calendar._SDN == "undefined") {
        // table of short day names
        if (typeof Calendar._SDN_len == "undefined")
            Calendar._SDN_len = 3;
        var ar = new Array();
        for (var i = 8; i > 0;) {
            ar[--i] = Calendar._DN[i].substr(0, Calendar._SDN_len);
        }
        Calendar._SDN = ar;
        // table of short month names
        if (typeof Calendar._SMN_len == "undefined")
            Calendar._SMN_len = 3;
        ar = new Array();
        for (var i = 12; i > 0;) {
            ar[--i] = Calendar._MN[i].substr(0, Calendar._SMN_len);
        }
        Calendar._SMN = ar;
    }
};

// ** constants

/// "static", needed for event handlers.
Calendar._C = null;

/// detect a special case of "web browser"
Calendar.is_ie = ( /msie/i.test(navigator.userAgent) &&
           !/opera/i.test(navigator.userAgent) );

Calendar.is_ie5 = ( Calendar.is_ie && /msie 5\.0/i.test(navigator.userAgent) );

/// detect Opera browser
Calendar.is_opera = /opera/i.test(navigator.userAgent);

/// detect KHTML-based browsers
Calendar.is_khtml = /Konqueror|Safari|KHTML/i.test(navigator.userAgent);

/// detect Gecko browsers
Calendar.is_gecko = navigator.userAgent.match(/gecko/i);

// BEGIN: UTILITY FUNCTIONS; beware that these might be moved into a separate
//        library, at some point.

// Returns CSS property for element
Calendar.getStyle = function(element, style) {
    if (element.currentStyle) {
        var y = element.currentStyle[style];
    } else if (window.getComputedStyle) {
        var y = document.defaultView.getComputedStyle(element,null).getPropertyValue(style);
    }

    return y;
};

/*
 * Different ways to find element's absolute position
 */
Calendar.getAbsolutePos = function(element) {

    var res = new Object();
    res.x = 0; res.y = 0;

    // variant 1 (working best, copy-paste from prototype library)
    do {
        res.x += element.offsetLeft || 0;
        res.y += element.offsetTop  || 0;
        element = element.offsetParent;
        if (element) {
            if (element.tagName.toUpperCase() == 'BODY') break;
            var p = Calendar.getStyle(element, 'position');
            if ((p !== 'static') && (p !== 'relative')) break;
        }
    } while (element);

    return res;

    // variant 2 (good solution, but lost in IE8)
    if (element !== null) {
        res.x = element.offsetLeft;
        res.y = element.offsetTop;

        var offsetParent = element.offsetParent;
        var parentNode = element.parentNode;

        while (offsetParent !== null) {
            res.x += offsetParent.offsetLeft;
            res.y += offsetParent.offsetTop;

            if (offsetParent != document.body && offsetParent != document.documentElement) {
                res.x -= offsetParent.scrollLeft;
                res.y -= offsetParent.scrollTop;
            }
            //next lines are necessary to support FireFox problem with offsetParent
            if (Calendar.is_gecko) {
                while (offsetParent != parentNode && parentNode !== null) {
                    res.x -= parentNode.scrollLeft;
                    res.y -= parentNode.scrollTop;
                    parentNode = parentNode.parentNode;
                }
            }
            parentNode = offsetParent.parentNode;
            offsetParent = offsetParent.offsetParent;
        }
    }
    return res;

    // variant 2 (not working)

//    var SL = 0, ST = 0;
//    var is_div = /^div$/i.test(el.tagName);
//    if (is_div && el.scrollLeft)
//        SL = el.scrollLeft;
//    if (is_div && el.scrollTop)
//        ST = el.scrollTop;
//    var r = { x: el.offsetLeft - SL, y: el.offsetTop - ST };
//    if (el.offsetParent) {
//        var tmp = this.getAbsolutePos(el.offsetParent);
//        r.x += tmp.x;
//        r.y += tmp.y;
//    }
//    return r;
};

Calendar.isRelated = function (el, evt) {
    var related = evt.relatedTarget;
    if (!related) {
        var type = evt.type;
        if (type == "mouseover") {
            related = evt.fromElement;
        } else if (type == "mouseout") {
            related = evt.toElement;
        }
    }
    while (related) {
        if (related == el) {
            return true;
        }
        related = related.parentNode;
    }
    return false;
};

Calendar.removeClass = function(el, className) {
    if (!(el && el.className)) {
        return;
    }
    var cls = el.className.split(" ");
    var ar = new Array();
    for (var i = cls.length; i > 0;) {
        if (cls[--i] != className) {
            ar[ar.length] = cls[i];
        }
    }
    el.className = ar.join(" ");
};

Calendar.addClass = function(el, className) {
    Calendar.removeClass(el, className);
    el.className += " " + className;
};

// FIXME: the following 2 functions totally suck, are useless and should be replaced immediately.
Calendar.getElement = function(ev) {
    var f = Calendar.is_ie ? window.event.srcElement : ev.currentTarget;
    while (f.nodeType != 1 || /^div$/i.test(f.tagName))
        f = f.parentNode;
    return f;
};

Calendar.getTargetElement = function(ev) {
    var f = Calendar.is_ie ? window.event.srcElement : ev.target;
    while (f.nodeType != 1)
        f = f.parentNode;
    return f;
};

Calendar.stopEvent = function(ev) {
    ev || (ev = window.event);
    if (Calendar.is_ie) {
        ev.cancelBubble = true;
        ev.returnValue = false;
    } else {
        ev.preventDefault();
        ev.stopPropagation();
    }
    return false;
};

Calendar.addEvent = function(el, evname, func) {
    if (el.attachEvent) { // IE
        el.attachEvent("on" + evname, func);
    } else if (el.addEventListener) { // Gecko / W3C
        el.addEventListener(evname, func, true);
    } else {
        el["on" + evname] = func;
    }
};

Calendar.removeEvent = function(el, evname, func) {
    if (el.detachEvent) { // IE
        el.detachEvent("on" + evname, func);
    } else if (el.removeEventListener) { // Gecko / W3C
        el.removeEventListener(evname, func, true);
    } else {
        el["on" + evname] = null;
    }
};

Calendar.createElement = function(type, parent) {
    var el = null;
    if (document.createElementNS) {
        // use the XHTML namespace; IE won't normally get here unless
        // _they_ "fix" the DOM2 implementation.
        el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
    } else {
        el = document.createElement(type);
    }
    if (typeof parent != "undefined") {
        parent.appendChild(el);
    }
    return el;
};

// END: UTILITY FUNCTIONS

// BEGIN: CALENDAR STATIC FUNCTIONS

/** Internal -- adds a set of events to make some element behave like a button. */
Calendar._add_evs = function(el) {
    with (Calendar) {
        addEvent(el, "mouseover", dayMouseOver);
        addEvent(el, "mousedown", dayMouseDown);
        addEvent(el, "mouseout", dayMouseOut);
        if (is_ie) {
            addEvent(el, "dblclick", dayMouseDblClick);
            el.setAttribute("unselectable", true);
        }
    }
};

Calendar.findMonth = function(el) {
    if (typeof el.month != "undefined") {
        return el;
    } else if (typeof el.parentNode.month != "undefined") {
        return el.parentNode;
    }
    return null;
};

Calendar.findYear = function(el) {
    if (typeof el.year != "undefined") {
        return el;
    } else if (typeof el.parentNode.year != "undefined") {
        return el.parentNode;
    }
    return null;
};

Calendar.showMonthsCombo = function () {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    var cal = cal;
    var cd = cal.activeDiv;
    var mc = cal.monthsCombo;
    if (cal.hilitedMonth) {
        Calendar.removeClass(cal.hilitedMonth, "hilite");
    }
    if (cal.activeMonth) {
        Calendar.removeClass(cal.activeMonth, "active");
    }
    var mon = cal.monthsCombo.getElementsByTagName("div")[cal.date.getMonth()];
    Calendar.addClass(mon, "active");
    cal.activeMonth = mon;
    var s = mc.style;
    s.display = "block";
    if (cd.navtype < 0)
        s.left = cd.offsetLeft + "px";
    else {
        var mcw = mc.offsetWidth;
        if (typeof mcw == "undefined")
            // Konqueror brain-dead techniques
            mcw = 50;
        s.left = (cd.offsetLeft + cd.offsetWidth - mcw) + "px";
    }
    s.top = (cd.offsetTop + cd.offsetHeight) + "px";
};

Calendar.showYearsCombo = function (fwd) {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    var cal = cal;
    var cd = cal.activeDiv;
    var yc = cal.yearsCombo;
    if (cal.hilitedYear) {
        Calendar.removeClass(cal.hilitedYear, "hilite");
    }
    if (cal.activeYear) {
        Calendar.removeClass(cal.activeYear, "active");
    }
    cal.activeYear = null;
    var Y = cal.date.getFullYear() + (fwd ? 1 : -1);
    var yr = yc.firstChild;
    var show = false;
    for (var i = 12; i > 0; --i) {
        if (Y >= cal.minYear && Y <= cal.maxYear) {
            yr.innerHTML = Y;
            yr.year = Y;
            yr.style.display = "block";
            show = true;
        } else {
            yr.style.display = "none";
        }
        yr = yr.nextSibling;
        Y += fwd ? cal.yearStep : -cal.yearStep;
    }
    if (show) {
        var s = yc.style;
        s.display = "block";
        if (cd.navtype < 0)
            s.left = cd.offsetLeft + "px";
        else {
            var ycw = yc.offsetWidth;
            if (typeof ycw == "undefined")
                // Konqueror brain-dead techniques
                ycw = 50;
            s.left = (cd.offsetLeft + cd.offsetWidth - ycw) + "px";
        }
        s.top = (cd.offsetTop + cd.offsetHeight) + "px";
    }
};

// event handlers

Calendar.tableMouseUp = function(ev) {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    if (cal.timeout) {
        clearTimeout(cal.timeout);
    }
    var el = cal.activeDiv;
    if (!el) {
        return false;
    }
    var target = Calendar.getTargetElement(ev);
    ev || (ev = window.event);
    Calendar.removeClass(el, "active");
    if (target == el || target.parentNode == el) {
        Calendar.cellClick(el, ev);
    }
    var mon = Calendar.findMonth(target);
    var date = null;
    if (mon) {
        date = new CalendarDateObject(cal.date);
        if (mon.month != date.getMonth()) {
            date.setMonth(mon.month);
            cal.setDate(date);
            cal.dateClicked = false;
            cal.callHandler();
        }
    } else {
        var year = Calendar.findYear(target);
        if (year) {
            date = new CalendarDateObject(cal.date);
            if (year.year != date.getFullYear()) {
                date.setFullYear(year.year);
                cal.setDate(date);
                cal.dateClicked = false;
                cal.callHandler();
            }
        }
    }
    with (Calendar) {
        removeEvent(document, "mouseup", tableMouseUp);
        removeEvent(document, "mouseover", tableMouseOver);
        removeEvent(document, "mousemove", tableMouseOver);
        cal._hideCombos();
        _C = null;
        return stopEvent(ev);
    }
};

Calendar.tableMouseOver = function (ev) {
    var cal = Calendar._C;
    if (!cal) {
        return;
    }
    var el = cal.activeDiv;
    var target = Calendar.getTargetElement(ev);
    if (target == el || target.parentNode == el) {
        Calendar.addClass(el, "hilite active");
        Calendar.addClass(el.parentNode, "rowhilite");
    } else {
        if (typeof el.navtype == "undefined" || (el.navtype != 50 && (el.navtype == 0 || Math.abs(el.navtype) > 2)))
            Calendar.removeClass(el, "active");
        Calendar.removeClass(el, "hilite");
        Calendar.removeClass(el.parentNode, "rowhilite");
    }
    ev || (ev = window.event);
    if (el.navtype == 50 && target != el) {
        var pos = Calendar.getAbsolutePos(el);
        var w = el.offsetWidth;
        var x = ev.clientX;
        var dx;
        var decrease = true;
        if (x > pos.x + w) {
            dx = x - pos.x - w;
            decrease = false;
        } else
            dx = pos.x - x;

        if (dx < 0) dx = 0;
        var range = el._range;
        var current = el._current;
        var count = Math.floor(dx / 10) % range.length;
        for (var i = range.length; --i >= 0;)
            if (range[i] == current)
                break;
        while (count-- > 0)
            if (decrease) {
                if (--i < 0)
                    i = range.length - 1;
            } else if ( ++i >= range.length )
                i = 0;
        var newval = range[i];
        el.innerHTML = newval;

        cal.onUpdateTime();
    }
    var mon = Calendar.findMonth(target);
    if (mon) {
        if (mon.month != cal.date.getMonth()) {
            if (cal.hilitedMonth) {
                Calendar.removeClass(cal.hilitedMonth, "hilite");
            }
            Calendar.addClass(mon, "hilite");
            cal.hilitedMonth = mon;
        } else if (cal.hilitedMonth) {
            Calendar.removeClass(cal.hilitedMonth, "hilite");
        }
    } else {
        if (cal.hilitedMonth) {
            Calendar.removeClass(cal.hilitedMonth, "hilite");
        }
        var year = Calendar.findYear(target);
        if (year) {
            if (year.year != cal.date.getFullYear()) {
                if (cal.hilitedYear) {
                    Calendar.removeClass(cal.hilitedYear, "hilite");
                }
                Calendar.addClass(year, "hilite");
                cal.hilitedYear = year;
            } else if (cal.hilitedYear) {
                Calendar.removeClass(cal.hilitedYear, "hilite");
            }
        } else if (cal.hilitedYear) {
            Calendar.removeClass(cal.hilitedYear, "hilite");
        }
    }
    return Calendar.stopEvent(ev);
};

Calendar.tableMouseDown = function (ev) {
    if (Calendar.getTargetElement(ev) == Calendar.getElement(ev)) {
        return Calendar.stopEvent(ev);
    }
};

Calendar.calDragIt = function (ev) {
    var cal = Calendar._C;
    if (!(cal && cal.dragging)) {
        return false;
    }
    var posX;
    var posY;
    if (Calendar.is_ie) {
        posY = window.event.clientY + document.body.scrollTop;
        posX = window.event.clientX + document.body.scrollLeft;
    } else {
        posX = ev.pageX;
        posY = ev.pageY;
    }
    cal.hideShowCovered();
    var st = cal.element.style;
    st.left = (posX - cal.xOffs) + "px";
    st.top = (posY - cal.yOffs) + "px";
    return Calendar.stopEvent(ev);
};

Calendar.calDragEnd = function (ev) {
    var cal = Calendar._C;
    if (!cal) {
        return false;
    }
    cal.dragging = false;
    with (Calendar) {
        removeEvent(document, "mousemove", calDragIt);
        removeEvent(document, "mouseup", calDragEnd);
        tableMouseUp(ev);
    }
    cal.hideShowCovered();
};

Calendar.dayMouseDown = function(ev) {
    var el = Calendar.getElement(ev);
    if (el.disabled) {
        return false;
    }
    var cal = el.calendar;
    cal.activeDiv = el;
    Calendar._C = cal;
    if (el.navtype != 300) with (Calendar) {
        if (el.navtype == 50) {
            el._current = el.innerHTML;
            addEvent(document, "mousemove", tableMouseOver);
        } else
            addEvent(document, Calendar.is_ie5 ? "mousemove" : "mouseover", tableMouseOver);
        addClass(el, "hilite active");
        addEvent(document, "mouseup", tableMouseUp);
    } else if (cal.isPopup) {
        cal._dragStart(ev);
    }
    if (el.navtype == -1 || el.navtype == 1) {
        if (cal.timeout) clearTimeout(cal.timeout);
        cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250);
    } else if (el.navtype == -2 || el.navtype == 2) {
        if (cal.timeout) clearTimeout(cal.timeout);
        cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)" : "Calendar.showYearsCombo(false)", 250);
    } else {
        cal.timeout = null;
    }
    return Calendar.stopEvent(ev);
};

Calendar.dayMouseDblClick = function(ev) {
    Calendar.cellClick(Calendar.getElement(ev), ev || window.event);
    if (Calendar.is_ie) {
        document.selection.empty();
    }
};

Calendar.dayMouseOver = function(ev) {
    var el = Calendar.getElement(ev);
    if (Calendar.isRelated(el, ev) || Calendar._C || el.disabled) {
        return false;
    }
    if (el.ttip) {
        if (el.ttip.substr(0, 1) == "_") {
            el.ttip = el.caldate.print(el.calendar.ttDateFormat) + el.ttip.substr(1);
        }
        el.calendar.tooltips.innerHTML = el.ttip;
    }
    if (el.navtype != 300) {
        Calendar.addClass(el, "hilite");
        if (el.caldate) {
            Calendar.addClass(el.parentNode, "rowhilite");
        }
    }
    return Calendar.stopEvent(ev);
};

Calendar.dayMouseOut = function(ev) {
    with (Calendar) {
        var el = getElement(ev);
        if (isRelated(el, ev) || _C || el.disabled)
            return false;
        removeClass(el, "hilite");
        if (el.caldate)
            removeClass(el.parentNode, "rowhilite");
        if (el.calendar)
            el.calendar.tooltips.innerHTML = _TT["SEL_DATE"];
        return stopEvent(ev);
    }
};

/**
 *  A generic "click" handler :) handles all types of buttons defined in this
 *  calendar.
 */
Calendar.cellClick = function(el, ev) {
    var cal = el.calendar;
    var closing = false;
    var newdate = false;
    var date = null;
    if (typeof el.navtype == "undefined") {
        if (cal.currentDateEl) {
            Calendar.removeClass(cal.currentDateEl, "selected");
            Calendar.addClass(el, "selected");
            closing = (cal.currentDateEl == el);
            if (!closing) {
                cal.currentDateEl = el;
            }
        }
        cal.date.setDateOnly(el.caldate);
        date = cal.date;
        var other_month = !(cal.dateClicked = !el.otherMonth);
        if (!other_month && !cal.currentDateEl)
            cal._toggleMultipleDate(new CalendarDateObject(date));
        else
            newdate = !el.disabled;
        // a date was clicked
        if (other_month)
            cal._init(cal.firstDayOfWeek, date);
    } else {
        if (el.navtype == 200) {
            Calendar.removeClass(el, "hilite");
            cal.callCloseHandler();
            return;
        }
        date = new CalendarDateObject(cal.date);
        if (el.navtype == 0)
            date.setDateOnly(new CalendarDateObject()); // TODAY
        // unless "today" was clicked, we assume no date was clicked so
        // the selected handler will know not to close the calenar when
        // in single-click mode.
        // cal.dateClicked = (el.navtype == 0);
        cal.dateClicked = false;
        var year = date.getFullYear();
        var mon = date.getMonth();
        function setMonth(m) {
            var day = date.getDate();
            var max = date.getMonthDays(m);
            if (day > max) {
                date.setDate(max);
            }
            date.setMonth(m);
        };
        switch (el.navtype) {
            case 400:
            Calendar.removeClass(el, "hilite");
            var text = Calendar._TT["ABOUT"];
            if (typeof text != "undefined") {
                text += cal.showsTime ? Calendar._TT["ABOUT_TIME"] : "";
            } else {
                // FIXME: this should be removed as soon as lang files get updated!
                text = "Help and about box text is not translated into this language.\n" +
                    "If you know this language and you feel generous please update\n" +
                    "the corresponding file in \"lang\" subdir to match calendar-en.js\n" +
                    "and send it back to <mihai_bazon@yahoo.com> to get it into the distribution  ;-)\n\n" +
                    "Thank you!\n" +
                    "http://dynarch.com/mishoo/calendar.epl\n";
            }
            alert(text);
            return;
            case -2:
            if (year > cal.minYear) {
                date.setFullYear(year - 1);
            }
            break;
            case -1:
            if (mon > 0) {
                setMonth(mon - 1);
            } else if (year-- > cal.minYear) {
                date.setFullYear(year);
                setMonth(11);
            }
            break;
            case 1:
            if (mon < 11) {
                setMonth(mon + 1);
            } else if (year < cal.maxYear) {
                date.setFullYear(year + 1);
                setMonth(0);
            }
            break;
            case 2:
            if (year < cal.maxYear) {
                date.setFullYear(year + 1);
            }
            break;
            case 100:
            cal.setFirstDayOfWeek(el.fdow);
            return;
            case 50:
            var range = el._range;
            var current = el.innerHTML;
            for (var i = range.length; --i >= 0;)
                if (range[i] == current)
                    break;
            if (ev && ev.shiftKey) {
                if (--i < 0)
                    i = range.length - 1;
            } else if ( ++i >= range.length )
                i = 0;
            var newval = range[i];
            el.innerHTML = newval;
            cal.onUpdateTime();
            return;
            case 0:
            // TODAY will bring us here
            if ((typeof cal.getDateStatus == "function") &&
                cal.getDateStatus(date, date.getFullYear(), date.getMonth(), date.getDate())) {
                return false;
            }
            break;
        }
        if (!date.equalsTo(cal.date)) {
            cal.setDate(date);
            newdate = true;
        } else if (el.navtype == 0)
            newdate = closing = true;
    }
    if (newdate) {
        ev && cal.callHandler();
    }
    if (closing) {
        Calendar.removeClass(el, "hilite");
        ev && cal.callCloseHandler();
    }
};

// END: CALENDAR STATIC FUNCTIONS

// BEGIN: CALENDAR OBJECT FUNCTIONS

/**
 *  This function creates the calendar inside the given parent.  If _par is
 *  null than it creates a popup calendar inside the BODY element.  If _par is
 *  an element, be it BODY, then it creates a non-popup calendar (still
 *  hidden).  Some properties need to be set before calling this function.
 */
Calendar.prototype.create = function (_par) {
    var parent = null;
    if (! _par) {
        // default parent is the document body, in which case we create
        // a popup calendar.
        parent = document.getElementsByTagName("body")[0];
        this.isPopup = true;
    } else {
        parent = _par;
        this.isPopup = false;
    }
    this.date = this.dateStr ? new CalendarDateObject(this.dateStr) : new CalendarDateObject();

    var table = Calendar.createElement("table");
    this.table = table;
    table.cellSpacing = 0;
    table.cellPadding = 0;
    table.calendar = this;
    Calendar.addEvent(table, "mousedown", Calendar.tableMouseDown);

    var div = Calendar.createElement("div");
    this.element = div;
    div.className = "calendar";
    if (this.isPopup) {
        div.style.position = "absolute";
        div.style.display = "none";
    }
    div.appendChild(table);

    var thead = Calendar.createElement("thead", table);
    var cell = null;
    var row = null;

    var cal = this;
    var hh = function (text, cs, navtype) {
        cell = Calendar.createElement("td", row);
        cell.colSpan = cs;
        cell.className = "button";
        if (navtype != 0 && Math.abs(navtype) <= 2)
            cell.className += " nav";
        Calendar._add_evs(cell);
        cell.calendar = cal;
        cell.navtype = navtype;
        cell.innerHTML = "<div unselectable='on'>" + text + "</div>";
        return cell;
    };

    row = Calendar.createElement("tr", thead);
    var title_length = 6;
    (this.isPopup) && --title_length;
    (this.weekNumbers) && ++title_length;

    hh("?", 1, 400).ttip = Calendar._TT["INFO"];
    this.title = hh("", title_length, 300);
    this.title.className = "title";
    if (this.isPopup) {
        this.title.ttip = Calendar._TT["DRAG_TO_MOVE"];
        this.title.style.cursor = "move";
        hh("&#x00d7;", 1, 200).ttip = Calendar._TT["CLOSE"];
    }

    row = Calendar.createElement("tr", thead);
    row.className = "headrow";

    this._nav_py = hh("&#x00ab;", 1, -2);
    this._nav_py.ttip = Calendar._TT["PREV_YEAR"];

    this._nav_pm = hh("&#x2039;", 1, -1);
    this._nav_pm.ttip = Calendar._TT["PREV_MONTH"];

    this._nav_now = hh(Calendar._TT["TODAY"], this.weekNumbers ? 4 : 3, 0);
    this._nav_now.ttip = Calendar._TT["GO_TODAY"];

    this._nav_nm = hh("&#x203a;", 1, 1);
    this._nav_nm.ttip = Calendar._TT["NEXT_MONTH"];

    this._nav_ny = hh("&#x00bb;", 1, 2);
    this._nav_ny.ttip = Calendar._TT["NEXT_YEAR"];

    // day names
    row = Calendar.createElement("tr", thead);
    row.className = "daynames";
    if (this.weekNumbers) {
        cell = Calendar.createElement("td", row);
        cell.className = "name wn";
        cell.innerHTML = Calendar._TT["WK"];
    }
    for (var i = 7; i > 0; --i) {
        cell = Calendar.createElement("td", row);
        if (!i) {
            cell.navtype = 100;
            cell.calendar = this;
            Calendar._add_evs(cell);
        }
    }
    this.firstdayname = (this.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;
    this._displayWeekdays();

    var tbody = Calendar.createElement("tbody", table);
    this.tbody = tbody;

    for (i = 6; i > 0; --i) {
        row = Calendar.createElement("tr", tbody);
        if (this.weekNumbers) {
            cell = Calendar.createElement("td", row);
        }
        for (var j = 7; j > 0; --j) {
            cell = Calendar.createElement("td", row);
            cell.calendar = this;
            Calendar._add_evs(cell);
        }
    }

    if (this.showsTime) {
        row = Calendar.createElement("tr", tbody);
        row.className = "time";

        cell = Calendar.createElement("td", row);
        cell.className = "time";
        cell.colSpan = 2;
        cell.innerHTML = Calendar._TT["TIME"] || "&nbsp;";

        cell = Calendar.createElement("td", row);
        cell.className = "time";
        cell.colSpan = this.weekNumbers ? 4 : 3;

        (function(){
            function makeTimePart(className, init, range_start, range_end) {
                var part = Calendar.createElement("span", cell);
                part.className = className;
                part.innerHTML = init;
                part.calendar = cal;
                part.ttip = Calendar._TT["TIME_PART"];
                part.navtype = 50;
                part._range = [];
                if (typeof range_start != "number")
                    part._range = range_start;
                else {
                    for (var i = range_start; i <= range_end; ++i) {
                        var txt;
                        if (i < 10 && range_end >= 10) txt = '0' + i;
                        else txt = '' + i;
                        part._range[part._range.length] = txt;
                    }
                }
                Calendar._add_evs(part);
                return part;
            };
            var hrs = cal.date.getHours();
            var mins = cal.date.getMinutes();
            var t12 = !cal.time24;
            var pm = (hrs > 12);
            if (t12 && pm) hrs -= 12;
            var H = makeTimePart("hour", hrs, t12 ? 1 : 0, t12 ? 12 : 23);
            var span = Calendar.createElement("span", cell);
            span.innerHTML = ":";
            span.className = "colon";
            var M = makeTimePart("minute", mins, 0, 59);
            var AP = null;
            cell = Calendar.createElement("td", row);
            cell.className = "time";
            cell.colSpan = 2;
            if (t12)
                AP = makeTimePart("ampm", pm ? "pm" : "am", ["am", "pm"]);
            else
                cell.innerHTML = "&nbsp;";

            cal.onSetTime = function() {
                var pm, hrs = this.date.getHours(),
                    mins = this.date.getMinutes();
                if (t12) {
                    pm = (hrs >= 12);
                    if (pm) hrs -= 12;
                    if (hrs == 0) hrs = 12;
                    AP.innerHTML = pm ? "pm" : "am";
                }
                H.innerHTML = (hrs < 10) ? ("0" + hrs) : hrs;
                M.innerHTML = (mins < 10) ? ("0" + mins) : mins;
            };

            cal.onUpdateTime = function() {
                var date = this.date;
                var h = parseInt(H.innerHTML, 10);
                if (t12) {
                    if (/pm/i.test(AP.innerHTML) && h < 12)
                        h += 12;
                    else if (/am/i.test(AP.innerHTML) && h == 12)
                        h = 0;
                }
                var d = date.getDate();
                var m = date.getMonth();
                var y = date.getFullYear();
                date.setHours(h);
                date.setMinutes(parseInt(M.innerHTML, 10));
                date.setFullYear(y);
                date.setMonth(m);
                date.setDate(d);
                this.dateClicked = false;
                this.callHandler();
            };
        })();
    } else {
        this.onSetTime = this.onUpdateTime = function() {};
    }

    var tfoot = Calendar.createElement("tfoot", table);

    row = Calendar.createElement("tr", tfoot);
    row.className = "footrow";

    cell = hh(Calendar._TT["SEL_DATE"], this.weekNumbers ? 8 : 7, 300);
    cell.className = "ttip";
    if (this.isPopup) {
        cell.ttip = Calendar._TT["DRAG_TO_MOVE"];
        cell.style.cursor = "move";
    }
    this.tooltips = cell;

    div = Calendar.createElement("div", this.element);
    this.monthsCombo = div;
    div.className = "combo";
    for (i = 0; i < Calendar._MN.length; ++i) {
        var mn = Calendar.createElement("div");
        mn.className = Calendar.is_ie ? "label-IEfix" : "label";
        mn.month = i;
        mn.innerHTML = Calendar._SMN[i];
        div.appendChild(mn);
    }

    div = Calendar.createElement("div", this.element);
    this.yearsCombo = div;
    div.className = "combo";
    for (i = 12; i > 0; --i) {
        var yr = Calendar.createElement("div");
        yr.className = Calendar.is_ie ? "label-IEfix" : "label";
        div.appendChild(yr);
    }

    this._init(this.firstDayOfWeek, this.date);
    parent.appendChild(this.element);
};

/** keyboard navigation, only for popup calendars */
Calendar._keyEvent = function(ev) {
    var cal = window._dynarch_popupCalendar;
    if (!cal || cal.multiple)
        return false;
    (Calendar.is_ie) && (ev = window.event);
    var act = (Calendar.is_ie || ev.type == "keypress"),
        K = ev.keyCode;
    if (ev.ctrlKey) {
        switch (K) {
            case 37: // KEY left
            act && Calendar.cellClick(cal._nav_pm);
            break;
            case 38: // KEY up
            act && Calendar.cellClick(cal._nav_py);
            break;
            case 39: // KEY right
            act && Calendar.cellClick(cal._nav_nm);
            break;
            case 40: // KEY down
            act && Calendar.cellClick(cal._nav_ny);
            break;
            default:
            return false;
        }
    } else switch (K) {
        case 32: // KEY space (now)
        Calendar.cellClick(cal._nav_now);
        break;
        case 27: // KEY esc
        act && cal.callCloseHandler();
        break;
        case 37: // KEY left
        case 38: // KEY up
        case 39: // KEY right
        case 40: // KEY down
        if (act) {
            var prev, x, y, ne, el, step;
            prev = K == 37 || K == 38;
            step = (K == 37 || K == 39) ? 1 : 7;
            function setVars() {
                el = cal.currentDateEl;
                var p = el.pos;
                x = p & 15;
                y = p >> 4;
                ne = cal.ar_days[y][x];
            };setVars();
            function prevMonth() {
                var date = new CalendarDateObject(cal.date);
                date.setDate(date.getDate() - step);
                cal.setDate(date);
            };
            function nextMonth() {
                var date = new CalendarDateObject(cal.date);
                date.setDate(date.getDate() + step);
                cal.setDate(date);
            };
            while (1) {
                switch (K) {
                    case 37: // KEY left
                    if (--x >= 0)
                        ne = cal.ar_days[y][x];
                    else {
                        x = 6;
                        K = 38;
                        continue;
                    }
                    break;
                    case 38: // KEY up
                    if (--y >= 0)
                        ne = cal.ar_days[y][x];
                    else {
                        prevMonth();
                        setVars();
                    }
                    break;
                    case 39: // KEY right
                    if (++x < 7)
                        ne = cal.ar_days[y][x];
                    else {
                        x = 0;
                        K = 40;
                        continue;
                    }
                    break;
                    case 40: // KEY down
                    if (++y < cal.ar_days.length)
                        ne = cal.ar_days[y][x];
                    else {
                        nextMonth();
                        setVars();
                    }
                    break;
                }
                break;
            }
            if (ne) {
                if (!ne.disabled)
                    Calendar.cellClick(ne);
                else if (prev)
                    prevMonth();
                else
                    nextMonth();
            }
        }
        break;
        case 13: // KEY enter
        if (act)
            Calendar.cellClick(cal.currentDateEl, ev);
        break;
        default:
        return false;
    }
    return Calendar.stopEvent(ev);
};

/**
 *  (RE)Initializes the calendar to the given date and firstDayOfWeek
 */
Calendar.prototype._init = function (firstDayOfWeek, date) {
    var today = new CalendarDateObject(),
        TY = today.getFullYear(),
        TM = today.getMonth(),
        TD = today.getDate();
    this.table.style.visibility = "hidden";
    var year = date.getFullYear();
    if (year < this.minYear) {
        year = this.minYear;
        date.setFullYear(year);
    } else if (year > this.maxYear) {
        year = this.maxYear;
        date.setFullYear(year);
    }
    this.firstDayOfWeek = firstDayOfWeek;
    this.date = new CalendarDateObject(date);
    var month = date.getMonth();
    var mday = date.getDate();
    var no_days = date.getMonthDays();

    // calendar voodoo for computing the first day that would actually be
    // displayed in the calendar, even if it's from the previous month.
    // WARNING: this is magic. ;-)
    date.setDate(1);
    var day1 = (date.getDay() - this.firstDayOfWeek) % 7;
    if (day1 < 0)
        day1 += 7;
    date.setDate(-day1);
    date.setDate(date.getDate() + 1);

    var row = this.tbody.firstChild;
    var MN = Calendar._SMN[month];
    var ar_days = this.ar_days = new Array();
    var weekend = Calendar._TT["WEEKEND"];
    var dates = this.multiple ? (this.datesCells = {}) : null;
    for (var i = 0; i < 6; ++i, row = row.nextSibling) {
        var cell = row.firstChild;
        if (this.weekNumbers) {
            cell.className = "day wn";
            cell.innerHTML = date.getWeekNumber();
            cell = cell.nextSibling;
        }
        row.className = "daysrow";
        var hasdays = false, iday, dpos = ar_days[i] = [];
        for (var j = 0; j < 7; ++j, cell = cell.nextSibling, date.setDate(iday + 1)) {
            iday = date.getDate();
            var wday = date.getDay();
            cell.className = "day";
            cell.pos = i << 4 | j;
            dpos[j] = cell;
            var current_month = (date.getMonth() == month);
            if (!current_month) {
                if (this.showsOtherMonths) {
                    cell.className += " othermonth";
                    cell.otherMonth = true;
                } else {
                    cell.className = "emptycell";
                    cell.innerHTML = "&nbsp;";
                    cell.disabled = true;
                    continue;
                }
            } else {
                cell.otherMonth = false;
                hasdays = true;
            }
            cell.disabled = false;
            cell.innerHTML = this.getDateText ? this.getDateText(date, iday) : iday;
            if (dates)
                dates[date.print("%Y%m%d")] = cell;
            if (this.getDateStatus) {
                var status = this.getDateStatus(date, year, month, iday);
                if (this.getDateToolTip) {
                    var toolTip = this.getDateToolTip(date, year, month, iday);
                    if (toolTip)
                        cell.title = toolTip;
                }
                if (status === true) {
                    cell.className += " disabled";
                    cell.disabled = true;
                } else {
                    if (/disabled/i.test(status))
                        cell.disabled = true;
                    cell.className += " " + status;
                }
            }
            if (!cell.disabled) {
                cell.caldate = new CalendarDateObject(date);
                cell.ttip = "_";
                if (!this.multiple && current_month
                    && iday == mday && this.hiliteToday) {
                    cell.className += " selected";
                    this.currentDateEl = cell;
                }
                if (date.getFullYear() == TY &&
                    date.getMonth() == TM &&
                    iday == TD) {
                    cell.className += " today";
                    cell.ttip += Calendar._TT["PART_TODAY"];
                }
                if (weekend.indexOf(wday.toString()) != -1)
                    cell.className += cell.otherMonth ? " oweekend" : " weekend";
            }
        }
        if (!(hasdays || this.showsOtherMonths))
            row.className = "emptyrow";
    }
    this.title.innerHTML = Calendar._MN[month] + ", " + year;
    this.onSetTime();
    this.table.style.visibility = "visible";
    this._initMultipleDates();
    // PROFILE
    // this.tooltips.innerHTML = "Generated in " + ((new CalendarDateObject()) - today) + " ms";
};

Calendar.prototype._initMultipleDates = function() {
    if (this.multiple) {
        for (var i in this.multiple) {
            var cell = this.datesCells[i];
            var d = this.multiple[i];
            if (!d)
                continue;
            if (cell)
                cell.className += " selected";
        }
    }
};

Calendar.prototype._toggleMultipleDate = function(date) {
    if (this.multiple) {
        var ds = date.print("%Y%m%d");
        var cell = this.datesCells[ds];
        if (cell) {
            var d = this.multiple[ds];
            if (!d) {
                Calendar.addClass(cell, "selected");
                this.multiple[ds] = date;
            } else {
                Calendar.removeClass(cell, "selected");
                delete this.multiple[ds];
            }
        }
    }
};

Calendar.prototype.setDateToolTipHandler = function (unaryFunction) {
    this.getDateToolTip = unaryFunction;
};

/**
 *  Calls _init function above for going to a certain date (but only if the
 *  date is different than the currently selected one).
 */
Calendar.prototype.setDate = function (date) {
    if (!date.equalsTo(this.date)) {
        this._init(this.firstDayOfWeek, date);
    }
};

/**
 *  Refreshes the calendar.  Useful if the "disabledHandler" function is
 *  dynamic, meaning that the list of disabled date can change at runtime.
 *  Just * call this function if you think that the list of disabled dates
 *  should * change.
 */
Calendar.prototype.refresh = function () {
    this._init(this.firstDayOfWeek, this.date);
};

/** Modifies the "firstDayOfWeek" parameter (pass 0 for Synday, 1 for Monday, etc.). */
Calendar.prototype.setFirstDayOfWeek = function (firstDayOfWeek) {
    this._init(firstDayOfWeek, this.date);
    this._displayWeekdays();
};

/**
 *  Allows customization of what dates are enabled.  The "unaryFunction"
 *  parameter must be a function object that receives the date (as a JS Date
 *  object) and returns a boolean value.  If the returned value is true then
 *  the passed date will be marked as disabled.
 */
Calendar.prototype.setDateStatusHandler = Calendar.prototype.setDisabledHandler = function (unaryFunction) {
    this.getDateStatus = unaryFunction;
};

/** Customization of allowed year range for the calendar. */
Calendar.prototype.setRange = function (a, z) {
    this.minYear = a;
    this.maxYear = z;
};

/** Calls the first user handler (selectedHandler). */
Calendar.prototype.callHandler = function () {
    if (this.onSelected) {
        this.onSelected(this, this.date.print(this.dateFormat));
    }
};

/** Calls the second user handler (closeHandler). */
Calendar.prototype.callCloseHandler = function () {
    if (this.onClose) {
        this.onClose(this);
    }
    this.hideShowCovered();
};

/** Removes the calendar object from the DOM tree and destroys it. */
Calendar.prototype.destroy = function () {
    var el = this.element.parentNode;
    el.removeChild(this.element);
    Calendar._C = null;
    window._dynarch_popupCalendar = null;
};

/**
 *  Moves the calendar element to a different section in the DOM tree (changes
 *  its parent).
 */
Calendar.prototype.reparent = function (new_parent) {
    var el = this.element;
    el.parentNode.removeChild(el);
    new_parent.appendChild(el);
};

// This gets called when the user presses a mouse button anywhere in the
// document, if the calendar is shown.  If the click was outside the open
// calendar this function closes it.
Calendar._checkCalendar = function(ev) {
    var calendar = window._dynarch_popupCalendar;
    if (!calendar) {
        return false;
    }
    var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
    for (; el != null && el != calendar.element; el = el.parentNode);
    if (el == null) {
        // calls closeHandler which should hide the calendar.
        window._dynarch_popupCalendar.callCloseHandler();
        return Calendar.stopEvent(ev);
    }
};

/** Shows the calendar. */
Calendar.prototype.show = function () {
    var rows = this.table.getElementsByTagName("tr");
    for (var i = rows.length; i > 0;) {
        var row = rows[--i];
        Calendar.removeClass(row, "rowhilite");
        var cells = row.getElementsByTagName("td");
        for (var j = cells.length; j > 0;) {
            var cell = cells[--j];
            Calendar.removeClass(cell, "hilite");
            Calendar.removeClass(cell, "active");
        }
    }
    this.element.style.display = "block";
    this.hidden = false;
    if (this.isPopup) {
        window._dynarch_popupCalendar = this;
        Calendar.addEvent(document, "keydown", Calendar._keyEvent);
        Calendar.addEvent(document, "keypress", Calendar._keyEvent);
        Calendar.addEvent(document, "mousedown", Calendar._checkCalendar);
    }
    this.hideShowCovered();
};

/**
 *  Hides the calendar.  Also removes any "hilite" from the class of any TD
 *  element.
 */
Calendar.prototype.hide = function () {
    if (this.isPopup) {
        Calendar.removeEvent(document, "keydown", Calendar._keyEvent);
        Calendar.removeEvent(document, "keypress", Calendar._keyEvent);
        Calendar.removeEvent(document, "mousedown", Calendar._checkCalendar);
    }
    this.element.style.display = "none";
    this.hidden = true;
    this.hideShowCovered();
};

/**
 *  Shows the calendar at a given absolute position (beware that, depending on
 *  the calendar element style -- position property -- this might be relative
 *  to the parent's containing rectangle).
 */
Calendar.prototype.showAt = function (x, y) {
    var s = this.element.style;
    s.left = x + "px";
    s.top = y + "px";
    this.show();
};

/** Shows the calendar near a given element. */
Calendar.prototype.showAtElement = function (el, opts) {
    var self = this;
    var p = Calendar.getAbsolutePos(el);
    if (!opts || typeof opts != "string") {
        this.showAt(p.x, p.y + el.offsetHeight);
        return true;
    }
    function fixPosition(box) {
        if (box.x < 0)
            box.x = 0;
        if (box.y < 0)
            box.y = 0;
        var cp = document.createElement("div");
        var s = cp.style;
        s.position = "absolute";
        s.right = s.bottom = s.width = s.height = "0px";
        document.body.appendChild(cp);
        var br = Calendar.getAbsolutePos(cp);
        document.body.removeChild(cp);
        if (Calendar.is_ie) {
            br.y += document.documentElement.scrollTop;
            br.x += document.documentElement.scrollLeft;
        } else {
            br.y += window.scrollY;
            br.x += window.scrollX;
        }
        var tmp = box.x + box.width - br.x;
        if (tmp > 0) box.x -= tmp;
        tmp = box.y + box.height - br.y;
        if (tmp > 0) box.y -= tmp;
    };
    this.element.style.display = "block";
    Calendar.continuation_for_the_fucking_khtml_browser = function() {
        var w = self.element.offsetWidth;
        var h = self.element.offsetHeight;
        self.element.style.display = "none";
        var valign = opts.substr(0, 1);
        var halign = "l";
        if (opts.length > 1) {
            halign = opts.substr(1, 1);
        }
        // vertical alignment
        switch (valign) {
            case "T": p.y -= h; break;
            case "B": p.y += el.offsetHeight; break;
            case "C": p.y += (el.offsetHeight - h) / 2; break;
            case "t": p.y += el.offsetHeight - h; break;
            case "b": break; // already there
        }
        // horizontal alignment
        switch (halign) {
            case "L": p.x -= w; break;
            case "R": p.x += el.offsetWidth; break;
            case "C": p.x += (el.offsetWidth - w) / 2; break;
            case "l": p.x += el.offsetWidth - w; break;
            case "r": break; // already there
        }
        p.width = w;
        p.height = h + 40;
        self.monthsCombo.style.display = "none";
        fixPosition(p);
        self.showAt(p.x, p.y);
    };
    if (Calendar.is_khtml)
        setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()", 10);
    else
        Calendar.continuation_for_the_fucking_khtml_browser();
};

/** Customizes the date format. */
Calendar.prototype.setDateFormat = function (str) {
    this.dateFormat = str;
};

/** Customizes the tooltip date format. */
Calendar.prototype.setTtDateFormat = function (str) {
    this.ttDateFormat = str;
};

/**
 *  Tries to identify the date represented in a string.  If successful it also
 *  calls this.setDate which moves the calendar to the given date.
 */
Calendar.prototype.parseDate = function(str, fmt) {
    if (!fmt)
        fmt = this.dateFormat;
    this.setDate(Date.parseDate(str, fmt));
};

Calendar.prototype.hideShowCovered = function () {
    if (!Calendar.is_ie && !Calendar.is_opera)
        return;
    function getVisib(obj){
        var value = obj.style.visibility;
        if (!value) {
            if (document.defaultView && typeof (document.defaultView.getComputedStyle) == "function") { // Gecko, W3C
                if (!Calendar.is_khtml)
                    value = document.defaultView.
                        getComputedStyle(obj, "").getPropertyValue("visibility");
                else
                    value = '';
            } else if (obj.currentStyle) { // IE
                value = obj.currentStyle.visibility;
            } else
                value = '';
        }
        return value;
    };

    var tags = new Array("applet", "iframe", "select");
    var el = this.element;

    var p = Calendar.getAbsolutePos(el);
    var EX1 = p.x;
    var EX2 = el.offsetWidth + EX1;
    var EY1 = p.y;
    var EY2 = el.offsetHeight + EY1;

    for (var k = tags.length; k > 0; ) {
        var ar = document.getElementsByTagName(tags[--k]);
        var cc = null;

        for (var i = ar.length; i > 0;) {
            cc = ar[--i];

            p = Calendar.getAbsolutePos(cc);
            var CX1 = p.x;
            var CX2 = cc.offsetWidth + CX1;
            var CY1 = p.y;
            var CY2 = cc.offsetHeight + CY1;

            if (this.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
                if (!cc.__msh_save_visibility) {
                    cc.__msh_save_visibility = getVisib(cc);
                }
                cc.style.visibility = cc.__msh_save_visibility;
            } else {
                if (!cc.__msh_save_visibility) {
                    cc.__msh_save_visibility = getVisib(cc);
                }
                cc.style.visibility = "hidden";
            }
        }
    }
};

/** Internal function; it displays the bar with the names of the weekday. */
Calendar.prototype._displayWeekdays = function () {
    var fdow = this.firstDayOfWeek;
    var cell = this.firstdayname;
    var weekend = Calendar._TT["WEEKEND"];
    for (var i = 0; i < 7; ++i) {
        cell.className = "day name";
        var realday = (i + fdow) % 7;
        if (i) {
            cell.ttip = Calendar._TT["DAY_FIRST"].replace("%s", Calendar._DN[realday]);
            cell.navtype = 100;
            cell.calendar = this;
            cell.fdow = realday;
            Calendar._add_evs(cell);
        }
        if (weekend.indexOf(realday.toString()) != -1) {
            Calendar.addClass(cell, "weekend");
        }
        cell.innerHTML = Calendar._SDN[(i + fdow) % 7];
        cell = cell.nextSibling;
    }
};

/** Internal function.  Hides all combo boxes that might be displayed. */
Calendar.prototype._hideCombos = function () {
    this.monthsCombo.style.display = "none";
    this.yearsCombo.style.display = "none";
};

/** Internal function.  Starts dragging the element. */
Calendar.prototype._dragStart = function (ev) {
    if (this.dragging) {
        return;
    }
    this.dragging = true;
    var posX;
    var posY;
    if (Calendar.is_ie) {
        posY = window.event.clientY + document.body.scrollTop;
        posX = window.event.clientX + document.body.scrollLeft;
    } else {
        posY = ev.clientY + window.scrollY;
        posX = ev.clientX + window.scrollX;
    }
    var st = this.element.style;
    this.xOffs = posX - parseInt(st.left);
    this.yOffs = posY - parseInt(st.top);
    with (Calendar) {
        addEvent(document, "mousemove", calDragIt);
        addEvent(document, "mouseup", calDragEnd);
    }
};

// BEGIN: DATE OBJECT PATCHES

/** Adds the number of days array to the Date object. */
Date._MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

/** Constants used for time computations */
Date.SECOND = 1000 /* milliseconds */;
Date.MINUTE = 60 * Date.SECOND;
Date.HOUR   = 60 * Date.MINUTE;
Date.DAY    = 24 * Date.HOUR;
Date.WEEK   =  7 * Date.DAY;

Date.parseDate = function(str, fmt) {
    var today = new CalendarDateObject();
    var y = 0;
    var m = -1;
    var d = 0;

    // translate date into en_US, because split() cannot parse non-latin stuff
    var a = str;
    var i;
    for (i = 0; i < Calendar._MN.length; i++) {
        a = a.replace(Calendar._MN[i], enUS.m.wide[i]);
    }
    for (i = 0; i < Calendar._SMN.length; i++) {
        a = a.replace(Calendar._SMN[i], enUS.m.abbr[i]);
    }
    a = a.replace(Calendar._am, 'am');
    a = a.replace(Calendar._am.toLowerCase(), 'am');
    a = a.replace(Calendar._pm, 'pm');
    a = a.replace(Calendar._pm.toLowerCase(), 'pm');

    a = a.split(/\W+/);

    var b = fmt.match(/%./g);
    var i = 0, j = 0;
    var hr = 0;
    var min = 0;
    for (i = 0; i < a.length; ++i) {
        if (!a[i])
            continue;
        switch (b[i]) {
            case "%d":
            case "%e":
            d = parseInt(a[i], 10);
            break;

            case "%m":
            m = parseInt(a[i], 10) - 1;
            break;

            case "%Y":
            case "%y":
            y = parseInt(a[i], 10);
            (y < 100) && (y += (y > 29) ? 1900 : 2000);
            break;

            case "%b":
            for (j = 0; j < 12; ++j) {
                if (enUS.m.abbr[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
            }
            break;

            case "%B":
            for (j = 0; j < 12; ++j) {
                if (enUS.m.wide[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
            }
            break;

            case "%H":
            case "%I":
            case "%k":
            case "%l":
            hr = parseInt(a[i], 10);
            break;

            case "%P":
            case "%p":
            if (/pm/i.test(a[i]) && hr < 12)
                hr += 12;
            else if (/am/i.test(a[i]) && hr >= 12)
                hr -= 12;
            break;

            case "%M":
            min = parseInt(a[i], 10);
            break;
        }
    }
    if (isNaN(y)) y = today.getFullYear();
    if (isNaN(m)) m = today.getMonth();
    if (isNaN(d)) d = today.getDate();
    if (isNaN(hr)) hr = today.getHours();
    if (isNaN(min)) min = today.getMinutes();
    if (y != 0 && m != -1 && d != 0)
        return new CalendarDateObject(y, m, d, hr, min, 0);
    y = 0; m = -1; d = 0;
    for (i = 0; i < a.length; ++i) {
        if (a[i].search(/[a-zA-Z]+/) != -1) {
            var t = -1;
            for (j = 0; j < 12; ++j) {
                if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
            }
            if (t != -1) {
                if (m != -1) {
                    d = m+1;
                }
                m = t;
            }
        } else if (parseInt(a[i], 10) <= 12 && m == -1) {
            m = a[i]-1;
        } else if (parseInt(a[i], 10) > 31 && y == 0) {
            y = parseInt(a[i], 10);
            (y < 100) && (y += (y > 29) ? 1900 : 2000);
        } else if (d == 0) {
            d = a[i];
        }
    }
    if (y == 0)
        y = today.getFullYear();
    if (m != -1 && d != 0)
        return new CalendarDateObject(y, m, d, hr, min, 0);
    return today;
};

/** Returns the number of days in the current month */
Date.prototype.getMonthDays = function(month) {
    var year = this.getFullYear();
    if (typeof month == "undefined") {
        month = this.getMonth();
    }
    if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
        return 29;
    } else {
        return Date._MD[month];
    }
};

/** Returns the number of day in the year. */
Date.prototype.getDayOfYear = function() {
    var now = new CalendarDateObject(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
    var then = new CalendarDateObject(this.getFullYear(), 0, 0, 0, 0, 0);
    var time = now - then;
    return Math.floor(time / Date.DAY);
};

/** Returns the number of the week in year, as defined in ISO 8601. */
Date.prototype.getWeekNumber = function() {
    var d = new CalendarDateObject(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
    var DoW = d.getDay();
    d.setDate(d.getDate() - (DoW + 6) % 7 + 3); // Nearest Thu
    var ms = d.valueOf(); // GMT
    d.setMonth(0);
    d.setDate(4); // Thu in Week 1
    return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};

/** Checks date and time equality */
Date.prototype.equalsTo = function(date) {
    return ((this.getFullYear() == date.getFullYear()) &&
        (this.getMonth() == date.getMonth()) &&
        (this.getDate() == date.getDate()) &&
        (this.getHours() == date.getHours()) &&
        (this.getMinutes() == date.getMinutes()));
};

/** Set only the year, month, date parts (keep existing time) */
Date.prototype.setDateOnly = function(date) {
    var tmp = new CalendarDateObject(date);
    this.setDate(1);
    this.setFullYear(tmp.getFullYear());
    this.setMonth(tmp.getMonth());
    this.setDate(tmp.getDate());
};

/** Prints the date in a string according to the given format. */
Date.prototype.print = function (str) {
    var m = this.getMonth();
    var d = this.getDate();
    var y = this.getFullYear();
    var wn = this.getWeekNumber();
    var w = this.getDay();
    var s = {};
    var hr = this.getHours();
    var pm = (hr >= 12);
    var ir = (pm) ? (hr - 12) : hr;
    var dy = this.getDayOfYear();
    if (ir == 0)
        ir = 12;
    var min = this.getMinutes();
    var sec = this.getSeconds();
    s["%a"] = Calendar._SDN[w]; // abbreviated weekday name [FIXME: I18N]
    s["%A"] = Calendar._DN[w]; // full weekday name
    s["%b"] = Calendar._SMN[m]; // abbreviated month name [FIXME: I18N]
    s["%B"] = Calendar._MN[m]; // full month name
    // FIXME: %c : preferred date and time representation for the current locale
    s["%C"] = 1 + Math.floor(y / 100); // the century number
    s["%d"] = (d < 10) ? ("0" + d) : d; // the day of the month (range 01 to 31)
    s["%e"] = d; // the day of the month (range 1 to 31)
    // FIXME: %D : american date style: %m/%d/%y
    // FIXME: %E, %F, %G, %g, %h (man strftime)
    s["%H"] = (hr < 10) ? ("0" + hr) : hr; // hour, range 00 to 23 (24h format)
    s["%I"] = (ir < 10) ? ("0" + ir) : ir; // hour, range 01 to 12 (12h format)
    s["%j"] = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy; // day of the year (range 001 to 366)
    s["%k"] = hr;        // hour, range 0 to 23 (24h format)
    s["%l"] = ir;        // hour, range 1 to 12 (12h format)
    s["%m"] = (m < 9) ? ("0" + (1+m)) : (1+m); // month, range 01 to 12
    s["%M"] = (min < 10) ? ("0" + min) : min; // minute, range 00 to 59
    s["%n"] = "\n";        // a newline character
    s["%p"] = pm ? Calendar._pm.toUpperCase() : Calendar._am.toUpperCase();
    s["%P"] = pm ? Calendar._pm.toLowerCase() : Calendar._am.toLowerCase();
    // FIXME: %r : the time in am/pm notation %I:%M:%S %p
    // FIXME: %R : the time in 24-hour notation %H:%M
    s["%s"] = Math.floor(this.getTime() / 1000);
    s["%S"] = (sec < 10) ? ("0" + sec) : sec; // seconds, range 00 to 59
    s["%t"] = "\t";        // a tab character
    // FIXME: %T : the time in 24-hour notation (%H:%M:%S)
    s["%U"] = s["%W"] = s["%V"] = (wn < 10) ? ("0" + wn) : wn;
    s["%u"] = w + 1;    // the day of the week (range 1 to 7, 1 = MON)
    s["%w"] = w;        // the day of the week (range 0 to 6, 0 = SUN)
    // FIXME: %x : preferred date representation for the current locale without the time
    // FIXME: %X : preferred time representation for the current locale without the date
    s["%y"] = ('' + y).substr(2, 2); // year without the century (range 00 to 99)
    s["%Y"] = y;        // year with the century
    s["%%"] = "%";        // a literal '%' character

    var re = /%./g;
    if (!Calendar.is_ie5 && !Calendar.is_khtml)
        return str.replace(re, function (par) { return s[par] || par; });

    var a = str.match(re);
    for (var i = 0; i < a.length; i++) {
        var tmp = s[a[i]];
        if (tmp) {
            re = new RegExp(a[i], 'g');
            str = str.replace(re, tmp);
        }
    }

    return str;
};

Date.prototype.__msh_oldSetFullYear = Date.prototype.setFullYear;
Date.prototype.setFullYear = function(y) {
    var d = new CalendarDateObject(this);
    d.__msh_oldSetFullYear(y);
    if (d.getMonth() != this.getMonth())
        this.setDate(28);
    this.__msh_oldSetFullYear(y);
};

CalendarDateObject.prototype = new Date();
CalendarDateObject.prototype.constructor = CalendarDateObject;
CalendarDateObject.prototype.parent = Date.prototype;
function CalendarDateObject() {
    var dateObj;
    if (arguments.length > 1) {
        dateObj = eval("new this.parent.constructor("+Array.prototype.slice.call(arguments).join(",")+");");
    } else if (arguments.length > 0) {
        dateObj = new this.parent.constructor(arguments[0]);
    } else {
        dateObj = new this.parent.constructor();
        if (typeof(CalendarDateObject._SERVER_TIMZEONE_SECONDS) != "undefined") {
            dateObj.setTime((CalendarDateObject._SERVER_TIMZEONE_SECONDS + dateObj.getTimezoneOffset()*60)*1000);
        }
    }
    return dateObj;
}

// END: DATE OBJECT PATCHES


// global object that remembers the calendar
window._dynarch_popupCalendar = null;

/* ======================================================================================= */
/* merged js/calendar/calendar-setup.js                                                    */
/* ======================================================================================= */
/*  Copyright Mihai Bazon, 2002, 2003  |  http://dynarch.com/mishoo/
 * ---------------------------------------------------------------------------
 *
 * The DHTML Calendar
 *
 * Details and latest version at:
 * http://dynarch.com/mishoo/calendar.epl
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 *
 * This file defines helper functions for setting up the calendar.  They are
 * intended to help non-programmers get a working calendar on their site
 * quickly.  This script should not be seen as part of the calendar.  It just
 * shows you what one can do with the calendar, while in the same time
 * providing a quick and simple method for setting it up.  If you need
 * exhaustive customization of the calendar creation process feel free to
 * modify this code to suit your needs (this is recommended and much better
 * than modifying calendar.js itself).
 */
Calendar.setup=function(params){function param_default(pname,def){if(typeof params[pname]=="undefined"){params[pname]=def;}};param_default("inputField",null);param_default("displayArea",null);param_default("button",null);param_default("eventName","click");param_default("ifFormat","%Y/%m/%d");param_default("daFormat","%Y/%m/%d");param_default("singleClick",true);param_default("disableFunc",null);param_default("dateStatusFunc",params["disableFunc"]);param_default("dateText",null);param_default("firstDay",null);param_default("align","Br");param_default("range",[1900,2999]);param_default("weekNumbers",true);param_default("flat",null);param_default("flatCallback",null);param_default("onSelect",null);param_default("onClose",null);param_default("onUpdate",null);param_default("date",null);param_default("showsTime",false);param_default("timeFormat","24");param_default("electric",true);param_default("step",2);param_default("position",null);param_default("cache",false);param_default("showOthers",false);param_default("multiple",null);var tmp=["inputField","displayArea","button"];for(var i in tmp){if(typeof params[tmp[i]]=="string"){params[tmp[i]]=document.getElementById(params[tmp[i]]);}}if(!(params.flat||params.multiple||params.inputField||params.displayArea||params.button)){alert("Calendar.setup:\n  Nothing to setup (no fields found).  Please check your code");return false;}function onSelect(cal){var p=cal.params;var update=(cal.dateClicked||p.electric);if(update&&p.inputField){p.inputField.value=cal.date.print(p.ifFormat);if(typeof p.inputField.onchange=="function")p.inputField.onchange();if(typeof fireEvent == 'function')fireEvent(p.inputField, "change");}if(update&&p.displayArea)p.displayArea.innerHTML=cal.date.print(p.daFormat);if(update&&typeof p.onUpdate=="function")p.onUpdate(cal);if(update&&p.flat){if(typeof p.flatCallback=="function")p.flatCallback(cal);}if(update&&p.singleClick&&cal.dateClicked)cal.callCloseHandler();};if(params.flat!=null){if(typeof params.flat=="string")params.flat=document.getElementById(params.flat);if(!params.flat){alert("Calendar.setup:\n  Flat specified but can't find parent.");return false;}var cal=new Calendar(params.firstDay,params.date,params.onSelect||onSelect);cal.showsOtherMonths=params.showOthers;cal.showsTime=params.showsTime;cal.time24=(params.timeFormat=="24");cal.params=params;cal.weekNumbers=params.weekNumbers;cal.setRange(params.range[0],params.range[1]);cal.setDateStatusHandler(params.dateStatusFunc);cal.getDateText=params.dateText;if(params.ifFormat){cal.setDateFormat(params.ifFormat);}if(params.inputField&&typeof params.inputField.value=="string"){cal.parseDate(params.inputField.value);}cal.create(params.flat);cal.show();return false;}var triggerEl=params.button||params.displayArea||params.inputField;triggerEl["on"+params.eventName]=function(){var dateEl=params.inputField||params.displayArea;var dateFmt=params.inputField?params.ifFormat:params.daFormat;var mustCreate=false;var cal=window.calendar;if(dateEl)params.date=Date.parseDate(dateEl.value||dateEl.innerHTML,dateFmt);if(!(cal&&params.cache)){window.calendar=cal=new Calendar(params.firstDay,params.date,params.onSelect||onSelect,params.onClose||function(cal){cal.hide();});cal.showsTime=params.showsTime;cal.time24=(params.timeFormat=="24");cal.weekNumbers=params.weekNumbers;mustCreate=true;}else{if(params.date)cal.setDate(params.date);cal.hide();}if(params.multiple){cal.multiple={};for(var i=params.multiple.length;--i>=0;){var d=params.multiple[i];var ds=d.print("%Y%m%d");cal.multiple[ds]=d;}}cal.showsOtherMonths=params.showOthers;cal.yearStep=params.step;cal.setRange(params.range[0],params.range[1]);cal.params=params;cal.setDateStatusHandler(params.dateStatusFunc);cal.getDateText=params.dateText;cal.setDateFormat(dateFmt);if(mustCreate)cal.create();cal.refresh();if(!params.position)cal.showAtElement(params.button||params.displayArea||params.inputField,params.align);else cal.showAt(params.position[0],params.position[1]);return false;};return cal;};
/* ======================================================================================= */
/* merged js/mage/adminhtml/form.js                                                        */
/* ======================================================================================= */
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
var varienForm = new Class.create();

varienForm.prototype = {
    initialize : function(formId, validationUrl){
        this.formId = formId;
        this.validationUrl = validationUrl;
        this.submitUrl = false;

        if($(this.formId)){
            this.validator  = new Validation(this.formId, {onElementValidate : this.checkErrors.bind(this)});
        }
        this.errorSections = $H({});
    },

    checkErrors : function(result, elm){
        if(!result)
            elm.setHasError(true, this);
        else
            elm.setHasError(false, this);
    },

    validate : function(){
        if(this.validator && this.validator.validate()){
            if(this.validationUrl){
                this._validate();
            }
            return true;
        }
        return false;
    },

    submit : function(url){
        if (typeof varienGlobalEvents != undefined) {
            varienGlobalEvents.fireEvent('formSubmit', this.formId);
        }
        this.errorSections = $H({});
        this.canShowError = true;
        this.submitUrl = url;
        if(this.validator && this.validator.validate()){
            if(this.validationUrl){
                this._validate();
            }
            else{
                this._submit();
            }
            return true;
        }
        return false;
    },

    _validate : function(){
        new Ajax.Request(this.validationUrl,{
            method: 'post',
            parameters: $(this.formId).serialize(),
            onComplete: this._processValidationResult.bind(this),
            onFailure: this._processFailure.bind(this)
        });
    },

    _processValidationResult : function(transport){
        if (typeof varienGlobalEvents != undefined) {
            varienGlobalEvents.fireEvent('formValidateAjaxComplete', transport);
        }
        var response = transport.responseText.evalJSON();
        if(response.error){
            if($('messages')){
                $('messages').innerHTML = response.message;
            }
        }
        else{
            this._submit();
        }
    },

    _processFailure : function(transport){
        location.href = BASE_URL;
    },

    _submit : function(){
        var $form = $(this.formId);
        if(this.submitUrl){
            $form.action = this.submitUrl;
        }
        $form.submit();
    }
}

/**
 * redeclare Validation.isVisible function
 *
 * use for not visible elements validation
 */
Validation.isVisible = function(elm){
    while (elm && elm.tagName != 'BODY') {
        if (elm.disabled) return false;
        if ((Element.hasClassName(elm, 'template') && Element.hasClassName(elm, 'no-display'))
             || Element.hasClassName(elm, 'ignore-validate')){
            return false;
        }
        elm = elm.parentNode;
    }
    return true;
}

/**
 *  Additional elements methods
 */
var varienElementMethods = {
    setHasChanges : function(element, event){
        if($(element) && $(element).hasClassName('no-changes')) return;
        var elm = element;
        while(elm && elm.tagName != 'BODY') {
            if(elm.statusBar)
                Element.addClassName($(elm.statusBar), 'changed')
            elm = elm.parentNode;
        }
    },
    setHasError : function(element, flag, form){
        var elm = element;
        while(elm && elm.tagName != 'BODY') {
            if(elm.statusBar){
                if(form.errorSections.keys().indexOf(elm.statusBar.id)<0)
                    form.errorSections.set(elm.statusBar.id, flag);
                if(flag){
                    Element.addClassName($(elm.statusBar), 'error');
                    if(form.canShowError && $(elm.statusBar).show){
                        form.canShowError = false;
                        $(elm.statusBar).show();
                    }
                    form.errorSections.set(elm.statusBar.id, flag);
                }
                else if(!form.errorSections.get(elm.statusBar.id)){
                    Element.removeClassName($(elm.statusBar), 'error')
                }
            }
            elm = elm.parentNode;
        }
        this.canShowElement = false;
    }
}

Element.addMethods(varienElementMethods);

// Global bind changes
varienWindowOnloadCache = {};
function varienWindowOnload(useCache){
    var dataElements = $$('input', 'select', 'textarea');
    for(var i=0; i<dataElements.length;i++){
        if(dataElements[i] && dataElements[i].id){
            if ((!useCache) || (!varienWindowOnloadCache[dataElements[i].id])) {
                Event.observe(dataElements[i], 'change', dataElements[i].setHasChanges.bind(dataElements[i]));
                if (useCache) {
                    varienWindowOnloadCache[dataElements[i].id] = true;
                }
            }
        }
    }
}
Event.observe(window, 'load', varienWindowOnload);

RegionUpdater = Class.create();
RegionUpdater.prototype = {
    initialize: function (countryEl, regionTextEl, regionSelectEl, regions, disableAction, clearRegionValueOnDisable)
    {
        this.countryEl = $(countryEl);
        this.regionTextEl = $(regionTextEl);
        this.regionSelectEl = $(regionSelectEl);
//        // clone for select element (#6924)
//        this._regionSelectEl = {};
//        this.tpl = new Template('<select class="#{className}" name="#{name}" id="#{id}">#{innerHTML}</select>');
        this.config = regions['config'];
        delete regions.config;
        this.regions = regions;
        this.disableAction = (typeof disableAction=='undefined') ? 'hide' : disableAction;
        this.clearRegionValueOnDisable = (typeof clearRegionValueOnDisable == 'undefined') ? false : clearRegionValueOnDisable;

        if (this.regionSelectEl.options.length<=1) {
            this.update();
        }
        else {
            this.lastCountryId = this.countryEl.value;
        }

        this.countryEl.changeUpdater = this.update.bind(this);

        Event.observe(this.countryEl, 'change', this.update.bind(this));
    },

    _checkRegionRequired: function()
    {
        var label, wildCard;
        var elements = [this.regionTextEl, this.regionSelectEl];
        var that = this;
        if (typeof this.config == 'undefined') {
            return;
        }
        var regionRequired = this.config.regions_required.indexOf(this.countryEl.value) >= 0;

        elements.each(function(currentElement) {
            if(!currentElement) {
                return;
            }
            Validation.reset(currentElement);
            label = $$('label[for="' + currentElement.id + '"]')[0];
            if (label) {
                wildCard = label.down('em') || label.down('span.required');
                var topElement = label.up('tr') || label.up('li');
                if (!that.config.show_all_regions && topElement) {
                    if (regionRequired) {
                        topElement.show();
                    } else {
                        topElement.hide();
                    }
                }
            }

            if (label && wildCard) {
                if (!regionRequired) {
                    wildCard.hide();
                } else {
                    wildCard.show();
                }
            }

            if (!regionRequired || !currentElement.visible()) {
                if (currentElement.hasClassName('required-entry')) {
                    currentElement.removeClassName('required-entry');
                }
                if ('select' == currentElement.tagName.toLowerCase() &&
                    currentElement.hasClassName('validate-select')
                ) {
                    currentElement.removeClassName('validate-select');
                }
            } else {
                if (!currentElement.hasClassName('required-entry')) {
                    currentElement.addClassName('required-entry');
                }
                if ('select' == currentElement.tagName.toLowerCase() &&
                    !currentElement.hasClassName('validate-select')
                ) {
                    currentElement.addClassName('validate-select');
                }
            }
        });
    },

    update: function()
    {
        if (this.regions[this.countryEl.value]) {
//            if (!this.regionSelectEl) {
//                Element.insert(this.regionTextEl, {after : this.tpl.evaluate(this._regionSelectEl)});
//                this.regionSelectEl = $(this._regionSelectEl.id);
//            }
            if (this.lastCountryId!=this.countryEl.value) {
                var i, option, region, def;

                def = this.regionSelectEl.getAttribute('defaultValue');
                if (this.regionTextEl) {
                    if (!def) {
                        def = this.regionTextEl.value.toLowerCase();
                    }
                    this.regionTextEl.value = '';
                }

                this.regionSelectEl.options.length = 1;
                for (regionId in this.regions[this.countryEl.value]) {
                    region = this.regions[this.countryEl.value][regionId];

                    option = document.createElement('OPTION');
                    option.value = regionId;
                    option.text = region.name.stripTags();
                    option.title = region.name;

                    if (this.regionSelectEl.options.add) {
                        this.regionSelectEl.options.add(option);
                    } else {
                        this.regionSelectEl.appendChild(option);
                    }

                    if (regionId==def || region.name.toLowerCase()==def || region.code.toLowerCase()==def) {
                        this.regionSelectEl.value = regionId;
                    }
                }
            }

            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = 'none';
                    this.regionTextEl.style.disabled = true;
                }
                this.regionSelectEl.style.display = '';
                this.regionSelectEl.disabled = false;
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = true;
                }
                this.regionSelectEl.disabled = false;
            }
            this.setMarkDisplay(this.regionSelectEl, true);

            this.lastCountryId = this.countryEl.value;
        } else {
            if (this.disableAction=='hide') {
                if (this.regionTextEl) {
                    this.regionTextEl.style.display = '';
                    this.regionTextEl.style.disabled = false;
                }
                this.regionSelectEl.style.display = 'none';
                this.regionSelectEl.disabled = true;
            } else if (this.disableAction=='disable') {
                if (this.regionTextEl) {
                    this.regionTextEl.disabled = false;
                }
                this.regionSelectEl.disabled = true;
                if (this.clearRegionValueOnDisable) {
                    this.regionSelectEl.value = '';
                }
            } else if (this.disableAction=='nullify') {
                this.regionSelectEl.options.length = 1;
                this.regionSelectEl.value = '';
                this.regionSelectEl.selectedIndex = 0;
                this.lastCountryId = '';
            }
            this.setMarkDisplay(this.regionSelectEl, false);

//            // clone required stuff from select element and then remove it
//            this._regionSelectEl.className = this.regionSelectEl.className;
//            this._regionSelectEl.name      = this.regionSelectEl.name;
//            this._regionSelectEl.id        = this.regionSelectEl.id;
//            this._regionSelectEl.innerHTML = this.regionSelectEl.innerHTML;
//            Element.remove(this.regionSelectEl);
//            this.regionSelectEl = null;
        }
        varienGlobalEvents.fireEvent("address_country_changed", this.countryEl);
        this._checkRegionRequired();
    },

    setMarkDisplay: function(elem, display){
        if(elem.parentNode.parentNode){
            var marks = Element.select(elem.parentNode.parentNode, '.required');
            if(marks[0]){
                display ? marks[0].show() : marks[0].hide();
            }
        }
    }
}

regionUpdater = RegionUpdater;

/**
 * Fix errorrs in IE
 */
Event.pointerX = function(event){
    try{
        return event.pageX || (event.clientX +(document.documentElement.scrollLeft || document.body.scrollLeft));
    }
    catch(e){

    }
}
Event.pointerY = function(event){
    try{
        return event.pageY || (event.clientY +(document.documentElement.scrollTop || document.body.scrollTop));
    }
    catch(e){

    }
}

SelectUpdater = Class.create();
SelectUpdater.prototype = {
    initialize: function (firstSelect, secondSelect, selectFirstMessage, noValuesMessage, values, selected)
    {
        this.first = $(firstSelect);
        this.second = $(secondSelect);
        this.message = selectFirstMessage;
        this.values = values;
        this.noMessage = noValuesMessage;
        this.selected = selected;

        this.update();

        Event.observe(this.first, 'change', this.update.bind(this));
    },

    update: function()
    {
        this.second.length = 0;
        this.second.value = '';

        if (this.first.value && this.values[this.first.value]) {
            for (optionValue in this.values[this.first.value]) {
                optionTitle = this.values[this.first.value][optionValue];

                this.addOption(this.second, optionValue, optionTitle);
            }
            this.second.disabled = false;
        } else if (this.first.value && !this.values[this.first.value]) {
            this.addOption(this.second, '', this.noMessage);
        } else {
            this.addOption(this.second, '', this.message);
            this.second.disabled = true;
        }
    },

    addOption: function(select, value, text)
    {
        option = document.createElement('OPTION');
        option.value = value;
        option.text = text;

        if (this.selected && option.value == this.selected) {
            option.selected = true;
            this.selected = false;
        }

        if (select.options.add) {
            select.options.add(option);
        } else {
            select.appendChild(option);
        }
    }
}


/**
 * Observer that watches for dependent form elements
 * If an element depends on 1 or more of other elements, it should show up only when all of them gain specified values
 */
FormElementDependenceController = Class.create();
FormElementDependenceController.prototype = {
    /**
     * Structure of elements: {
     *     'id_of_dependent_element' : {
     *         'id_of_master_element_1' : 'reference_value',
     *         'id_of_master_element_2' : 'reference_value'
     *         'id_of_master_element_3' : ['reference_value1', 'reference_value2']
     *         ...
     *     }
     * }
     * @param object elementsMap
     * @param object config
     */
    initialize : function (elementsMap, config)
    {
        if (config) {
            this._config = config;
        }
        for (var idTo in elementsMap) {
            for (var idFrom in elementsMap[idTo]) {
                if ($(idFrom)) {
                    Event.observe($(idFrom), 'change', this.trackChange.bindAsEventListener(this, idTo, elementsMap[idTo]));
                    this.trackChange(null, idTo, elementsMap[idTo]);
                } else {
                    this.trackChange(null, idTo, elementsMap[idTo]);
                }
            }
        }
    },

    /**
     * Misc. config options
     * Keys are underscored intentionally
     */
    _config : {
        levels_up : 1 // how many levels up to travel when toggling element
    },

    /**
     * Define whether target element should be toggled and show/hide its row
     *
     * @param object e - event
     * @param string idTo - id of target element
     * @param valuesFrom - ids of master elements and reference values
     * @return
     */
    trackChange : function(e, idTo, valuesFrom)
    {
        // define whether the target should show up
        var shouldShowUp = true;
        for (var idFrom in valuesFrom) {
            var from = $(idFrom);
            if (valuesFrom[idFrom] instanceof Array) {
                if (!from || valuesFrom[idFrom].indexOf(from.value) == -1) {
                    shouldShowUp = false;
                }
            } else {
                if (!from || from.value != valuesFrom[idFrom]) {
                    shouldShowUp = false;
                }
            }
        }

        // toggle target row
        if (shouldShowUp) {
            var currentConfig = this._config;
            $(idTo).up(this._config.levels_up).select('input', 'select', 'td').each(function (item) {
                // don't touch hidden inputs (and Use Default inputs too), bc they may have custom logic
                if ((!item.type || item.type != 'hidden') && !($(item.id+'_inherit') && $(item.id+'_inherit').checked)
                    && !(currentConfig.can_edit_price != undefined && !currentConfig.can_edit_price)) {
                    item.disabled = false;
                }
            });
            $(idTo).up(this._config.levels_up).show();
        } else {
            $(idTo).up(this._config.levels_up).select('input', 'select', 'td').each(function (item){
                // don't touch hidden inputs (and Use Default inputs too), bc they may have custom logic
                if ((!item.type || item.type != 'hidden') && !($(item.id+'_inherit') && $(item.id+'_inherit').checked)) {
                    item.disabled = true;
                }
            });
            $(idTo).up(this._config.levels_up).hide();
        }
    }
};

/* ======================================================================================= */
/* merged js/mage/adminhtml/events.js                                                      */
/* ======================================================================================= */
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
// from http://www.someelement.com/2007/03/eventpublisher-custom-events-la-pubsub.html
varienEvents = Class.create();

varienEvents.prototype = {
    initialize: function() {
        this.arrEvents = {};
        this.eventPrefix = '';
    },

    /**
    * Attaches a {handler} function to the publisher's {eventName} event for execution upon the event firing
    * @param {String} eventName
    * @param {Function} handler
    * @param {Boolean} asynchFlag [optional] Defaults to false if omitted. Indicates whether to execute {handler} asynchronously (true) or not (false).
    */
    attachEventHandler : function(eventName, handler) {
        if ((typeof handler == 'undefined') || (handler == null)) {
            return;
        }
        eventName = eventName + this.eventPrefix;
        // using an event cache array to track all handlers for proper cleanup
        if (this.arrEvents[eventName] == null){
            this.arrEvents[eventName] = [];
        }
        //create a custom object containing the handler method and the asynch flag
        var asynchVar = arguments.length > 2 ? arguments[2] : false;
        var handlerObj = {
            method: handler,
            asynch: asynchVar
        };
        this.arrEvents[eventName].push(handlerObj);
    },

    /**
    * Removes a single handler from a specific event
    * @param {String} eventName The event name to clear the handler from
    * @param {Function} handler A reference to the handler function to un-register from the event
    */
    removeEventHandler : function(eventName, handler) {
        eventName = eventName + this.eventPrefix;
        if (this.arrEvents[eventName] != null){
            this.arrEvents[eventName] = this.arrEvents[eventName].reject(function(obj) { return obj.method == handler; });
        }
    },

    /**
    * Removes all handlers from a single event
    * @param {String} eventName The event name to clear handlers from
    */
    clearEventHandlers : function(eventName) {
        eventName = eventName + this.eventPrefix;
        this.arrEvents[eventName] = null;
    },

    /**
    * Removes all handlers from ALL events
    */
    clearAllEventHandlers : function() {
        this.arrEvents = {};
    },

    /**
    * Fires the event {eventName}, resulting in all registered handlers to be executed.
    * It also collects and returns results of all non-asynchronous handlers
    * @param {String} eventName The name of the event to fire
    * @params {Object} args [optional] Any object, will be passed into the handler function as the only argument
    * @return {Array}
    */
    fireEvent : function(eventName) {
        var evtName = eventName + this.eventPrefix;
        var results = [];
        var result;
        if (this.arrEvents[evtName] != null) {
            var len = this.arrEvents[evtName].length; //optimization
            for (var i = 0; i < len; i++) {
                try {
                    if (arguments.length > 1) {
                        if (this.arrEvents[evtName][i].asynch) {
                            var eventArgs = arguments[1];
                            var method = this.arrEvents[evtName][i].method.bind(this);
                            setTimeout(function() { method(eventArgs) }.bind(this), 10);
                        }
                        else{
                            result = this.arrEvents[evtName][i].method(arguments[1]);
                        }
                    }
                    else {
                        if (this.arrEvents[evtName][i].asynch) {
                            var eventHandler = this.arrEvents[evtName][i].method;
                            setTimeout(eventHandler, 1);
                        }
                        else if (this.arrEvents && this.arrEvents[evtName] && this.arrEvents[evtName][i] && this.arrEvents[evtName][i].method){
                            result = this.arrEvents[evtName][i].method();
                        }
                    }
                    results.push(result);
                }
                catch (e) {
                    if (this.id){
                        alert("error: error in " + this.id + ".fireEvent():\n\nevent name: " + eventName + "\n\nerror message: " + e.message);
                    }
                    else {
                        alert("error: error in [unknown object].fireEvent():\n\nevent name: " + eventName + "\n\nerror message: " + e.message);
                    }
                }
            }
        }
        return results;
    }
};

varienGlobalEvents = new varienEvents();


/* ======================================================================================= */
/* merged js/mage/adminhtml/loader.js                                                      */
/* ======================================================================================= */
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

var SessionError = Class.create();
SessionError.prototype = {
    initialize: function(errorText) {
        this.errorText = errorText;
    },
    toString: function()
    {
        return 'Session Error:' + this.errorText;
    }
};

Ajax.Request.addMethods({
    initialize: function($super, url, options){
        $super(options);
        this.transport = Ajax.getTransport();
        if (!url.match(new RegExp('[?&]isAjax=true',''))) {
            url = url.match(new RegExp('\\?',"g")) ? url + '&isAjax=true' : url + '?isAjax=true';
        }
        if (Object.isString(this.options.parameters)
            && this.options.parameters.indexOf('form_key=') == -1
        ) {
            this.options.parameters += '&' + Object.toQueryString({
                form_key: FORM_KEY
            });
        } else {
            if (!this.options.parameters) {
                this.options.parameters = {
                    form_key: FORM_KEY
                };
            }
            if (!this.options.parameters.form_key) {
                this.options.parameters.form_key = FORM_KEY;
            }
        }

        this.request(url);
    },
    respondToReadyState: function(readyState) {
        var state = Ajax.Request.Events[readyState], response = new Ajax.Response(this);

        if (state == 'Complete') {
            try {
                this._complete = true;
                if (response.responseText.isJSON()) {
                    var jsonObject = response.responseText.evalJSON();
                    if (jsonObject.ajaxExpired && jsonObject.ajaxRedirect) {
                        window.location.replace(jsonObject.ajaxRedirect);
                        throw new SessionError('session expired');
                    }
                }

                (this.options['on' + response.status]
                 || this.options['on' + (this.success() ? 'Success' : 'Failure')]
                 || Prototype.emptyFunction)(response, response.headerJSON);
            } catch (e) {
                this.dispatchException(e);
                if (e instanceof SessionError) {
                    return;
                }
            }

            var contentType = response.getHeader('Content-type');
            if (this.options.evalJS == 'force'
                || (this.options.evalJS && this.isSameOrigin() && contentType
                && contentType.match(/^\s*(text|application)\/(x-)?(java|ecma)script(;.*)?\s*$/i))) {
                this.evalResponse();
            }
        }

        try {
            (this.options['on' + state] || Prototype.emptyFunction)(response, response.headerJSON);
            Ajax.Responders.dispatch('on' + state, this, response, response.headerJSON);
        } catch (e) {
            this.dispatchException(e);
        }

        if (state == 'Complete') {
            // avoid memory leak in MSIE: clean up
            this.transport.onreadystatechange = Prototype.emptyFunction;
        }
    }
});

Ajax.Updater.respondToReadyState = Ajax.Request.respondToReadyState;
//Ajax.Updater = Object.extend(Ajax.Updater, {
//  initialize: function($super, container, url, options) {
//    this.container = {
//      success: (container.success || container),
//      failure: (container.failure || (container.success ? null : container))
//    };
//
//    options = Object.clone(options);
//    var onComplete = options.onComplete;
//    options.onComplete = (function(response, json) {
//      this.updateContent(response.responseText);
//      if (Object.isFunction(onComplete)) onComplete(response, json);
//    }).bind(this);
//
//    $super((url.match(new RegExp('\\?',"g")) ? url + '&isAjax=1' : url + '?isAjax=1'), options);
//  }
//});

var varienLoader = new Class.create();

varienLoader.prototype = {
    initialize : function(caching){
        this.callback= false;
        this.cache   = $H();
        this.caching = caching || false;
        this.url     = false;
    },

    getCache : function(url){
        if(this.cache.get(url)){
            return this.cache.get(url)
        }
        return false;
    },

    load : function(url, params, callback){
        this.url      = url;
        this.callback = callback;

        if(this.caching){
            var transport = this.getCache(url);
            if(transport){
                this.processResult(transport);
                return;
            }
        }

        if (typeof(params.updaterId) != 'undefined') {
            new varienUpdater(params.updaterId, url, {
                evalScripts : true,
                onComplete: this.processResult.bind(this),
                onFailure: this._processFailure.bind(this)
            });
        }
        else {
            new Ajax.Request(url,{
                method: 'post',
                parameters: params || {},
                onComplete: this.processResult.bind(this),
                onFailure: this._processFailure.bind(this)
            });
        }
    },

    _processFailure : function(transport){
        location.href = BASE_URL;
    },

    processResult : function(transport){
        if(this.caching){
            this.cache.set(this.url, transport);
        }
        if(this.callback){
            this.callback(transport.responseText);
        }
    }
}

if (!window.varienLoaderHandler)
    var varienLoaderHandler = new Object();

varienLoaderHandler.handler = {
    onCreate: function(request) {
        if(request.options.loaderArea===false){
            return;
        }

        request.options.loaderArea = $$('#html-body .wrapper')[0]; // Blocks all page

        if(request && request.options.loaderArea){
            Element.clonePosition($('loading-mask'), $(request.options.loaderArea), {offsetLeft:-2})
            toggleSelectsUnderBlock($('loading-mask'), false);
            Element.show('loading-mask');
            setLoaderPosition();
            if(request.options.loaderArea=='html-body'){
                //Element.show('loading-process');
            }
        }
        else{
            //Element.show('loading-process');
        }
    },

    onComplete: function(transport) {
        if(Ajax.activeRequestCount == 0) {
            //Element.hide('loading-process');
            toggleSelectsUnderBlock($('loading-mask'), true);
            Element.hide('loading-mask');
        }
    }
};

/**
 * @todo need calculate middle of visible area and scroll bind
 */
function setLoaderPosition(){
    var elem = $('loading_mask_loader');
    if (elem && Prototype.Browser.IE) {
        var elementDims = elem.getDimensions();
        var viewPort = document.viewport.getDimensions();
        var offsets = document.viewport.getScrollOffsets();
        elem.style.left = Math.floor(viewPort.width / 2 + offsets.left - elementDims.width / 2) + 'px';
        elem.style.top = Math.floor(viewPort.height / 2 + offsets.top - elementDims.height / 2) + 'px';
        elem.style.position = 'absolute';
    }
}

/*function getRealHeight() {
    var body = document.body;
    if (window.innerHeight && window.scrollMaxY) {
        return window.innerHeight + window.scrollMaxY;
    }
    return Math.max(body.scrollHeight, body.offsetHeight);
}*/



function toggleSelectsUnderBlock(block, flag){
    if(Prototype.Browser.IE){
        var selects = document.getElementsByTagName("select");
        for(var i=0; i<selects.length; i++){
            /**
             * @todo: need check intersection
             */
            if(flag){
                if(selects[i].needShowOnSuccess){
                    selects[i].needShowOnSuccess = false;
                    // Element.show(selects[i])
                    selects[i].style.visibility = '';
                }
            }
            else{
                if(Element.visible(selects[i])){
                    // Element.hide(selects[i]);
                    selects[i].style.visibility = 'hidden';
                    selects[i].needShowOnSuccess = true;
                }
            }
        }
    }
}

Ajax.Responders.register(varienLoaderHandler.handler);

var varienUpdater = Class.create(Ajax.Updater, {
    updateContent: function($super, responseText) {
        if (responseText.isJSON()) {
            var responseJSON = responseText.evalJSON();
            if (responseJSON.ajaxExpired && responseJSON.ajaxRedirect) {
                window.location.replace(responseJSON.ajaxRedirect);
            }
        } else {
            $super(responseText);
        }
    }
});

/* ======================================================================================= */
/* merged js/mage/adminhtml/tools.js                                                       */
/* ======================================================================================= */
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
function setLocation(url){
    window.location.href = url;
}

function confirmSetLocation(message, url){
    if( confirm(message) ) {
        setLocation(url);
    }
    return false;
}

function deleteConfirm(message, url) {
    confirmSetLocation(message, url);
}

function setElementDisable(element, disable){
    if($(element)){
        $(element).disabled = disable;
    }
}

function toggleParentVis(obj) {
    obj = $(obj).parentNode;
    if( obj.style.display == 'none' ) {
        obj.style.display = '';
    } else {
        obj.style.display = 'none';
    }
}

// to fix new app/design/adminhtml/default/default/template/widget/form/renderer/fieldset.phtml
// with toggleParentVis
function toggleFieldsetVis(obj) {
    id = obj;
    obj = $(obj);
    if( obj.style.display == 'none' ) {
        obj.style.display = '';
    } else {
        obj.style.display = 'none';
    }
    obj = obj.parentNode.childElements();
    for (var i = 0; i < obj.length; i++) {
        if (obj[i].id != undefined
            && obj[i].id == id
            && obj[(i-1)].classNames() == 'entry-edit-head')
        {
            if (obj[i-1].style.display == 'none') {
                obj[i-1].style.display = '';
            } else {
                obj[i-1].style.display = 'none';
            }
        }
    }
}

function toggleVis(obj) {
    obj = $(obj);
    if( obj.style.display == 'none' ) {
        obj.style.display = '';
    } else {
        obj.style.display = 'none';
    }
}

function imagePreview(element){
    if($(element)){
        var win = window.open('', 'preview', 'width=400,height=400,resizable=1,scrollbars=1');
        win.document.open();
        win.document.write('<body style="padding:0;margin:0"><img src="'+$(element).src+'" id="image_preview"/></body>');
        win.document.close();
        Event.observe(win, 'load', function(){
            var img = win.document.getElementById('image_preview');
            win.resizeTo(img.width+40, img.height+80)
        });
    }
}

function checkByProductPriceType(elem) {
    if (elem.id == 'price_type') {
        this.productPriceType = elem.value;
        return false;
    } else {
        if (elem.id == 'price' && this.productPriceType == 0) {
            return false;
        }
        return true;
    }
}

Event.observe(window, 'load', function() {
    if ($('price_default') && $('price_default').checked) {
        $('price').disabled = 'disabled';
    }
});

function toggleValueElements(checkbox, container, excludedElements, checked){
    if(container && checkbox){
        var ignoredElements = [checkbox];
        if (typeof excludedElements != 'undefined') {
            if (Object.prototype.toString.call(excludedElements) != '[object Array]') {
                excludedElements = [excludedElements];
            }
            for (var i = 0; i < excludedElements.length; i++) {
                ignoredElements.push(excludedElements[i]);
            }
        }
        //var elems = container.select('select', 'input');
        var elems = Element.select(container, ['select', 'input', 'textarea', 'button', 'img']);
        var isDisabled = (checked != undefined ? checked : checkbox.checked);
        elems.each(function (elem) {
            if (checkByProductPriceType(elem)) {
                var i = ignoredElements.length;
                while (i-- && elem != ignoredElements[i]);
                if (i != -1) {
                    return;
                }

                elem.disabled = isDisabled;
                if (isDisabled) {
                    elem.addClassName('disabled');
                } else {
                    elem.removeClassName('disabled');
                }
                if (elem.nodeName.toLowerCase() == 'img') {
                    isDisabled ? elem.hide() : elem.show();
                }
            }
        });
    }
}

/**
 * @todo add validation for fields
 */
function submitAndReloadArea(area, url) {
    if($(area)) {
        var fields = $(area).select('input', 'select', 'textarea');
        var data = Form.serializeElements(fields, true);
        url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
        new Ajax.Request(url, {
            parameters: $H(data),
            loaderArea: area,
            onSuccess: function(transport) {
                try {
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON()
                        if (response.error) {
                            alert(response.message);
                        }
                        if(response.ajaxExpired && response.ajaxRedirect) {
                            setLocation(response.ajaxRedirect);
                        }
                    } else {
                        $(area).update(transport.responseText);
                    }
                }
                catch (e) {
                    $(area).update(transport.responseText);
                }
            }
        });
    }
}

/********** MESSAGES ***********/
/*
Event.observe(window, 'load', function() {
    $$('.messages .error-msg').each(function(el) {
        new Effect.Highlight(el, {startcolor:'#E13422', endcolor:'#fdf9f8', duration:1});
    });
    $$('.messages .warning-msg').each(function(el) {
        new Effect.Highlight(el, {startcolor:'#E13422', endcolor:'#fdf9f8', duration:1});
    });
    $$('.messages .notice-msg').each(function(el) {
        new Effect.Highlight(el, {startcolor:'#E5B82C', endcolor:'#fbf7e9', duration:1});
    });
    $$('.messages .success-msg').each(function(el) {
        new Effect.Highlight(el, {startcolor:'#507477', endcolor:'#f2fafb', duration:1});
    });
});
*/
function syncOnchangeValue(baseElem, distElem){
    var compare = {baseElem:baseElem, distElem:distElem}
    Event.observe(baseElem, 'change', function(){
        if($(this.baseElem) && $(this.distElem)){
            $(this.distElem).value = $(this.baseElem).value;
        }
    }.bind(compare));
}

// Insert some content to the cursor position of input element
function updateElementAtCursor(el, value, win) {
    if (win == undefined) {
        win = window.self;
    }
    if (document.selection) {
        el.focus();
        sel = win.document.selection.createRange();
        sel.text = value;
    } else if (el.selectionStart || el.selectionStart == '0') {
        var startPos = el.selectionStart;
        var endPos = el.selectionEnd;
        el.value = el.value.substring(0, startPos) + value + el.value.substring(endPos, el.value.length);
    } else {
        el.value += value;
    }
}

// Firebug detection
function firebugEnabled() {
    if(window.console && window.console.firebug) {
        return true;
    }
    return false;
}

function disableElement(elem) {
    elem.disabled = true;
    elem.addClassName('disabled');
}

function enableElement(elem) {
    elem.disabled = false;
    elem.removeClassName('disabled');
}

function disableElements(search){
    $$('.' + search).each(disableElement);
}

function enableElements(search){
    $$('.' + search).each(enableElement);
}

/********** Toolbar toggle object to manage normal/floating toolbar toggle during vertical scroll ***********/
var toolbarToggle = {
    // Properties
    header: null, // Normal toolbar
    headerOffset: null, // Normal toolbar offset - calculated once
    headerCopy: null, // Floating toolbar
    eventsAdded: false, // We're listening to scroll/resize
    compatible: !navigator.appVersion.match('MSIE 6.'), // Whether object is compatible with browser (do not support old browsers, legacy code)

    // Inits object and pushes it into work. Can be used to init/reset(update) object by current DOM.
    reset: function () {
        // Maybe we are already using floating toolbar - just remove it to update from html
        if (this.headerCopy) {
            this.headerCopy.remove();
        }
        this.createToolbar();
        this.updateForScroll();
    },

    // Creates toolbar and inits all needed properties
    createToolbar: function () {
        if (!this.compatible) {
            return;
        }

        // Extract header that we will use as toolbar
        var headers = $$('.content-header');
        for (var i = headers.length - 1; i >= 0; i--) {
            if (!headers[i].hasClassName('skip-header')) {
                this.header = headers[i];
                break;
            }
        }
        if (!this.header) {
            return;
        }

        // Calculate header offset once - for optimization
        this.headerOffset = Element.cumulativeOffset(this.header)[1];

        // Toolbar buttons
        var buttons = $$('.content-buttons')[0];
        if (buttons) {
            // Wrap buttons with 'placeholder' div - to serve as container for buttons
            Element.insert(buttons, {before: '<div class="content-buttons-placeholder"></div>'});
            buttons.placeholder = buttons.previous('.content-buttons-placeholder');
            buttons.remove();
            buttons.placeholder.appendChild(buttons);

            this.headerOffset = Element.cumulativeOffset(buttons)[1];
        }

        // Create copy of header, that will serve as floating toolbar docked to top of window
        this.headerCopy = $(document.createElement('div'));
        this.headerCopy.appendChild(this.header.cloneNode(true));
        document.body.insertBefore(this.headerCopy, document.body.lastChild)
        this.headerCopy.addClassName('content-header-floating');

        // Remove duplicated buttons and their container
        var placeholder = this.headerCopy.down('.content-buttons-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
    },

    // Checks whether object properties are ready and valid
    ready: function () {
        // Return definitely boolean value
        return (this.compatible && this.header && this.headerCopy && this.headerCopy.parentNode) ? true : false;
    },

    // Updates toolbars for current scroll - shows/hides normal and floating toolbar
    updateForScroll: function () {
        if (!this.ready()) {
            return;
        }

        // scrolling offset calculation via www.quirksmode.org
        var s;
        if (self.pageYOffset){
            s = self.pageYOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {
            s = document.documentElement.scrollTop;
        } else if (document.body) {
            s = document.body.scrollTop;
        }

        // Show floating or normal toolbar
        if (s > this.headerOffset) {
            // Page offset is more than header offset, switch to floating toolbar
            this.showFloatingToolbar();
        } else {
            // Page offset is less than header offset, switch to normal toolbar
            this.showNormalToolbar();
        }
    },

    // Shows normal toolbar (and hides floating one)
    showNormalToolbar: function () {
        if (!this.ready()) {
            return;
        }

        var buttons = $$('.content-buttons')[0];
        if (buttons && buttons.oldParent && buttons.oldParent != buttons.parentNode) {
            buttons.remove();
            if(buttons.oldBefore) {
                buttons.oldParent.insertBefore(buttons, buttons.oldBefore);
            } else {
                buttons.oldParent.appendChild(buttons);
            }
        }

        this.headerCopy.style.display = 'none';
    },

    // Shows floating toolbar (and hides normal one)
    // Notice that buttons could had changed in html by setting new inner html,
    // so our added custom properties (placeholder, oldParent) can be not present in them any more
    showFloatingToolbar: function () {
        if (!this.ready()) {
            return;
        }

        var buttons = $$('.content-buttons')[0];

        if (buttons) {
            // Remember original parent in normal toolbar to which these buttons belong
            if (!buttons.oldParent) {
                buttons.oldParent = buttons.parentNode;
                buttons.oldBefore = buttons.previous();
            }

            // Move buttons from normal to floating toolbar
            if (buttons.oldParent == buttons.parentNode) {
                // Make static dimensions for placeholder, so it's not collapsed when buttons are removed
                if (buttons.placeholder) {
                    var dimensions = buttons.placeholder.getDimensions()
                    buttons.placeholder.style.width = dimensions.width + 'px';
                    buttons.placeholder.style.height = dimensions.height + 'px';
                }

                // Move to floating
                var target = this.headerCopy.down('div');
                if (target) {
                    buttons.hide();
                    buttons.remove();

                    target.appendChild(buttons);
                    buttons.show();
                }
            }
        }

        this.headerCopy.style.display = 'block';
    },

    // Starts object on window load
    startOnLoad: function () {
        if (!this.compatible) {
            return;
        }

        if (!this.funcOnWindowLoad) {
            this.funcOnWindowLoad = this.start.bind(this);
        }
        Event.observe(window, 'load', this.funcOnWindowLoad);
    },

    // Removes object start on window load
    removeOnLoad: function () {
        if (!this.funcOnWindowLoad) {
            return;
        }
        Event.stopObserving(window, 'load', this.funcOnWindowLoad);
    },

    // Starts object by creating toolbar and enabling scroll/resize events
    start: function () {
        if (!this.compatible) {
            return;
        }

        this.reset();
        this.startListening();
    },

    // Stops object by removing toolbar and stopping listening to events
    stop: function () {
        this.stopListening();
        this.removeOnLoad();
        this.showNormalToolbar();
    },

    // Addes events on scroll/resize
    startListening: function () {
        if (this.eventsAdded) {
            return;
        }

        if (!this.funcUpdateForViewport) {
            this.funcUpdateForViewport = this.updateForScroll.bind(this);
        }

        Event.observe(window, 'scroll', this.funcUpdateForViewport);
        Event.observe(window, 'resize', this.funcUpdateForViewport);

        this.eventsAdded = true;
    },

    // Removes listening to events on resize/update
    stopListening: function () {
        if (!this.eventsAdded) {
            return;
        }
        Event.stopObserving(window, 'scroll', this.funcUpdateForViewport);
        Event.stopObserving(window, 'resize', this.funcUpdateForViewport);

        this.eventsAdded = false;
    }
}

// Deprecated since 1.4.2.0-beta1 - use toolbarToggle.reset() instead
function updateTopButtonToolbarToggle()
{
    toolbarToggle.reset();
}

// Deprecated since 1.4.2.0-beta1 - use toolbarToggle.createToolbar() instead
function createTopButtonToolbarToggle()
{
    toolbarToggle.createToolbar();
}

// Deprecated since 1.4.2.0-beta1 - use toolbarToggle.updateForScroll() instead
function floatingTopButtonToolbarToggle()
{
    toolbarToggle.updateForScroll();
}

// Start toolbar on window load
toolbarToggle.startOnLoad();


/** Cookie Reading And Writing **/

var Cookie = {
    all: function() {
        var pairs = document.cookie.split(';');
        var cookies = {};
        pairs.each(function(item, index) {
            var pair = item.strip().split('=');
            cookies[unescape(pair[0])] = unescape(pair[1]);
        });

        return cookies;
    },
    read: function(cookieName) {
        var cookies = this.all();
        if(cookies[cookieName]) {
            return cookies[cookieName];
        }
        return null;
    },
    write: function(cookieName, cookieValue, cookieLifeTime) {
        var expires = '';
        if (cookieLifeTime) {
            var date = new Date();
            date.setTime(date.getTime()+(cookieLifeTime*1000));
            expires = '; expires='+date.toGMTString();
        }
        var urlPath = '/' + BASE_URL.split('/').slice(3).join('/'); // Get relative path
        document.cookie = escape(cookieName) + "=" + escape(cookieValue) + expires + "; path=" + urlPath;
    },
    clear: function(cookieName) {
        this.write(cookieName, '', -1);
    }
};

var Fieldset = {
    cookiePrefix: 'fh-',
    applyCollapse: function(containerId) {
        //var collapsed = Cookie.read(this.cookiePrefix + containerId);
        //if (collapsed !== null) {
        //    Cookie.clear(this.cookiePrefix + containerId);
        //}
        if ($(containerId + '-state')) {
            collapsed = $(containerId + '-state').value == 1 ? 0 : 1;
        } else {
            collapsed = $(containerId + '-head').collapsed;
        }
        if (collapsed==1 || collapsed===undefined) {
           $(containerId + '-head').removeClassName('open');
           if($(containerId + '-head').up('.section-config')) {
                $(containerId + '-head').up('.section-config').removeClassName('active');
           }
           $(containerId).hide();
        } else {
           $(containerId + '-head').addClassName('open');
           if($(containerId + '-head').up('.section-config')) {
                $(containerId + '-head').up('.section-config').addClassName('active');
           }
           $(containerId).show();
        }
    },
    toggleCollapse: function(containerId, saveThroughAjax) {
        if ($(containerId + '-state')) {
            collapsed = $(containerId + '-state').value == 1 ? 0 : 1;
        } else {
            collapsed = $(containerId + '-head').collapsed;
        }
        //Cookie.read(this.cookiePrefix + containerId);
        if(collapsed==1 || collapsed===undefined) {
            //Cookie.write(this.cookiePrefix + containerId,  0, 30*24*60*60);
            if ($(containerId + '-state')) {
                $(containerId + '-state').value = 1;
            }
            $(containerId + '-head').collapsed = 0;
        } else {
            //Cookie.clear(this.cookiePrefix + containerId);
            if ($(containerId + '-state')) {
                $(containerId + '-state').value = 0;
            }
            $(containerId + '-head').collapsed = 1;
        }

        this.applyCollapse(containerId);
        if (typeof saveThroughAjax != "undefined") {
            this.saveState(saveThroughAjax, {container: containerId, value: $(containerId + '-state').value});
        }
    },
    addToPrefix: function (value) {
        this.cookiePrefix += value + '-';
    },
    saveState: function(url, parameters) {
        new Ajax.Request(url, {
            method: 'get',
            parameters: Object.toQueryString(parameters),
            loaderArea: false
        });
    }
};

var Base64 = {
    // private property
    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
     //'+/=', '-_,'
    // public method for encoding
    encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
            output = output +
            this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
            this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
        }

        return output;
    },

    // public method for decoding
    decode: function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }
        }
        output = Base64._utf8_decode(output);
        return output;
    },

    mageEncode: function(input){
        return this.encode(input).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, ',');
    },

    mageDecode: function(output){
        output = output.replace(/\-/g, '+').replace(/_/g, '/').replace(/,/g, '=');
        return this.decode(output);
    },

    idEncode: function(input){
        return this.encode(input).replace(/\+/g, ':').replace(/\//g, '_').replace(/=/g, '-');
    },

    idDecode: function(output){
        output = output.replace(/\-/g, '=').replace(/_/g, '/').replace(/\:/g, '\+');
        return this.decode(output);
    },

    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
};

/**
 * Array functions
 */

/**
 * Callback function for sort numeric values
 *
 * @param val1
 * @param val2
 */
function sortNumeric(val1, val2)
{
    return val1 - val2;
};

/* ======================================================================================= */
/* merged js/mage/adminhtml/hash.js                                                        */
/* ======================================================================================= */
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
/*
 * Caudium - An extensible World Wide Web server
 * Copyright C 2002 The Caudium Group
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 */

/*
 * base64.js - a JavaScript implementation of the base64 algorithm,
 *             (mostly) as defined in RFC 2045.
 *
 * This is a direct JavaScript reimplementation of the original C code
 * as found in the Exim mail transport agent, by Philip Hazel.
 *
 * $Id: base64.js,v 1.7 2002/07/16 17:21:23 kazmer Exp $
 *
 */


function encode_base64( what )
{
    var base64_encodetable = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    var result = "";
    var len = what.length;
    var x, y;
    var ptr = 0;

    while( len-- > 0 )
    {
        x = what.charCodeAt( ptr++ );
        result += base64_encodetable.charAt( ( x >> 2 ) & 63 );

        if( len-- <= 0 )
        {
            result += base64_encodetable.charAt( ( x << 4 ) & 63 );
            result += "==";
            break;
        }

        y = what.charCodeAt( ptr++ );
        result += base64_encodetable.charAt( ( ( x << 4 ) | ( ( y >> 4 ) & 15 ) ) & 63 );

        if ( len-- <= 0 )
        {
            result += base64_encodetable.charAt( ( y << 2 ) & 63 );
            result += "=";
            break;
        }

        x = what.charCodeAt( ptr++ );
        result += base64_encodetable.charAt( ( ( y << 2 ) | ( ( x >> 6 ) & 3 ) ) & 63 );
        result += base64_encodetable.charAt( x & 63 );

    }

    return result;
}


function decode_base64( what )
{
    var base64_decodetable = new Array (
        255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255,
        255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255,
        255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255,  62, 255, 255, 255,  63,
         52,  53,  54,  55,  56,  57,  58,  59,  60,  61, 255, 255, 255, 255, 255, 255,
        255,   0,   1,   2,   3,   4,   5,   6,   7,   8,   9,  10,  11,  12,  13,  14,
         15,  16,  17,  18,  19,  20,  21,  22,  23,  24,  25, 255, 255, 255, 255, 255,
        255,  26,  27,  28,  29,  30,  31,  32,  33,  34,  35,  36,  37,  38,  39,  40,
         41,  42,  43,  44,  45,  46,  47,  48,  49,  50,  51, 255, 255, 255, 255, 255
    );
    var result = "";
    var len = what.length;
    var x, y;
    var ptr = 0;

    while( !isNaN( x = what.charCodeAt( ptr++ ) ) )
    {
        if( x == 13 || x == 10 )
            continue;

        if( ( x > 127 ) || (( x = base64_decodetable[x] ) == 255) )
            return false;
        if( ( isNaN( y = what.charCodeAt( ptr++ ) ) ) || (( y = base64_decodetable[y] ) == 255) )
            return false;

        result += String.fromCharCode( (x << 2) | (y >> 4) );

        if( (x = what.charCodeAt( ptr++ )) == 61 )
        {
            if( (what.charCodeAt( ptr++ ) != 61) || (!isNaN(what.charCodeAt( ptr ) ) ) )
                return false;
        }
        else
        {
            if( ( x > 127 ) || (( x = base64_decodetable[x] ) == 255) )
                return false;
            result += String.fromCharCode( (y << 4) | (x >> 2) );
            if( (y = what.charCodeAt( ptr++ )) == 61 )
            {
                if( !isNaN(what.charCodeAt( ptr ) ) )
                    return false;
            }
            else
            {
                if( (y > 127) || ((y = base64_decodetable[y]) == 255) )
                    return false;
                result += String.fromCharCode( (x << 6) | y );
            }
        }
    }
    return result;
}

function wrap76( what )
{
    var result = "";
    var i;

    for(i=0; i < what.length; i+=76)
    {
        result += what.substring(i, i+76) + String.fromCharCode(13) + String.fromCharCode(10);
    }
    return result;
};

/* ======================================================================================= */
/* merged js/mage/adminhtml/accordion.js                                                   */
/* ======================================================================================= */
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
var varienAccordion = new Class.create();
varienAccordion.prototype = {
    initialize : function(containerId, activeOnlyOne){
        this.containerId = containerId;
        this.activeOnlyOne = activeOnlyOne || false;
        this.container   = $(this.containerId);
        this.items       = $$('#'+this.containerId+' dt');
        this.loader      = new varienLoader(true);

        var links = $$('#'+this.containerId+' dt a');
        for(var i in links){
            if(links[i].href){
                Event.observe(links[i],'click',this.clickItem.bind(this));
                this.items[i].dd = this.items[i].next('dd');
                this.items[i].link = links[i];
            }
        }

        this.initFromCookie();
    },
    initFromCookie : function () {
        var activeItemId, visibility;
        if (this.activeOnlyOne &&
            (activeItemId = Cookie.read(this.cookiePrefix() + 'active-item')) !== null) {
            this.hideAllItems();
            this.showItem(this.getItemById(activeItemId));
        } else if(!this.activeOnlyOne) {
            this.items.each(function(item){
                if((visibility = Cookie.read(this.cookiePrefix() + item.id)) !== null) {
                    if(visibility == 0) {
                        this.hideItem(item);
                    } else {
                        this.showItem(item);
                    }
                }
            }.bind(this));
        }
    },
    cookiePrefix: function () {
        return 'accordion-' + this.containerId + '-';
    },
    getItemById : function (itemId) {
        var result = null;

        this.items.each(function(item){
            if (item.id == itemId) {
                result = item;
                throw $break;
            }
        });

        return result;
    },
    clickItem : function(event){
        var item = Event.findElement(event, 'dt');
        if(this.activeOnlyOne){
            this.hideAllItems();
            this.showItem(item);
            Cookie.write(this.cookiePrefix() + 'active-item', item.id, 30*24*60*60);
        }
        else{
            if(this.isItemVisible(item)){
                this.hideItem(item);
                Cookie.write(this.cookiePrefix() + item.id, 0, 30*24*60*60);
            }
            else {
                this.showItem(item);
                Cookie.write(this.cookiePrefix() + item.id, 1, 30*24*60*60);
            }
        }
        Event.stop(event);
    },
    showItem : function(item){
        if(item && item.link){
            if(item.link.href){
                this.loadContent(item);
            }

            Element.addClassName(item, 'open');
            Element.addClassName(item.dd, 'open');
        }
    },
    hideItem : function(item){
        Element.removeClassName(item, 'open');
        Element.removeClassName(item.dd, 'open');
    },
    isItemVisible : function(item){
        return Element.hasClassName(item, 'open');
    },
    loadContent : function(item){
        if(item.link.href.indexOf('#') == item.link.href.length-1){
            return;
        }
        if (Element.hasClassName(item.link, 'ajax')) {
            this.loadingItem = item;
            this.loader.load(item.link.href, {updaterId : this.loadingItem.dd.id}, this.setItemContent.bind(this));
            return;
        }
        location.href = item.link.href;
    },
    setItemContent : function(content){
        if (content.isJSON) {
            return;
        }
        this.loadingItem.dd.innerHTML = content;
    },
    hideAllItems : function(){
        for(var i in this.items){
            if(this.items[i].id){
                Element.removeClassName(this.items[i], 'open');
                Element.removeClassName(this.items[i].dd, 'open');
            }
        }
    }
};

/* ======================================================================================= */
/* merged js/extjs/ext-tree.js                                                             */
/* ======================================================================================= */
/*
 * Ext JS Library 1.0.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://www.extjs.com/license
 */

Ext={};window["undefined"]=window["undefined"];Ext.apply=function(o,c,_3){if(_3){Ext.apply(o,_3);}if(o&&c&&typeof c=="object"){for(var p in c){o[p]=c[p];}}return o;};(function(){var _5=0;var ua=navigator.userAgent.toLowerCase();var _7=document.compatMode=="CSS1Compat",_8=ua.indexOf("opera")>-1,_9=(/webkit|khtml/).test(ua),_a=ua.indexOf("msie")>-1,_b=ua.indexOf("msie 7")>-1,_c=!_9&&ua.indexOf("gecko")>-1,_d=_a&&!_7,_e=(ua.indexOf("windows")!=-1||ua.indexOf("win32")!=-1),_f=(ua.indexOf("macintosh")!=-1||ua.indexOf("mac os x")!=-1),_10=window.location.href.toLowerCase().indexOf("https")===0;if(_a&&!_b){try{document.execCommand("BackgroundImageCache",false,true);}catch(e){}}Ext.apply(Ext,{isStrict:_7,isSecure:_10,isReady:false,SSL_SECURE_URL:"javascript:false",BLANK_IMAGE_URL:"http:/"+"/extjs.com/s.gif",emptyFn:function(){},applyIf:function(o,c){if(o&&c){for(var p in c){if(typeof o[p]=="undefined"){o[p]=c[p];}}}return o;},addBehaviors:function(o){if(!Ext.isReady){Ext.onReady(function(){Ext.addBehaviors(o);});return;}var _15={};for(var b in o){var _17=b.split("@");if(_17[1]){var s=_17[0];if(!_15[s]){_15[s]=Ext.select(s);}_15[s].on(_17[1],o[b]);}}_15=null;},id:function(el,_1a){_1a=_1a||"ext-gen";el=Ext.getDom(el);var id=_1a+(++_5);return el?(el.id?el.id:(el.id=id)):id;},extend:function(){var io=function(o){for(var m in o){this[m]=o[m];}};return function(sb,sp,_21){if(typeof sp=="object"){_21=sp;sp=sb;sb=function(){sp.apply(this,arguments);};}var F=function(){},sbp,spp=sp.prototype;F.prototype=spp;sbp=sb.prototype=new F();sbp.constructor=sb;sb.superclass=spp;if(spp.constructor==Object.prototype.constructor){spp.constructor=sp;}sb.override=function(o){Ext.override(sb,o);};sbp.override=io;sbp.__extcls=sb;Ext.override(sb,_21);return sb;};}(),override:function(_26,_27){if(_27){var p=_26.prototype;for(var _29 in _27){p[_29]=_27[_29];}}},namespace:function(){var a=arguments,o=null,i,j,d,rt;for(i=0;i<a.length;++i){d=a[i].split(".");rt=d[0];eval("if (typeof "+rt+" == \"undefined\"){"+rt+" = {};} o = "+rt+";");for(j=1;j<d.length;++j){o[d[j]]=o[d[j]]||{};o=o[d[j]];}}},urlEncode:function(o){if(!o){return "";}var buf=[];for(var key in o){var ov=o[key];var _34=typeof ov;if(_34=="undefined"){buf.push(encodeURIComponent(key),"=&");}else{if(_34!="function"&&_34!="object"){buf.push(encodeURIComponent(key),"=",encodeURIComponent(ov),"&");}else{if(ov instanceof Array){for(var i=0,len=ov.length;i<len;i++){buf.push(encodeURIComponent(key),"=",encodeURIComponent(ov[i]===undefined?"":ov[i]),"&");}}}}}buf.pop();return buf.join("");},urlDecode:function(_37,_38){if(!_37||!_37.length){return {};}var obj={};var _3a=_37.split("&");var _3b,_3c,_3d;for(var i=0,len=_3a.length;i<len;i++){_3b=_3a[i].split("=");_3c=decodeURIComponent(_3b[0]);_3d=decodeURIComponent(_3b[1]);if(_38!==true){if(typeof obj[_3c]=="undefined"){obj[_3c]=_3d;}else{if(typeof obj[_3c]=="string"){obj[_3c]=[obj[_3c]];obj[_3c].push(_3d);}else{obj[_3c].push(_3d);}}}else{obj[_3c]=_3d;}}return obj;},each:function(_40,fn,_42){if(typeof _40.length=="undefined"||typeof _40=="string"){_40=[_40];}for(var i=0,len=_40.length;i<len;i++){if(fn.call(_42||_40[i],_40[i],i,_40)===false){return i;}}},combine:function(){var as=arguments,l=as.length,r=[];for(var i=0;i<l;i++){var a=as[i];if(a instanceof Array){r=r.concat(a);}else{if(a.length!==undefined&&!a.substr){r=r.concat(Array.prototype.slice.call(a,0));}else{r.push(a);}}}return r;},escapeRe:function(s){return s.replace(/([.*+?^${}()|[\]\/\\])/g,"\\$1");},callback:function(cb,_4c,_4d,_4e){if(typeof cb=="function"){if(_4e){cb.defer(_4e,_4c,_4d||[]);}else{cb.apply(_4c,_4d||[]);}}},getDom:function(el){if(!el){return null;}return el.dom?el.dom:(typeof el=="string"?document.getElementById(el):el);},getCmp:function(id){return Ext.ComponentMgr.get(id);},num:function(v,_52){if(typeof v!="number"){return _52;}return v;},destroy:function(){for(var i=0,a=arguments,len=a.length;i<len;i++){var as=a[i];if(as){if(as.dom){as.removeAllListeners();as.remove();continue;}if(typeof as.purgeListeners=="function"){as.purgeListeners();}if(typeof as.destroy=="function"){as.destroy();}}}},isOpera:_8,isSafari:_9,isIE:_a,isIE7:_b,isGecko:_c,isBorderBox:_d,isWindows:_e,isMac:_f,useShims:((_a&&!_b)||(_c&&_f))});})();Ext.namespace("Ext","Ext.util","Ext.grid","Ext.dd","Ext.tree","Ext.data","Ext.form","Ext.menu","Ext.state","Ext.lib","Ext.layout");Ext.apply(Function.prototype,{createCallback:function(){var _57=arguments;var _58=this;return function(){return _58.apply(window,_57);};},createDelegate:function(obj,_5a,_5b){var _5c=this;return function(){var _5d=_5a||arguments;if(_5b===true){_5d=Array.prototype.slice.call(arguments,0);_5d=_5d.concat(_5a);}else{if(typeof _5b=="number"){_5d=Array.prototype.slice.call(arguments,0);var _5e=[_5b,0].concat(_5a);Array.prototype.splice.apply(_5d,_5e);}}return _5c.apply(obj||window,_5d);};},defer:function(_5f,obj,_61,_62){var fn=this.createDelegate(obj,_61,_62);if(_5f){return setTimeout(fn,_5f);}fn();return 0;},createSequence:function(fcn,_65){if(typeof fcn!="function"){return this;}var _66=this;return function(){var _67=_66.apply(this||window,arguments);fcn.apply(_65||this||window,arguments);return _67;};},createInterceptor:function(fcn,_69){if(typeof fcn!="function"){return this;}var _6a=this;return function(){fcn.target=this;fcn.method=_6a;if(fcn.apply(_69||this||window,arguments)===false){return;}return _6a.apply(this||window,arguments);};}});Ext.applyIf(String,{escape:function(_6b){return _6b.replace(/('|\\)/g,"\\$1");},leftPad:function(val,_6d,ch){var _6f=new String(val);if(ch==null){ch=" ";}while(_6f.length<_6d){_6f=ch+_6f;}return _6f;},format:function(_70){var _71=Array.prototype.slice.call(arguments,1);return _70.replace(/\{(\d+)\}/g,function(m,i){return _71[i];});}});String.prototype.toggle=function(_74,_75){return this==_74?_75:_74;};Ext.applyIf(Number.prototype,{constrain:function(min,max){return Math.min(Math.max(this,min),max);}});Ext.applyIf(Array.prototype,{indexOf:function(o){for(var i=0,len=this.length;i<len;i++){if(this[i]==o){return i;}}return -1;},remove:function(o){var _7c=this.indexOf(o);if(_7c!=-1){this.splice(_7c,1);}}});Date.prototype.getElapsed=function(_7d){return Math.abs((_7d||new Date()).getTime()-this.getTime());};



(function(){var _1;Ext.lib.Dom={getViewWidth:function(_2){return _2?this.getDocumentWidth():this.getViewportWidth();},getViewHeight:function(_3){return _3?this.getDocumentHeight():this.getViewportHeight();},getDocumentHeight:function(){var _4=(document.compatMode!="CSS1Compat")?document.body.scrollHeight:document.documentElement.scrollHeight;return Math.max(_4,this.getViewportHeight());},getDocumentWidth:function(){var _5=(document.compatMode!="CSS1Compat")?document.body.scrollWidth:document.documentElement.scrollWidth;return Math.max(_5,this.getViewportWidth());},getViewportHeight:function(){var _6=self.innerHeight;var _7=document.compatMode;if((_7||Ext.isIE)&&!Ext.isOpera){_6=(_7=="CSS1Compat")?document.documentElement.clientHeight:document.body.clientHeight;}return _6;},getViewportWidth:function(){var _8=self.innerWidth;var _9=document.compatMode;if(_9||Ext.isIE){_8=(_9=="CSS1Compat")?document.documentElement.clientWidth:document.body.clientWidth;}return _8;},isAncestor:function(p,c){p=Ext.getDom(p);c=Ext.getDom(c);if(!p||!c){return false;}if(p.contains&&!Ext.isSafari){return p.contains(c);}else{if(p.compareDocumentPosition){return !!(p.compareDocumentPosition(c)&16);}else{var _c=c.parentNode;while(_c){if(_c==p){return true;}else{if(!_c.tagName||_c.tagName.toUpperCase()=="HTML"){return false;}}_c=_c.parentNode;}return false;}}},getRegion:function(el){return Ext.lib.Region.getRegion(el);},getY:function(el){return this.getXY(el)[1];},getX:function(el){return this.getXY(el)[0];},getXY:function(el){var p,pe,b,_14,bd=document.body;el=Ext.getDom(el);if(el.getBoundingClientRect){b=el.getBoundingClientRect();_14=fly(document).getScroll();return [b.left+_14.left,b.top+_14.top];}else{var x=el.offsetLeft,y=el.offsetTop;p=el.offsetParent;var _18=false;if(p!=el){while(p){x+=p.offsetLeft;y+=p.offsetTop;if(Ext.isSafari&&!_18&&fly(p).getStyle("position")=="absolute"){_18=true;}if(Ext.isGecko){pe=fly(p);var bt=parseInt(pe.getStyle("borderTopWidth"),10)||0;var bl=parseInt(pe.getStyle("borderLeftWidth"),10)||0;x+=bl;y+=bt;if(p!=el&&pe.getStyle("overflow")!="visible"){x+=bl;y+=bt;}}p=p.offsetParent;}}if(Ext.isSafari&&(_18||fly(el).getStyle("position")=="absolute")){x-=bd.offsetLeft;y-=bd.offsetTop;}}p=el.parentNode;while(p&&p!=bd){if(!Ext.isOpera||(Ext.isOpera&&p.tagName!="TR"&&fly(p).getStyle("display")!="inline")){x-=p.scrollLeft;y-=p.scrollTop;}p=p.parentNode;}return [x,y];},setXY:function(el,xy){el=Ext.fly(el,"_setXY");el.position();var pts=el.translatePoints(xy);if(xy[0]!==false){el.dom.style.left=pts.left+"px";}if(xy[1]!==false){el.dom.style.top=pts.top+"px";}},setX:function(el,x){this.setXY(el,[x,false]);},setY:function(el,y){this.setXY(el,[false,y]);}};Ext.lib.Event={getPageX:function(e){return Event.pointerX(e.browserEvent||e);},getPageY:function(e){return Event.pointerY(e.browserEvent||e);},getXY:function(e){e=e.browserEvent||e;return [Event.pointerX(e),Event.pointerY(e)];},getTarget:function(e){return Event.element(e.browserEvent||e);},resolveTextNode:function(_26){if(_26&&3==_26.nodeType){return _26.parentNode;}else{return _26;}},getRelatedTarget:function(ev){ev=ev.browserEvent||ev;var t=ev.relatedTarget;if(!t){if(ev.type=="mouseout"){t=ev.toElement;}else{if(ev.type=="mouseover"){t=ev.fromElement;}}}return this.resolveTextNode(t);},on:function(el,_2a,fn){Event.observe(el,_2a,fn,false);},un:function(el,_2d,fn){Event.stopObserving(el,_2d,fn,false);},purgeElement:function(el){},preventDefault:function(e){e=e.browserEvent||e;if(e.preventDefault){e.preventDefault();}else{e.returnValue=false;}},stopPropagation:function(e){e=e.browserEvent||e;if(e.stopPropagation){e.stopPropagation();}else{e.cancelBubble=true;}},stopEvent:function(e){Event.stop(e.browserEvent||e);},onAvailable:function(el,fn,_35,_36){var _37=new Date(),iid;var f=function(){if(_37.getElapsed()>10000){clearInterval(iid);}var el=document.getElementById(id);if(el){clearInterval(iid);fn.call(_35||window,el);}};iid=setInterval(f,50);}};Ext.lib.Ajax=function(){var _3b=function(cb){return cb.success?function(xhr){cb.success.call(cb.scope||window,{responseText:xhr.responseText,responseXML:xhr.responseXML,argument:cb.argument});}:Ext.emptyFn;};var _3e=function(cb){return cb.failure?function(xhr){cb.failure.call(cb.scope||window,{responseText:xhr.responseText,responseXML:xhr.responseXML,argument:cb.argument});}:Ext.emptyFn;};return {request:function(_41,uri,cb,_44){new Ajax.Request(uri,{method:_41,parameters:_44||"",timeout:cb.timeout,onSuccess:_3b(cb),onFailure:_3e(cb)});},formRequest:function(_45,uri,cb,_48,_49,_4a){new Ajax.Request(uri,{method:Ext.getDom(_45).method||"POST",parameters:Form.serialize(_45)+(_48?"&"+_48:""),timeout:cb.timeout,onSuccess:_3b(cb),onFailure:_3e(cb)});},isCallInProgress:function(_4b){return false;},abort:function(_4c){return false;},serializeForm:function(_4d){return Form.serialize(_4d.dom||_4d,true);}};}();Ext.lib.Anim=function(){var _4e={easeOut:function(pos){return 1-Math.pow(1-pos,2);},easeIn:function(pos){return 1-Math.pow(1-pos,2);}};var _51=function(cb,_53){return {stop:function(_54){this.effect.cancel();},isAnimated:function(){return this.effect.state=="running";},proxyCallback:function(){Ext.callback(cb,_53);}};};return {scroll:function(el,_56,_57,_58,cb,_5a){var _5b=_51(cb,_5a);el=Ext.getDom(el);el.scrollLeft=_56.to[0];el.scrollTop=_56.to[1];_5b.proxyCallback();return _5b;},motion:function(el,_5d,_5e,_5f,cb,_61){return this.run(el,_5d,_5e,_5f,cb,_61);},color:function(el,_63,_64,_65,cb,_67){return this.run(el,_63,_64,_65,cb,_67);},run:function(el,_69,_6a,_6b,cb,_6d,_6e){var o={};for(var k in _69){switch(k){case "points":var by,pts,e=Ext.fly(el,"_animrun");e.position();if(by=_69.points.by){var xy=e.getXY();pts=e.translatePoints([xy[0]+by[0],xy[1]+by[1]]);}else{pts=e.translatePoints(_69.points.to);}o.left=pts.left+"px";o.top=pts.top+"px";break;case "width":o.width=_69.width.to+"px";break;case "height":o.height=_69.height.to+"px";break;case "opacity":o.opacity=String(_69.opacity.to);break;default:o[k]=String(_69[k].to);break;}}var _75=_51(cb,_6d);_75.effect=new Effect.Morph(Ext.id(el),{duration:_6a,afterFinish:_75.proxyCallback,transition:_4e[_6b]||Effect.Transitions.linear,style:o});return _75;}};}();function fly(el){if(!_1){_1=new Ext.Element.Flyweight();}_1.dom=el;return _1;}Ext.lib.Region=function(t,r,b,l){this.top=t;this[1]=t;this.right=r;this.bottom=b;this.left=l;this[0]=l;};Ext.lib.Region.prototype={contains:function(_7b){return (_7b.left>=this.left&&_7b.right<=this.right&&_7b.top>=this.top&&_7b.bottom<=this.bottom);},getArea:function(){return ((this.bottom-this.top)*(this.right-this.left));},intersect:function(_7c){var t=Math.max(this.top,_7c.top);var r=Math.min(this.right,_7c.right);var b=Math.min(this.bottom,_7c.bottom);var l=Math.max(this.left,_7c.left);if(b>=t&&r>=l){return new Ext.lib.Region(t,r,b,l);}else{return null;}},union:function(_81){var t=Math.min(this.top,_81.top);var r=Math.max(this.right,_81.right);var b=Math.max(this.bottom,_81.bottom);var l=Math.min(this.left,_81.left);return new Ext.lib.Region(t,r,b,l);},adjust:function(t,l,b,r){this.top+=t;this.left+=l;this.right+=r;this.bottom+=b;return this;}};Ext.lib.Region.getRegion=function(el){var p=Ext.lib.Dom.getXY(el);var t=p[1];var r=p[0]+el.offsetWidth;var b=p[1]+el.offsetHeight;var l=p[0];return new Ext.lib.Region(t,r,b,l);};Ext.lib.Point=function(x,y){if(x instanceof Array){y=x[1];x=x[0];}this.x=this.right=this.left=this[0]=x;this.y=this.top=this.bottom=this[1]=y;};Ext.lib.Point.prototype=new Ext.lib.Region();if(Ext.isIE){Event.observe(window,"unload",function(){var p=Function.prototype;delete p.createSequence;delete p.defer;delete p.createDelegate;delete p.createCallback;delete p.createInterceptor;});}})();



Ext.DomHelper=function(){var _1=null;var _2=/^(?:br|frame|hr|img|input|link|meta|range|spacer|wbr|area|param|col)$/i;var _3=function(o){if(typeof o=="string"){return o;}var b="";if(!o.tag){o.tag="div";}b+="<"+o.tag;for(var _6 in o){if(_6=="tag"||_6=="children"||_6=="cn"||_6=="html"||typeof o[_6]=="function"){continue;}if(_6=="style"){var s=o["style"];if(typeof s=="function"){s=s.call();}if(typeof s=="string"){b+=" style=\""+s+"\"";}else{if(typeof s=="object"){b+=" style=\"";for(var _8 in s){if(typeof s[_8]!="function"){b+=_8+":"+s[_8]+";";}}b+="\"";}}}else{if(_6=="cls"){b+=" class=\""+o["cls"]+"\"";}else{if(_6=="htmlFor"){b+=" for=\""+o["htmlFor"]+"\"";}else{b+=" "+_6+"=\""+o[_6]+"\"";}}}}if(_2.test(o.tag)){b+="/>";}else{b+=">";var cn=o.children||o.cn;if(cn){if(cn instanceof Array){for(var i=0,_b=cn.length;i<_b;i++){b+=_3(cn[i],b);}}else{b+=_3(cn,b);}}if(o.html){b+=o.html;}b+="</"+o.tag+">";}return b;};var _c=function(o,_e){var el=document.createElement(o.tag);var _10=el.setAttribute?true:false;for(var _11 in o){if(_11=="tag"||_11=="children"||_11=="cn"||_11=="html"||_11=="style"||typeof o[_11]=="function"){continue;}if(_11=="cls"){el.className=o["cls"];}else{if(_10){el.setAttribute(_11,o[_11]);}else{el[_11]=o[_11];}}}Ext.DomHelper.applyStyles(el,o.style);var cn=o.children||o.cn;if(cn){if(cn instanceof Array){for(var i=0,len=cn.length;i<len;i++){_c(cn[i],el);}}else{_c(cn,el);}}if(o.html){el.innerHTML=o.html;}if(_e){_e.appendChild(el);}return el;};var _15=function(_16,s,h,e){_1.innerHTML=[s,h,e].join("");var i=-1,el=_1;while(++i<_16){el=el.firstChild;}return el;};var ts="<table>",te="</table>",tbs=ts+"<tbody>",tbe="</tbody>"+te,trs=tbs+"<tr>",tre="</tr>"+tbe;var _22=function(tag,_24,el,_26){if(!_1){_1=document.createElement("div");}var _27;var _28=null;if(tag=="td"){if(_24=="afterbegin"||_24=="beforeend"){return;}if(_24=="beforebegin"){_28=el;el=el.parentNode;}else{_28=el.nextSibling;el=el.parentNode;}_27=_15(4,trs,_26,tre);}else{if(tag=="tr"){if(_24=="beforebegin"){_28=el;el=el.parentNode;_27=_15(3,tbs,_26,tbe);}else{if(_24=="afterend"){_28=el.nextSibling;el=el.parentNode;_27=_15(3,tbs,_26,tbe);}else{if(_24=="afterbegin"){_28=el.firstChild;}_27=_15(4,trs,_26,tre);}}}else{if(tag=="tbody"){if(_24=="beforebegin"){_28=el;el=el.parentNode;_27=_15(2,ts,_26,te);}else{if(_24=="afterend"){_28=el.nextSibling;el=el.parentNode;_27=_15(2,ts,_26,te);}else{if(_24=="afterbegin"){_28=el.firstChild;}_27=_15(3,tbs,_26,tbe);}}}else{if(_24=="beforebegin"||_24=="afterend"){return;}if(_24=="afterbegin"){_28=el.firstChild;}_27=_15(2,ts,_26,te);}}}el.insertBefore(_27,_28);return _27;};return {useDom:false,markup:function(o){return _3(o);},applyStyles:function(el,_2b){if(_2b){el=Ext.fly(el);if(typeof _2b=="string"){var re=/\s?([a-z\-]*)\:\s?([^;]*);?/gi;var _2d;while((_2d=re.exec(_2b))!=null){el.setStyle(_2d[1],_2d[2]);}}else{if(typeof _2b=="object"){for(var _2e in _2b){el.setStyle(_2e,_2b[_2e]);}}else{if(typeof _2b=="function"){Ext.DomHelper.applyStyles(el,_2b.call());}}}}},insertHtml:function(_2f,el,_31){_2f=_2f.toLowerCase();if(el.insertAdjacentHTML){var tag=el.tagName.toLowerCase();if(tag=="table"||tag=="tbody"||tag=="tr"||tag=="td"){var rs;if(rs=_22(tag,_2f,el,_31)){return rs;}}switch(_2f){case "beforebegin":el.insertAdjacentHTML(_2f,_31);return el.previousSibling;case "afterbegin":el.insertAdjacentHTML(_2f,_31);return el.firstChild;case "beforeend":el.insertAdjacentHTML(_2f,_31);return el.lastChild;case "afterend":el.insertAdjacentHTML(_2f,_31);return el.nextSibling;}throw "Illegal insertion point -> \""+_2f+"\"";}var _34=el.ownerDocument.createRange();var _35;switch(_2f){case "beforebegin":_34.setStartBefore(el);_35=_34.createContextualFragment(_31);el.parentNode.insertBefore(_35,el);return el.previousSibling;case "afterbegin":if(el.firstChild){_34.setStartBefore(el.firstChild);_35=_34.createContextualFragment(_31);el.insertBefore(_35,el.firstChild);return el.firstChild;}else{el.innerHTML=_31;return el.firstChild;}case "beforeend":if(el.lastChild){_34.setStartAfter(el.lastChild);_35=_34.createContextualFragment(_31);el.appendChild(_35);return el.lastChild;}else{el.innerHTML=_31;return el.lastChild;}case "afterend":_34.setStartAfter(el);_35=_34.createContextualFragment(_31);el.parentNode.insertBefore(_35,el.nextSibling);return el.nextSibling;}throw "Illegal insertion point -> \""+_2f+"\"";},insertBefore:function(el,o,_38){return this.doInsert(el,o,_38,"beforeBegin");},insertAfter:function(el,o,_3b){return this.doInsert(el,o,_3b,"afterEnd","nextSibling");},insertFirst:function(el,o,_3e){return this.doInsert(el,o,_3e,"afterBegin");},doInsert:function(el,o,_41,pos,_43){el=Ext.getDom(el);var _44;if(this.useDom){_44=_c(o,null);el.parentNode.insertBefore(_44,_43?el[_43]:el);}else{var _45=_3(o);_44=this.insertHtml(pos,el,_45);}return _41?Ext.get(_44,true):_44;},append:function(el,o,_48){el=Ext.getDom(el);var _49;if(this.useDom){_49=_c(o,null);el.appendChild(_49);}else{var _4a=_3(o);_49=this.insertHtml("beforeEnd",el,_4a);}return _48?Ext.get(_49,true):_49;},overwrite:function(el,o,_4d){el=Ext.getDom(el);el.innerHTML=_3(o);return _4d?Ext.get(el.firstChild,true):el.firstChild;},createTemplate:function(o){var _4f=_3(o);return new Ext.Template(_4f);}};}();



Ext.Template=function(_1){if(_1 instanceof Array){_1=_1.join("");}else{if(arguments.length>1){_1=Array.prototype.join.call(arguments,"");}}this.html=_1;};Ext.Template.prototype={applyTemplate:function(_2){if(this.compiled){return this.compiled(_2);}var _3=this.disableFormats!==true;var fm=Ext.util.Format,_5=this;var fn=function(m,_8,_9,_a){if(_9&&_3){if(_9.substr(0,5)=="this."){return _5.call(_9.substr(5),_2[_8]);}else{if(_a){var re=/^\s*['"](.*)["']\s*$/;_a=_a.split(",");for(var i=0,_d=_a.length;i<_d;i++){_a[i]=_a[i].replace(re,"$1");}_a=[_2[_8]].concat(_a);}else{_a=[_2[_8]];}return fm[_9].apply(fm,_a);}}else{return _2[_8]!==undefined?_2[_8]:"";}};return this.html.replace(this.re,fn);},set:function(_e,_f){this.html=_e;this.compiled=null;if(_f){this.compile();}return this;},disableFormats:false,re:/\{([\w-]+)(?:\:([\w\.]*)(?:\((.*?)?\))?)?\}/g,compile:function(){var fm=Ext.util.Format;var _11=this.disableFormats!==true;var sep=Ext.isGecko?"+":",";var fn=function(m,_15,_16,_17){if(_16&&_11){_17=_17?","+_17:"";if(_16.substr(0,5)!="this."){_16="fm."+_16+"(";}else{_16="this.call(\""+_16.substr(5)+"\", ";_17="";}}else{_17="",_16="(values['"+_15+"'] == undefined ? '' : ";}return "'"+sep+_16+"values['"+_15+"']"+_17+")"+sep+"'";};var _18;if(Ext.isGecko){_18="this.compiled = function(values){ return '"+this.html.replace(/(\r\n|\n)/g,"\\n").replace("'","\\'").replace(this.re,fn)+"';};";}else{_18=["this.compiled = function(values){ return ['"];_18.push(this.html.replace(/(\r\n|\n)/g,"\\n").replace("'","\\'").replace(this.re,fn));_18.push("'].join('');};");_18=_18.join("");}eval(_18);return this;},call:function(_19,_1a){return this[_19](_1a);},insertFirst:function(el,_1c,_1d){return this.doInsert("afterBegin",el,_1c,_1d);},insertBefore:function(el,_1f,_20){return this.doInsert("beforeBegin",el,_1f,_20);},insertAfter:function(el,_22,_23){return this.doInsert("afterEnd",el,_22,_23);},append:function(el,_25,_26){return this.doInsert("beforeEnd",el,_25,_26);},doInsert:function(_27,el,_29,_2a){el=Ext.getDom(el);var _2b=Ext.DomHelper.insertHtml(_27,el,this.applyTemplate(_29));return _2a?Ext.get(_2b,true):_2b;},overwrite:function(el,_2d,_2e){el=Ext.getDom(el);el.innerHTML=this.applyTemplate(_2d);return _2e?Ext.get(el.firstChild,true):el.firstChild;}};Ext.Template.prototype.apply=Ext.Template.prototype.applyTemplate;Ext.DomHelper.Template=Ext.Template;Ext.Template.from=function(el){el=Ext.getDom(el);return new Ext.Template(el.value||el.innerHTML);};Ext.MasterTemplate=function(){Ext.MasterTemplate.superclass.constructor.apply(this,arguments);this.originalHtml=this.html;var st={};var m,re=this.subTemplateRe;re.lastIndex=0;var _33=0;while(m=re.exec(this.html)){var _34=m[1],_35=m[2];st[_33]={name:_34,index:_33,buffer:[],tpl:new Ext.Template(_35)};if(_34){st[_34]=st[_33];}st[_33].tpl.compile();st[_33].tpl.call=this.call.createDelegate(this);_33++;}this.subCount=_33;this.subs=st;};Ext.extend(Ext.MasterTemplate,Ext.Template,{subTemplateRe:/<tpl(?:\sname="([\w-]+)")?>((?:.|\n)*?)<\/tpl>/gi,add:function(_36,_37){if(arguments.length==1){_37=arguments[0];_36=0;}var s=this.subs[_36];s.buffer[s.buffer.length]=s.tpl.apply(_37);return this;},fill:function(_39,_3a,_3b){var a=arguments;if(a.length==1||(a.length==2&&typeof a[1]=="boolean")){_3a=a[0];_39=0;_3b=a[1];}if(_3b){this.reset();}for(var i=0,len=_3a.length;i<len;i++){this.add(_39,_3a[i]);}return this;},reset:function(){var s=this.subs;for(var i=0;i<this.subCount;i++){s[i].buffer=[];}return this;},applyTemplate:function(_41){var s=this.subs;var _43=-1;this.html=this.originalHtml.replace(this.subTemplateRe,function(m,_45){return s[++_43].buffer.join("");});return Ext.MasterTemplate.superclass.applyTemplate.call(this,_41);},apply:function(){return this.applyTemplate.apply(this,arguments);},compile:function(){return this;}});Ext.MasterTemplate.prototype.addAll=Ext.MasterTemplate.prototype.fill;Ext.MasterTemplate.from=function(el){el=Ext.getDom(el);return new Ext.MasterTemplate(el.value||el.innerHTML);};



Ext.DomQuery=function(){var _1={},_2={},_3={};var _4=/\S/;var _5=/^\s+|\s+$/g;var _6=/\{(\d+)\}/g;var _7=/^(\s?[\/>]\s?|\s|$)/;var _8=/^(#)?([\w-\*]+)/;function child(p,_a){var i=0;var n=p.firstChild;while(n){if(n.nodeType==1){if(++i==_a){return n;}}n=n.nextSibling;}return null;}function next(n){while((n=n.nextSibling)&&n.nodeType!=1){}return n;}function prev(n){while((n=n.previousSibling)&&n.nodeType!=1){}return n;}function clean(d){var n=d.firstChild,ni=-1;while(n){var nx=n.nextSibling;if(n.nodeType==3&&!_4.test(n.nodeValue)){d.removeChild(n);}else{n.nodeIndex=++ni;}n=nx;}return this;}function byClassName(c,a,v,re,cn){if(!v){return c;}var r=[];for(var i=0,ci;ci=c[i];i++){cn=ci.className;if(cn&&(" "+cn+" ").indexOf(v)!=-1){r[r.length]=ci;}}return r;}function attrValue(n,_1c){if(!n.tagName&&typeof n.length!="undefined"){n=n[0];}if(!n){return null;}if(_1c=="for"){return n.htmlFor;}if(_1c=="class"||_1c=="className"){return n.className;}return n.getAttribute(_1c)||n[_1c];}function getNodes(ns,_1e,_1f){var _20=[],cs;if(!ns){return _20;}_1e=_1e?_1e.replace(_5,""):"";_1f=_1f||"*";if(typeof ns.getElementsByTagName!="undefined"){ns=[ns];}if(_1e!="/"&&_1e!=">"){for(var i=0,ni;ni=ns[i];i++){cs=ni.getElementsByTagName(_1f);for(var j=0,ci;ci=cs[j];j++){_20[_20.length]=ci;}}}else{for(var i=0,ni;ni=ns[i];i++){var cn=ni.getElementsByTagName(_1f);for(var j=0,cj;cj=cn[j];j++){if(cj.parentNode==ni){_20[_20.length]=cj;}}}}return _20;}function concat(a,b){if(b.slice){return a.concat(b);}for(var i=0,l=b.length;i<l;i++){a[a.length]=b[i];}return a;}function byTag(cs,_2d){if(cs.tagName||cs==document){cs=[cs];}if(!_2d){return cs;}var r=[];_2d=_2d.toLowerCase();for(var i=0,ci;ci=cs[i];i++){if(ci.nodeType==1&&ci.tagName.toLowerCase()==_2d){r[r.length]=ci;}}return r;}function byId(cs,_32,id){if(cs.tagName||cs==document){cs=[cs];}if(!id){return cs;}var r=[];for(var i=0,ci;ci=cs[i];i++){if(ci&&ci.id==id){r[r.length]=ci;return r;}}return r;}function byAttribute(cs,_38,_39,op,_3b){var r=[],st=_3b=="{";var f=Ext.DomQuery.operators[op];for(var i=0;ci=cs[i];i++){var a;if(st){a=Ext.DomQuery.getStyle(ci,_38);}else{if(_38=="class"||_38=="className"){a=ci.className;}else{if(_38=="for"){a=ci.htmlFor;}else{if(_38=="href"){a=ci.getAttribute("href",2);}else{a=ci.getAttribute(_38);}}}}if((f&&f(a,_39))||(!f&&a)){r[r.length]=ci;}}return r;}function byPseudo(cs,_42,_43){return Ext.DomQuery.pseudos[_42](cs,_43);}var _44=window.ActiveXObject?true:false;var key=30803;function nodupIEXml(cs){var d=++key;cs[0].setAttribute("_nodup",d);var r=[cs[0]];for(var i=1,len=cs.length;i<len;i++){var c=cs[i];if(!c.getAttribute("_nodup")!=d){c.setAttribute("_nodup",d);r[r.length]=c;}}for(var i=0,len=cs.length;i<len;i++){cs[i].removeAttribute("_nodup");}return r;}function nodup(cs){if(!cs){return [];}var len=cs.length,c,i,r=cs,cj;if(!len||typeof cs.nodeType!="undefined"||len==1){return cs;}if(_44&&typeof cs[0].selectSingleNode!="undefined"){return nodupIEXml(cs);}var d=++key;cs[0]._nodup=d;for(i=1;c=cs[i];i++){if(c._nodup!=d){c._nodup=d;}else{r=[];for(var j=0;j<i;j++){r[r.length]=cs[j];}for(j=i+1;cj=cs[j];j++){if(cj._nodup!=d){cj._nodup=d;r[r.length]=cj;}}return r;}}return r;}function quickDiffIEXml(c1,c2){var d=++key;for(var i=0,len=c1.length;i<len;i++){c1[i].setAttribute("_qdiff",d);}var r=[];for(var i=0,len=c2.length;i<len;i++){if(c2[i].getAttribute("_qdiff")!=d){r[r.length]=c2[i];}}for(var i=0,len=c1.length;i<len;i++){c1[i].removeAttribute("_qdiff");}return r;}function quickDiff(c1,c2){var _5c=c1.length;if(!_5c){return c2;}if(_44&&c1[0].selectSingleNode){return quickDiffIEXml(c1,c2);}var d=++key;for(var i=0;i<_5c;i++){c1[i]._qdiff=d;}var r=[];for(var i=0,len=c2.length;i<len;i++){if(c2[i]._qdiff!=d){r[r.length]=c2[i];}}return r;}function quickId(ns,_62,_63,id){if(ns==_63){var d=_63.ownerDocument||_63;return d.getElementById(id);}ns=getNodes(ns,_62,"*");return byId(ns,null,id);}return {getStyle:function(el,_67){return Ext.fly(el).getStyle(_67);},compile:function(_68,_69){while(_68.substr(0,1)=="/"){_68=_68.substr(1);}_69=_69||"select";var fn=["var f = function(root){\n var mode; var n = root || document;\n"];var q=_68,_6c,lq;var tk=Ext.DomQuery.matchers;var _6f=tk.length;var mm;while(q&&lq!=q){lq=q;var tm=q.match(_8);if(_69=="select"){if(tm){if(tm[1]=="#"){fn[fn.length]="n = quickId(n, mode, root, \""+tm[2]+"\");";}else{fn[fn.length]="n = getNodes(n, mode, \""+tm[2]+"\");";}q=q.replace(tm[0],"");}else{if(q.substr(0,1)!="@"){fn[fn.length]="n = getNodes(n, mode, \"*\");";}}}else{if(tm){if(tm[1]=="#"){fn[fn.length]="n = byId(n, null, \""+tm[2]+"\");";}else{fn[fn.length]="n = byTag(n, \""+tm[2]+"\");";}q=q.replace(tm[0],"");}}while(!(mm=q.match(_7))){var _72=false;for(var j=0;j<_6f;j++){var t=tk[j];var m=q.match(t.re);if(m){fn[fn.length]=t.select.replace(_6,function(x,i){return m[i];});q=q.replace(m[0],"");_72=true;break;}}if(!_72){throw "Error parsing selector, parsing failed at \""+q+"\"";}}if(mm[1]){fn[fn.length]="mode=\""+mm[1]+"\";";q=q.replace(mm[1],"");}}fn[fn.length]="return nodup(n);\n}";eval(fn.join(""));return f;},select:function(_78,_79,_7a){if(!_79||_79==document){_79=document;}if(typeof _79=="string"){_79=document.getElementById(_79);}var _7b=_78.split(",");var _7c=[];for(var i=0,len=_7b.length;i<len;i++){var p=_7b[i].replace(_5,"");if(!_1[p]){_1[p]=Ext.DomQuery.compile(p);if(!_1[p]){throw p+" is not a valid selector";}}var _80=_1[p](_79);if(_80&&_80!=document){_7c=_7c.concat(_80);}}return _7c;},selectNode:function(_81,_82){return Ext.DomQuery.select(_81,_82)[0];},selectValue:function(_83,_84,_85){_83=_83.replace(_5,"");if(!_3[_83]){_3[_83]=Ext.DomQuery.compile(_83,"select");}var n=_3[_83](_84);n=n[0]?n[0]:n;var v=(n&&n.firstChild?n.firstChild.nodeValue:null);return (v===null?_85:v);},selectNumber:function(_88,_89,_8a){var v=Ext.DomQuery.selectValue(_88,_89,_8a||0);return parseFloat(v);},is:function(el,ss){if(typeof el=="string"){el=document.getElementById(el);}var _8e=(el instanceof Array);var _8f=Ext.DomQuery.filter(_8e?el:[el],ss);return _8e?(_8f.length==el.length):(_8f.length>0);},filter:function(els,ss,_92){ss=ss.replace(_5,"");if(!_2[ss]){_2[ss]=Ext.DomQuery.compile(ss,"simple");}var _93=_2[ss](els);return _92?quickDiff(_93,els):_93;},matchers:[{re:/^\.([\w-]+)/,select:"n = byClassName(n, null, \" {1} \");"},{re:/^\:([\w-]+)(?:\(((?:[^\s>\/]*|.*?))\))?/,select:"n = byPseudo(n, \"{1}\", \"{2}\");"},{re:/^(?:([\[\{])(?:@)?([\w-]+)\s?(?:(=|.=)\s?['"]?(.*?)["']?)?[\]\}])/,select:"n = byAttribute(n, \"{2}\", \"{4}\", \"{3}\", \"{1}\");"},{re:/^#([\w-]+)/,select:"n = byId(n, null, \"{1}\");"},{re:/^@([\w-]+)/,select:"return {firstChild:{nodeValue:attrValue(n, \"{1}\")}};"}],operators:{"=":function(a,v){return a==v;},"!=":function(a,v){return a!=v;},"^=":function(a,v){return a&&a.substr(0,v.length)==v;},"$=":function(a,v){return a&&a.substr(a.length-v.length)==v;},"*=":function(a,v){return a&&a.indexOf(v)!==-1;},"%=":function(a,v){return (a%v)==0;}},pseudos:{"first-child":function(c){var r=[],n;for(var i=0,ci;ci=n=c[i];i++){while((n=n.previousSibling)&&n.nodeType!=1){}if(!n){r[r.length]=ci;}}return r;},"last-child":function(c){var r=[];for(var i=0,ci;ci=n=c[i];i++){while((n=n.nextSibling)&&n.nodeType!=1){}if(!n){r[r.length]=ci;}}return r;},"nth-child":function(c,a){var r=[];if(a!="odd"&&a!="even"){for(var i=0,ci;ci=c[i];i++){var m=child(ci.parentNode,a);if(m==ci){r[r.length]=m;}}return r;}var p;for(var i=0,l=c.length;i<l;i++){var cp=c[i].parentNode;if(cp!=p){clean(cp);p=cp;}}for(var i=0,ci;ci=c[i];i++){var m=false;if(a=="odd"){m=((ci.nodeIndex+1)%2==1);}else{if(a=="even"){m=((ci.nodeIndex+1)%2==0);}}if(m){r[r.length]=ci;}}return r;},"only-child":function(c){var r=[];for(var i=0,ci;ci=c[i];i++){if(!prev(ci)&&!next(ci)){r[r.length]=ci;}}return r;},"empty":function(c){var r=[];for(var i=0,ci;ci=c[i];i++){var cns=ci.childNodes,j=0,cn,_bd=true;while(cn=cns[j]){++j;if(cn.nodeType==1||cn.nodeType==3){_bd=false;break;}}if(_bd){r[r.length]=ci;}}return r;},"contains":function(c,v){var r=[];for(var i=0,ci;ci=c[i];i++){if(ci.innerHTML.indexOf(v)!==-1){r[r.length]=ci;}}return r;},"nodeValue":function(c,v){var r=[];for(var i=0,ci;ci=c[i];i++){if(ci.firstChild&&ci.firstChild.nodeValue==v){r[r.length]=ci;}}return r;},"checked":function(c){var r=[];for(var i=0,ci;ci=c[i];i++){if(ci.checked==true){r[r.length]=ci;}}return r;},"not":function(c,ss){return Ext.DomQuery.filter(c,ss,true);},"odd":function(c){return this["nth-child"](c,"odd");},"even":function(c){return this["nth-child"](c,"even");},"nth":function(c,a){return c[a-1]||[];},"first":function(c){return c[0]||[];},"last":function(c){return c[c.length-1]||[];},"has":function(c,ss){var s=Ext.DomQuery.select;var r=[];for(var i=0,ci;ci=c[i];i++){if(s(ss,ci).length>0){r[r.length]=ci;}}return r;},"next":function(c,ss){var is=Ext.DomQuery.is;var r=[];for(var i=0,ci;ci=c[i];i++){var n=next(ci);if(n&&is(n,ss)){r[r.length]=ci;}}return r;},"prev":function(c,ss){var is=Ext.DomQuery.is;var r=[];for(var i=0,ci;ci=c[i];i++){var n=prev(ci);if(n&&is(n,ss)){r[r.length]=ci;}}return r;}}};}();Ext.query=Ext.DomQuery.select;



Ext.util.Observable=function(){if(this.listeners){this.on(this.listeners);delete this.listeners;}};Ext.util.Observable.prototype={fireEvent:function(){var ce=this.events[arguments[0].toLowerCase()];if(typeof ce=="object"){return ce.fire.apply(ce,Array.prototype.slice.call(arguments,1));}else{return true;}},filterOptRe:/^(?:scope|delay|buffer|single)$/,addListener:function(_2,fn,_4,o){if(typeof _2=="object"){o=_2;for(var e in o){if(this.filterOptRe.test(e)){continue;}if(typeof o[e]=="function"){this.addListener(e,o[e],o.scope,o);}else{this.addListener(e,o[e].fn,o[e].scope,o[e]);}}return;}o=(!o||typeof o=="boolean")?{}:o;_2=_2.toLowerCase();var ce=this.events[_2]||true;if(typeof ce=="boolean"){ce=new Ext.util.Event(this,_2);this.events[_2]=ce;}ce.addListener(fn,_4,o);},removeListener:function(_8,fn,_a){var ce=this.events[_8.toLowerCase()];if(typeof ce=="object"){ce.removeListener(fn,_a);}},purgeListeners:function(){for(var _c in this.events){if(typeof this.events[_c]=="object"){this.events[_c].clearListeners();}}},relayEvents:function(o,_e){var _f=function(_10){return function(){return this.fireEvent.apply(this,Ext.combine(_10,Array.prototype.slice.call(arguments,0)));};};for(var i=0,len=_e.length;i<len;i++){var _13=_e[i];if(!this.events[_13]){this.events[_13]=true;}o.on(_13,_f(_13),this);}},addEvents:function(o){if(!this.events){this.events={};}Ext.applyIf(this.events,o);},hasListener:function(_15){var e=this.events[_15];return typeof e=="object"&&e.listeners.length>0;}};Ext.util.Observable.prototype.on=Ext.util.Observable.prototype.addListener;Ext.util.Observable.prototype.un=Ext.util.Observable.prototype.removeListener;Ext.util.Observable.capture=function(o,fn,_19){o.fireEvent=o.fireEvent.createInterceptor(fn,_19);};Ext.util.Observable.releaseCapture=function(o){o.fireEvent=Ext.util.Observable.prototype.fireEvent;};(function(){var _1b=function(h,o,_1e){var _1f=new Ext.util.DelayedTask();return function(){_1f.delay(o.buffer,h,_1e,Array.prototype.slice.call(arguments,0));};};var _20=function(h,e,fn,_24){return function(){e.removeListener(fn,_24);return h.apply(_24,arguments);};};var _25=function(h,o,_28){return function(){var _29=Array.prototype.slice.call(arguments,0);setTimeout(function(){h.apply(_28,_29);},o.delay||10);};};Ext.util.Event=function(obj,_2b){this.name=_2b;this.obj=obj;this.listeners=[];};Ext.util.Event.prototype={addListener:function(fn,_2d,_2e){var o=_2e||{};_2d=_2d||this.obj;if(!this.isListening(fn,_2d)){var l={fn:fn,scope:_2d,options:o};var h=fn;if(o.delay){h=_25(h,o,_2d);}if(o.single){h=_20(h,this,fn,_2d);}if(o.buffer){h=_1b(h,o,_2d);}l.fireFn=h;if(!this.firing){this.listeners.push(l);}else{this.listeners=this.listeners.slice(0);this.listeners.push(l);}}},findListener:function(fn,_33){_33=_33||this.obj;var ls=this.listeners;for(var i=0,len=ls.length;i<len;i++){var l=ls[i];if(l.fn==fn&&l.scope==_33){return i;}}return -1;},isListening:function(fn,_39){return this.findListener(fn,_39)!=-1;},removeListener:function(fn,_3b){var _3c;if((_3c=this.findListener(fn,_3b))!=-1){if(!this.firing){this.listeners.splice(_3c,1);}else{this.listeners=this.listeners.slice(0);this.listeners.splice(_3c,1);}return true;}return false;},clearListeners:function(){this.listeners=[];},fire:function(){var ls=this.listeners,_3e,len=ls.length;if(len>0){this.firing=true;var _40=Array.prototype.slice.call(arguments,0);for(var i=0;i<len;i++){var l=ls[i];if(l.fireFn.apply(l.scope,arguments)===false){this.firing=false;return false;}}this.firing=false;}return true;}};})();



Ext.EventManager=function(){var _1,_2,_3=false;var _4,_5,_6,_7;var E=Ext.lib.Event;var D=Ext.lib.Dom;var _a=function(){if(!_3){_3=true;Ext.isReady=true;if(_2){clearInterval(_2);}if(Ext.isGecko||Ext.isOpera){document.removeEventListener("DOMContentLoaded",_a,false);}if(_1){_1.fire();_1.clearListeners();}}};var _b=function(){_1=new Ext.util.Event();if(Ext.isGecko||Ext.isOpera){document.addEventListener("DOMContentLoaded",_a,false);}else{if(Ext.isIE){document.write("<s"+"cript id=\"ie-deferred-loader\" defer=\"defer\" src=\"/"+"/:\"></s"+"cript>");var _c=document.getElementById("ie-deferred-loader");_c.onreadystatechange=function(){if(this.readyState=="complete"){_a();_c.onreadystatechange=null;_c.parentNode.removeChild(_c);}};}else{if(Ext.isSafari){_2=setInterval(function(){var rs=document.readyState;if(rs=="complete"){_a();}},10);}}}E.on(window,"load",_a);};var _e=function(h,o){var _11=new Ext.util.DelayedTask(h);return function(e){e=new Ext.EventObjectImpl(e);_11.delay(o.buffer,h,null,[e]);};};var _13=function(h,el,_16,fn){return function(e){Ext.EventManager.removeListener(el,_16,fn);h(e);};};var _19=function(h,o){return function(e){e=new Ext.EventObjectImpl(e);setTimeout(function(){h(e);},o.delay||10);};};var _1d=function(_1e,_1f,opt,fn,_22){var o=(!opt||typeof opt=="boolean")?{}:opt;fn=fn||o.fn;_22=_22||o.scope;var el=Ext.getDom(_1e);if(!el){throw "Error listening for \""+_1f+"\". Element \""+_1e+"\" doesn't exist.";}var h=function(e){e=Ext.EventObject.setEvent(e);var t;if(o.delegate){t=e.getTarget(o.delegate,el);if(!t){return;}}else{t=e.target;}if(o.stopEvent===true){e.stopEvent();}if(o.preventDefault===true){e.preventDefault();}if(o.stopPropagation===true){e.stopPropagation();}if(o.normalized===false){e=e.browserEvent;}fn.call(_22||el,e,t,o);};if(o.delay){h=_19(h,o);}if(o.single){h=_13(h,el,_1f,fn);}if(o.buffer){h=_e(h,o);}fn._handlers=fn._handlers||[];fn._handlers.push([Ext.id(el),_1f,h]);E.on(el,_1f,h);if(_1f=="mousewheel"&&el.addEventListener){el.addEventListener("DOMMouseScroll",h,false);E.on(window,"unload",function(){el.removeEventListener("DOMMouseScroll",h,false);});}if(_1f=="mousedown"&&el==document){Ext.EventManager.stoppedMouseDownEvent.addListener(h);}return h;};var _28=function(el,_2a,fn){var id=Ext.id(el),hds=fn._handlers,hd=fn;if(hds){for(var i=0,len=hds.length;i<len;i++){var h=hds[i];if(h[0]==id&&h[1]==_2a){hd=h[2];hds.splice(i,1);break;}}}E.un(el,_2a,hd);el=Ext.getDom(el);if(_2a=="mousewheel"&&el.addEventListener){el.removeEventListener("DOMMouseScroll",hd,false);}if(_2a=="mousedown"&&el==document){Ext.EventManager.stoppedMouseDownEvent.removeListener(hd);}};var _32=/^(?:scope|delay|buffer|single|stopEvent|preventDefault|stopPropagation|normalized)$/;var pub={wrap:function(fn,_35,_36){return function(e){Ext.EventObject.setEvent(e);fn.call(_36?_35||window:window,Ext.EventObject,_35);};},addListener:function(_38,_39,fn,_3b,_3c){if(typeof _39=="object"){var o=_39;for(var e in o){if(_32.test(e)){continue;}if(typeof o[e]=="function"){_1d(_38,e,o,o[e],o.scope);}else{_1d(_38,e,o[e]);}}return;}return _1d(_38,_39,_3c,fn,_3b);},removeListener:function(_3f,_40,fn){return _28(_3f,_40,fn);},onDocumentReady:function(fn,_43,_44){if(_3){fn.call(_43||window,_43);return;}if(!_1){_b();}_1.addListener(fn,_43,_44);},onWindowResize:function(fn,_46,_47){if(!_4){_4=new Ext.util.Event();_5=new Ext.util.DelayedTask(function(){_4.fire(D.getViewWidth(),D.getViewHeight());});E.on(window,"resize",function(){if(Ext.isIE){_5.delay(50);}else{_4.fire(D.getViewWidth(),D.getViewHeight());}});}_4.addListener(fn,_46,_47);},onTextResize:function(fn,_49,_4a){if(!_6){_6=new Ext.util.Event();var _4b=new Ext.Element(document.createElement("div"));_4b.dom.className="x-text-resize";_4b.dom.innerHTML="X";_4b.appendTo(document.body);_7=_4b.dom.offsetHeight;setInterval(function(){if(_4b.dom.offsetHeight!=_7){_6.fire(_7,_7=_4b.dom.offsetHeight);}},this.textResizeInterval);}_6.addListener(fn,_49,_4a);},removeResizeListener:function(fn,_4d){if(_4){_4.removeListener(fn,_4d);}},fireResize:function(){if(_4){_4.fire(D.getViewWidth(),D.getViewHeight());}},ieDeferSrc:false,textResizeInterval:50};pub.on=pub.addListener;pub.un=pub.removeListener;pub.stoppedMouseDownEvent=new Ext.util.Event();return pub;}();Ext.onReady=Ext.EventManager.onDocumentReady;Ext.onReady(function(){var bd=Ext.get(document.body);if(!bd){return;}var cls=Ext.isIE?"ext-ie":Ext.isGecko?"ext-gecko":Ext.isOpera?"ext-opera":Ext.isSafari?"ext-safari":"";if(Ext.isBorderBox){cls+=" ext-border-box";}if(Ext.isStrict){cls+=" ext-strict";}bd.addClass(cls);});Ext.EventObject=function(){var E=Ext.lib.Event;var _51={63234:37,63235:39,63232:38,63233:40,63276:33,63277:34,63272:46,63273:36,63275:35};var _52=Ext.isIE?{1:0,4:1,2:2}:(Ext.isSafari?{1:0,2:1,3:2}:{0:0,1:1,2:2});Ext.EventObjectImpl=function(e){if(e){this.setEvent(e.browserEvent||e);}};Ext.EventObjectImpl.prototype={browserEvent:null,button:-1,shiftKey:false,ctrlKey:false,altKey:false,BACKSPACE:8,TAB:9,RETURN:13,ENTER:13,SHIFT:16,CONTROL:17,ESC:27,SPACE:32,PAGEUP:33,PAGEDOWN:34,END:35,HOME:36,LEFT:37,UP:38,RIGHT:39,DOWN:40,DELETE:46,F5:116,setEvent:function(e){if(e==this||(e&&e.browserEvent)){return e;}this.browserEvent=e;if(e){this.button=e.button?_52[e.button]:(e.which?e.which-1:-1);this.shiftKey=e.shiftKey;this.ctrlKey=e.ctrlKey||e.metaKey;this.altKey=e.altKey;this.keyCode=e.keyCode;this.charCode=e.charCode;this.target=E.getTarget(e);this.xy=E.getXY(e);}else{this.button=-1;this.shiftKey=false;this.ctrlKey=false;this.altKey=false;this.keyCode=0;this.charCode=0;this.target=null;this.xy=[0,0];}return this;},stopEvent:function(){if(this.browserEvent){if(this.browserEvent.type=="mousedown"){Ext.EventManager.stoppedMouseDownEvent.fire(this);}E.stopEvent(this.browserEvent);}},preventDefault:function(){if(this.browserEvent){E.preventDefault(this.browserEvent);}},isNavKeyPress:function(){var k=this.keyCode;k=Ext.isSafari?(_51[k]||k):k;return (k>=33&&k<=40)||k==this.RETURN||k==this.TAB||k==this.ESC;},isSpecialKey:function(){var k=this.keyCode;return k==9||k==13||k==40||k==27||(k==16)||(k==17)||(k>=18&&k<=20)||(k>=33&&k<=35)||(k>=36&&k<=39)||(k>=44&&k<=45);},stopPropagation:function(){if(this.browserEvent){if(this.browserEvent.type=="mousedown"){Ext.EventManager.stoppedMouseDownEvent.fire(this);}E.stopPropagation(this.browserEvent);}},getCharCode:function(){return this.charCode||this.keyCode;},getKey:function(){var k=this.keyCode||this.charCode;return Ext.isSafari?(_51[k]||k):k;},getPageX:function(){return this.xy[0];},getPageY:function(){return this.xy[1];},getTime:function(){if(this.browserEvent){return E.getTime(this.browserEvent);}return null;},getXY:function(){return this.xy;},getTarget:function(_58,_59,_5a){return _58?Ext.fly(this.target).findParent(_58,_59,_5a):this.target;},getRelatedTarget:function(){if(this.browserEvent){return E.getRelatedTarget(this.browserEvent);}return null;},getWheelDelta:function(){var e=this.browserEvent;var _5c=0;if(e.wheelDelta){_5c=e.wheelDelta/120;if(window.opera){_5c=-_5c;}}else{if(e.detail){_5c=-e.detail/3;}}return _5c;},hasModifier:function(){return ((this.ctrlKey||this.altKey)||this.shiftKey)?true:false;},within:function(el,_5e){var t=this[_5e?"getRelatedTarget":"getTarget"]();return t&&Ext.fly(el).contains(t);},getPoint:function(){return new Ext.lib.Point(this.xy[0],this.xy[1]);}};return new Ext.EventObjectImpl();}();



(function(){var D=Ext.lib.Dom;var E=Ext.lib.Event;var A=Ext.lib.Anim;var _4={};var _5=/(-[a-z])/gi;var _6=function(m,a){return a.charAt(1).toUpperCase();};var _9=document.defaultView;Ext.Element=function(_a,_b){var _c=typeof _a=="string"?document.getElementById(_a):_a;if(!_c){return null;}if(!_b&&Ext.Element.cache[_c.id]){return Ext.Element.cache[_c.id];}this.dom=_c;this.id=_c.id||Ext.id(_c);};var El=Ext.Element;El.prototype={originalDisplay:"",visibilityMode:1,defaultUnit:"px",setVisibilityMode:function(_e){this.visibilityMode=_e;return this;},enableDisplayMode:function(_f){this.setVisibilityMode(El.DISPLAY);if(typeof _f!="undefined"){this.originalDisplay=_f;}return this;},findParent:function(_10,_11,_12){var p=this.dom,b=document.body,_15=0,dq=Ext.DomQuery,_17;_11=_11||50;if(typeof _11!="number"){_17=Ext.getDom(_11);_11=10;}while(p&&p.nodeType==1&&_15<_11&&p!=b&&p!=_17){if(dq.is(p,_10)){return _12?Ext.get(p):p;}_15++;p=p.parentNode;}return null;},findParentNode:function(_18,_19,_1a){var p=Ext.fly(this.dom.parentNode,"_internal");return p?p.findParent(_18,_19,_1a):null;},up:function(_1c,_1d){return this.findParentNode(_1c,_1d,true);},is:function(_1e){return Ext.DomQuery.is(this.dom,_1e);},animate:function(_1f,_20,_21,_22,_23){this.anim(_1f,{duration:_20,callback:_21,easing:_22},_23);return this;},anim:function(_24,opt,_26,_27,_28,cb){_26=_26||"run";opt=opt||{};var _2a=Ext.lib.Anim[_26](this.dom,_24,(opt.duration||_27)||0.35,(opt.easing||_28)||"easeOut",function(){Ext.callback(cb,this);Ext.callback(opt.callback,opt.scope||this,[this,opt]);},this);opt.anim=_2a;return _2a;},preanim:function(a,i){return !a[i]?false:(typeof a[i]=="object"?a[i]:{duration:a[i+1],callback:a[i+2],easing:a[i+3]});},clean:function(_2d){if(this.isCleaned&&_2d!==true){return this;}var ns=/\S/;var d=this.dom,n=d.firstChild,ni=-1;while(n){var nx=n.nextSibling;if(n.nodeType==3&&!ns.test(n.nodeValue)){d.removeChild(n);}else{n.nodeIndex=++ni;}n=nx;}this.isCleaned=true;return this;},calcOffsetsTo:function(el){el=Ext.get(el),d=el.dom;var _34=false;if(el.getStyle("position")=="static"){el.position("relative");_34=true;}var x=0,y=0;var op=this.dom;while(op&&op!=d&&op.tagName!="HTML"){x+=op.offsetLeft;y+=op.offsetTop;op=op.offsetParent;}if(_34){el.position("static");}return [x,y];},scrollIntoView:function(_38,_39){var c=Ext.getDom(_38)||document.body;var el=this.dom;var o=this.calcOffsetsTo(c),l=o[0],t=o[1],b=t+el.offsetHeight,r=l+el.offsetWidth;var ch=c.clientHeight;var ct=parseInt(c.scrollTop,10);var cl=parseInt(c.scrollLeft,10);var cb=ct+ch;var cr=cl+c.clientWidth;if(t<ct){c.scrollTop=t;}else{if(b>cb){c.scrollTop=b-ch;}}if(_39!==false){if(l<cl){c.scrollLeft=l;}else{if(r>cr){c.scrollLeft=r-c.clientWidth;}}}return this;},scrollChildIntoView:function(_46){Ext.fly(_46,"_scrollChildIntoView").scrollIntoView(this);},autoHeight:function(_47,_48,_49,_4a){var _4b=this.getHeight();this.clip();this.setHeight(1);setTimeout(function(){var _4c=parseInt(this.dom.scrollHeight,10);if(!_47){this.setHeight(_4c);this.unclip();if(typeof _49=="function"){_49();}}else{this.setHeight(_4b);this.setHeight(_4c,_47,_48,function(){this.unclip();if(typeof _49=="function"){_49();}}.createDelegate(this),_4a);}}.createDelegate(this),0);return this;},contains:function(el){if(!el){return false;}return D.isAncestor(this.dom,el.dom?el.dom:el);},isVisible:function(_4e){var vis=!(this.getStyle("visibility")=="hidden"||this.getStyle("display")=="none");if(_4e!==true||!vis){return vis;}var p=this.dom.parentNode;while(p&&p.tagName.toLowerCase()!="body"){if(!Ext.fly(p,"_isVisible").isVisible()){return false;}p=p.parentNode;}return true;},select:function(_51,_52){return El.select("#"+Ext.id(this.dom)+" "+_51,_52);},query:function(_53,_54){return Ext.DomQuery.select("#"+Ext.id(this.dom)+" "+_53);},child:function(_55,_56){var n=Ext.DomQuery.selectNode("#"+Ext.id(this.dom)+" "+_55);return _56?n:Ext.get(n);},down:function(_58,_59){var n=Ext.DomQuery.selectNode("#"+Ext.id(this.dom)+" > "+_58);return _59?n:Ext.get(n);},initDD:function(_5b,_5c,_5d){var dd=new Ext.dd.DD(Ext.id(this.dom),_5b,_5c);return Ext.apply(dd,_5d);},initDDProxy:function(_5f,_60,_61){var dd=new Ext.dd.DDProxy(Ext.id(this.dom),_5f,_60);return Ext.apply(dd,_61);},initDDTarget:function(_63,_64,_65){var dd=new Ext.dd.DDTarget(Ext.id(this.dom),_63,_64);return Ext.apply(dd,_65);},setVisible:function(_67,_68){if(!_68||!A){if(this.visibilityMode==El.DISPLAY){this.setDisplayed(_67);}else{this.fixDisplay();this.dom.style.visibility=_67?"visible":"hidden";}}else{var dom=this.dom;var _6a=this.visibilityMode;if(_67){this.setOpacity(0.01);this.setVisible(true);}this.anim({opacity:{to:(_67?1:0)}},this.preanim(arguments,1),null,0.35,"easeIn",function(){if(!_67){if(_6a==El.DISPLAY){dom.style.display="none";}else{dom.style.visibility="hidden";}Ext.get(dom).setOpacity(1);}});}return this;},isDisplayed:function(){return this.getStyle("display")!="none";},toggle:function(_6b){this.setVisible(!this.isVisible(),this.preanim(arguments,0));return this;},setDisplayed:function(_6c){if(typeof _6c=="boolean"){_6c=_6c?this.originalDisplay:"none";}this.setStyle("display",_6c);return this;},focus:function(){try{this.dom.focus();}catch(e){}return this;},blur:function(){try{this.dom.blur();}catch(e){}return this;},addClass:function(_6d){if(_6d instanceof Array){for(var i=0,len=_6d.length;i<len;i++){this.addClass(_6d[i]);}}else{if(_6d&&!this.hasClass(_6d)){this.dom.className=this.dom.className+" "+_6d;}}return this;},radioClass:function(_70){var _71=this.dom.parentNode.childNodes;for(var i=0;i<_71.length;i++){var s=_71[i];if(s.nodeType==1){Ext.get(s).removeClass(_70);}}this.addClass(_70);return this;},removeClass:function(_74){if(!_74||!this.dom.className){return this;}if(_74 instanceof Array){for(var i=0,len=_74.length;i<len;i++){this.removeClass(_74[i]);}}else{if(this.hasClass(_74)){var re=this.classReCache[_74];if(!re){re=new RegExp("(?:^|\\s+)"+_74+"(?:\\s+|$)","g");this.classReCache[_74]=re;}this.dom.className=this.dom.className.replace(re," ");}}return this;},classReCache:{},toggleClass:function(_78){if(this.hasClass(_78)){this.removeClass(_78);}else{this.addClass(_78);}return this;},hasClass:function(_79){return _79&&(" "+this.dom.className+" ").indexOf(" "+_79+" ")!=-1;},replaceClass:function(_7a,_7b){this.removeClass(_7a);this.addClass(_7b);return this;},getStyles:function(){var a=arguments,len=a.length,r={};for(var i=0;i<len;i++){r[a[i]]=this.getStyle(a[i]);}return r;},getStyle:function(){return _9&&_9.getComputedStyle?function(_80){var el=this.dom,v,cs,_84;if(_80=="float"){_80="cssFloat";}if(v=el.style[_80]){return v;}if(cs=_9.getComputedStyle(el,"")){if(!(_84=_4[_80])){_84=_4[_80]=_80.replace(_5,_6);}return cs[_84];}return null;}:function(_85){var el=this.dom,v,cs,_89;if(_85=="opacity"){if(typeof el.filter=="string"){var fv=parseFloat(el.filter.match(/alpha\(opacity=(.*)\)/i)[1]);if(!isNaN(fv)){return fv?fv/100:0;}}return 1;}else{if(_85=="float"){_85="styleFloat";}}if(!(_89=_4[_85])){_89=_4[_85]=_85.replace(_5,_6);}if(v=el.style[_89]){return v;}if(cs=el.currentStyle){return cs[_89];}return null;};}(),setStyle:function(_8b,_8c){if(typeof _8b=="string"){var _8d;if(!(_8d=_4[_8b])){_8d=_4[_8b]=_8b.replace(_5,_6);}if(_8d=="opacity"){this.setOpacity(_8c);}else{this.dom.style[_8d]=_8c;}}else{for(var _8e in _8b){if(typeof _8b[_8e]!="function"){this.setStyle(_8e,_8b[_8e]);}}}return this;},applyStyles:function(_8f){Ext.DomHelper.applyStyles(this.dom,_8f);return this;},getX:function(){return D.getX(this.dom);},getY:function(){return D.getY(this.dom);},getXY:function(){return D.getXY(this.dom);},setX:function(x,_91){if(!_91||!A){D.setX(this.dom,x);}else{this.setXY([x,this.getY()],this.preanim(arguments,1));}return this;},setY:function(y,_93){if(!_93||!A){D.setY(this.dom,y);}else{this.setXY([this.getX(),y],this.preanim(arguments,1));}return this;},setLeft:function(_94){this.setStyle("left",this.addUnits(_94));return this;},setTop:function(top){this.setStyle("top",this.addUnits(top));return this;},setRight:function(_96){this.setStyle("right",this.addUnits(_96));return this;},setBottom:function(_97){this.setStyle("bottom",this.addUnits(_97));return this;},setXY:function(pos,_99){if(!_99||!A){D.setXY(this.dom,pos);}else{this.anim({points:{to:pos}},this.preanim(arguments,1),"motion");}return this;},setLocation:function(x,y,_9c){this.setXY([x,y],this.preanim(arguments,2));return this;},moveTo:function(x,y,_9f){this.setXY([x,y],this.preanim(arguments,2));return this;},getRegion:function(){return D.getRegion(this.dom);},getHeight:function(_a0){var h=this.dom.offsetHeight||0;return _a0!==true?h:h-this.getBorderWidth("tb")-this.getPadding("tb");},getWidth:function(_a2){var w=this.dom.offsetWidth||0;return _a2!==true?w:w-this.getBorderWidth("lr")-this.getPadding("lr");},getComputedHeight:function(){var h=Math.max(this.dom.offsetHeight,this.dom.clientHeight);if(!h){h=parseInt(this.getStyle("height"),10)||0;if(!this.isBorderBox()){h+=this.getFrameWidth("tb");}}return h;},getComputedWidth:function(){var w=Math.max(this.dom.offsetWidth,this.dom.clientWidth);if(!w){w=parseInt(this.getStyle("width"),10)||0;if(!this.isBorderBox()){w+=this.getFrameWidth("lr");}}return w;},getSize:function(_a6){return {width:this.getWidth(_a6),height:this.getHeight(_a6)};},getViewSize:function(){var d=this.dom,doc=document,aw=0,ah=0;if(d==doc||d==doc.body){return {width:D.getViewWidth(),height:D.getViewHeight()};}else{return {width:d.clientWidth,height:d.clientHeight};}},getValue:function(_ab){return _ab?parseInt(this.dom.value,10):this.dom.value;},adjustWidth:function(_ac){if(typeof _ac=="number"){if(this.autoBoxAdjust&&!this.isBorderBox()){_ac-=(this.getBorderWidth("lr")+this.getPadding("lr"));}if(_ac<0){_ac=0;}}return _ac;},adjustHeight:function(_ad){if(typeof _ad=="number"){if(this.autoBoxAdjust&&!this.isBorderBox()){_ad-=(this.getBorderWidth("tb")+this.getPadding("tb"));}if(_ad<0){_ad=0;}}return _ad;},setWidth:function(_ae,_af){_ae=this.adjustWidth(_ae);if(!_af||!A){this.dom.style.width=this.addUnits(_ae);}else{this.anim({width:{to:_ae}},this.preanim(arguments,1));}return this;},setHeight:function(_b0,_b1){_b0=this.adjustHeight(_b0);if(!_b1||!A){this.dom.style.height=this.addUnits(_b0);}else{this.anim({height:{to:_b0}},this.preanim(arguments,1));}return this;},setSize:function(_b2,_b3,_b4){if(typeof _b2=="object"){_b3=_b2.height;_b2=_b2.width;}_b2=this.adjustWidth(_b2);_b3=this.adjustHeight(_b3);if(!_b4||!A){this.dom.style.width=this.addUnits(_b2);this.dom.style.height=this.addUnits(_b3);}else{this.anim({width:{to:_b2},height:{to:_b3}},this.preanim(arguments,2));}return this;},setBounds:function(x,y,_b7,_b8,_b9){if(!_b9||!A){this.setSize(_b7,_b8);this.setLocation(x,y);}else{_b7=this.adjustWidth(_b7);_b8=this.adjustHeight(_b8);this.anim({points:{to:[x,y]},width:{to:_b7},height:{to:_b8}},this.preanim(arguments,4),"motion");}return this;},setRegion:function(_ba,_bb){this.setBounds(_ba.left,_ba.top,_ba.right-_ba.left,_ba.bottom-_ba.top,this.preanim(arguments,1));return this;},addListener:function(_bc,fn,_be,_bf){Ext.EventManager.on(this.dom,_bc,fn,_be||this,_bf);},removeListener:function(_c0,fn){Ext.EventManager.removeListener(this.dom,_c0,fn);return this;},removeAllListeners:function(){E.purgeElement(this.dom);return this;},relayEvent:function(_c2,_c3){this.on(_c2,function(e){_c3.fireEvent(_c2,e);});},setOpacity:function(_c5,_c6){if(!_c6||!A){var s=this.dom.style;if(Ext.isIE){s.zoom=1;s.filter=(s.filter||"").replace(/alpha\([^\)]*\)/gi,"")+(_c5==1?"":"alpha(opacity="+_c5*100+")");}else{s.opacity=_c5;}}else{this.anim({opacity:{to:_c5}},this.preanim(arguments,1),null,0.35,"easeIn");}return this;},getLeft:function(_c8){if(!_c8){return this.getX();}else{return parseInt(this.getStyle("left"),10)||0;}},getRight:function(_c9){if(!_c9){return this.getX()+this.getWidth();}else{return (this.getLeft(true)+this.getWidth())||0;}},getTop:function(_ca){if(!_ca){return this.getY();}else{return parseInt(this.getStyle("top"),10)||0;}},getBottom:function(_cb){if(!_cb){return this.getY()+this.getHeight();}else{return (this.getTop(true)+this.getHeight())||0;}},position:function(pos,_cd,x,y){if(!pos){if(this.getStyle("position")=="static"){this.setStyle("position","relative");}}else{this.setStyle("position",pos);}if(_cd){this.setStyle("z-index",_cd);}if(x!==undefined&&y!==undefined){this.setXY([x,y]);}else{if(x!==undefined){this.setX(x);}else{if(y!==undefined){this.setY(y);}}}},clearPositioning:function(_d0){_d0=_d0||"";this.setStyle({"left":_d0,"right":_d0,"top":_d0,"bottom":_d0,"z-index":"","position":"static"});return this;},getPositioning:function(){var l=this.getStyle("left");var t=this.getStyle("top");return {"position":this.getStyle("position"),"left":l,"right":l?"":this.getStyle("right"),"top":t,"bottom":t?"":this.getStyle("bottom"),"z-index":this.getStyle("z-index")};},getBorderWidth:function(_d3){return this.addStyles(_d3,El.borders);},getPadding:function(_d4){return this.addStyles(_d4,El.paddings);},setPositioning:function(pc){this.applyStyles(pc);if(pc.right=="auto"){this.dom.style.right="";}if(pc.bottom=="auto"){this.dom.style.bottom="";}return this;},fixDisplay:function(){if(this.getStyle("display")=="none"){this.setStyle("visibility","hidden");this.setStyle("display",this.originalDisplay);if(this.getStyle("display")=="none"){this.setStyle("display","block");}}},setLeftTop:function(_d6,top){this.dom.style.left=this.addUnits(_d6);this.dom.style.top=this.addUnits(top);return this;},move:function(_d8,_d9,_da){var xy=this.getXY();_d8=_d8.toLowerCase();switch(_d8){case "l":case "left":this.moveTo(xy[0]-_d9,xy[1],this.preanim(arguments,2));break;case "r":case "right":this.moveTo(xy[0]+_d9,xy[1],this.preanim(arguments,2));break;case "t":case "top":case "up":this.moveTo(xy[0],xy[1]-_d9,this.preanim(arguments,2));break;case "b":case "bottom":case "down":this.moveTo(xy[0],xy[1]+_d9,this.preanim(arguments,2));break;}return this;},clip:function(){if(!this.isClipped){this.isClipped=true;this.originalClip={"o":this.getStyle("overflow"),"x":this.getStyle("overflow-x"),"y":this.getStyle("overflow-y")};this.setStyle("overflow","hidden");this.setStyle("overflow-x","hidden");this.setStyle("overflow-y","hidden");}return this;},unclip:function(){if(this.isClipped){this.isClipped=false;var o=this.originalClip;if(o.o){this.setStyle("overflow",o.o);}if(o.x){this.setStyle("overflow-x",o.x);}if(o.y){this.setStyle("overflow-y",o.y);}}return this;},getAnchorXY:function(_dd,_de,s){var w,h,vp=false;if(!s){var d=this.dom;if(d==document.body||d==document){vp=true;w=D.getViewWidth();h=D.getViewHeight();}else{w=this.getWidth();h=this.getHeight();}}else{w=s.width;h=s.height;}var x=0,y=0,r=Math.round;switch((_dd||"tl").toLowerCase()){case "c":x=r(w*0.5);y=r(h*0.5);break;case "t":x=r(w*0.5);y=0;break;case "l":x=0;y=r(h*0.5);break;case "r":x=w;y=r(h*0.5);break;case "b":x=r(w*0.5);y=h;break;case "tl":x=0;y=0;break;case "bl":x=0;y=h;break;case "br":x=w;y=h;break;case "tr":x=w;y=0;break;}if(_de===true){return [x,y];}if(vp){var sc=this.getScroll();return [x+sc.left,y+sc.top];}var o=this.getXY();return [x+o[0],y+o[1]];},getAlignToXY:function(el,p,o){el=Ext.get(el),d=this.dom;if(!el.dom){throw "Element.alignTo with an element that doesn't exist";}var c=false;var p1="",p2="";o=o||[0,0];if(!p){p="tl-bl";}else{if(p=="?"){p="tl-bl?";}else{if(p.indexOf("-")==-1){p="tl-"+p;}}}p=p.toLowerCase();var m=p.match(/^([a-z]+)-([a-z]+)(\?)?$/);if(!m){throw "Element.alignTo with an invalid alignment "+p;}p1=m[1],p2=m[2],c=m[3]?true:false;var a1=this.getAnchorXY(p1,true);var a2=el.getAnchorXY(p2,false);var x=a2[0]-a1[0]+o[0];var y=a2[1]-a1[1]+o[1];if(c){var w=this.getWidth(),h=this.getHeight(),r=el.getRegion();var dw=D.getViewWidth()-5,dh=D.getViewHeight()-5;var p1y=p1.charAt(0),p1x=p1.charAt(p1.length-1);var p2y=p2.charAt(0),p2x=p2.charAt(p2.length-1);var _fd=((p1y=="t"&&p2y=="b")||(p1y=="b"&&p2y=="t"));var _fe=((p1x=="r"&&p2x=="l")||(p1x=="l"&&p2x=="r"));var doc=document;var _100=(doc.documentElement.scrollLeft||doc.body.scrollLeft||0)+5;var _101=(doc.documentElement.scrollTop||doc.body.scrollTop||0)+5;if((x+w)>dw){x=_fe?r.left-w:dw-w;}if(x<_100){x=_fe?r.right:_100;}if((y+h)>dh){y=_fd?r.top-h:dh-h;}if(y<_101){y=_fd?r.bottom:_101;}}return [x,y];},getConstrainToXY:function(){var os={top:0,left:0,bottom:0,right:0};return function(el,_104,_105){el=Ext.get(el);_105=_105?Ext.applyIf(_105,os):os;var vw,vh,vx=0,vy=0;if(el.dom==document.body||el.dom==document){vw=Ext.lib.Dom.getViewWidth();vh=Ext.lib.Dom.getViewHeight();}else{vw=el.dom.clientWidth;vh=el.dom.clientHeight;if(!_104){var vxy=el.getXY();vx=vxy[0];vy=vxy[1];}}var s=el.getScroll();vx+=_105.left+s.left;vy+=_105.top+s.top;vw-=_105.right;vh-=_105.bottom;var vr=vx+vw;var vb=vy+vh;var xy=!_104?this.getXY():[this.getLeft(true),this.getTop(true)];var x=xy[0],y=xy[1];var w=this.dom.offsetWidth,h=this.dom.offsetHeight;var _113=false;if((x+w)>vr){x=vr-w;_113=true;}if((y+h)>vb){y=vb-h;_113=true;}if(x<vx){x=vx;_113=true;}if(y<vy){y=vy;_113=true;}return _113?[x,y]:false;};}(),alignTo:function(_114,_115,_116,_117){var xy=this.getAlignToXY(_114,_115,_116);this.setXY(xy,this.preanim(arguments,3));return this;},anchorTo:function(el,_11a,_11b,_11c,_11d,_11e){var _11f=function(){this.alignTo(el,_11a,_11b,_11c);Ext.callback(_11e,this);};Ext.EventManager.onWindowResize(_11f,this);var tm=typeof _11d;if(tm!="undefined"){Ext.EventManager.on(window,"scroll",_11f,this,{buffer:tm=="number"?_11d:50});}_11f.call(this);return this;},clearOpacity:function(){if(window.ActiveXObject){this.dom.style.filter="";}else{this.dom.style.opacity="";this.dom.style["-moz-opacity"]="";this.dom.style["-khtml-opacity"]="";}return this;},hide:function(_121){this.setVisible(false,this.preanim(arguments,0));return this;},show:function(_122){this.setVisible(true,this.preanim(arguments,0));return this;},addUnits:function(size){return Ext.Element.addUnits(size,this.defaultUnit);},beginMeasure:function(){var el=this.dom;if(el.offsetWidth||el.offsetHeight){return this;}var _125=[];var p=this.dom,b=document.body;while((!el.offsetWidth&&!el.offsetHeight)&&p&&p.tagName&&p!=b){var pe=Ext.get(p);if(pe.getStyle("display")=="none"){_125.push({el:p,visibility:pe.getStyle("visibility")});p.style.visibility="hidden";p.style.display="block";}p=p.parentNode;}this._measureChanged=_125;return this;},endMeasure:function(){var _129=this._measureChanged;if(_129){for(var i=0,len=_129.length;i<len;i++){var r=_129[i];r.el.style.visibility=r.visibility;r.el.style.display="none";}this._measureChanged=null;}return this;},update:function(html,_12e,_12f){if(typeof html=="undefined"){html="";}if(_12e!==true){this.dom.innerHTML=html;if(typeof _12f=="function"){_12f();}return this;}var id=Ext.id();var dom=this.dom;html+="<span id=\""+id+"\"></span>";E.onAvailable(id,function(){var hd=document.getElementsByTagName("head")[0];var re=/(?:<script([^>]*)?>)((\n|\r|.)*?)(?:<\/script>)/ig;var _134=/\ssrc=([\'\"])(.*?)\1/i;var _135=/\stype=([\'\"])(.*?)\1/i;var _136;while(_136=re.exec(html)){var _137=_136[1];var _138=_137?_137.match(_134):false;if(_138&&_138[2]){var s=document.createElement("script");s.src=_138[2];var _13a=_137.match(_135);if(_13a&&_13a[2]){s.type=_13a[2];}hd.appendChild(s);}else{if(_136[2]&&_136[2].length>0){eval(_136[2]);}}}var el=document.getElementById(id);if(el){el.parentNode.removeChild(el);}if(typeof _12f=="function"){_12f();}});dom.innerHTML=html.replace(/(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)/ig,"");return this;},load:function(){var um=this.getUpdateManager();um.update.apply(um,arguments);return this;},getUpdateManager:function(){if(!this.updateManager){this.updateManager=new Ext.UpdateManager(this);}return this.updateManager;},unselectable:function(){this.dom.unselectable="on";this.swallowEvent("selectstart",true);this.applyStyles("-moz-user-select:none;-khtml-user-select:none;");this.addClass("x-unselectable");return this;},getCenterXY:function(){return this.getAlignToXY(document,"c-c");},center:function(_13d){this.alignTo(_13d||document,"c-c");return this;},isBorderBox:function(){return _13e[this.dom.tagName.toLowerCase()]||Ext.isBorderBox;},getBox:function(_13f,_140){var xy;if(!_140){xy=this.getXY();}else{var left=parseInt(this.getStyle("left"),10)||0;var top=parseInt(this.getStyle("top"),10)||0;xy=[left,top];}var el=this.dom,w=el.offsetWidth,h=el.offsetHeight,bx;if(!_13f){bx={x:xy[0],y:xy[1],0:xy[0],1:xy[1],width:w,height:h};}else{var l=this.getBorderWidth("l")+this.getPadding("l");var r=this.getBorderWidth("r")+this.getPadding("r");var t=this.getBorderWidth("t")+this.getPadding("t");var b=this.getBorderWidth("b")+this.getPadding("b");bx={x:xy[0]+l,y:xy[1]+t,0:xy[0]+l,1:xy[1]+t,width:w-(l+r),height:h-(t+b)};}bx.right=bx.x+bx.width;bx.bottom=bx.y+bx.height;return bx;},getFrameWidth:function(_14c){return this.getPadding(_14c)+this.getBorderWidth(_14c);},setBox:function(box,_14e,_14f){var w=box.width,h=box.height;if((_14e&&!this.autoBoxAdjust)&&!this.isBorderBox()){w-=(this.getBorderWidth("lr")+this.getPadding("lr"));h-=(this.getBorderWidth("tb")+this.getPadding("tb"));}this.setBounds(box.x,box.y,w,h,this.preanim(arguments,2));return this;},repaint:function(){var dom=this.dom;this.addClass("x-repaint");setTimeout(function(){Ext.get(dom).removeClass("x-repaint");},1);return this;},getMargins:function(side){if(!side){return {top:parseInt(this.getStyle("margin-top"),10)||0,left:parseInt(this.getStyle("margin-left"),10)||0,bottom:parseInt(this.getStyle("margin-bottom"),10)||0,right:parseInt(this.getStyle("margin-right"),10)||0};}else{return this.addStyles(side,El.margins);}},addStyles:function(_154,_155){var val=0;for(var i=0,len=_154.length;i<len;i++){var w=parseInt(this.getStyle(_155[_154.charAt(i)]),10);if(!isNaN(w)){val+=w;}}return val;},createProxy:function(_15a,_15b,_15c){if(_15b){_15b=Ext.getDom(_15b);}else{_15b=document.body;}_15a=typeof _15a=="object"?_15a:{tag:"div",cls:_15a};var _15d=Ext.DomHelper.append(_15b,_15a,true);if(_15c){_15d.setBox(this.getBox());}return _15d;},mask:function(msg,_15f){if(this.getStyle("position")=="static"){this.setStyle("position","relative");}if(!this._mask){this._mask=Ext.DomHelper.append(this.dom,{tag:"div",cls:"ext-el-mask"},true);}this.addClass("x-masked");this._mask.setDisplayed(true);if(typeof msg=="string"){if(!this._maskMsg){this._maskMsg=Ext.DomHelper.append(this.dom,{tag:"div",cls:"ext-el-mask-msg",cn:{tag:"div"}},true);}var mm=this._maskMsg;mm.dom.className=_15f?"ext-el-mask-msg "+_15f:"ext-el-mask-msg";mm.dom.firstChild.innerHTML=msg;mm.setDisplayed(true);mm.center(this);}return this._mask;},unmask:function(_161){if(this._mask){if(_161===true){this._mask.remove();delete this._mask;if(this._maskMsg){this._maskMsg.remove();delete this._maskMsg;}}else{this._mask.setDisplayed(false);if(this._maskMsg){this._maskMsg.setDisplayed(false);}}}this.removeClass("x-masked");},isMasked:function(){return this._mask&&this._mask.isVisible();},createShim:function(){var el=document.createElement("iframe");el.frameBorder="no";el.className="ext-shim";if(Ext.isIE&&Ext.isSecure){el.src=Ext.SSL_SECURE_URL;}var shim=Ext.get(this.dom.parentNode.insertBefore(el,this.dom));shim.autoBoxAdjust=false;return shim;},remove:function(){if(this.dom.parentNode){this.dom.parentNode.removeChild(this.dom);}delete El.cache[this.dom.id];},addClassOnOver:function(_164,_165){this.on("mouseover",function(){Ext.fly(this,"_internal").addClass(_164);},this.dom);var _166=function(e){if(_165!==true||!e.within(this,true)){Ext.fly(this,"_internal").removeClass(_164);}};this.on("mouseout",_166,this.dom);return this;},addClassOnFocus:function(_168){this.on("focus",function(){Ext.fly(this,"_internal").addClass(_168);},this.dom);this.on("blur",function(){Ext.fly(this,"_internal").removeClass(_168);},this.dom);return this;},addClassOnClick:function(_169){var dom=this.dom;this.on("mousedown",function(){Ext.fly(dom,"_internal").addClass(_169);var d=Ext.get(document);var fn=function(){Ext.fly(dom,"_internal").removeClass(_169);d.removeListener("mouseup",fn);};d.on("mouseup",fn);});return this;},swallowEvent:function(_16d,_16e){var fn=function(e){e.stopPropagation();if(_16e){e.preventDefault();}};if(_16d instanceof Array){for(var i=0,len=_16d.length;i<len;i++){this.on(_16d[i],fn);}return this;}this.on(_16d,fn);return this;},fitToParent:function(_173,_174){var p=Ext.get(_174||this.dom.parentNode);this.setSize(p.getComputedWidth()-p.getFrameWidth("lr"),p.getComputedHeight()-p.getFrameWidth("tb"));if(_173===true){Ext.EventManager.onWindowResize(this.fitToParent.createDelegate(this,[]));}return this;},getNextSibling:function(){var n=this.dom.nextSibling;while(n&&n.nodeType!=1){n=n.nextSibling;}return n;},getPrevSibling:function(){var n=this.dom.previousSibling;while(n&&n.nodeType!=1){n=n.previousSibling;}return n;},appendChild:function(el){el=Ext.get(el);el.appendTo(this);return this;},createChild:function(_179,_17a,_17b){_179=_179||{tag:"div"};if(_17a){return Ext.DomHelper.insertBefore(_17a,_179,_17b!==true);}return Ext.DomHelper[!this.dom.firstChild?"overwrite":"append"](this.dom,_179,_17b!==true);},appendTo:function(el){el=Ext.getDom(el);el.appendChild(this.dom);return this;},insertBefore:function(el){el=Ext.getDom(el);el.parentNode.insertBefore(this.dom,el);return this;},insertAfter:function(el){el=Ext.getDom(el);el.parentNode.insertBefore(this.dom,el.nextSibling);return this;},insertFirst:function(el,_180){el=el||{};if(typeof el=="object"&&!el.nodeType){return this.createChild(el,this.dom.firstChild,_180);}else{el=Ext.getDom(el);this.dom.insertBefore(el,this.dom.firstChild);return !_180?Ext.get(el):el;}},insertSibling:function(el,_182,_183){_182=_182?_182.toLowerCase():"before";el=el||{};var rt,_185=_182=="before"?this.dom:this.dom.nextSibling;if(typeof el=="object"&&!el.nodeType){if(_182=="after"&&!this.dom.nextSibling){rt=Ext.DomHelper.append(this.dom.parentNode,el,!_183);}else{rt=Ext.DomHelper[_182=="after"?"insertAfter":"insertBefore"](this.dom,el,!_183);}}else{rt=this.dom.parentNode.insertBefore(Ext.getDom(el),_182=="before"?this.dom:this.dom.nextSibling);if(!_183){rt=Ext.get(rt);}}return rt;},wrap:function(_186,_187){if(!_186){_186={tag:"div"};}var _188=Ext.DomHelper.insertBefore(this.dom,_186,!_187);_188.dom?_188.dom.appendChild(this.dom):_188.appendChild(this.dom);return _188;},replace:function(el){el=Ext.get(el);this.insertBefore(el);el.remove();return this;},insertHtml:function(_18a,html){return Ext.DomHelper.insertHtml(_18a,this.dom,html);},set:function(o,_18d){var el=this.dom;_18d=typeof _18d=="undefined"?(el.setAttribute?true:false):_18d;for(var attr in o){if(attr=="style"||typeof o[attr]=="function"){continue;}if(attr=="cls"){el.className=o["cls"];}else{if(_18d){el.setAttribute(attr,o[attr]);}else{el[attr]=o[attr];}}}Ext.DomHelper.applyStyles(el,o.style);return this;},addKeyListener:function(key,fn,_192){var _193;if(typeof key!="object"||key instanceof Array){_193={key:key,fn:fn,scope:_192};}else{_193={key:key.key,shift:key.shift,ctrl:key.ctrl,alt:key.alt,fn:fn,scope:_192};}return new Ext.KeyMap(this,_193);},addKeyMap:function(_194){return new Ext.KeyMap(this,_194);},isScrollable:function(){var dom=this.dom;return dom.scrollHeight>dom.clientHeight||dom.scrollWidth>dom.clientWidth;},scrollTo:function(side,_197,_198){var prop=side.toLowerCase()=="left"?"scrollLeft":"scrollTop";if(!_198||!A){this.dom[prop]=_197;}else{var to=prop=="scrollLeft"?[_197,this.dom.scrollTop]:[this.dom.scrollLeft,_197];this.anim({scroll:{"to":to}},this.preanim(arguments,2),"scroll");}return this;},scroll:function(_19b,_19c,_19d){if(!this.isScrollable()){return;}var el=this.dom;var l=el.scrollLeft,t=el.scrollTop;var w=el.scrollWidth,h=el.scrollHeight;var cw=el.clientWidth,ch=el.clientHeight;_19b=_19b.toLowerCase();var _1a5=false;var a=this.preanim(arguments,2);switch(_19b){case "l":case "left":if(w-l>cw){var v=Math.min(l+_19c,w-cw);this.scrollTo("left",v,a);_1a5=true;}break;case "r":case "right":if(l>0){var v=Math.max(l-_19c,0);this.scrollTo("left",v,a);_1a5=true;}break;case "t":case "top":case "up":if(t>0){var v=Math.max(t-_19c,0);this.scrollTo("top",v,a);_1a5=true;}break;case "b":case "bottom":case "down":if(h-t>ch){var v=Math.min(t+_19c,h-ch);this.scrollTo("top",v,a);_1a5=true;}break;}return _1a5;},translatePoints:function(x,y){if(typeof x=="object"||x instanceof Array){y=x[1];x=x[0];}var p=this.getStyle("position");var o=this.getXY();var l=parseInt(this.getStyle("left"),10);var t=parseInt(this.getStyle("top"),10);if(isNaN(l)){l=(p=="relative")?0:this.dom.offsetLeft;}if(isNaN(t)){t=(p=="relative")?0:this.dom.offsetTop;}return {left:(x-o[0]+l),top:(y-o[1]+t)};},getScroll:function(){var d=this.dom,doc=document;if(d==doc||d==doc.body){var l=window.pageXOffset||doc.documentElement.scrollLeft||doc.body.scrollLeft||0;var t=window.pageYOffset||doc.documentElement.scrollTop||doc.body.scrollTop||0;return {left:l,top:t};}else{return {left:d.scrollLeft,top:d.scrollTop};}},getColor:function(attr,_1b3,_1b4){var v=this.getStyle(attr);if(!v||v=="transparent"||v=="inherit"){return _1b3;}var _1b6=typeof _1b4=="undefined"?"#":_1b4;if(v.substr(0,4)=="rgb("){var rvs=v.slice(4,v.length-1).split(",");for(var i=0;i<3;i++){var h=parseInt(rvs[i]).toString(16);if(h<16){h="0"+h;}_1b6+=h;}}else{if(v.substr(0,1)=="#"){if(v.length==4){for(var i=1;i<4;i++){var c=v.charAt(i);_1b6+=c+c;}}else{if(v.length==7){_1b6+=v.substr(1);}}}}return (_1b6.length>5?_1b6.toLowerCase():_1b3);},boxWrap:function(cls){cls=cls||"x-box";var el=Ext.get(this.insertHtml("beforeBegin",String.format("<div class=\"{0}\">"+El.boxMarkup+"</div>",cls)));el.child("."+cls+"-mc").dom.appendChild(this.dom);return el;},getAttributeNS:Ext.isIE?function(ns,name){var d=this.dom;var type=typeof d[ns+":"+name];if(type!="undefined"&&type!="unknown"){return d[ns+":"+name];}return d[name];}:function(ns,name){var d=this.dom;return d.getAttributeNS(ns,name)||d.getAttribute(ns+":"+name)||d.getAttribute(name)||d[name];}};var ep=El.prototype;ep.on=ep.addListener;ep.mon=ep.addListener;ep.un=ep.removeListener;ep.autoBoxAdjust=true;ep.autoDisplayMode=true;El.unitPattern=/\d+(px|em|%|en|ex|pt|in|cm|mm|pc)$/i;El.addUnits=function(v,_1c6){if(v===""||v=="auto"){return v;}if(v===undefined){return "";}if(typeof v=="number"||!El.unitPattern.test(v)){return v+(_1c6||"px");}return v;};El.boxMarkup="<div class=\"{0}-tl\"><div class=\"{0}-tr\"><div class=\"{0}-tc\"></div></div></div><div class=\"{0}-ml\"><div class=\"{0}-mr\"><div class=\"{0}-mc\"></div></div></div><div class=\"{0}-bl\"><div class=\"{0}-br\"><div class=\"{0}-bc\"></div></div></div>";El.VISIBILITY=1;El.DISPLAY=2;El.borders={l:"border-left-width",r:"border-right-width",t:"border-top-width",b:"border-bottom-width"};El.paddings={l:"padding-left",r:"padding-right",t:"padding-top",b:"padding-bottom"};El.margins={l:"margin-left",r:"margin-right",t:"margin-top",b:"margin-bottom"};El.cache={};var _1c7;El.get=function(el){var ex,elm,id;if(!el){return null;}if(typeof el=="string"){if(!(elm=document.getElementById(el))){return null;}if(ex=El.cache[el]){ex.dom=elm;}else{ex=El.cache[el]=new El(elm);}return ex;}else{if(el.tagName){if(!(id=el.id)){id=Ext.id(el);}if(ex=El.cache[id]){ex.dom=el;}else{ex=El.cache[id]=new El(el);}return ex;}else{if(el instanceof El){if(el!=_1c7){el.dom=document.getElementById(el.id)||el.dom;El.cache[el.id]=el;}return el;}else{if(el.isComposite){return el;}else{if(el instanceof Array){return El.select(el);}else{if(el==document){if(!_1c7){var f=function(){};f.prototype=El.prototype;_1c7=new f();_1c7.dom=document;}return _1c7;}}}}}}return null;};El.uncache=function(el){for(var i=0,a=arguments,len=a.length;i<len;i++){if(a[i]){delete El.cache[a[i].id||a[i]];}}};El.Flyweight=function(dom){this.dom=dom;};El.Flyweight.prototype=El.prototype;El._flyweights={};El.fly=function(el,_1d3){_1d3=_1d3||"_global";el=Ext.getDom(el);if(!el){return null;}if(!El._flyweights[_1d3]){El._flyweights[_1d3]=new El.Flyweight();}El._flyweights[_1d3].dom=el;return El._flyweights[_1d3];};Ext.get=El.get;Ext.fly=El.fly;var _13e=Ext.isStrict?{select:1}:{input:1,select:1,textarea:1};if(Ext.isIE||Ext.isGecko){_13e["button"]=1;}Ext.EventManager.on(window,"unload",function(){delete El.cache;delete El._flyweights;});})();



Ext.enableFx=true;Ext.Fx={slideIn:function(_1,o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){_1=_1||"t";this.fixDisplay();var r=this.getFxRestore();var b=this.getBox();this.setSize(b);var _6=this.fxWrap(r.pos,o,"hidden");var st=this.dom.style;st.visibility="visible";st.position="absolute";var _8=function(){el.fxUnwrap(_6,r.pos,o);st.width=r.width;st.height=r.height;el.afterFx(o);};var a,pt={to:[b.x,b.y]},bw={to:b.width},bh={to:b.height};switch(_1.toLowerCase()){case "t":_6.setSize(b.width,0);st.left=st.bottom="0";a={height:bh};break;case "l":_6.setSize(0,b.height);st.right=st.top="0";a={width:bw};break;case "r":_6.setSize(0,b.height);_6.setX(b.right);st.left=st.top="0";a={width:bw,points:pt};break;case "b":_6.setSize(b.width,0);_6.setY(b.bottom);st.left=st.top="0";a={height:bh,points:pt};break;case "tl":_6.setSize(0,0);st.right=st.bottom="0";a={width:bw,height:bh};break;case "bl":_6.setSize(0,0);_6.setY(b.y+b.height);st.right=st.top="0";a={width:bw,height:bh,points:pt};break;case "br":_6.setSize(0,0);_6.setXY([b.right,b.bottom]);st.left=st.top="0";a={width:bw,height:bh,points:pt};break;case "tr":_6.setSize(0,0);_6.setX(b.x+b.width);st.left=st.bottom="0";a={width:bw,height:bh,points:pt};break;}this.dom.style.visibility="visible";_6.show();arguments.callee.anim=_6.fxanim(a,o,"motion",0.5,"easeOut",_8);});return this;},slideOut:function(_d,o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){_d=_d||"t";var r=this.getFxRestore();var b=this.getBox();this.setSize(b);var _12=this.fxWrap(r.pos,o,"visible");var st=this.dom.style;st.visibility="visible";st.position="absolute";_12.setSize(b);var _14=function(){if(o.useDisplay){el.setDisplayed(false);}else{el.hide();}el.fxUnwrap(_12,r.pos,o);st.width=r.width;st.height=r.height;el.afterFx(o);};var a,_16={to:0};switch(_d.toLowerCase()){case "t":st.left=st.bottom="0";a={height:_16};break;case "l":st.right=st.top="0";a={width:_16};break;case "r":st.left=st.top="0";a={width:_16,points:{to:[b.right,b.y]}};break;case "b":st.left=st.top="0";a={height:_16,points:{to:[b.x,b.bottom]}};break;case "tl":st.right=st.bottom="0";a={width:_16,height:_16};break;case "bl":st.right=st.top="0";a={width:_16,height:_16,points:{to:[b.x,b.bottom]}};break;case "br":st.left=st.top="0";a={width:_16,height:_16,points:{to:[b.x+b.width,b.bottom]}};break;case "tr":st.left=st.bottom="0";a={width:_16,height:_16,points:{to:[b.right,b.y]}};break;}arguments.callee.anim=_12.fxanim(a,o,"motion",0.5,"easeOut",_14);});return this;},puff:function(o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){this.clearOpacity();this.show();var r=this.getFxRestore();var st=this.dom.style;var _1b=function(){if(o.useDisplay){el.setDisplayed(false);}else{el.hide();}el.clearOpacity();el.setPositioning(r.pos);st.width=r.width;st.height=r.height;st.fontSize="";el.afterFx(o);};var _1c=this.getWidth();var _1d=this.getHeight();arguments.callee.anim=this.fxanim({width:{to:this.adjustWidth(_1c*2)},height:{to:this.adjustHeight(_1d*2)},points:{by:[-(_1c*0.5),-(_1d*0.5)]},opacity:{to:0},fontSize:{to:200,unit:"%"}},o,"motion",0.5,"easeOut",_1b);});return this;},switchOff:function(o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){this.clearOpacity();this.clip();var r=this.getFxRestore();var st=this.dom.style;var _22=function(){if(o.useDisplay){el.setDisplayed(false);}else{el.hide();}el.clearOpacity();el.setPositioning(r.pos);st.width=r.width;st.height=r.height;el.afterFx(o);};this.fxanim({opacity:{to:0.3}},null,null,0.1,null,function(){this.clearOpacity();(function(){this.fxanim({height:{to:1},points:{by:[0,this.getHeight()*0.5]}},o,"motion",0.3,"easeIn",_22);}).defer(100,this);});});return this;},highlight:function(_23,o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){_23=_23||"ffff9c";attr=o.attr||"backgroundColor";this.clearOpacity();this.show();var _26=this.getColor(attr);var _27=this.dom.style[attr];endColor=(o.endColor||_26)||"ffffff";var _28=function(){el.dom.style[attr]=_27;el.afterFx(o);};var a={};a[attr]={from:_23,to:endColor};arguments.callee.anim=this.fxanim(a,o,"color",1,"easeIn",_28);});return this;},frame:function(_2a,_2b,o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){_2a=_2a||"#C3DAF9";if(_2a.length==6){_2a="#"+_2a;}_2b=_2b||1;duration=o.duration||1;this.show();var b=this.getBox();var _2f=function(){var _30=this.createProxy({tag:"div",style:{visbility:"hidden",position:"absolute","z-index":"35000",border:"0px solid "+_2a}});var _31=Ext.isBorderBox?2:1;_30.animate({top:{from:b.y,to:b.y-20},left:{from:b.x,to:b.x-20},borderWidth:{from:0,to:10},opacity:{from:1,to:0},height:{from:b.height,to:(b.height+(20*_31))},width:{from:b.width,to:(b.width+(20*_31))}},duration,function(){_30.remove();});if(--_2b>0){_2f.defer((duration/2)*1000,this);}else{el.afterFx(o);}};_2f.call(this);});return this;},pause:function(_32){var el=this.getFxEl();var o={};el.queueFx(o,function(){setTimeout(function(){el.afterFx(o);},_32*1000);});return this;},fadeIn:function(o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){this.setOpacity(0);this.fixDisplay();this.dom.style.visibility="visible";var to=o.endOpacity||1;arguments.callee.anim=this.fxanim({opacity:{to:to}},o,null,0.5,"easeOut",function(){if(to==1){this.clearOpacity();}el.afterFx(o);});});return this;},fadeOut:function(o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){arguments.callee.anim=this.fxanim({opacity:{to:o.endOpacity||0}},o,null,0.5,"easeOut",function(){if(this.visibilityMode==Ext.Element.DISPLAY||o.useDisplay){this.dom.style.display="none";}else{this.dom.style.visibility="hidden";}this.clearOpacity();el.afterFx(o);});});return this;},scale:function(w,h,o){this.shift(Ext.apply({},o,{width:w,height:h}));return this;},shift:function(o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){var a={},w=o.width,h=o.height,x=o.x,y=o.y,op=o.opacity;if(w!==undefined){a.width={to:this.adjustWidth(w)};}if(h!==undefined){a.height={to:this.adjustHeight(h)};}if(x!==undefined||y!==undefined){a.points={to:[x!==undefined?x:this.getX(),y!==undefined?y:this.getY()]};}if(op!==undefined){a.opacity={to:op};}if(o.xy!==undefined){a.points={to:o.xy};}arguments.callee.anim=this.fxanim(a,o,"motion",0.35,"easeOut",function(){el.afterFx(o);});});return this;},ghost:function(_45,o){var el=this.getFxEl();o=o||{};el.queueFx(o,function(){_45=_45||"b";var r=this.getFxRestore();var w=this.getWidth(),h=this.getHeight();var st=this.dom.style;var _4c=function(){if(o.useDisplay){el.setDisplayed(false);}else{el.hide();}el.clearOpacity();el.setPositioning(r.pos);st.width=r.width;st.height=r.height;el.afterFx(o);};var a={opacity:{to:0},points:{}},pt=a.points;switch(_45.toLowerCase()){case "t":pt.by=[0,-h];break;case "l":pt.by=[-w,0];break;case "r":pt.by=[w,0];break;case "b":pt.by=[0,h];break;case "tl":pt.by=[-w,-h];break;case "bl":pt.by=[-w,h];break;case "br":pt.by=[w,h];break;case "tr":pt.by=[w,-h];break;}arguments.callee.anim=this.fxanim(a,o,"motion",0.5,"easeOut",_4c);});return this;},syncFx:function(){this.fxDefaults=Ext.apply(this.fxDefaults||{},{block:false,concurrent:true,stopFx:false});return this;},sequenceFx:function(){this.fxDefaults=Ext.apply(this.fxDefaults||{},{block:false,concurrent:false,stopFx:false});return this;},nextFx:function(){var ef=this.fxQueue[0];if(ef){ef.call(this);}},hasActiveFx:function(){return this.fxQueue&&this.fxQueue[0];},stopFx:function(){if(this.hasActiveFx()){var cur=this.fxQueue[0];if(cur&&cur.anim&&cur.anim.isAnimated()){this.fxQueue=[cur];cur.anim.stop(true);}}return this;},beforeFx:function(o){if(this.hasActiveFx()&&!o.concurrent){if(o.stopFx){this.stopFx();return true;}return false;}return true;},hasFxBlock:function(){var q=this.fxQueue;return q&&q[0]&&q[0].block;},queueFx:function(o,fn){if(!this.fxQueue){this.fxQueue=[];}if(!this.hasFxBlock()){Ext.applyIf(o,this.fxDefaults);if(!o.concurrent){var run=this.beforeFx(o);fn.block=o.block;this.fxQueue.push(fn);if(run){this.nextFx();}}else{fn.call(this);}}return this;},fxWrap:function(pos,o,vis){var _59;if(!o.wrap||!(_59=Ext.get(o.wrap))){var _5a;if(o.fixPosition){_5a=this.getXY();}var div=document.createElement("div");div.style.visibility=vis;_59=Ext.get(this.dom.parentNode.insertBefore(div,this.dom));_59.setPositioning(pos);if(_59.getStyle("position")=="static"){_59.position("relative");}this.clearPositioning("auto");_59.clip();_59.dom.appendChild(this.dom);if(_5a){_59.setXY(_5a);}}return _59;},fxUnwrap:function(_5c,pos,o){this.clearPositioning();this.setPositioning(pos);if(!o.wrap){_5c.dom.parentNode.insertBefore(this.dom,_5c.dom);_5c.remove();}},getFxRestore:function(){var st=this.dom.style;return {pos:this.getPositioning(),width:st.width,height:st.height};},afterFx:function(o){if(o.afterStyle){this.applyStyles(o.afterStyle);}if(o.afterCls){this.addClass(o.afterCls);}if(o.remove===true){this.remove();}Ext.callback(o.callback,o.scope,[this]);if(!o.concurrent){this.fxQueue.shift();this.nextFx();}},getFxEl:function(){return Ext.get(this.dom);},fxanim:function(_61,opt,_63,_64,_65,cb){_63=_63||"run";opt=opt||{};var _67=Ext.lib.Anim[_63](this.dom,_61,(opt.duration||_64)||0.35,(opt.easing||_65)||"easeOut",function(){Ext.callback(cb,this);},this);opt.anim=_67;return _67;}};Ext.Fx.resize=Ext.Fx.scale;Ext.apply(Ext.Element.prototype,Ext.Fx);



Ext.CompositeElement=function(_1){this.elements=[];this.addElements(_1);};Ext.CompositeElement.prototype={isComposite:true,addElements:function(_2){if(!_2){return this;}if(typeof _2=="string"){_2=Ext.Element.selectorFunction(_2);}var _3=this.elements;var _4=_3.length-1;for(var i=0,_6=_2.length;i<_6;i++){_3[++_4]=Ext.get(_2[i],true);}return this;},invoke:function(fn,_8){var _9=this.elements;for(var i=0,_b=_9.length;i<_b;i++){Ext.Element.prototype[fn].apply(_9[i],_8);}return this;},add:function(_c){if(typeof _c=="string"){this.addElements(Ext.Element.selectorFunction(_c));}else{if(_c.length!==undefined){this.addElements(_c);}else{this.addElements([_c]);}}return this;},each:function(fn,_e){var _f=this.elements;for(var i=0,len=_f.length;i<len;i++){if(fn.call(_e||_f[i],_f[i],this,i)===false){break;}}return this;},item:function(_12){return this.elements[_12];}};(function(){Ext.CompositeElement.createCall=function(_13,_14){if(!_13[_14]){_13[_14]=function(){return this.invoke(_14,arguments);};}};for(var _15 in Ext.Element.prototype){if(typeof Ext.Element.prototype[_15]=="function"){Ext.CompositeElement.createCall(Ext.CompositeElement.prototype,_15);}}})();Ext.CompositeElementLite=function(els){Ext.CompositeElementLite.superclass.constructor.call(this,els);var _17=function(){};_17.prototype=Ext.Element.prototype;this.el=new Ext.Element.Flyweight();};Ext.extend(Ext.CompositeElementLite,Ext.CompositeElement,{addElements:function(els){if(els){if(els instanceof Array){this.elements=this.elements.concat(els);}else{var _19=this.elements;var _1a=_19.length-1;for(var i=0,len=els.length;i<len;i++){_19[++_1a]=els[i];}}}return this;},invoke:function(fn,_1e){var els=this.elements;var el=this.el;for(var i=0,len=els.length;i<len;i++){el.dom=els[i];Ext.Element.prototype[fn].apply(el,_1e);}return this;},item:function(_23){this.el.dom=this.elements[_23];return this.el;},addListener:function(_24,_25,_26,opt){var els=this.elements;for(var i=0,len=els.length;i<len;i++){Ext.EventManager.on(els[i],_24,_25,_26||els[i],opt);}return this;},each:function(fn,_2c){var els=this.elements;var el=this.el;for(var i=0,len=els.length;i<len;i++){el.dom=els[i];if(fn.call(_2c||el,el,this,i)===false){break;}}return this;}});Ext.CompositeElementLite.prototype.on=Ext.CompositeElementLite.prototype.addListener;if(Ext.DomQuery){Ext.Element.selectorFunction=Ext.DomQuery.select;}Ext.Element.select=function(_31,_32){var els;if(typeof _31=="string"){els=Ext.Element.selectorFunction(_31);}else{if(_31.length!==undefined){els=_31;}else{throw "Invalid selector";}}if(_32===true){return new Ext.CompositeElement(els);}else{return new Ext.CompositeElementLite(els);}};Ext.select=Ext.Element.select;



Ext.UpdateManager=function(el,_2){el=Ext.get(el);if(!_2&&el.updateManager){return el.updateManager;}this.el=el;this.defaultUrl=null;this.addEvents({"beforeupdate":true,"update":true,"failure":true});var d=Ext.UpdateManager.defaults;this.sslBlankUrl=d.sslBlankUrl;this.disableCaching=d.disableCaching;this.indicatorText=d.indicatorText;this.showLoadIndicator=d.showLoadIndicator;this.timeout=d.timeout;this.loadScripts=d.loadScripts;this.transaction=null;this.autoRefreshProcId=null;this.refreshDelegate=this.refresh.createDelegate(this);this.updateDelegate=this.update.createDelegate(this);this.formUpdateDelegate=this.formUpdate.createDelegate(this);this.successDelegate=this.processSuccess.createDelegate(this);this.failureDelegate=this.processFailure.createDelegate(this);this.renderer=new Ext.UpdateManager.BasicRenderer();Ext.UpdateManager.superclass.constructor.call(this);};Ext.extend(Ext.UpdateManager,Ext.util.Observable,{getEl:function(){return this.el;},update:function(_4,_5,_6,_7){if(this.fireEvent("beforeupdate",this.el,_4,_5)!==false){var _8=this.method;if(typeof _4=="object"){var _9=_4;_4=_9.url;_5=_5||_9.params;_6=_6||_9.callback;_7=_7||_9.discardUrl;if(_6&&_9.scope){_6=_6.createDelegate(_9.scope);}if(typeof _9.method!="undefined"){_8=_9.method;}if(typeof _9.nocache!="undefined"){this.disableCaching=_9.nocache;}if(typeof _9.text!="undefined"){this.indicatorText="<div class=\"loading-indicator\">"+_9.text+"</div>";}if(typeof _9.scripts!="undefined"){this.loadScripts=_9.scripts;}if(typeof _9.timeout!="undefined"){this.timeout=_9.timeout;}}this.showLoading();if(!_7){this.defaultUrl=_4;}if(typeof _4=="function"){_4=_4.call(this);}if(typeof _5=="function"){_5=_5();}if(_5&&typeof _5!="string"){var _a=[];for(var _b in _5){if(typeof _5[_b]!="function"){_a.push(encodeURIComponent(_b),"=",encodeURIComponent(_5[_b]),"&");}}delete _a[_a.length-1];_5=_a.join("");}var cb={success:this.successDelegate,failure:this.failureDelegate,timeout:(this.timeout*1000),argument:{"url":_4,"form":null,"callback":_6,"params":_5}};_8=_8||(_5?"POST":"GET");if(_8=="GET"){_4=this.prepareUrl(_4);}this.transaction=Ext.lib.Ajax.request(_8,_4,cb,_5);}},formUpdate:function(_d,_e,_f,_10){if(this.fireEvent("beforeupdate",this.el,_d,_e)!==false){formEl=Ext.getDom(_d);if(typeof _e=="function"){_e=_e.call(this);}if(typeof params=="function"){params=params();}_e=_e||formEl.action;var cb={success:this.successDelegate,failure:this.failureDelegate,timeout:(this.timeout*1000),argument:{"url":_e,"form":formEl,"callback":_10,"reset":_f}};var _12=false;var _13=formEl.getAttribute("enctype");if(_13&&_13.toLowerCase()=="multipart/form-data"){_12=true;cb.upload=this.successDelegate;}this.transaction=Ext.lib.Ajax.formRequest(formEl,_e,cb,null,_12,this.sslBlankUrl);this.showLoading.defer(1,this);}},refresh:function(_14){if(this.defaultUrl==null){return;}this.update(this.defaultUrl,null,_14,true);},startAutoRefresh:function(_15,url,_17,_18,_19){if(_19){this.update(url||this.defaultUrl,_17,_18,true);}if(this.autoRefreshProcId){clearInterval(this.autoRefreshProcId);}this.autoRefreshProcId=setInterval(this.update.createDelegate(this,[url||this.defaultUrl,_17,_18,true]),_15*1000);},stopAutoRefresh:function(){if(this.autoRefreshProcId){clearInterval(this.autoRefreshProcId);delete this.autoRefreshProcId;}},isAutoRefreshing:function(){return this.autoRefreshProcId?true:false;},showLoading:function(){if(this.showLoadIndicator){this.el.update(this.indicatorText);}},prepareUrl:function(url){if(this.disableCaching){var _1b="_dc="+(new Date().getTime());if(url.indexOf("?")!==-1){url+="&"+_1b;}else{url+="?"+_1b;}}return url;},processSuccess:function(_1c){this.transaction=null;if(_1c.argument.form&&_1c.argument.reset){try{_1c.argument.form.reset();}catch(e){}}if(this.loadScripts){this.renderer.render(this.el,_1c,this,this.updateComplete.createDelegate(this,[_1c]));}else{this.renderer.render(this.el,_1c,this);this.updateComplete(_1c);}},updateComplete:function(_1d){this.fireEvent("update",this.el,_1d);if(typeof _1d.argument.callback=="function"){_1d.argument.callback(this.el,true,_1d);}},processFailure:function(_1e){this.transaction=null;this.fireEvent("failure",this.el,_1e);if(typeof _1e.argument.callback=="function"){_1e.argument.callback(this.el,false,_1e);}},setRenderer:function(_1f){this.renderer=_1f;},getRenderer:function(){return this.renderer;},setDefaultUrl:function(_20){this.defaultUrl=_20;},abort:function(){if(this.transaction){Ext.lib.Ajax.abort(this.transaction);}},isUpdating:function(){if(this.transaction){return Ext.lib.Ajax.isCallInProgress(this.transaction);}return false;}});Ext.UpdateManager.defaults={timeout:30,loadScripts:false,sslBlankUrl:(Ext.SSL_SECURE_URL||"javascript:false"),disableCaching:false,showLoadIndicator:true,indicatorText:"<div class=\"loading-indicator\">Loading...</div>"};Ext.UpdateManager.updateElement=function(el,url,_23,_24){var um=Ext.get(el,true).getUpdateManager();Ext.apply(um,_24);um.update(url,_23,_24?_24.callback:null);};Ext.UpdateManager.update=Ext.UpdateManager.updateElement;Ext.UpdateManager.BasicRenderer=function(){};Ext.UpdateManager.BasicRenderer.prototype={render:function(el,_27,_28,_29){el.update(_27.responseText,_28.loadScripts,_29);}};



Ext.util.DelayedTask=function(fn,_2,_3){var id=null,d,t;var _7=function(){var _8=new Date().getTime();if(_8-t>=d){clearInterval(id);id=null;fn.apply(_2,_3||[]);}};this.delay=function(_9,_a,_b,_c){if(id&&_9!=d){this.cancel();}d=_9;t=new Date().getTime();fn=_a||fn;_2=_b||_2;_3=_c||_3;if(!id){id=setInterval(_7,d);}};this.cancel=function(){if(id){clearInterval(id);id=null;}};};



Ext.util.TaskRunner=function(_1){_1=_1||10;var _2=[],_3=[];var id=0;var _5=false;var _6=function(){_5=false;clearInterval(id);id=0;};var _7=function(){if(!_5){_5=true;id=setInterval(_8,_1);}};var _9=function(_a){_3.push(_a);if(_a.onStop){_a.onStop();}};var _8=function(){if(_3.length>0){for(var i=0,_c=_3.length;i<_c;i++){_2.remove(_3[i]);}_3=[];if(_2.length<1){_6();return;}}var _d=new Date().getTime();for(var i=0,_c=_2.length;i<_c;++i){var t=_2[i];var _f=_d-t.taskRunTime;if(t.interval<=_f){var rt=t.run.apply(t.scope||t,t.args||[++t.taskRunCount]);t.taskRunTime=_d;if(rt===false||t.taskRunCount===t.repeat){_9(t);return;}}if(t.duration&&t.duration<=(_d-t.taskStartTime)){_9(t);}}};this.start=function(_11){_2.push(_11);_11.taskStartTime=new Date().getTime();_11.taskRunTime=0;_11.taskRunCount=0;_7();return _11;};this.stop=function(_12){_9(_12);return _12;};this.stopAll=function(){_6();for(var i=0,len=_2.length;i<len;i++){if(_2[i].onStop){_2[i].onStop();}}_2=[];_3=[];};};Ext.TaskMgr=new Ext.util.TaskRunner();



Ext.util.MixedCollection=function(_1,_2){this.items=[];this.map={};this.keys=[];this.length=0;this.addEvents({"clear":true,"add":true,"replace":true,"remove":true,"sort":true});this.allowFunctions=_1===true;if(_2){this.getKey=_2;}Ext.util.MixedCollection.superclass.constructor.call(this);};Ext.extend(Ext.util.MixedCollection,Ext.util.Observable,{allowFunctions:false,add:function(_3,o){if(arguments.length==1){o=arguments[0];_3=this.getKey(o);}if(typeof _3=="undefined"||_3===null){this.length++;this.items.push(o);this.keys.push(null);}else{var _5=this.map[_3];if(_5){return this.replace(_3,o);}this.length++;this.items.push(o);this.map[_3]=o;this.keys.push(_3);}this.fireEvent("add",this.length-1,o,_3);return o;},getKey:function(o){return o.id;},replace:function(_7,o){if(arguments.length==1){o=arguments[0];_7=this.getKey(o);}var _9=this.item(_7);if(typeof _7=="undefined"||_7===null||typeof _9=="undefined"){return this.add(_7,o);}var _a=this.indexOfKey(_7);this.items[_a]=o;this.map[_7]=o;this.fireEvent("replace",_7,_9,o);return o;},addAll:function(_b){if(arguments.length>1||_b instanceof Array){var _c=arguments.length>1?arguments:_b;for(var i=0,_e=_c.length;i<_e;i++){this.add(_c[i]);}}else{for(var _f in _b){if(this.allowFunctions||typeof _b[_f]!="function"){this.add(_b[_f],_f);}}}},each:function(fn,_11){var _12=[].concat(this.items);for(var i=0,len=_12.length;i<len;i++){if(fn.call(_11||_12[i],_12[i],i,len)===false){break;}}},eachKey:function(fn,_16){for(var i=0,len=this.keys.length;i<len;i++){fn.call(_16||window,this.keys[i],this.items[i],i,len);}},find:function(fn,_1a){for(var i=0,len=this.items.length;i<len;i++){if(fn.call(_1a||window,this.items[i],this.keys[i])){return this.items[i];}}return null;},insert:function(_1d,key,o){if(arguments.length==2){o=arguments[1];key=this.getKey(o);}if(_1d>=this.length){return this.add(key,o);}this.length++;this.items.splice(_1d,0,o);if(typeof key!="undefined"&&key!=null){this.map[key]=o;}this.keys.splice(_1d,0,key);this.fireEvent("add",_1d,o,key);return o;},remove:function(o){return this.removeAt(this.indexOf(o));},removeAt:function(_21){if(_21<this.length&&_21>=0){this.length--;var o=this.items[_21];this.items.splice(_21,1);var key=this.keys[_21];if(typeof key!="undefined"){delete this.map[key];}this.keys.splice(_21,1);this.fireEvent("remove",o,key);}},removeKey:function(key){return this.removeAt(this.indexOfKey(key));},getCount:function(){return this.length;},indexOf:function(o){if(!this.items.indexOf){for(var i=0,len=this.items.length;i<len;i++){if(this.items[i]==o){return i;}}return -1;}else{return this.items.indexOf(o);}},indexOfKey:function(key){if(!this.keys.indexOf){for(var i=0,len=this.keys.length;i<len;i++){if(this.keys[i]==key){return i;}}return -1;}else{return this.keys.indexOf(key);}},item:function(key){var _2c=typeof this.map[key]!="undefined"?this.map[key]:this.items[key];return typeof _2c!="function"||this.allowFunctions?_2c:null;},itemAt:function(_2d){return this.items[_2d];},key:function(key){return this.map[key];},contains:function(o){return this.indexOf(o)!=-1;},containsKey:function(key){return typeof this.map[key]!="undefined";},clear:function(){this.length=0;this.items=[];this.keys=[];this.map={};this.fireEvent("clear");},first:function(){return this.items[0];},last:function(){return this.items[this.length-1];},_sort:function(_31,dir,fn){var dsc=String(dir).toUpperCase()=="DESC"?-1:1;fn=fn||function(a,b){return a-b;};var c=[],k=this.keys,_39=this.items;for(var i=0,len=_39.length;i<len;i++){c[c.length]={key:k[i],value:_39[i],index:i};}c.sort(function(a,b){var v=fn(a[_31],b[_31])*dsc;if(v==0){v=(a.index<b.index?-1:1);}return v;});for(var i=0,len=c.length;i<len;i++){_39[i]=c[i].value;k[i]=c[i].key;}this.fireEvent("sort",this);},sort:function(dir,fn){this._sort("value",dir,fn);},keySort:function(dir,fn){this._sort("key",dir,fn||function(a,b){return String(a).toUpperCase()-String(b).toUpperCase();});},getRange:function(_45,end){var _47=this.items;if(_47.length<1){return [];}_45=_45||0;end=Math.min(typeof end=="undefined"?this.length-1:end,this.length-1);var r=[];if(_45<=end){for(var i=_45;i<=end;i++){r[r.length]=_47[i];}}else{for(var i=_45;i>=end;i--){r[r.length]=_47[i];}}return r;},filter:function(_4a,_4b){if(!_4b.exec){_4b=String(_4b);if(_4b.length==0){return this.clone();}_4b=new RegExp("^"+Ext.escapeRe(_4b),"i");}return this.filterBy(function(o){return o&&_4b.test(o[_4a]);});},filterBy:function(fn,_4e){var r=new Ext.util.MixedCollection();r.getKey=this.getKey;var k=this.keys,it=this.items;for(var i=0,len=it.length;i<len;i++){if(fn.call(_4e||this,it[i],k[i])){r.add(k[i],it[i]);}}return r;},clone:function(){var r=new Ext.util.MixedCollection();var k=this.keys,it=this.items;for(var i=0,len=it.length;i<len;i++){r.add(k[i],it[i]);}r.getKey=this.getKey;return r;}});Ext.util.MixedCollection.prototype.get=Ext.util.MixedCollection.prototype.item;



Ext.util.JSON=new (function(){var _1={}.hasOwnProperty?true:false;var _2=function(n){return n<10?"0"+n:n;};var m={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r","\"":"\\\"","\\":"\\\\"};var _5=function(s){if(/["\\\x00-\x1f]/.test(s)){return "\""+s.replace(/([\x00-\x1f\\"])/g,function(a,b){var c=m[b];if(c){return c;}c=b.charCodeAt();return "\\u00"+Math.floor(c/16).toString(16)+(c%16).toString(16);})+"\"";}return "\""+s+"\"";};var _a=function(o){var a=["["],b,i,l=o.length,v;for(i=0;i<l;i+=1){v=o[i];switch(typeof v){case "undefined":case "function":case "unknown":break;default:if(b){a.push(",");}a.push(v===null?"null":Ext.util.JSON.encode(v));b=true;}}a.push("]");return a.join("");};var _11=function(o){return "\""+o.getFullYear()+"-"+_2(o.getMonth()+1)+"-"+_2(o.getDate())+"T"+_2(o.getHours())+":"+_2(o.getMinutes())+":"+_2(o.getSeconds())+"\"";};this.encode=function(o){if(typeof o=="undefined"||o===null){return "null";}else{if(o instanceof Array){return _a(o);}else{if(o instanceof Date){return _11(o);}else{if(typeof o=="string"){return _5(o);}else{if(typeof o=="number"){return isFinite(o)?String(o):"null";}else{if(typeof o=="boolean"){return String(o);}else{var a=["{"],b,i,v;for(i in o){if(!_1||o.hasOwnProperty(i)){v=o[i];switch(typeof v){case "undefined":case "function":case "unknown":break;default:if(b){a.push(",");}a.push(this.encode(i),":",v===null?"null":this.encode(v));b=true;}}}a.push("}");return a.join("");}}}}}}};this.decode=function(_18){return eval("("+_18+")");};})();Ext.encode=Ext.util.JSON.encode;Ext.decode=Ext.util.JSON.decode;



Ext.util.Format=function(){var _1=/^\s+|\s+$/g;return {ellipsis:function(_2,_3){if(_2&&_2.length>_3){return _2.substr(0,_3-3)+"...";}return _2;},undef:function(_4){return typeof _4!="undefined"?_4:"";},htmlEncode:function(_5){return !_5?_5:String(_5).replace(/&/g,"&amp;").replace(/>/g,"&gt;").replace(/</g,"&lt;").replace(/"/g,"&quot;");},trim:function(_6){return String(_6).replace(_1,"");},substr:function(_7,_8,_9){return String(_7).substr(_8,_9);},lowercase:function(_a){return String(_a).toLowerCase();},uppercase:function(_b){return String(_b).toUpperCase();},capitalize:function(_c){return !_c?_c:_c.charAt(0).toUpperCase()+_c.substr(1).toLowerCase();},call:function(_d,fn){if(arguments.length>2){var _f=Array.prototype.slice.call(arguments,2);_f.unshift(_d);return eval(fn).apply(window,_f);}else{return eval(fn).call(window,_d);}},usMoney:function(v){v=(Math.round((v-0)*100))/100;v=(v==Math.floor(v))?v+".00":((v*10==Math.floor(v*10))?v+"0":v);return "$"+v;},date:function(v,_12){if(!v){return "";}if(!(v instanceof Date)){v=new Date(Date.parse(v));}return v.dateFormat(_12||"m/d/Y");},dateRenderer:function(_13){return function(v){return Ext.util.Format.date(v,_13);};},stripTagsRE:/<\/?[^>]+>/gi,stripTags:function(v){return !v?v:String(v).replace(this.stripTagsRE,"");}};}();



Ext.util.CSS=function(){var _1=null;var _2=document;var _3=/(-[a-z])/gi;var _4=function(m,a){return a.charAt(1).toUpperCase();};return {createStyleSheet:function(_7){var ss;if(Ext.isIE){ss=_2.createStyleSheet();ss.cssText=_7;}else{var _9=_2.getElementsByTagName("head")[0];var _a=_2.createElement("style");_a.setAttribute("type","text/css");try{_a.appendChild(_2.createTextNode(_7));}catch(e){_a.cssText=_7;}_9.appendChild(_a);ss=_a.styleSheet?_a.styleSheet:(_a.sheet||_2.styleSheets[_2.styleSheets.length-1]);}this.cacheStyleSheet(ss);return ss;},removeStyleSheet:function(id){var _c=_2.getElementById(id);if(_c){_c.parentNode.removeChild(_c);}},swapStyleSheet:function(id,_e){this.removeStyleSheet(id);var ss=_2.createElement("link");ss.setAttribute("rel","stylesheet");ss.setAttribute("type","text/css");ss.setAttribute("id",id);ss.setAttribute("href",_e);_2.getElementsByTagName("head")[0].appendChild(ss);},refreshCache:function(){return this.getRules(true);},cacheStyleSheet:function(ss){if(!_1){_1={};}try{var _11=ss.cssRules||ss.rules;for(var j=_11.length-1;j>=0;--j){_1[_11[j].selectorText]=_11[j];}}catch(e){}},getRules:function(_13){if(_1==null||_13){_1={};var ds=_2.styleSheets;for(var i=0,len=ds.length;i<len;i++){try{this.cacheStyleSheet(ds[i]);}catch(e){}}}return _1;},getRule:function(_17,_18){var rs=this.getRules(_18);if(!(_17 instanceof Array)){return rs[_17];}for(var i=0;i<_17.length;i++){if(rs[_17[i]]){return rs[_17[i]];}}return null;},updateRule:function(_1b,_1c,_1d){if(!(_1b instanceof Array)){var _1e=this.getRule(_1b);if(_1e){_1e.style[_1c.replace(_3,_4)]=_1d;return true;}}else{for(var i=0;i<_1b.length;i++){if(this.updateRule(_1b[i],_1c,_1d)){return true;}}}return false;}};}();



Ext.util.ClickRepeater=function(el,_2){this.el=Ext.get(el);this.el.unselectable();Ext.apply(this,_2);this.addEvents({"mousedown":true,"click":true,"mouseup":true});this.el.on("mousedown",this.handleMouseDown,this);if(this.preventDefault||this.stopDefault){this.el.on("click",function(e){if(this.preventDefault){e.preventDefault();}if(this.stopDefault){e.stopEvent();}},this);}if(this.handler){this.on("click",this.handler,this.scope||this);}Ext.util.ClickRepeater.superclass.constructor.call(this);};Ext.extend(Ext.util.ClickRepeater,Ext.util.Observable,{interval:20,delay:250,preventDefault:true,stopDefault:false,timer:0,docEl:Ext.get(document),handleMouseDown:function(){clearTimeout(this.timer);this.el.blur();if(this.pressClass){this.el.addClass(this.pressClass);}this.mousedownTime=new Date();this.docEl.on("mouseup",this.handleMouseUp,this);this.el.on("mouseout",this.handleMouseOut,this);this.fireEvent("mousedown",this);this.fireEvent("click",this);this.timer=this.click.defer(this.delay||this.interval,this);},click:function(){this.fireEvent("click",this);this.timer=this.click.defer(this.getInterval(),this);},getInterval:function(){if(!this.accelerate){return this.interval;}var _4=this.mousedownTime.getElapsed();if(_4<500){return 400;}else{if(_4<1700){return 320;}else{if(_4<2600){return 250;}else{if(_4<3500){return 180;}else{if(_4<4400){return 140;}else{if(_4<5300){return 80;}else{if(_4<6200){return 50;}else{return 10;}}}}}}}},handleMouseOut:function(){clearTimeout(this.timer);if(this.pressClass){this.el.removeClass(this.pressClass);}this.el.on("mouseover",this.handleMouseReturn,this);},handleMouseReturn:function(){this.el.un("mouseover",this.handleMouseReturn);if(this.pressClass){this.el.addClass(this.pressClass);}this.click();},handleMouseUp:function(){clearTimeout(this.timer);this.el.un("mouseover",this.handleMouseReturn);this.el.un("mouseout",this.handleMouseOut);this.docEl.un("mouseup",this.handleMouseUp);this.el.removeClass(this.pressClass);this.fireEvent("mouseup",this);}});



Ext.KeyNav=function(el,_2){this.el=Ext.get(el);Ext.apply(this,_2);if(!this.disabled){this.disabled=true;this.enable();}};Ext.KeyNav.prototype={disabled:false,defaultEventAction:"stopEvent",prepareEvent:function(e){var k=e.getKey();var h=this.keyToHandler[k];if(Ext.isSafari&&h&&k>=37&&k<=40){e.stopEvent();}},relay:function(e){var k=e.getKey();var h=this.keyToHandler[k];if(h&&this[h]){if(this.doRelay(e,this[h],h)!==true){e[this.defaultEventAction]();}}},doRelay:function(e,h,_b){return h.call(this.scope||this,e);},enter:false,left:false,right:false,up:false,down:false,tab:false,esc:false,pageUp:false,pageDown:false,del:false,home:false,end:false,keyToHandler:{37:"left",39:"right",38:"up",40:"down",33:"pageUp",34:"pageDown",46:"del",36:"home",35:"end",13:"enter",27:"esc",9:"tab"},enable:function(){if(this.disabled){if(Ext.isIE){this.el.on("keydown",this.relay,this);}else{this.el.on("keydown",this.prepareEvent,this);this.el.on("keypress",this.relay,this);}this.disabled=false;}},disable:function(){if(!this.disabled){if(Ext.isIE){this.el.un("keydown",this.relay);}else{this.el.un("keydown",this.prepareEvent);this.el.un("keypress",this.relay);}this.disabled=true;}}};



Ext.KeyMap=function(el,_2,_3){this.el=Ext.get(el);this.eventName=_3||"keydown";this.bindings=[];if(_2 instanceof Array){for(var i=0,_5=_2.length;i<_5;i++){this.addBinding(_2[i]);}}else{this.addBinding(_2);}this.keyDownDelegate=Ext.EventManager.wrap(this.handleKeyDown,this,true);this.enable();};Ext.KeyMap.prototype={stopEvent:false,addBinding:function(_6){var _7=_6.key,_8=_6.shift,_9=_6.ctrl,_a=_6.alt,fn=_6.fn,_c=_6.scope;if(typeof _7=="string"){var ks=[];var _e=_7.toUpperCase();for(var j=0,len=_e.length;j<len;j++){ks.push(_e.charCodeAt(j));}_7=ks;}var _11=_7 instanceof Array;var _12=function(e){if((!_8||e.shiftKey)&&(!_9||e.ctrlKey)&&(!_a||e.altKey)){var k=e.getKey();if(_11){for(var i=0,len=_7.length;i<len;i++){if(_7[i]==k){if(this.stopEvent){e.stopEvent();}fn.call(_c||window,k,e);return;}}}else{if(k==_7){if(this.stopEvent){e.stopEvent();}fn.call(_c||window,k,e);}}}};this.bindings.push(_12);},handleKeyDown:function(e){if(this.enabled){var b=this.bindings;for(var i=0,len=b.length;i<len;i++){b[i].call(this,e);}}},isEnabled:function(){return this.enabled;},enable:function(){if(!this.enabled){this.el.on(this.eventName,this.keyDownDelegate);this.enabled=true;}},disable:function(){if(this.enabled){this.el.removeListener(this.eventName,this.keyDownDelegate);this.enabled=false;}}};



Ext.util.TextMetrics=function(){var _1;return {measure:function(el,_3,_4){if(!_1){_1=Ext.util.TextMetrics.Instance(el,_4);}_1.bind(el);_1.setFixedWidth(_4||"auto");return _1.getSize(_3);},createInstance:function(el,_6){return Ext.util.TextMetrics.Instance(el,_6);}};}();Ext.util.TextMetrics.Instance=function(_7,_8){var ml=new Ext.Element(document.createElement("div"));document.body.appendChild(ml.dom);ml.position("absolute");ml.setLeftTop(-1000,-1000);ml.hide();if(_8){mi.setWidth(_8);}var _a={getSize:function(_b){ml.update(_b);var s=ml.getSize();ml.update("");return s;},bind:function(el){ml.setStyle(Ext.fly(el).getStyles("font-size","font-style","font-weight","font-family","line-height"));},setFixedWidth:function(_e){ml.setWidth(_e);},getWidth:function(_f){ml.dom.style.width="auto";return this.getSize(_f).width;},getHeight:function(_10){return this.getSize(_10).height;}};_a.bind(_7);return _a;};Ext.Element.measureText=Ext.util.TextMetrics.measure;



(function(){var _1=Ext.EventManager;var _2=Ext.lib.Dom;Ext.dd.DragDrop=function(id,_4,_5){if(id){this.init(id,_4,_5);}};Ext.dd.DragDrop.prototype={id:null,config:null,dragElId:null,handleElId:null,invalidHandleTypes:null,invalidHandleIds:null,invalidHandleClasses:null,startPageX:0,startPageY:0,groups:null,locked:false,lock:function(){this.locked=true;},unlock:function(){this.locked=false;},isTarget:true,padding:null,_domRef:null,__ygDragDrop:true,constrainX:false,constrainY:false,minX:0,maxX:0,minY:0,maxY:0,maintainOffset:false,xTicks:null,yTicks:null,primaryButtonOnly:true,available:false,hasOuterHandles:false,b4StartDrag:function(x,y){},startDrag:function(x,y){},b4Drag:function(e){},onDrag:function(e){},onDragEnter:function(e,id){},b4DragOver:function(e){},onDragOver:function(e,id){},b4DragOut:function(e){},onDragOut:function(e,id){},b4DragDrop:function(e){},onDragDrop:function(e,id){},onInvalidDrop:function(e){},b4EndDrag:function(e){},endDrag:function(e){},b4MouseDown:function(e){},onMouseDown:function(e){},onMouseUp:function(e){},onAvailable:function(){},defaultPadding:{left:0,right:0,top:0,bottom:0},constrainTo:function(_1d,pad,_1f){if(typeof pad=="number"){pad={left:pad,right:pad,top:pad,bottom:pad};}pad=pad||this.defaultPadding;var b=Ext.get(this.getEl()).getBox();var ce=Ext.get(_1d);var s=ce.getScroll();var c,cd=ce.dom;if(cd==document.body){c={x:s.left,y:s.top,width:Ext.lib.Dom.getViewWidth(),height:Ext.lib.Dom.getViewHeight()};}else{xy=ce.getXY();c={x:xy[0]+s.left,y:xy[1]+s.top,width:cd.clientWidth,height:cd.clientHeight};}var _25=b.y-c.y;var _26=b.x-c.x;this.resetConstraints();this.setXConstraint(_26-(pad.left||0),c.width-_26-b.width-(pad.right||0));this.setYConstraint(_25-(pad.top||0),c.height-_25-b.height-(pad.bottom||0));},getEl:function(){if(!this._domRef){this._domRef=Ext.getDom(this.id);}return this._domRef;},getDragEl:function(){return Ext.getDom(this.dragElId);},init:function(id,_28,_29){this.initTarget(id,_28,_29);_1.on(this.id,"mousedown",this.handleMouseDown,this);},initTarget:function(id,_2b,_2c){this.config=_2c||{};this.DDM=Ext.dd.DDM;this.groups={};if(typeof id!=="string"){id=Ext.id(id);}this.id=id;this.addToGroup((_2b)?_2b:"default");this.handleElId=id;this.setDragElId(id);this.invalidHandleTypes={A:"A"};this.invalidHandleIds={};this.invalidHandleClasses=[];this.applyConfig();this.handleOnAvailable();},applyConfig:function(){this.padding=this.config.padding||[0,0,0,0];this.isTarget=(this.config.isTarget!==false);this.maintainOffset=(this.config.maintainOffset);this.primaryButtonOnly=(this.config.primaryButtonOnly!==false);},handleOnAvailable:function(){this.available=true;this.resetConstraints();this.onAvailable();},setPadding:function(_2d,_2e,_2f,_30){if(!_2e&&0!==_2e){this.padding=[_2d,_2d,_2d,_2d];}else{if(!_2f&&0!==_2f){this.padding=[_2d,_2e,_2d,_2e];}else{this.padding=[_2d,_2e,_2f,_30];}}},setInitPosition:function(_31,_32){var el=this.getEl();if(!this.DDM.verifyEl(el)){return;}var dx=_31||0;var dy=_32||0;var p=_2.getXY(el);this.initPageX=p[0]-dx;this.initPageY=p[1]-dy;this.lastPageX=p[0];this.lastPageY=p[1];this.setStartPosition(p);},setStartPosition:function(pos){var p=pos||_2.getXY(this.getEl());this.deltaSetXY=null;this.startPageX=p[0];this.startPageY=p[1];},addToGroup:function(_39){this.groups[_39]=true;this.DDM.regDragDrop(this,_39);},removeFromGroup:function(_3a){if(this.groups[_3a]){delete this.groups[_3a];}this.DDM.removeDDFromGroup(this,_3a);},setDragElId:function(id){this.dragElId=id;},setHandleElId:function(id){if(typeof id!=="string"){id=Ext.id(id);}this.handleElId=id;this.DDM.regHandle(this.id,id);},setOuterHandleElId:function(id){if(typeof id!=="string"){id=Ext.id(id);}_1.on(id,"mousedown",this.handleMouseDown,this);this.setHandleElId(id);this.hasOuterHandles=true;},unreg:function(){_1.un(this.id,"mousedown",this.handleMouseDown);this._domRef=null;this.DDM._remove(this);},isLocked:function(){return (this.DDM.isLocked()||this.locked);},handleMouseDown:function(e,oDD){if(this.primaryButtonOnly&&e.button!=0){return;}if(this.isLocked()){return;}this.DDM.refreshCache(this.groups);var pt=new Ext.lib.Point(Ext.lib.Event.getPageX(e),Ext.lib.Event.getPageY(e));if(!this.hasOuterHandles&&!this.DDM.isOverTarget(pt,this)){}else{if(this.clickValidator(e)){this.setStartPosition();this.b4MouseDown(e);this.onMouseDown(e);this.DDM.handleMouseDown(e,this);this.DDM.stopEvent(e);}else{}}},clickValidator:function(e){var _42=Ext.lib.Event.getTarget(e);return (this.isValidHandleChild(_42)&&(this.id==this.handleElId||this.DDM.handleWasClicked(_42,this.id)));},addInvalidHandleType:function(_43){var _44=_43.toUpperCase();this.invalidHandleTypes[_44]=_44;},addInvalidHandleId:function(id){if(typeof id!=="string"){id=Ext.id(id);}this.invalidHandleIds[id]=id;},addInvalidHandleClass:function(_46){this.invalidHandleClasses.push(_46);},removeInvalidHandleType:function(_47){var _48=_47.toUpperCase();delete this.invalidHandleTypes[_48];},removeInvalidHandleId:function(id){if(typeof id!=="string"){id=Ext.id(id);}delete this.invalidHandleIds[id];},removeInvalidHandleClass:function(_4a){for(var i=0,len=this.invalidHandleClasses.length;i<len;++i){if(this.invalidHandleClasses[i]==_4a){delete this.invalidHandleClasses[i];}}},isValidHandleChild:function(_4d){var _4e=true;var _4f;try{_4f=_4d.nodeName.toUpperCase();}catch(e){_4f=_4d.nodeName;}_4e=_4e&&!this.invalidHandleTypes[_4f];_4e=_4e&&!this.invalidHandleIds[_4d.id];for(var i=0,len=this.invalidHandleClasses.length;_4e&&i<len;++i){_4e=!_2.hasClass(_4d,this.invalidHandleClasses[i]);}return _4e;},setXTicks:function(_52,_53){this.xTicks=[];this.xTickSize=_53;var _54={};for(var i=this.initPageX;i>=this.minX;i=i-_53){if(!_54[i]){this.xTicks[this.xTicks.length]=i;_54[i]=true;}}for(i=this.initPageX;i<=this.maxX;i=i+_53){if(!_54[i]){this.xTicks[this.xTicks.length]=i;_54[i]=true;}}this.xTicks.sort(this.DDM.numericSort);},setYTicks:function(_56,_57){this.yTicks=[];this.yTickSize=_57;var _58={};for(var i=this.initPageY;i>=this.minY;i=i-_57){if(!_58[i]){this.yTicks[this.yTicks.length]=i;_58[i]=true;}}for(i=this.initPageY;i<=this.maxY;i=i+_57){if(!_58[i]){this.yTicks[this.yTicks.length]=i;_58[i]=true;}}this.yTicks.sort(this.DDM.numericSort);},setXConstraint:function(_5a,_5b,_5c){this.leftConstraint=_5a;this.rightConstraint=_5b;this.minX=this.initPageX-_5a;this.maxX=this.initPageX+_5b;if(_5c){this.setXTicks(this.initPageX,_5c);}this.constrainX=true;},clearConstraints:function(){this.constrainX=false;this.constrainY=false;this.clearTicks();},clearTicks:function(){this.xTicks=null;this.yTicks=null;this.xTickSize=0;this.yTickSize=0;},setYConstraint:function(iUp,_5e,_5f){this.topConstraint=iUp;this.bottomConstraint=_5e;this.minY=this.initPageY-iUp;this.maxY=this.initPageY+_5e;if(_5f){this.setYTicks(this.initPageY,_5f);}this.constrainY=true;},resetConstraints:function(){if(this.initPageX||this.initPageX===0){var dx=(this.maintainOffset)?this.lastPageX-this.initPageX:0;var dy=(this.maintainOffset)?this.lastPageY-this.initPageY:0;this.setInitPosition(dx,dy);}else{this.setInitPosition();}if(this.constrainX){this.setXConstraint(this.leftConstraint,this.rightConstraint,this.xTickSize);}if(this.constrainY){this.setYConstraint(this.topConstraint,this.bottomConstraint,this.yTickSize);}},getTick:function(val,_63){if(!_63){return val;}else{if(_63[0]>=val){return _63[0];}else{for(var i=0,len=_63.length;i<len;++i){var _66=i+1;if(_63[_66]&&_63[_66]>=val){var _67=val-_63[i];var _68=_63[_66]-val;return (_68>_67)?_63[i]:_63[_66];}}return _63[_63.length-1];}}},toString:function(){return ("DragDrop "+this.id);}};})();if(!Ext.dd.DragDropMgr){Ext.dd.DragDropMgr=function(){var _69=Ext.EventManager;return {ids:{},handleIds:{},dragCurrent:null,dragOvers:{},deltaX:0,deltaY:0,preventDefault:true,stopPropagation:true,initalized:false,locked:false,init:function(){this.initialized=true;},POINT:0,INTERSECT:1,mode:0,_execOnAll:function(_6a,_6b){for(var i in this.ids){for(var j in this.ids[i]){var oDD=this.ids[i][j];if(!this.isTypeOfDD(oDD)){continue;}oDD[_6a].apply(oDD,_6b);}}},_onLoad:function(){this.init();_69.on(document,"mouseup",this.handleMouseUp,this,true);_69.on(window,"unload",this._onUnload,this,true);_69.on(window,"resize",this._onResize,this,true);},_onResize:function(e){this._execOnAll("resetConstraints",[]);},lock:function(){this.locked=true;},unlock:function(){this.locked=false;},isLocked:function(){return this.locked;},locationCache:{},useCache:true,clickPixelThresh:3,clickTimeThresh:350,dragThreshMet:false,clickTimeout:null,startX:0,startY:0,regDragDrop:function(oDD,_71){if(!this.initialized){this.init();}if(!this.ids[_71]){this.ids[_71]={};}this.ids[_71][oDD.id]=oDD;},removeDDFromGroup:function(oDD,_73){if(!this.ids[_73]){this.ids[_73]={};}var obj=this.ids[_73];if(obj&&obj[oDD.id]){delete obj[oDD.id];}},_remove:function(oDD){for(var g in oDD.groups){if(g&&this.ids[g][oDD.id]){delete this.ids[g][oDD.id];}}delete this.handleIds[oDD.id];},regHandle:function(_77,_78){if(!this.handleIds[_77]){this.handleIds[_77]={};}this.handleIds[_77][_78]=_78;},isDragDrop:function(id){return (this.getDDById(id))?true:false;},getRelated:function(_7a,_7b){var _7c=[];for(var i in _7a.groups){for(j in this.ids[i]){var dd=this.ids[i][j];if(!this.isTypeOfDD(dd)){continue;}if(!_7b||dd.isTarget){_7c[_7c.length]=dd;}}}return _7c;},isLegalTarget:function(oDD,_80){var _81=this.getRelated(oDD,true);for(var i=0,len=_81.length;i<len;++i){if(_81[i].id==_80.id){return true;}}return false;},isTypeOfDD:function(oDD){return (oDD&&oDD.__ygDragDrop);},isHandle:function(_85,_86){return (this.handleIds[_85]&&this.handleIds[_85][_86]);},getDDById:function(id){for(var i in this.ids){if(this.ids[i][id]){return this.ids[i][id];}}return null;},handleMouseDown:function(e,oDD){_69.on(document,"mousemove",this.handleMouseMove,this,true);this.currentTarget=Ext.lib.Event.getTarget(e);this.dragCurrent=oDD;var el=oDD.getEl();this.startX=Ext.lib.Event.getPageX(e);this.startY=Ext.lib.Event.getPageY(e);this.deltaX=this.startX-el.offsetLeft;this.deltaY=this.startY-el.offsetTop;this.dragThreshMet=false;this.clickTimeout=setTimeout(function(){var DDM=Ext.dd.DDM;DDM.startDrag(DDM.startX,DDM.startY);},this.clickTimeThresh);},startDrag:function(x,y){clearTimeout(this.clickTimeout);if(this.dragCurrent){this.dragCurrent.b4StartDrag(x,y);this.dragCurrent.startDrag(x,y);}this.dragThreshMet=true;},handleMouseUp:function(e){_69.un(document,"mousemove",this.handleMouseMove,this,true);if(!this.dragCurrent){return true;}clearTimeout(this.clickTimeout);if(this.dragThreshMet){this.fireEvents(e,true);}else{}this.stopDrag(e);this.stopEvent(e);},stopEvent:function(e){if(this.stopPropagation){e.stopPropagation();}if(this.preventDefault){e.preventDefault();}},stopDrag:function(e){if(this.dragCurrent){if(this.dragThreshMet){this.dragCurrent.b4EndDrag(e);this.dragCurrent.endDrag(e);}this.dragCurrent.onMouseUp(e);}this.dragCurrent=null;this.dragOvers={};},handleMouseMove:function(e){if(!this.dragCurrent){return true;}if(Ext.isIE&&(e.button!==0&&e.button!==1&&e.button!==2)){this.stopEvent(e);return this.handleMouseUp(e);}if(!this.dragThreshMet){var _93=Math.abs(this.startX-Ext.lib.Event.getPageX(e));var _94=Math.abs(this.startY-Ext.lib.Event.getPageY(e));if(_93>this.clickPixelThresh||_94>this.clickPixelThresh){this.startDrag(this.startX,this.startY);}}if(this.dragThreshMet){this.dragCurrent.b4Drag(e);this.dragCurrent.onDrag(e);if(!this.dragCurrent.moveOnly){this.fireEvents(e,false);}}this.stopEvent(e);return true;},fireEvents:function(e,_96){var dc=this.dragCurrent;if(!dc||dc.isLocked()){return;}var x=Ext.lib.Event.getPageX(e);var y=Ext.lib.Event.getPageY(e);var pt=new Ext.lib.Point(x,y);var _9b=[];var _9c=[];var _9d=[];var _9e=[];var _9f=[];for(var i in this.dragOvers){var ddo=this.dragOvers[i];if(!this.isTypeOfDD(ddo)){continue;}if(!this.isOverTarget(pt,ddo,this.mode)){_9c.push(ddo);}_9b[i]=true;delete this.dragOvers[i];}for(var _a2 in dc.groups){if("string"!=typeof _a2){continue;}for(i in this.ids[_a2]){var oDD=this.ids[_a2][i];if(!this.isTypeOfDD(oDD)){continue;}if(oDD.isTarget&&!oDD.isLocked()&&oDD!=dc){if(this.isOverTarget(pt,oDD,this.mode)){if(_96){_9e.push(oDD);}else{if(!_9b[oDD.id]){_9f.push(oDD);}else{_9d.push(oDD);}this.dragOvers[oDD.id]=oDD;}}}}}if(this.mode){if(_9c.length){dc.b4DragOut(e,_9c);dc.onDragOut(e,_9c);}if(_9f.length){dc.onDragEnter(e,_9f);}if(_9d.length){dc.b4DragOver(e,_9d);dc.onDragOver(e,_9d);}if(_9e.length){dc.b4DragDrop(e,_9e);dc.onDragDrop(e,_9e);}}else{var len=0;for(i=0,len=_9c.length;i<len;++i){dc.b4DragOut(e,_9c[i].id);dc.onDragOut(e,_9c[i].id);}for(i=0,len=_9f.length;i<len;++i){dc.onDragEnter(e,_9f[i].id);}for(i=0,len=_9d.length;i<len;++i){dc.b4DragOver(e,_9d[i].id);dc.onDragOver(e,_9d[i].id);}for(i=0,len=_9e.length;i<len;++i){dc.b4DragDrop(e,_9e[i].id);dc.onDragDrop(e,_9e[i].id);}}if(_96&&!_9e.length){dc.onInvalidDrop(e);}},getBestMatch:function(dds){var _a6=null;var len=dds.length;if(len==1){_a6=dds[0];}else{for(var i=0;i<len;++i){var dd=dds[i];if(dd.cursorIsOver){_a6=dd;break;}else{if(!_a6||_a6.overlap.getArea()<dd.overlap.getArea()){_a6=dd;}}}}return _a6;},refreshCache:function(_aa){for(var _ab in _aa){if("string"!=typeof _ab){continue;}for(var i in this.ids[_ab]){var oDD=this.ids[_ab][i];if(this.isTypeOfDD(oDD)){var loc=this.getLocation(oDD);if(loc){this.locationCache[oDD.id]=loc;}else{delete this.locationCache[oDD.id];}}}}},verifyEl:function(el){try{if(el){var _b0=el.offsetParent;if(_b0){return true;}}}catch(e){}return false;},getLocation:function(oDD){if(!this.isTypeOfDD(oDD)){return null;}var el=oDD.getEl(),pos,x1,x2,y1,y2,t,r,b,l;try{pos=Ext.lib.Dom.getXY(el);}catch(e){}if(!pos){return null;}x1=pos[0];x2=x1+el.offsetWidth;y1=pos[1];y2=y1+el.offsetHeight;t=y1-oDD.padding[0];r=x2+oDD.padding[1];b=y2+oDD.padding[2];l=x1-oDD.padding[3];return new Ext.lib.Region(t,r,b,l);},isOverTarget:function(pt,_bd,_be){var loc=this.locationCache[_bd.id];if(!loc||!this.useCache){loc=this.getLocation(_bd);this.locationCache[_bd.id]=loc;}if(!loc){return false;}_bd.cursorIsOver=loc.contains(pt);var dc=this.dragCurrent;if(!dc||!dc.getTargetCoord||(!_be&&!dc.constrainX&&!dc.constrainY)){return _bd.cursorIsOver;}_bd.overlap=null;var pos=dc.getTargetCoord(pt.x,pt.y);var el=dc.getDragEl();var _c3=new Ext.lib.Region(pos.y,pos.x+el.offsetWidth,pos.y+el.offsetHeight,pos.x);var _c4=_c3.intersect(loc);if(_c4){_bd.overlap=_c4;return (_be)?true:_bd.cursorIsOver;}else{return false;}},_onUnload:function(e,me){Ext.dd.DragDropMgr.unregAll();},unregAll:function(){if(this.dragCurrent){this.stopDrag();this.dragCurrent=null;}this._execOnAll("unreg",[]);for(i in this.elementCache){delete this.elementCache[i];}this.elementCache={};this.ids={};},elementCache:{},getElWrapper:function(id){var _c8=this.elementCache[id];if(!_c8||!_c8.el){_c8=this.elementCache[id]=new this.ElementWrapper(Ext.getDom(id));}return _c8;},getElement:function(id){return Ext.getDom(id);},getCss:function(id){var el=Ext.getDom(id);return (el)?el.style:null;},ElementWrapper:function(el){this.el=el||null;this.id=this.el&&el.id;this.css=this.el&&el.style;},getPosX:function(el){return Ext.lib.Dom.getX(el);},getPosY:function(el){return Ext.lib.Dom.getY(el);},swapNode:function(n1,n2){if(n1.swapNode){n1.swapNode(n2);}else{var p=n2.parentNode;var s=n2.nextSibling;if(s==n1){p.insertBefore(n1,n2);}else{if(n2==n1.nextSibling){p.insertBefore(n2,n1);}else{n1.parentNode.replaceChild(n2,n1);p.insertBefore(n1,s);}}}},getScroll:function(){var t,l,dde=document.documentElement,db=document.body;if(dde&&(dde.scrollTop||dde.scrollLeft)){t=dde.scrollTop;l=dde.scrollLeft;}else{if(db){t=db.scrollTop;l=db.scrollLeft;}else{}}return {top:t,left:l};},getStyle:function(el,_d8){return Ext.fly(el).getStyle(_d8);},getScrollTop:function(){return this.getScroll().top;},getScrollLeft:function(){return this.getScroll().left;},moveToEl:function(_d9,_da){var _db=Ext.lib.Dom.getXY(_da);Ext.lib.Dom.setXY(_d9,_db);},numericSort:function(a,b){return (a-b);},_timeoutCount:0,_addListeners:function(){var DDM=Ext.dd.DDM;if(Ext.lib.Event&&document){DDM._onLoad();}else{if(DDM._timeoutCount>2000){}else{setTimeout(DDM._addListeners,10);if(document&&document.body){DDM._timeoutCount+=1;}}}},handleWasClicked:function(_df,id){if(this.isHandle(id,_df.id)){return true;}else{var p=_df.parentNode;while(p){if(this.isHandle(id,p.id)){return true;}else{p=p.parentNode;}}}return false;}};}();Ext.dd.DDM=Ext.dd.DragDropMgr;Ext.dd.DDM._addListeners();}Ext.dd.DD=function(id,_e3,_e4){if(id){this.init(id,_e3,_e4);}};Ext.extend(Ext.dd.DD,Ext.dd.DragDrop,{scroll:true,autoOffset:function(_e5,_e6){var x=_e5-this.startPageX;var y=_e6-this.startPageY;this.setDelta(x,y);},setDelta:function(_e9,_ea){this.deltaX=_e9;this.deltaY=_ea;},setDragElPos:function(_eb,_ec){var el=this.getDragEl();this.alignElWithMouse(el,_eb,_ec);},alignElWithMouse:function(el,_ef,_f0){var _f1=this.getTargetCoord(_ef,_f0);var fly=el.dom?el:Ext.fly(el);if(!this.deltaSetXY){var _f3=[_f1.x,_f1.y];fly.setXY(_f3);var _f4=fly.getLeft(true);var _f5=fly.getTop(true);this.deltaSetXY=[_f4-_f1.x,_f5-_f1.y];}else{fly.setLeftTop(_f1.x+this.deltaSetXY[0],_f1.y+this.deltaSetXY[1]);}this.cachePosition(_f1.x,_f1.y);this.autoScroll(_f1.x,_f1.y,el.offsetHeight,el.offsetWidth);return _f1;},cachePosition:function(_f6,_f7){if(_f6){this.lastPageX=_f6;this.lastPageY=_f7;}else{var _f8=Ext.lib.Dom.getXY(this.getEl());this.lastPageX=_f8[0];this.lastPageY=_f8[1];}},autoScroll:function(x,y,h,w){if(this.scroll){var _fd=Ext.lib.Dom.getViewWidth();var _fe=Ext.lib.Dom.getViewHeight();var st=this.DDM.getScrollTop();var sl=this.DDM.getScrollLeft();var bot=h+y;var _102=w+x;var _103=(_fd+st-y-this.deltaY);var _104=(_fe+sl-x-this.deltaX);var _105=40;var _106=(document.all)?80:30;if(bot>_fd&&_103<_105){window.scrollTo(sl,st+_106);}if(y<st&&st>0&&y-st<_105){window.scrollTo(sl,st-_106);}if(_102>_fe&&_104<_105){window.scrollTo(sl+_106,st);}if(x<sl&&sl>0&&x-sl<_105){window.scrollTo(sl-_106,st);}}},getTargetCoord:function(_107,_108){var x=_107-this.deltaX;var y=_108-this.deltaY;if(this.constrainX){if(x<this.minX){x=this.minX;}if(x>this.maxX){x=this.maxX;}}if(this.constrainY){if(y<this.minY){y=this.minY;}if(y>this.maxY){y=this.maxY;}}x=this.getTick(x,this.xTicks);y=this.getTick(y,this.yTicks);return {x:x,y:y};},applyConfig:function(){Ext.dd.DD.superclass.applyConfig.call(this);this.scroll=(this.config.scroll!==false);},b4MouseDown:function(e){this.autoOffset(Ext.lib.Event.getPageX(e),Ext.lib.Event.getPageY(e));},b4Drag:function(e){this.setDragElPos(Ext.lib.Event.getPageX(e),Ext.lib.Event.getPageY(e));},toString:function(){return ("DD "+this.id);}});Ext.dd.DDProxy=function(id,_10e,_10f){if(id){this.init(id,_10e,_10f);this.initFrame();}};Ext.dd.DDProxy.dragElId="ygddfdiv";Ext.extend(Ext.dd.DDProxy,Ext.dd.DD,{resizeFrame:true,centerFrame:false,createFrame:function(){var self=this;var body=document.body;if(!body||!body.firstChild){setTimeout(function(){self.createFrame();},50);return;}var div=this.getDragEl();if(!div){div=document.createElement("div");div.id=this.dragElId;var s=div.style;s.position="absolute";s.visibility="hidden";s.cursor="move";s.border="2px solid #aaa";s.zIndex=999;body.insertBefore(div,body.firstChild);}},initFrame:function(){this.createFrame();},applyConfig:function(){Ext.dd.DDProxy.superclass.applyConfig.call(this);this.resizeFrame=(this.config.resizeFrame!==false);this.centerFrame=(this.config.centerFrame);this.setDragElId(this.config.dragElId||Ext.dd.DDProxy.dragElId);},showFrame:function(_114,_115){var el=this.getEl();var _117=this.getDragEl();var s=_117.style;this._resizeProxy();if(this.centerFrame){this.setDelta(Math.round(parseInt(s.width,10)/2),Math.round(parseInt(s.height,10)/2));}this.setDragElPos(_114,_115);Ext.fly(_117).show();},_resizeProxy:function(){if(this.resizeFrame){var el=this.getEl();Ext.fly(this.getDragEl()).setSize(el.offsetWidth,el.offsetHeight);}},b4MouseDown:function(e){var x=Ext.lib.Event.getPageX(e);var y=Ext.lib.Event.getPageY(e);this.autoOffset(x,y);this.setDragElPos(x,y);},b4StartDrag:function(x,y){this.showFrame(x,y);},b4EndDrag:function(e){Ext.fly(this.getDragEl()).hide();},endDrag:function(e){var lel=this.getEl();var del=this.getDragEl();del.style.visibility="";this.beforeMove();lel.style.visibility="hidden";Ext.dd.DDM.moveToEl(lel,del);del.style.visibility="hidden";lel.style.visibility="";this.afterDrag();},beforeMove:function(){},afterDrag:function(){},toString:function(){return ("DDProxy "+this.id);}});Ext.dd.DDTarget=function(id,_124,_125){if(id){this.initTarget(id,_124,_125);}};Ext.extend(Ext.dd.DDTarget,Ext.dd.DragDrop,{toString:function(){return ("DDTarget "+this.id);}});



Ext.dd.ScrollManager=function(){var _1=Ext.dd.DragDropMgr;var _2={};var _3=null;var _4={};var _5=function(e){_3=null;_7();};var _8=function(){if(_1.dragCurrent){_1.refreshCache(_1.dragCurrent.groups);}};var _9=function(){if(_1.dragCurrent){var _a=Ext.dd.ScrollManager;if(!_a.animate){if(_4.el.scroll(_4.dir,_a.increment)){_8();}}else{_4.el.scroll(_4.dir,_a.increment,true,_a.animDuration,_8);}}};var _7=function(){if(_4.id){clearInterval(_4.id);}_4.id=0;_4.el=null;_4.dir="";};var _b=function(el,_d){_7();_4.el=el;_4.dir=_d;_4.id=setInterval(_9,Ext.dd.ScrollManager.frequency);};var _e=function(e,_10){if(_10||!_1.dragCurrent){return;}var dds=Ext.dd.ScrollManager;if(!_3||_3!=_1.dragCurrent){_3=_1.dragCurrent;dds.refreshCache();}var xy=Ext.lib.Event.getXY(e);var pt=new Ext.lib.Point(xy[0],xy[1]);for(var id in _2){var el=_2[id],r=el._region;if(r.contains(pt)&&el.isScrollable()){if(r.bottom-pt.y<=dds.thresh){if(_4.el!=el){_b(el,"down");}return;}else{if(r.right-pt.x<=dds.thresh){if(_4.el!=el){_b(el,"left");}return;}else{if(pt.y-r.top<=dds.thresh){if(_4.el!=el){_b(el,"up");}return;}else{if(pt.x-r.left<=dds.thresh){if(_4.el!=el){_b(el,"right");}return;}}}}}}_7();};_1.fireEvents=_1.fireEvents.createSequence(_e,_1);_1.stopDrag=_1.stopDrag.createSequence(_5,_1);return {register:function(el){if(el instanceof Array){for(var i=0,len=el.length;i<len;i++){this.register(el[i]);}}else{el=Ext.get(el);_2[el.id]=el;}},unregister:function(el){if(el instanceof Array){for(var i=0,len=el.length;i<len;i++){this.unregister(el[i]);}}else{el=Ext.get(el);delete _2[el.id];}},thresh:25,increment:100,frequency:500,animate:true,animDuration:0.4,refreshCache:function(){for(var id in _2){if(typeof _2[id]=="object"){_2[id]._region=_2[id].getRegion();}}}};}();



Ext.dd.Registry=function(){var _1={};var _2={};var _3=0;var _4=function(el,_6){if(typeof el=="string"){return el;}var id=el.id;if(!id&&_6!==false){id="extdd-"+(++_3);el.id=id;}return id;};return {register:function(el,_9){_9=_9||{};if(typeof el=="string"){el=document.getElementById(el);}_9.ddel=el;_1[_4(el)]=_9;if(_9.isHandle!==false){_2[_9.ddel.id]=_9;}if(_9.handles){var hs=_9.handles;for(var i=0,_c=hs.length;i<_c;i++){_2[_4(hs[i])]=_9;}}},unregister:function(el){var id=_4(el,false);var _f=_1[id];if(_f){delete _1[id];if(_f.handles){var hs=_f.handles;for(var i=0,len=hs.length;i<len;i++){delete _2[_4(hs[i],false)];}}}},getHandle:function(id){if(typeof id!="string"){id=id.id;}return _2[id];},getHandleFromEvent:function(e){var t=Ext.lib.Event.getTarget(e);return t?_2[t.id]:null;},getTarget:function(id){if(typeof id!="string"){id=id.id;}return _1[id];},getTargetFromEvent:function(e){var t=Ext.lib.Event.getTarget(e);return t?_1[t.id]||_2[t.id]:null;}};}();



Ext.dd.StatusProxy=function(_1){Ext.apply(this,_1);this.id=this.id||Ext.id();this.el=new Ext.Layer({dh:{id:this.id,tag:"div",cls:"x-dd-drag-proxy "+this.dropNotAllowed,children:[{tag:"div",cls:"x-dd-drop-icon"},{tag:"div",cls:"x-dd-drag-ghost"}]},shadow:!_1||_1.shadow!==false});this.ghost=Ext.get(this.el.dom.childNodes[1]);this.dropStatus=this.dropNotAllowed;};Ext.dd.StatusProxy.prototype={dropAllowed:"x-dd-drop-ok",dropNotAllowed:"x-dd-drop-nodrop",setStatus:function(_2){_2=_2||this.dropNotAllowed;if(this.dropStatus!=_2){this.el.replaceClass(this.dropStatus,_2);this.dropStatus=_2;}},reset:function(_3){this.el.dom.className="x-dd-drag-proxy "+this.dropNotAllowed;this.dropStatus=this.dropNotAllowed;if(_3){this.ghost.update("");}},update:function(_4){if(typeof _4=="string"){this.ghost.update(_4);}else{this.ghost.update("");_4.style.margin="0";this.ghost.dom.appendChild(_4);}},getEl:function(){return this.el;},getGhost:function(){return this.ghost;},hide:function(_5){this.el.hide();if(_5){this.reset(true);}},stop:function(){if(this.anim&&this.anim.isAnimated&&this.anim.isAnimated()){this.anim.stop();}},show:function(){this.el.show();},sync:function(){this.el.sync();},repair:function(xy,_7,_8){this.callback=_7;this.scope=_8;if(xy&&this.animRepair!==false){this.el.addClass("x-dd-drag-repair");this.el.hideUnders(true);this.anim=this.el.shift({duration:this.repairDuration||0.5,easing:"easeOut",xy:xy,stopFx:true,callback:this.afterRepair,scope:this});}else{this.afterRepair();}},afterRepair:function(){this.hide(true);if(typeof this.callback=="function"){this.callback.call(this.scope||this);}this.callback==null;this.scope==null;}};



Ext.dd.DragSource=function(el,_2){this.el=Ext.get(el);this.dragData={};Ext.apply(this,_2);if(!this.proxy){this.proxy=new Ext.dd.StatusProxy();}this.el.on("mouseup",this.handleMouseUp);Ext.dd.DragSource.superclass.constructor.call(this,this.el.dom,this.ddGroup||this.group,{dragElId:this.proxy.id,resizeFrame:false,isTarget:false,scroll:this.scroll===true});this.dragging=false;};Ext.extend(Ext.dd.DragSource,Ext.dd.DDProxy,{dropAllowed:"x-dd-drop-ok",dropNotAllowed:"x-dd-drop-nodrop",getDragData:function(e){return this.dragData;},onDragEnter:function(e,id){var _6=Ext.dd.DragDropMgr.getDDById(id);this.cachedTarget=_6;if(this.beforeDragEnter(_6,e,id)!==false){if(_6.isNotifyTarget){var _7=_6.notifyEnter(this,e,this.dragData);this.proxy.setStatus(_7);}else{this.proxy.setStatus(this.dropAllowed);}if(this.afterDragEnter){this.afterDragEnter(_6,e,id);}}},beforeDragEnter:function(_8,e,id){return true;},alignElWithMouse:function(){Ext.dd.DragSource.superclass.alignElWithMouse.apply(this,arguments);this.proxy.sync();},onDragOver:function(e,id){var _d=this.cachedTarget||Ext.dd.DragDropMgr.getDDById(id);if(this.beforeDragOver(_d,e,id)!==false){if(_d.isNotifyTarget){var _e=_d.notifyOver(this,e,this.dragData);this.proxy.setStatus(_e);}if(this.afterDragOver){this.afterDragOver(_d,e,id);}}},beforeDragOver:function(_f,e,id){return true;},onDragOut:function(e,id){var _14=this.cachedTarget||Ext.dd.DragDropMgr.getDDById(id);if(this.beforeDragOut(_14,e,id)!==false){if(_14.isNotifyTarget){_14.notifyOut(this,e,this.dragData);}this.proxy.reset();if(this.afterDragOut){this.afterDragOut(_14,e,id);}}this.cachedTarget=null;},beforeDragOut:function(_15,e,id){return true;},onDragDrop:function(e,id){var _1a=this.cachedTarget||Ext.dd.DragDropMgr.getDDById(id);if(this.beforeDragDrop(_1a,e,id)!==false){if(_1a.isNotifyTarget){if(_1a.notifyDrop(this,e,this.dragData)){this.onValidDrop(_1a,e,id);}else{this.onInvalidDrop(_1a,e,id);}}else{this.onValidDrop(_1a,e,id);}if(this.afterDragDrop){this.afterDragDrop(_1a,e,id);}}},beforeDragDrop:function(_1b,e,id){return true;},onValidDrop:function(_1e,e,id){this.hideProxy();},getRepairXY:function(e,_22){return this.el.getXY();},onInvalidDrop:function(_23,e,id){this.beforeInvalidDrop(_23,e,id);if(this.cachedTarget){if(this.cachedTarget.isNotifyTarget){this.cachedTarget.notifyOut(this,e,this.dragData);}this.cacheTarget=null;}this.proxy.repair(this.getRepairXY(e,this.dragData),this.afterRepair,this);if(this.afterInvalidDrop){this.afterInvalidDrop(e,id);}},afterRepair:function(){if(Ext.enableFx){this.el.highlight(this.hlColor||"c3daf9");}this.dragging=false;},beforeInvalidDrop:function(_26,e,id){return true;},handleMouseDown:function(e){if(this.dragging){return;}if(Ext.QuickTips){Ext.QuickTips.disable();}var _2a=this.getDragData(e);if(_2a&&this.onBeforeDrag(_2a,e)!==false){this.dragData=_2a;this.proxy.stop();Ext.dd.DragSource.superclass.handleMouseDown.apply(this,arguments);}},handleMouseUp:function(e){if(Ext.QuickTips){Ext.QuickTips.enable();}},onBeforeDrag:function(_2c,e){return true;},onStartDrag:Ext.emptyFn,startDrag:function(x,y){this.proxy.reset();this.dragging=true;this.proxy.update("");this.onInitDrag(x,y);this.proxy.show();},onInitDrag:function(x,y){var _32=this.el.dom.cloneNode(true);_32.id=Ext.id();this.proxy.update(_32);this.onStartDrag(x,y);return true;},getProxy:function(){return this.proxy;},hideProxy:function(){this.proxy.hide();this.proxy.reset(true);this.dragging=false;},triggerCacheRefresh:function(){Ext.dd.DDM.refreshCache(this.groups);},b4EndDrag:function(e){},endDrag:function(e){this.onEndDrag(this.dragData,e);},onEndDrag:function(_35,e){},autoOffset:function(x,y){this.setDelta(-12,-20);}});



Ext.dd.DropTarget=function(el,_2){this.el=Ext.get(el);Ext.apply(this,_2);if(this.containerScroll){Ext.dd.ScrollManager.register(this.el);}Ext.dd.DropTarget.superclass.constructor.call(this,this.el.dom,this.ddGroup||this.group,{isTarget:true});};Ext.extend(Ext.dd.DropTarget,Ext.dd.DDTarget,{dropAllowed:"x-dd-drop-ok",dropNotAllowed:"x-dd-drop-nodrop",isTarget:true,isNotifyTarget:true,notifyEnter:function(dd,e,_5){if(this.overClass){this.el.addClass(this.overClass);}return this.dropAllowed;},notifyOver:function(dd,e,_8){return this.dropAllowed;},notifyOut:function(dd,e,_b){if(this.overClass){this.el.removeClass(this.overClass);}},notifyDrop:function(dd,e,_e){return false;}});



Ext.dd.DragZone=function(el,_2){Ext.dd.DragZone.superclass.constructor.call(this,el,_2);if(this.containerScroll){Ext.dd.ScrollManager.register(this.el);}};Ext.extend(Ext.dd.DragZone,Ext.dd.DragSource,{getDragData:function(e){return Ext.dd.Registry.getHandleFromEvent(e);},onInitDrag:function(x,y){this.proxy.update(this.dragData.ddel.cloneNode(true));this.onStartDrag(x,y);return true;},afterRepair:function(){if(Ext.enableFx){Ext.Element.fly(this.dragData.ddel).highlight(this.hlColor||"c3daf9");}this.dragging=false;},getRepairXY:function(e){return Ext.Element.fly(this.dragData.ddel).getXY();}});



Ext.dd.DropZone=function(el,_2){Ext.dd.DropZone.superclass.constructor.call(this,el,_2);};Ext.extend(Ext.dd.DropZone,Ext.dd.DropTarget,{getTargetFromEvent:function(e){return Ext.dd.Registry.getTargetFromEvent(e);},onNodeEnter:function(n,dd,e,_7){},onNodeOver:function(n,dd,e,_b){return this.dropAllowed;},onNodeOut:function(n,dd,e,_f){},onNodeDrop:function(n,dd,e,_13){return false;},onContainerOver:function(dd,e,_16){return this.dropNotAllowed;},onContainerDrop:function(dd,e,_19){return false;},notifyEnter:function(dd,e,_1c){return this.dropNotAllowed;},notifyOver:function(dd,e,_1f){var n=this.getTargetFromEvent(e);if(!n){if(this.lastOverNode){this.onNodeOut(this.lastOverNode,dd,e,_1f);this.lastOverNode=null;}return this.onContainerOver(dd,e,_1f);}if(this.lastOverNode!=n){if(this.lastOverNode){this.onNodeOut(this.lastOverNode,dd,e,_1f);}this.onNodeEnter(n,dd,e,_1f);this.lastOverNode=n;}return this.onNodeOver(n,dd,e,_1f);},notifyOut:function(dd,e,_23){if(this.lastOverNode){this.onNodeOut(this.lastOverNode,dd,e,_23);this.lastOverNode=null;}},notifyDrop:function(dd,e,_26){if(this.lastOverNode){this.onNodeOut(this.lastOverNode,dd,e,_26);this.lastOverNode=null;}var n=this.getTargetFromEvent(e);return n?this.onNodeDrop(n,dd,e,_26):this.onContainerDrop(dd,e,_26);},triggerCacheRefresh:function(){Ext.dd.DDM.refreshCache(this.groups);}});



Ext.data.SortTypes={none:function(s){return s;},stripTagsRE:/<\/?[^>]+>/gi,asText:function(s){return String(s).replace(this.stripTagsRE,"");},asUCText:function(s){return String(s).toUpperCase().replace(this.stripTagsRE,"");},asUCString:function(s){return String(s).toUpperCase();},asDate:function(s){if(!s){return 0;}if(s instanceof Date){return s.getTime();}return Date.parse(String(s));},asFloat:function(s){var _7=parseFloat(String(s).replace(/,/g,""));if(isNaN(_7)){_7=0;}return _7;},asInt:function(s){var _9=parseInt(String(s).replace(/,/g,""));if(isNaN(_9)){_9=0;}return _9;}};



Ext.data.Record=function(_1,id){this.id=(id||id===0)?id:++Ext.data.Record.AUTO_ID;this.data=_1;};Ext.data.Record.create=function(o){var f=function(){f.superclass.constructor.apply(this,arguments);};Ext.extend(f,Ext.data.Record);var p=f.prototype;p.fields=new Ext.util.MixedCollection(false,function(_6){return _6.name;});for(var i=0,_8=o.length;i<_8;i++){p.fields.add(new Ext.data.Field(o[i]));}f.getField=function(_9){return p.fields.get(_9);};return f;};Ext.data.Record.AUTO_ID=1000;Ext.data.Record.EDIT="edit";Ext.data.Record.REJECT="reject";Ext.data.Record.COMMIT="commit";Ext.data.Record.prototype={dirty:false,editing:false,error:null,modified:null,join:function(_a){this.store=_a;},set:function(_b,_c){if(this.data[_b]==_c){return;}this.dirty=true;if(!this.modified){this.modified={};}if(typeof this.modified[_b]=="undefined"){this.modified[_b]=this.data[_b];}this.data[_b]=_c;if(!this.editing){this.store.afterEdit(this);}},get:function(_d){return this.data[_d];},beginEdit:function(){this.editing=true;this.modified={};},cancelEdit:function(){this.editing=false;delete this.modified;},endEdit:function(){this.editing=false;if(this.dirty&&this.store){this.store.afterEdit(this);}},reject:function(){var m=this.modified;for(var n in m){if(typeof m[n]!="function"){this.data[n]=m[n];}}this.dirty=false;delete this.modified;this.editing=false;if(this.store){this.store.afterReject(this);}},commit:function(){this.dirty=false;delete this.modified;this.editing=false;if(this.store){this.store.afterCommit(this);}},hasError:function(){return this.error!=null;},clearError:function(){this.error=null;}};



Ext.data.Store=function(_1){this.data=new Ext.util.MixedCollection(false);this.data.getKey=function(o){return o.id;};this.baseParams={};this.paramNames={"start":"start","limit":"limit","sort":"sort","dir":"dir"};Ext.apply(this,_1);if(this.reader&&!this.recordType){this.recordType=this.reader.recordType;}this.fields=this.recordType.prototype.fields;this.modified=[];this.addEvents({datachanged:true,add:true,remove:true,update:true,clear:true,beforeload:true,load:true,loadexception:true});if(this.proxy){this.relayEvents(this.proxy,["loadexception"]);}this.sortToggle={};Ext.data.Store.superclass.constructor.call(this);};Ext.extend(Ext.data.Store,Ext.util.Observable,{remoteSort:false,lastOptions:null,add:function(_3){_3=[].concat(_3);for(var i=0,_5=_3.length;i<_5;i++){_3[i].join(this);}var _6=this.data.length;this.data.addAll(_3);this.fireEvent("add",this,_3,_6);},remove:function(_7){var _8=this.data.indexOf(_7);this.data.removeAt(_8);this.fireEvent("remove",this,_7,_8);},removeAll:function(){this.data.clear();this.fireEvent("clear",this);},insert:function(_9,_a){_a=[].concat(_a);for(var i=0,_c=_a.length;i<_c;i++){this.data.insert(_9,_a[i]);_a[i].join(this);}this.fireEvent("add",this,_a,_9);},indexOf:function(_d){return this.data.indexOf(_d);},indexOfId:function(id){return this.data.indexOfKey(id);},getById:function(id){return this.data.key(id);},getAt:function(_10){return this.data.itemAt(_10);},getRange:function(_11,end){return this.data.getRange(_11,end);},storeOptions:function(o){o=Ext.apply({},o);delete o.callback;delete o.scope;this.lastOptions=o;},load:function(_14){_14=_14||{};if(this.fireEvent("beforeload",this,_14)!==false){this.storeOptions(_14);var p=Ext.apply(_14.params||{},this.baseParams);if(this.sortInfo&&this.remoteSort){var pn=this.paramNames;p[pn["sort"]]=this.sortInfo.field;p[pn["dir"]]=this.sortInfo.direction;}this.proxy.load(p,this.reader,this.loadRecords,this,_14);}},reload:function(_17){this.load(Ext.applyIf(_17||{},this.lastOptions));},loadRecords:function(o,_19,_1a){if(!o||_1a===false){if(_1a!==false){this.fireEvent("load",this,[],_19);}if(_19.callback){_19.callback.call(_19.scope||this,[],_19,false);}return;}var r=o.records,t=o.totalRecords||r.length;for(var i=0,len=r.length;i<len;i++){r[i].join(this);}if(!_19||_19.add!==true){this.data.clear();this.data.addAll(r);this.totalLength=t;this.applySort();this.fireEvent("datachanged",this);}else{this.totalLength=Math.max(t,this.data.length+r.length);this.data.addAll(r);}this.fireEvent("load",this,r,_19);if(_19.callback){_19.callback.call(_19.scope||this,r,_19,true);}},loadData:function(o,_20){var r=this.reader.readRecords(o);this.loadRecords(r,{add:_20},true);},getCount:function(){return this.data.length||0;},getTotalCount:function(){return this.totalLength||0;},getSortState:function(){return this.sortInfo;},applySort:function(){if(this.sortInfo&&!this.remoteSort){var s=this.sortInfo,f=s.field;var st=this.fields.get(f).sortType;var fn=function(r1,r2){var v1=st(r1.data[f]),v2=st(r2.data[f]);return v1>v2?1:(v1<v2?-1:0);};this.data.sort(s.direction,fn);if(this.snapshot&&this.snapshot!=this.data){this.snapshot.sort(s.direction,fn);}}},setDefaultSort:function(_2a,dir){this.sortInfo={field:_2a,direction:dir?dir.toUpperCase():"ASC"};},sort:function(_2c,dir){var f=this.fields.get(_2c);if(!dir){if(this.sortInfo&&this.sortInfo.field==f.name){dir=(this.sortToggle[f.name]||"ASC").toggle("ASC","DESC");}else{dir=f.sortDir;}}this.sortToggle[f.name]=dir;this.sortInfo={field:f.name,direction:dir};if(!this.remoteSort){this.applySort();this.fireEvent("datachanged",this);}else{this.load(this.lastOptions);}},each:function(fn,_30){this.data.each(fn,_30);},getModifiedRecords:function(){return this.modified;},filter:function(_31,_32){if(!_32.exec){_32=String(_32);if(_32.length==0){return this.clearFilter();}_32=new RegExp("^"+Ext.escapeRe(_32),"i");}this.filterBy(function(r){return _32.test(r.data[_31]);});},filterBy:function(fn,_35){var _36=this.snapshot||this.data;this.snapshot=_36;this.data=_36.filterBy(fn,_35);this.fireEvent("datachanged",this);},clearFilter:function(_37){if(this.snapshot&&this.snapshot!=this.data){this.data=this.snapshot;delete this.snapshot;if(_37!==true){this.fireEvent("datachanged",this);}}},afterEdit:function(_38){if(this.modified.indexOf(_38)==-1){this.modified.push(_38);}this.fireEvent("update",this,_38,Ext.data.Record.EDIT);},afterReject:function(_39){this.modified.remove(_39);this.fireEvent("update",this,_39,Ext.data.Record.REJECT);},afterCommit:function(_3a){this.modified.remove(_3a);this.fireEvent("update",this,_3a,Ext.data.Record.COMMIT);},commitChanges:function(){var m=this.modified.slice(0);this.modified=[];for(var i=0,len=m.length;i<len;i++){m[i].commit();}},rejectChanges:function(){var m=this.modified.slice(0);this.modified=[];for(var i=0,len=m.length;i<len;i++){m[i].reject();}}});



Ext.data.Connection=function(_1){Ext.apply(this,_1);this.addEvents({"beforerequest":true,"requestcomplete":true,"requestexception":true});Ext.data.Connection.superclass.constructor.call(this);};Ext.extend(Ext.data.Connection,Ext.util.Observable,{timeout:30000,request:function(_2){if(this.fireEvent("beforerequest",this,_2)!==false){var p=_2.params;if(typeof p=="object"){p=Ext.urlEncode(Ext.apply(_2.params,this.extraParams));}var cb={success:this.handleResponse,failure:this.handleFailure,scope:this,argument:{options:_2},timeout:this.timeout};var _5=_2.method||this.method||(p?"POST":"GET");var _6=_2.url||this.url;if(this.autoAbort!==false){this.abort();}if(_5=="GET"&&p){_6+=(_6.indexOf("?")!=-1?"&":"?")+p;p="";}this.transId=Ext.lib.Ajax.request(_5,_6,cb,p);}else{if(typeof _2.callback=="function"){_2.callback.call(_2.scope||window,_2,null,null);}}},isLoading:function(){return this.transId?true:false;},abort:function(){if(this.isLoading()){Ext.lib.Ajax.abort(this.transId);}},handleResponse:function(_7){this.transId=false;var _8=_7.argument.options;this.fireEvent("requestcomplete",this,_7,_8);if(typeof _8.callback=="function"){_8.callback.call(_8.scope||window,_8,true,_7);}},handleFailure:function(_9,e){this.transId=false;var _b=_9.argument.options;this.fireEvent("requestexception",this,_9,_b,e);if(typeof _b.callback=="function"){_b.callback.call(_b.scope||window,_b,false,_9);}}});



Ext.data.Field=function(_1){if(typeof _1=="string"){_1={name:_1};}Ext.apply(this,_1);if(!this.type){this.type="auto";}var st=Ext.data.SortTypes;if(typeof this.sortType=="string"){this.sortType=st[this.sortType];}if(!this.sortType){switch(this.type){case "string":this.sortType=st.asUCString;break;case "date":this.sortType=st.asDate;break;default:this.sortType=st.none;}}var _3=/[\$,%]/g;if(!this.convert){var cv,_5=this.dateFormat;switch(this.type){case "":case "auto":case undefined:cv=function(v){return v;};break;case "string":cv=function(v){return String(v);};break;case "int":cv=function(v){return v!==undefined&&v!==null&&v!==""?parseInt(String(v).replace(_3,""),10):"";};break;case "float":cv=function(v){return v!==undefined&&v!==null&&v!==""?parseFloat(String(v).replace(_3,""),10):"";};break;case "bool":case "boolean":cv=function(v){return v===true||v==="true"||v==1;};break;case "date":cv=function(v){if(!v){return "";}if(v instanceof Date){return v;}if(_5){if(_5=="timestamp"){return new Date(v*1000);}return Date.parseDate(v,_5);}var _c=Date.parse(v);return _c?new Date(_c):null;};break;}this.convert=cv;}};Ext.data.Field.prototype={dateFormat:null,defaultValue:"",mapping:null,sortType:null,sortDir:"ASC"};



Ext.data.DataReader=function(_1,_2){this.meta=_1;this.recordType=_2 instanceof Array?Ext.data.Record.create(_2):_2;};Ext.data.DataReader.prototype={};



Ext.data.DataProxy=function(){this.addEvents({beforeload:true,load:true,loadexception:true});Ext.data.DataProxy.superclass.constructor.call(this);};Ext.extend(Ext.data.DataProxy,Ext.util.Observable);



Ext.data.MemoryProxy=function(_1){Ext.data.MemoryProxy.superclass.constructor.call(this);this.data=_1;};Ext.extend(Ext.data.MemoryProxy,Ext.data.DataProxy,{load:function(_2,_3,_4,_5,_6){_2=_2||{};var _7;try{_7=_3.readRecords(this.data);}catch(e){this.fireEvent("loadexception",this,_6,null,e);_4.call(_5,null,_6,false);return;}_4.call(_5,_7,_6,true);},update:function(_8,_9){}});



Ext.data.HttpProxy=function(_1){Ext.data.HttpProxy.superclass.constructor.call(this);this.conn=_1.events?_1:new Ext.data.Connection(_1);};Ext.extend(Ext.data.HttpProxy,Ext.data.DataProxy,{getConnection:function(){return this.conn;},load:function(_2,_3,_4,_5,_6){if(this.fireEvent("beforeload",this,_2)!==false){this.conn.request({params:_2||{},request:{callback:_4,scope:_5,arg:_6},reader:_3,callback:this.loadResponse,scope:this});}else{_4.call(_5||this,null,_6,false);}},loadResponse:function(o,_8,_9){if(!_8){this.fireEvent("loadexception",this,o,_9);o.request.callback.call(o.request.scope,null,o.request.arg,false);return;}var _a;try{_a=o.reader.read(_9);}catch(e){this.fireEvent("loadexception",this,o,_9,e);o.request.callback.call(o.request.scope,null,o.request.arg,false);return;}this.fireEvent("load",this,o,o.request.arg);o.request.callback.call(o.request.scope,_a,o.request.arg,true);},update:function(_b){},updateResponse:function(_c){}});



Ext.data.ScriptTagProxy=function(_1){Ext.data.ScriptTagProxy.superclass.constructor.call(this);Ext.apply(this,_1);this.head=document.getElementsByTagName("head")[0];};Ext.data.ScriptTagProxy.TRANS_ID=1000;Ext.extend(Ext.data.ScriptTagProxy,Ext.data.DataProxy,{timeout:30000,callbackParam:"callback",nocache:true,load:function(_2,_3,_4,_5,_6){if(this.fireEvent("beforeload",this,_2)!==false){var p=Ext.urlEncode(Ext.apply(_2,this.extraParams));var _8=this.url;_8+=(_8.indexOf("?")!=-1?"&":"?")+p;if(this.nocache){_8+="&_dc="+(new Date().getTime());}var _9=++Ext.data.ScriptTagProxy.TRANS_ID;var _a={id:_9,cb:"stcCallback"+_9,scriptId:"stcScript"+_9,params:_2,arg:_6,url:_8,callback:_4,scope:_5,reader:_3};var _b=this;window[_a.cb]=function(o){_b.handleResponse(o,_a);};_8+=String.format("&{0}={1}",this.callbackParam,_a.cb);if(this.autoAbort!==false){this.abort();}_a.timeoutId=this.handleFailure.defer(this.timeout,this,[_a]);var _d=document.createElement("script");_d.setAttribute("src",_8);_d.setAttribute("type","text/javascript");_d.setAttribute("id",_a.scriptId);this.head.appendChild(_d);this.trans=_a;}else{_4.call(_5||this,null,_6,false);}},isLoading:function(){return this.trans?true:false;},abort:function(){if(this.isLoading()){this.destroyTrans(this.trans);}},destroyTrans:function(_e,_f){this.head.removeChild(document.getElementById(_e.scriptId));clearTimeout(_e.timeoutId);if(_f){window[_e.cb]=undefined;try{delete window[_e.cb];}catch(e){}}else{window[_e.cb]=function(){window[_e.cb]=undefined;try{delete window[_e.cb];}catch(e){}};}},handleResponse:function(o,_11){this.trans=false;this.destroyTrans(_11,true);var _12;try{_12=_11.reader.readRecords(o);}catch(e){this.fireEvent("loadexception",this,o,_11.arg,e);_11.callback.call(_11.scope||window,null,_11.arg,false);return;}this.fireEvent("load",this,o,_11.arg);_11.callback.call(_11.scope||window,_12,_11.arg,true);},handleFailure:function(_13){this.trans=false;this.destroyTrans(_13,false);this.fireEvent("loadexception",this,null,_13.arg);_13.callback.call(_13.scope||window,null,_13.arg,false);}});



Ext.data.JsonReader=function(_1,_2){Ext.data.JsonReader.superclass.constructor.call(this,_1,_2);};Ext.extend(Ext.data.JsonReader,Ext.data.DataReader,{read:function(_3){var _4=_3.responseText;var o=eval("("+_4+")");if(!o){throw {message:"JsonReader.read: Json object not found"};}return this.readRecords(o);},simpleAccess:function(_6,_7){return _6[_7];},getJsonAccessor:function(){var re=/[\[\.]/;return function(_9){try{return (re.test(_9))?new Function("obj","return obj."+_9):function(_a){return _a[_9];};}catch(e){}return Ext.emptyFn;};}(),readRecords:function(o){this.jsonData=o;var s=this.meta,_d=this.recordType,f=_d.prototype.fields,fi=f.items,fl=f.length;if(!this.ef){if(s.totalProperty){this.getTotal=this.getJsonAccessor(s.totalProperty);}if(s.successProperty){this.getSuccess=this.getJsonAccessor(s.successProperty);}this.getRoot=s.root?this.getJsonAccessor(s.root):function(p){return p;};if(s.id){var g=this.getJsonAccessor(s.id);this.getId=function(rec){var r=g(rec);return (r===undefined||r==="")?null:r;};}else{this.getId=function(){return null;};}this.ef=[];for(var i=0;i<fl;i++){f=fi[i];var map=(f.mapping!==undefined&&f.mapping!==null)?f.mapping:f.name;this.ef[i]=this.getJsonAccessor(map);}}var _17=this.getRoot(o),c=_17.length,_19=c,_1a=true;if(s.totalProperty){var v=parseInt(this.getTotal(o),10);if(!isNaN(v)){_19=v;}}if(s.successProperty){var v=this.getSuccess(o);if(v===false||v==="false"){_1a=false;}}var _1c=[];for(var i=0;i<c;i++){var n=_17[i];var _1e={};var id=this.getId(n);for(var j=0;j<fl;j++){f=fi[j];var v=this.ef[j](n);_1e[f.name]=f.convert((v!==undefined)?v:f.defaultValue);}var _21=new _d(_1e,id);_21.json=n;_1c[i]=_21;}return {success:_1a,records:_1c,totalRecords:_19};}});



Ext.data.ArrayReader=function(_1,_2){Ext.data.ArrayReader.superclass.constructor.call(this,_1,_2);};Ext.extend(Ext.data.ArrayReader,Ext.data.JsonReader,{readRecords:function(o){var _4=this.meta?this.meta.id:null;var _5=this.recordType,_6=_5.prototype.fields;var _7=[];var _8=o;for(var i=0;i<_8.length;i++){var n=_8[i];var _b={};var id=((_4||_4===0)&&n[_4]!==undefined&&n[_4]!==""?n[_4]:null);for(var j=0,_e=_6.length;j<_e;j++){var f=_6.items[j];var k=f.mapping!==undefined&&f.mapping!==null?f.mapping:j;var v=n[k]!==undefined?n[k]:f.defaultValue;v=f.convert(v);_b[f.name]=v;}var _12=new _5(_b,id);_12.json=n;_7[_7.length]=_12;}return {records:_7,totalRecords:_7.length};}});



Ext.data.Tree=function(_1){this.nodeHash={};this.root=null;if(_1){this.setRootNode(_1);}this.addEvents({"append":true,"remove":true,"move":true,"insert":true,"beforeappend":true,"beforeremove":true,"beforemove":true,"beforeinsert":true});Ext.data.Tree.superclass.constructor.call(this);};Ext.extend(Ext.data.Tree,Ext.util.Observable,{pathSeparator:"/",getRootNode:function(){return this.root;},setRootNode:function(_2){this.root=_2;_2.ownerTree=this;_2.isRoot=true;this.registerNode(_2);return _2;},getNodeById:function(id){return this.nodeHash[id];},registerNode:function(_4){this.nodeHash[_4.id]=_4;},unregisterNode:function(_5){delete this.nodeHash[_5.id];},toString:function(){return "[Tree"+(this.id?" "+this.id:"")+"]";}});Ext.data.Node=function(_6){this.attributes=_6||{};this.leaf=this.attributes.leaf;this.id=this.attributes.id;if(!this.id){this.id=Ext.id(null,"ynode-");this.attributes.id=this.id;}this.childNodes=[];if(!this.childNodes.indexOf){this.childNodes.indexOf=function(o){for(var i=0,_9=this.length;i<_9;i++){if(this[i]==o){return i;}}return -1;};}this.parentNode=null;this.firstChild=null;this.lastChild=null;this.previousSibling=null;this.nextSibling=null;this.addEvents({"append":true,"remove":true,"move":true,"insert":true,"beforeappend":true,"beforeremove":true,"beforemove":true,"beforeinsert":true});this.listeners=this.attributes.listeners;Ext.data.Node.superclass.constructor.call(this);};Ext.extend(Ext.data.Node,Ext.util.Observable,{fireEvent:function(_a){if(Ext.data.Node.superclass.fireEvent.apply(this,arguments)===false){return false;}var ot=this.getOwnerTree();if(ot){if(ot.fireEvent.apply(this.ownerTree,arguments)===false){return false;}}return true;},isLeaf:function(){return this.leaf===true;},setFirstChild:function(_c){this.firstChild=_c;},setLastChild:function(_d){this.lastChild=_d;},isLast:function(){return (!this.parentNode?true:this.parentNode.lastChild==this);},isFirst:function(){return (!this.parentNode?true:this.parentNode.firstChild==this);},hasChildNodes:function(){return !this.isLeaf()&&this.childNodes.length>0;},appendChild:function(_e){var _f=false;if(_e instanceof Array){_f=_e;}else{if(arguments.length>1){_f=arguments;}}if(_f){for(var i=0,len=_f.length;i<len;i++){this.appendChild(_f[i]);}}else{if(this.fireEvent("beforeappend",this.ownerTree,this,_e)===false){return false;}var _12=this.childNodes.length;var _13=_e.parentNode;if(_13){if(_e.fireEvent("beforemove",_e.getOwnerTree(),_e,_13,this,_12)===false){return false;}_13.removeChild(_e);}_12=this.childNodes.length;if(_12==0){this.setFirstChild(_e);}this.childNodes.push(_e);_e.parentNode=this;var ps=this.childNodes[_12-1];if(ps){_e.previousSibling=ps;ps.nextSibling=_e;}else{_e.previousSibling=null;}_e.nextSibling=null;this.setLastChild(_e);_e.setOwnerTree(this.getOwnerTree());this.fireEvent("append",this.ownerTree,this,_e,_12);if(_13){_e.fireEvent("move",this.ownerTree,_e,_13,this,_12);}return _e;}},removeChild:function(_15){var _16=this.childNodes.indexOf(_15);if(_16==-1){return false;}if(this.fireEvent("beforeremove",this.ownerTree,this,_15)===false){return false;}this.childNodes.splice(_16,1);if(_15.previousSibling){_15.previousSibling.nextSibling=_15.nextSibling;}if(_15.nextSibling){_15.nextSibling.previousSibling=_15.previousSibling;}if(this.firstChild==_15){this.setFirstChild(_15.nextSibling);}if(this.lastChild==_15){this.setLastChild(_15.previousSibling);}_15.setOwnerTree(null);_15.parentNode=null;_15.previousSibling=null;_15.nextSibling=null;this.fireEvent("remove",this.ownerTree,this,_15);return _15;},insertBefore:function(_17,_18){if(!_18){return this.appendChild(_17);}if(_17==_18){return false;}if(this.fireEvent("beforeinsert",this.ownerTree,this,_17,_18)===false){return false;}var _19=this.childNodes.indexOf(_18);var _1a=_17.parentNode;var _1b=_19;if(_1a==this&&this.childNodes.indexOf(_17)<_19){_1b--;}if(_1a){if(_17.fireEvent("beforemove",_17.getOwnerTree(),_17,_1a,this,_19,_18)===false){return false;}_1a.removeChild(_17);}if(_1b==0){this.setFirstChild(_17);}this.childNodes.splice(_1b,0,_17);_17.parentNode=this;var ps=this.childNodes[_1b-1];if(ps){_17.previousSibling=ps;ps.nextSibling=_17;}else{_17.previousSibling=null;}_17.nextSibling=_18;_18.previousSibling=_17;_17.setOwnerTree(this.getOwnerTree());this.fireEvent("insert",this.ownerTree,this,_17,_18);if(_1a){_17.fireEvent("move",this.ownerTree,_17,_1a,this,_1b,_18);}return _17;},item:function(_1d){return this.childNodes[_1d];},replaceChild:function(_1e,_1f){this.insertBefore(_1e,_1f);this.removeChild(_1f);return _1f;},indexOf:function(_20){return this.childNodes.indexOf(_20);},getOwnerTree:function(){if(!this.ownerTree){var p=this;while(p){if(p.ownerTree){this.ownerTree=p.ownerTree;break;}p=p.parentNode;}}return this.ownerTree;},getDepth:function(){var _22=0;var p=this;while(p.parentNode){++_22;p=p.parentNode;}return _22;},setOwnerTree:function(_24){if(_24!=this.ownerTree){if(this.ownerTree){this.ownerTree.unregisterNode(this);}this.ownerTree=_24;var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].setOwnerTree(_24);}if(_24){_24.registerNode(this);}}},getPath:function(_28){_28=_28||"id";var p=this.parentNode;var b=[this.attributes[_28]];while(p){b.unshift(p.attributes[_28]);p=p.parentNode;}var sep=this.getOwnerTree().pathSeparator;return sep+b.join(sep);},bubble:function(fn,_2d,_2e){var p=this;while(p){if(fn.call(_2d||p,_2e||p)===false){break;}p=p.parentNode;}},cascade:function(fn,_31,_32){if(fn.call(_31||this,_32||this)!==false){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].cascade(fn,_31,_32);}}},eachChild:function(fn,_37,_38){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){if(fn.call(_37||this,_38||cs[i])===false){break;}}},findChild:function(_3c,_3d){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){if(cs[i].attributes[_3c]==_3d){return cs[i];}}return null;},findChildBy:function(fn,_42){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){if(fn.call(_42||cs[i],cs[i])===true){return cs[i];}}return null;},sort:function(fn,_47){var cs=this.childNodes;var len=cs.length;if(len>0){var _4a=_47?function(){fn.apply(_47,arguments);}:fn;cs.sort(_4a);for(var i=0;i<len;i++){var n=cs[i];n.previousSibling=cs[i-1];n.nextSibling=cs[i+1];if(i==0){this.setFirstChild(n);}if(i==len-1){this.setLastChild(n);}}}},contains:function(_4d){return _4d.isAncestor(this);},isAncestor:function(_4e){var p=this.parentNode;while(p){if(p==_4e){return true;}p=p.parentNode;}return false;},toString:function(){return "[Node"+(this.id?" "+this.id:"")+"]";}});



Ext.ComponentMgr=function(){var _1=new Ext.util.MixedCollection();return {register:function(c){_1.add(c);},unregister:function(c){_1.remove(c);},get:function(id){return _1.get(id);},onAvailable:function(id,fn,_7){_1.on("add",function(_8,o){if(o.id==id){fn.call(_7||o,o);_1.un("add",fn,_7);}});}};}();Ext.Component=function(_a){_a=_a||{};if(_a.tagName||_a.dom||typeof _a=="string"){_a={el:_a,id:_a.id||_a};}this.initialConfig=_a;Ext.apply(this,_a);this.addEvents({disable:true,enable:true,beforeshow:true,show:true,beforehide:true,hide:true,beforerender:true,render:true,beforedestroy:true,destroy:true});if(!this.id){this.id="ext-comp-"+(++Ext.Component.AUTO_ID);}Ext.ComponentMgr.register(this);Ext.Component.superclass.constructor.call(this);this.initComponent();};Ext.Component.AUTO_ID=1000;Ext.extend(Ext.Component,Ext.util.Observable,{hidden:false,disabled:false,disabledClass:"x-item-disabled",rendered:false,allowDomMove:true,ctype:"Ext.Component",actionMode:"el",getActionEl:function(){return this[this.actionMode];},initComponent:Ext.emptyFn,render:function(_b,_c){if(!this.rendered&&this.fireEvent("beforerender",this)!==false){if(!_b&&this.el){this.el=Ext.get(this.el);_b=this.el.dom.parentNode;this.allowDomMove=false;}this.container=Ext.get(_b);this.rendered=true;if(_c!==undefined){if(typeof _c=="number"){_c=this.container.dom.childNodes[_c];}else{_c=Ext.getDom(_c);}}this.onRender(this.container,_c||null);if(this.cls){this.el.addClass(this.cls);delete this.cls;}if(this.style){this.el.applyStyles(this.style);delete this.style;}this.fireEvent("render",this);this.afterRender(this.container);if(this.hidden){this.hide();}if(this.disabled){this.disable();}}return this;},onRender:function(ct,_e){if(this.el){this.el=Ext.get(this.el);if(this.allowDomMove!==false){ct.dom.insertBefore(this.el.dom,_e);}}},getAutoCreate:function(){var _f=typeof this.autoCreate=="object"?this.autoCreate:Ext.apply({},this.defaultAutoCreate);if(this.id&&!_f.id){_f.id=this.id;}return _f;},afterRender:Ext.emptyFn,destroy:function(){if(this.fireEvent("beforedestroy",this)!==false){this.purgeListeners();this.beforeDestroy();if(this.rendered){this.el.removeAllListeners();this.el.remove();if(this.actionMode=="container"){this.container.remove();}}this.onDestroy();Ext.ComponentMgr.unregister(this);this.fireEvent("destroy",this);}},beforeDestroy:function(){},onDestroy:function(){},getEl:function(){return this.el;},getId:function(){return this.id;},focus:function(_10){if(this.rendered){this.el.focus();if(_10===true){this.el.dom.select();}}return this;},blur:function(){if(this.rendered){this.el.blur();}return this;},disable:function(){if(this.rendered){this.onDisable();}this.disabled=true;this.fireEvent("disable",this);return this;},onDisable:function(){this.getActionEl().addClass(this.disabledClass);this.el.dom.disabled=true;},enable:function(){if(this.rendered){this.onEnable();}this.disabled=false;this.fireEvent("enable",this);return this;},onEnable:function(){this.getActionEl().removeClass(this.disabledClass);this.el.dom.disabled=false;},setDisabled:function(_11){this[_11?"disable":"enable"]();},show:function(){if(this.fireEvent("beforeshow",this)!==false){this.hidden=false;if(this.rendered){this.onShow();}this.fireEvent("show",this);}return this;},onShow:function(){var st=this.getActionEl().dom.style;st.display="";st.visibility="visible";},hide:function(){if(this.fireEvent("beforehide",this)!==false){this.hidden=true;if(this.rendered){this.onHide();}this.fireEvent("hide",this);}return this;},onHide:function(){this.getActionEl().dom.style.display="none";},setVisible:function(_13){if(_13){this.show();}else{this.hide();}return this;},isVisible:function(){return this.getActionEl().isVisible();},cloneConfig:function(_14){_14=_14||{};var id=_14.id||Ext.id();var cfg=Ext.applyIf(_14,this.initialConfig);cfg.id=id;return new this.__extcls(cfg);}});



(function(){Ext.Layer=function(_1,_2){_1=_1||{};var dh=Ext.DomHelper;var cp=_1.parentEl,_5=cp?Ext.getDom(cp):document.body;if(_2){this.dom=Ext.getDom(_2);}if(!this.dom){var o=_1.dh||{tag:"div",cls:"x-layer"};this.dom=dh.append(_5,o);}if(_1.cls){this.addClass(_1.cls);}this.constrain=_1.constrain!==false;this.visibilityMode=Ext.Element.VISIBILITY;if(_1.id){this.id=this.dom.id=_1.id;}else{this.id=Ext.id(this.dom);}this.zindex=_1.zindex||this.getZIndex();this.position("absolute",this.zindex);if(_1.shadow){this.shadowOffset=_1.shadowOffset||4;this.shadow=new Ext.Shadow({offset:this.shadowOffset,mode:_1.shadow});}else{this.shadowOffset=0;}this.useShim=_1.shim!==false&&Ext.useShims;this.useDisplay=_1.useDisplay;this.hide();};var _7=Ext.Element.prototype;var _8=[];Ext.extend(Ext.Layer,Ext.Element,{getZIndex:function(){return this.zindex||parseInt(this.getStyle("z-index"),10)||11000;},getShim:function(){if(!this.useShim){return null;}if(this.shim){return this.shim;}var _9=_8.shift();if(!_9){_9=this.createShim();_9.enableDisplayMode("block");_9.dom.style.display="none";_9.dom.style.visibility="visible";}var pn=this.dom.parentNode;if(_9.dom.parentNode!=pn){pn.insertBefore(_9.dom,this.dom);}_9.setStyle("z-index",this.getZIndex()-2);this.shim=_9;return _9;},hideShim:function(){if(this.shim){this.shim.setDisplayed(false);_8.push(this.shim);delete this.shim;}},disableShadow:function(){if(this.shadow){this.shadowDisabled=true;this.shadow.hide();this.lastShadowOffset=this.shadowOffset;this.shadowOffset=0;}},enableShadow:function(_b){if(this.shadow){this.shadowDisabled=false;this.shadowOffset=this.lastShadowOffset;delete this.lastShadowOffset;if(_b){this.sync(true);}}},sync:function(_c){var sw=this.shadow;if(!this.updating&&this.isVisible()&&(sw||this.useShim)){var sh=this.getShim();var w=this.getWidth(),h=this.getHeight();var l=this.getLeft(true),t=this.getTop(true);if(sw&&!this.shadowDisabled){if(_c&&!sw.isVisible()){sw.show(this);}else{sw.realign(l,t,w,h);}if(sh){if(_c){sh.show();}var a=sw.adjusts,s=sh.dom.style;s.left=(Math.min(l,l+a.l))+"px";s.top=(Math.min(t,t+a.t))+"px";s.width=(w+a.w)+"px";s.height=(h+a.h)+"px";}}else{if(sh){if(_c){sh.show();}sh.setSize(w,h);sh.setLeftTop(l,t);}}}},destroy:function(){this.hideShim();if(this.shadow){this.shadow.hide();}this.removeAllListeners();var pn=this.dom.parentNode;if(pn){pn.removeChild(this.dom);}Ext.Element.uncache(this.id);},remove:function(){this.destroy();},beginUpdate:function(){this.updating=true;},endUpdate:function(){this.updating=false;this.sync(true);},hideUnders:function(_16){if(this.shadow){this.shadow.hide();}this.hideShim();},constrainXY:function(){if(this.constrain){var vw=Ext.lib.Dom.getViewWidth(),vh=Ext.lib.Dom.getViewHeight();var s=Ext.get(document).getScroll();var xy=this.getXY();var x=xy[0],y=xy[1];var w=this.dom.offsetWidth+this.shadowOffset,h=this.dom.offsetHeight+this.shadowOffset;var _1f=false;if((x+w)>vw+s.left){x=vw-w-this.shadowOffset;_1f=true;}if((y+h)>vh+s.top){y=vh-h-this.shadowOffset;_1f=true;}if(x<s.left){x=s.left;_1f=true;}if(y<s.top){y=s.top;_1f=true;}if(_1f){if(this.avoidY){var ay=this.avoidY;if(y<=ay&&(y+h)>=ay){y=ay-h-5;}}xy=[x,y];this.storeXY(xy);_7.setXY.call(this,xy);this.sync();}}},isVisible:function(){return this.visible;},showAction:function(){this.visible=true;if(this.useDisplay===true){this.setDisplayed("");}else{if(this.lastXY){_7.setXY.call(this,this.lastXY);}else{if(this.lastLT){_7.setLeftTop.call(this,this.lastLT[0],this.lastLT[1]);}}}},hideAction:function(){this.visible=false;if(this.useDisplay===true){this.setDisplayed(false);}else{this.setLeftTop(-10000,-10000);}},setVisible:function(v,a,d,c,e){if(v){this.showAction();}if(a&&v){var cb=function(){this.sync(true);if(c){c();}}.createDelegate(this);_7.setVisible.call(this,true,true,d,cb,e);}else{if(!v){this.hideUnders(true);}var cb=c;if(a){cb=function(){this.hideAction();if(c){c();}}.createDelegate(this);}_7.setVisible.call(this,v,a,d,cb,e);if(v){this.sync(true);}else{if(!a){this.hideAction();}}}},storeXY:function(xy){delete this.lastLT;this.lastXY=xy;},storeLeftTop:function(_28,top){delete this.lastXY;this.lastLT=[_28,top];},beforeFx:function(){this.beforeAction();return Ext.Layer.superclass.beforeFx.apply(this,arguments);},afterFx:function(){Ext.Layer.superclass.afterFx.apply(this,arguments);this.sync(this.isVisible());},beforeAction:function(){if(!this.updating&&this.shadow){this.shadow.hide();}},setLeft:function(_2a){this.storeLeftTop(_2a,this.getTop(true));_7.setLeft.apply(this,arguments);this.sync();},setTop:function(top){this.storeLeftTop(this.getLeft(true),top);_7.setTop.apply(this,arguments);this.sync();},setLeftTop:function(_2c,top){this.storeLeftTop(_2c,top);_7.setLeftTop.apply(this,arguments);this.sync();},setXY:function(xy,a,d,c,e){this.fixDisplay();this.beforeAction();this.storeXY(xy);var cb=this.createCB(c);_7.setXY.call(this,xy,a,d,cb,e);if(!a){cb();}},createCB:function(c){var el=this;return function(){el.constrainXY();el.sync(true);if(c){c();}};},setX:function(x,a,d,c,e){this.setXY([x,this.getY()],a,d,c,e);},setY:function(y,a,d,c,e){this.setXY([this.getX(),y],a,d,c,e);},setSize:function(w,h,a,d,c,e){this.beforeAction();var cb=this.createCB(c);_7.setSize.call(this,w,h,a,d,cb,e);if(!a){cb();}},setWidth:function(w,a,d,c,e){this.beforeAction();var cb=this.createCB(c);_7.setWidth.call(this,w,a,d,cb,e);if(!a){cb();}},setHeight:function(h,a,d,c,e){this.beforeAction();var cb=this.createCB(c);_7.setHeight.call(this,h,a,d,cb,e);if(!a){cb();}},setBounds:function(x,y,w,h,a,d,c,e){this.beforeAction();var cb=this.createCB(c);if(!a){this.storeXY([x,y]);_7.setXY.call(this,[x,y]);_7.setSize.call(this,w,h,a,d,cb,e);cb();}else{_7.setBounds.call(this,x,y,w,h,a,d,cb,e);}return this;},setZIndex:function(_5c){this.zindex=_5c;this.setStyle("z-index",_5c+2);if(this.shadow){this.shadow.setZIndex(_5c+1);}if(this.shim){this.shim.setStyle("z-index",_5c);}}});})();



Ext.Shadow=function(_1){Ext.apply(this,_1);if(typeof this.mode!="string"){this.mode=this.defaultMode;}var o=this.offset,a={h:0};switch(this.mode.toLowerCase()){case "drop":a.w=0;a.l=a.t=o;break;case "sides":a.w=(o*2);a.l=-o;a.t=o;break;case "frame":a.w=a.h=(o*2);a.l=a.t=-o;break;}this.adjusts=a;};Ext.Shadow.prototype={offset:4,defaultMode:"drop",show:function(_4){_4=Ext.get(_4);if(!this.el){this.el=Ext.Shadow.Pool.pull();if(this.el.dom.nextSibling!=_4.dom){this.el.insertBefore(_4);}}this.el.setStyle("z-index",this.zIndex||parseInt(_4.getStyle("z-index"),10)-1);if(Ext.isIE){this.el.dom.style.filter="progid:DXImageTransform.Microsoft.alpha(opacity=50) progid:DXImageTransform.Microsoft.Blur(pixelradius="+this.offset+")";}this.realign(_4.getLeft(true),_4.getTop(true),_4.getWidth(),_4.getHeight());this.el.dom.style.display="block";},isVisible:function(){return this.el?true:false;},realign:function(l,t,w,h){if(!this.el){return;}var a=this.adjusts,d=this.el.dom,s=d.style;var _c=0;if(Ext.isIE){_c=-(this.offset);}s.left=(l+a.l+_c)+"px";s.top=(t+a.t+_c)+"px";var sw=(w+a.w),sh=(h+a.h),_f=sw+"px",shs=sh+"px";if(s.width!=_f||s.height!=shs){s.width=_f;s.height=shs;if(!Ext.isIE){var cn=d.childNodes;var sww=Math.max(0,(sw-12))+"px";cn[0].childNodes[1].style.width=sww;cn[1].childNodes[1].style.width=sww;cn[2].childNodes[1].style.width=sww;cn[1].style.height=Math.max(0,(sh-12))+"px";}}},hide:function(){if(this.el){this.el.dom.style.display="none";Ext.Shadow.Pool.push(this.el);delete this.el;}},setZIndex:function(z){this.zIndex=z;if(this.el){this.el.setStyle("z-index",z);}}};Ext.Shadow.Pool=function(){var p=[];var _15=Ext.isIE?"<div class=\"x-ie-shadow\"></div>":"<div class=\"x-shadow\"><div class=\"xst\"><div class=\"xstl\"></div><div class=\"xstc\"></div><div class=\"xstr\"></div></div><div class=\"xsc\"><div class=\"xsml\"></div><div class=\"xsmc\"></div><div class=\"xsmr\"></div></div><div class=\"xsb\"><div class=\"xsbl\"></div><div class=\"xsbc\"></div><div class=\"xsbr\"></div></div></div>";return {pull:function(){var sh=p.shift();if(!sh){sh=Ext.get(Ext.DomHelper.insertHtml("beforeBegin",document.body.firstChild,_15));sh.autoBoxAdjust=false;}return sh;},push:function(sh){p.push(sh);}};}();



Ext.Editor=function(_1,_2){Ext.Editor.superclass.constructor.call(this,_2);this.field=_1;this.addEvents({"beforestartedit":true,"startedit":true,"beforecomplete":true,"complete":true,"specialkey":true});};Ext.extend(Ext.Editor,Ext.Component,{value:"",alignment:"c-c?",shadow:"frame",updateEl:false,onRender:function(ct,_4){this.el=new Ext.Layer({shadow:this.shadow,cls:"x-editor",parentEl:ct,shim:this.shim,shadowOffset:3,id:this.id});this.el.setStyle("overflow",Ext.isGecko?"auto":"hidden");this.field.render(this.el);if(Ext.isGecko){this.field.el.dom.setAttribute("autocomplete","off");}this.field.show();this.field.on("blur",this.onBlur,this);this.relayEvents(this.field,["specialkey"]);if(this.field.grow){this.field.on("autosize",this.el.sync,this.el,{delay:1});}},startEdit:function(el,_6){if(this.editing){this.completeEdit();}this.boundEl=Ext.get(el);var v=_6!==undefined?_6:this.boundEl.dom.innerHTML;if(!this.rendered){this.render(this.parentEl||document.body);}if(this.fireEvent("beforestartedit",this,this.boundEl,v)===false){return;}this.startValue=v;this.field.setValue(v);if(this.autoSize){var sz=this.boundEl.getSize();switch(this.autoSize){case "width":this.setSize(sz.width,"");break;case "height":this.setSize("",sz.height);break;default:this.setSize(sz.width,sz.height);}}this.el.alignTo(this.boundEl,this.alignment);this.editing=true;if(Ext.QuickTips){Ext.QuickTips.disable();}this.show();},setSize:function(w,h){this.field.setSize(w,h);if(this.el){this.el.sync();}},realign:function(){this.el.alignTo(this.boundEl,this.alignment);},completeEdit:function(_b){if(!this.editing){return;}var v=this.getValue();if(this.revertInvalid!==false&&!this.field.isValid()){v=this.startValue;this.cancelEdit(true);}if(String(v)==String(this.startValue)&&this.ignoreNoChange){this.editing=false;this.hide();return;}if(this.fireEvent("beforecomplete",this,v,this.startValue)!==false){this.editing=false;if(this.updateEl&&this.boundEl){this.boundEl.update(v);}if(_b!==true){this.hide();}this.fireEvent("complete",this,v,this.startValue);}},onShow:function(){this.el.show();if(this.hideEl!==false){this.boundEl.hide();}this.field.show();this.field.focus();this.fireEvent("startedit",this.boundEl,this.startValue);},cancelEdit:function(_d){if(this.editing){this.setValue(this.startValue);if(_d!==true){this.hide();}}},onBlur:function(){if(this.allowBlur!==true&&this.editing){this.completeEdit();}},onHide:function(){if(this.editing){this.completeEdit();return;}this.field.blur();if(this.field.collapse){this.field.collapse();}this.el.hide();if(this.hideEl!==false){this.boundEl.show();}if(Ext.QuickTips){Ext.QuickTips.enable();}},setValue:function(v){this.field.setValue(v);},getValue:function(){return this.field.getValue();}});



Ext.tree.TreePanel=function(el,_2){Ext.tree.TreePanel.superclass.constructor.call(this);this.el=Ext.get(el);this.el.addClass("x-tree");this.id=this.el.id;Ext.apply(this,_2);this.addEvents({"beforeload":true,"load":true,"textchange":true,"beforeexpand":true,"beforecollapse":true,"expand":true,"disabledchange":true,"collapse":true,"beforeclick":true,"click":true,"dblclick":true,"contextmenu":true,"beforechildrenrendered":true,"startdrag":true,"enddrag":true,"dragdrop":true,"beforenodedrop":true,"nodedrop":true,"nodedragover":true});if(this.singleExpand){this.on("beforeexpand",this.restrictExpand,this);}};Ext.extend(Ext.tree.TreePanel,Ext.data.Tree,{rootVisible:true,animate:Ext.enableFx,lines:true,enableDD:false,hlDrop:Ext.enableFx,restrictExpand:function(_3){var p=_3.parentNode;if(p){if(p.expandedChild&&p.expandedChild.parentNode==p){p.expandedChild.collapse();}p.expandedChild=_3;}},setRootNode:function(_5){Ext.tree.TreePanel.superclass.setRootNode.call(this,_5);if(!this.rootVisible){_5.ui=new Ext.tree.RootTreeNodeUI(_5);}return _5;},getEl:function(){return this.el;},getLoader:function(){return this.loader;},expandAll:function(){this.root.expand(true);},collapseAll:function(){this.root.collapse(true);},getSelectionModel:function(){if(!this.selModel){this.selModel=new Ext.tree.DefaultSelectionModel();}return this.selModel;},expandPath:function(_6,_7,_8){_7=_7||"id";var _9=_6.split(this.pathSeparator);var _a=this.root;if(_a.attributes[_7]!=_9[1]){if(_8){_8(false,null);}return;}var _b=1;var f=function(){if(++_b==_9.length){if(_8){_8(true,_a);}return;}var c=_a.findChild(_7,_9[_b]);if(!c){if(_8){_8(false,_a);}return;}_a=c;c.expand(false,false,f);};_a.expand(false,false,f);},selectPath:function(_e,_f,_10){_f=_f||"id";var _11=_e.split(this.pathSeparator);var v=_11.pop();if(_11.length>0){var f=function(_14,_15){if(_14&&_15){var n=_15.findChild(_f,v);if(n){n.select();if(_10){_10(true,n);}}}else{if(_10){_10(false,n);}}};this.expandPath(_11.join(this.pathSeparator),_f,f);}else{this.root.select();if(_10){_10(true,this.root);}}},render:function(){this.container=this.el.createChild({tag:"ul",cls:"x-tree-root-ct "+(this.lines?"x-tree-lines":"x-tree-no-lines")});if(this.containerScroll){Ext.dd.ScrollManager.register(this.el);}if((this.enableDD||this.enableDrop)&&!this.dropZone){this.dropZone=new Ext.tree.TreeDropZone(this,this.dropConfig||{ddGroup:this.ddGroup||"TreeDD",appendOnly:this.ddAppendOnly===true});}if((this.enableDD||this.enableDrag)&&!this.dragZone){this.dragZone=new Ext.tree.TreeDragZone(this,this.dragConfig||{ddGroup:this.ddGroup||"TreeDD",scroll:this.ddScroll});}this.getSelectionModel().init(this);this.root.render();if(!this.rootVisible){this.root.renderChildren();}return this;}});



Ext.tree.DefaultSelectionModel=function(){this.selNode=null;this.addEvents({"selectionchange":true,"beforeselect":true});};Ext.extend(Ext.tree.DefaultSelectionModel,Ext.util.Observable,{init:function(_1){this.tree=_1;_1.el.on("keydown",this.onKeyDown,this);_1.on("click",this.onNodeClick,this);},onNodeClick:function(_2,e){this.select(_2);},select:function(_4){var _5=this.selNode;if(_5!=_4&&this.fireEvent("beforeselect",this,_4,_5)!==false){if(_5){_5.ui.onSelectedChange(false);}this.selNode=_4;_4.ui.onSelectedChange(true);this.fireEvent("selectionchange",this,_4,_5);}return _4;},unselect:function(_6){if(this.selNode==_6){this.clearSelections();}},clearSelections:function(){var n=this.selNode;if(n){n.ui.onSelectedChange(false);this.selNode=null;this.fireEvent("selectionchange",this,null);}return n;},getSelectedNode:function(){return this.selNode;},isSelected:function(_8){return this.selNode==_8;},selectPrevious:function(){var s=this.selNode||this.lastSelNode;if(!s){return null;}var ps=s.previousSibling;if(ps){if(!ps.isExpanded()||ps.childNodes.length<1){return this.select(ps);}else{var lc=ps.lastChild;while(lc&&lc.isExpanded()&&lc.childNodes.length>0){lc=lc.lastChild;}return this.select(lc);}}else{if(s.parentNode&&(this.tree.rootVisible||!s.parentNode.isRoot)){return this.select(s.parentNode);}}return null;},selectNext:function(){var s=this.selNode||this.lastSelNode;if(!s){return null;}if(s.firstChild&&s.isExpanded()){return this.select(s.firstChild);}else{if(s.nextSibling){return this.select(s.nextSibling);}else{if(s.parentNode){var _d=null;s.parentNode.bubble(function(){if(this.nextSibling){_d=this.getOwnerTree().selModel.select(this.nextSibling);return false;}});return _d;}}}return null;},onKeyDown:function(e){var s=this.selNode||this.lastSelNode;var sm=this;if(!s){return;}var k=e.getKey();switch(k){case e.DOWN:e.stopEvent();this.selectNext();break;case e.UP:e.stopEvent();this.selectPrevious();break;case e.RIGHT:e.preventDefault();if(s.hasChildNodes()){if(!s.isExpanded()){s.expand();}else{if(s.firstChild){this.select(s.firstChild,e);}}}break;case e.LEFT:e.preventDefault();if(s.hasChildNodes()&&s.isExpanded()){s.collapse();}else{if(s.parentNode&&(this.tree.rootVisible||s.parentNode!=this.tree.getRootNode())){this.select(s.parentNode,e);}}break;}}});Ext.tree.MultiSelectionModel=function(){this.selNodes=[];this.selMap={};this.addEvents({"selectionchange":true});};Ext.extend(Ext.tree.MultiSelectionModel,Ext.util.Observable,{init:function(_12){this.tree=_12;_12.el.on("keydown",this.onKeyDown,this);_12.on("click",this.onNodeClick,this);},onNodeClick:function(_13,e){this.select(_13,e,e.ctrlKey);},select:function(_15,e,_17){if(_17!==true){this.clearSelections(true);}if(this.isSelected(_15)){this.lastSelNode=_15;return _15;}this.selNodes.push(_15);this.selMap[_15.id]=_15;this.lastSelNode=_15;_15.ui.onSelectedChange(true);this.fireEvent("selectionchange",this,this.selNodes);return _15;},unselect:function(_18){if(this.selMap[_18.id]){_18.ui.onSelectedChange(false);var sn=this.selNodes;var _1a=-1;if(sn.indexOf){_1a=sn.indexOf(_18);}else{for(var i=0,len=sn.length;i<len;i++){if(sn[i]==_18){_1a=i;break;}}}if(_1a!=-1){this.selNodes.splice(_1a,1);}delete this.selMap[_18.id];this.fireEvent("selectionchange",this,this.selNodes);}},clearSelections:function(_1d){var sn=this.selNodes;if(sn.length>0){for(var i=0,len=sn.length;i<len;i++){sn[i].ui.onSelectedChange(false);}this.selNodes=[];this.selMap={};if(_1d!==true){this.fireEvent("selectionchange",this,this.selNodes);}}},isSelected:function(_21){return this.selMap[_21.id]?true:false;},getSelectedNodes:function(){return this.selNodes;},onKeyDown:Ext.tree.DefaultSelectionModel.prototype.onKeyDown,selectNext:Ext.tree.DefaultSelectionModel.prototype.selectNext,selectPrevious:Ext.tree.DefaultSelectionModel.prototype.selectPrevious});



Ext.tree.TreeNode=function(_1){_1=_1||{};if(typeof _1=="string"){_1={text:_1};}this.childrenRendered=false;this.rendered=false;Ext.tree.TreeNode.superclass.constructor.call(this,_1);this.expanded=_1.expanded===true;this.isTarget=_1.isTarget!==false;this.draggable=_1.draggable!==false&&_1.allowDrag!==false;this.allowChildren=_1.allowChildren!==false&&_1.allowDrop!==false;this.text=_1.text;this.disabled=_1.disabled===true;this.addEvents({"textchange":true,"beforeexpand":true,"beforecollapse":true,"expand":true,"disabledchange":true,"collapse":true,"beforeclick":true,"click":true,"dblclick":true,"contextmenu":true,"beforechildrenrendered":true});var _2=this.attributes.uiProvider||Ext.tree.TreeNodeUI;this.ui=new _2(this);};Ext.extend(Ext.tree.TreeNode,Ext.data.Node,{preventHScroll:true,isExpanded:function(){return this.expanded;},getUI:function(){return this.ui;},setFirstChild:function(_3){var of=this.firstChild;Ext.tree.TreeNode.superclass.setFirstChild.call(this,_3);if(this.childrenRendered&&of&&_3!=of){of.renderIndent(true,true);}if(this.rendered){this.renderIndent(true,true);}},setLastChild:function(_5){var ol=this.lastChild;Ext.tree.TreeNode.superclass.setLastChild.call(this,_5);if(this.childrenRendered&&ol&&_5!=ol){ol.renderIndent(true,true);}if(this.rendered){this.renderIndent(true,true);}},appendChild:function(){var _7=Ext.tree.TreeNode.superclass.appendChild.apply(this,arguments);if(_7&&this.childrenRendered){_7.render();}this.ui.updateExpandIcon();return _7;},removeChild:function(_8){this.ownerTree.getSelectionModel().unselect(_8);Ext.tree.TreeNode.superclass.removeChild.apply(this,arguments);if(this.childrenRendered){_8.ui.remove();}if(this.childNodes.length<1){this.collapse(false,false);}else{this.ui.updateExpandIcon();}return _8;},insertBefore:function(_9,_a){var _b=Ext.tree.TreeNode.superclass.insertBefore.apply(this,arguments);if(_b&&_a&&this.childrenRendered){_9.render();}this.ui.updateExpandIcon();return _b;},setText:function(_c){var _d=this.text;this.text=_c;this.attributes.text=_c;if(this.rendered){this.ui.onTextChange(this,_c,_d);}this.fireEvent("textchange",this,_c,_d);},select:function(){this.getOwnerTree().getSelectionModel().select(this);},unselect:function(){this.getOwnerTree().getSelectionModel().unselect(this);},isSelected:function(){return this.getOwnerTree().getSelectionModel().isSelected(this);},expand:function(_e,_f,_10){if(!this.expanded){if(this.fireEvent("beforeexpand",this,_e,_f)===false){return;}if(!this.childrenRendered){this.renderChildren();}this.expanded=true;if(!this.isHiddenRoot()&&(this.getOwnerTree().animate&&_f!==false)||_f){this.ui.animExpand(function(){this.fireEvent("expand",this);if(typeof _10=="function"){_10(this);}if(_e===true){this.expandChildNodes(true);}}.createDelegate(this));return;}else{this.ui.expand();this.fireEvent("expand",this);if(typeof _10=="function"){_10(this);}}}else{if(typeof _10=="function"){_10(this);}}if(_e===true){this.expandChildNodes(true);}},isHiddenRoot:function(){return this.isRoot&&!this.getOwnerTree().rootVisible;},collapse:function(_11,_12){if(this.expanded&&!this.isHiddenRoot()){if(this.fireEvent("beforecollapse",this,_11,_12)===false){return;}this.expanded=false;if((this.getOwnerTree().animate&&_12!==false)||_12){this.ui.animCollapse(function(){this.fireEvent("collapse",this);if(_11===true){this.collapseChildNodes(true);}}.createDelegate(this));return;}else{this.ui.collapse();this.fireEvent("collapse",this);}}if(_11===true){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].collapse(true);}}},delayedExpand:function(_16){if(!this.expandProcId){this.expandProcId=this.expand.defer(_16,this);}},cancelExpand:function(){if(this.expandProcId){clearTimeout(this.expandProcId);}this.expandProcId=false;},toggle:function(){if(this.expanded){this.collapse();}else{this.expand();}},ensureVisible:function(_17){var _18=this.getOwnerTree();_18.expandPath(this.getPath(),false,function(){_18.getEl().scrollChildIntoView(this.ui.anchor);Ext.callback(_17);}.createDelegate(this));},expandChildNodes:function(_19){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].expand(_19);}},collapseChildNodes:function(_1d){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].collapse(_1d);}},disable:function(){this.disabled=true;this.unselect();if(this.rendered&&this.ui.onDisableChange){this.ui.onDisableChange(this,true);}this.fireEvent("disabledchange",this,true);},enable:function(){this.disabled=false;if(this.rendered&&this.ui.onDisableChange){this.ui.onDisableChange(this,false);}this.fireEvent("disabledchange",this,false);},renderChildren:function(_21){if(_21!==false){this.fireEvent("beforechildrenrendered",this);}var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].render(true);}this.childrenRendered=true;},sort:function(fn,_26){Ext.tree.TreeNode.superclass.sort.apply(this,arguments);if(this.childrenRendered){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].render(true);}}},render:function(_2a){this.ui.render(_2a);if(!this.rendered){this.rendered=true;if(this.expanded){this.expanded=false;this.expand(false,false);}}},renderIndent:function(_2b,_2c){if(_2c){this.ui.childIndent=null;}this.ui.renderIndent();if(_2b===true&&this.childrenRendered){var cs=this.childNodes;for(var i=0,len=cs.length;i<len;i++){cs[i].renderIndent(true,_2c);}}}});



Ext.tree.AsyncTreeNode=function(_1){this.loaded=false;this.loading=false;Ext.tree.AsyncTreeNode.superclass.constructor.apply(this,arguments);this.addEvents({"beforeload":true,"load":true});};Ext.extend(Ext.tree.AsyncTreeNode,Ext.tree.TreeNode,{expand:function(_2,_3,_4){if(this.loading){var _5;var f=function(){if(!this.loading){clearInterval(_5);this.expand(_2,_3,_4);}}.createDelegate(this);_5=setInterval(f,200);return;}if(!this.loaded){if(this.fireEvent("beforeload",this)===false){return;}this.loading=true;this.ui.beforeLoad(this);var _7=this.loader||this.attributes.loader||this.getOwnerTree().getLoader();if(_7){_7.load(this,this.loadComplete.createDelegate(this,[_2,_3,_4]));return;}}Ext.tree.AsyncTreeNode.superclass.expand.call(this,_2,_3,_4);},isLoading:function(){return this.loading;},loadComplete:function(_8,_9,_a){this.loading=false;this.loaded=true;this.ui.afterLoad(this);this.fireEvent("load",this);this.expand(_8,_9,_a);},isLoaded:function(){return this.loaded;},hasChildNodes:function(){if(!this.isLeaf()&&!this.loaded){return true;}else{return Ext.tree.AsyncTreeNode.superclass.hasChildNodes.call(this);}},reload:function(_b){this.collapse(false,false);while(this.firstChild){this.removeChild(this.firstChild);}this.childrenRendered=false;this.loaded=false;if(this.isHiddenRoot()){this.expanded=false;}this.expand(false,false,_b);}});



Ext.tree.TreeNodeUI=function(_1){this.node=_1;this.rendered=false;this.animating=false;this.emptyIcon=Ext.BLANK_IMAGE_URL;};Ext.tree.TreeNodeUI.prototype={removeChild:function(_2){if(this.rendered){this.ctNode.removeChild(_2.ui.getEl());}},beforeLoad:function(){this.addClass("x-tree-node-loading");},afterLoad:function(){this.removeClass("x-tree-node-loading");},onTextChange:function(_3,_4,_5){if(this.rendered){this.textNode.innerHTML=_4;}},onDisableChange:function(_6,_7){this.disabled=_7;if(_7){this.addClass("x-tree-node-disabled");}else{this.removeClass("x-tree-node-disabled");}},onSelectedChange:function(_8){if(_8){this.focus();this.addClass("x-tree-selected");}else{this.removeClass("x-tree-selected");}},onMove:function(_9,_a,_b,_c,_d,_e){this.childIndent=null;if(this.rendered){var _f=_c.ui.getContainer();if(!_f){this.holder=document.createElement("div");this.holder.appendChild(this.wrap);return;}var _10=_e?_e.ui.getEl():null;if(_10){_f.insertBefore(this.wrap,_10);}else{_f.appendChild(this.wrap);}this.node.renderIndent(true);}},addClass:function(cls){if(this.elNode){Ext.fly(this.elNode).addClass(cls);}},removeClass:function(cls){if(this.elNode){Ext.fly(this.elNode).removeClass(cls);}},remove:function(){if(this.rendered){this.holder=document.createElement("div");this.holder.appendChild(this.wrap);}},fireEvent:function(){return this.node.fireEvent.apply(this.node,arguments);},initEvents:function(){this.node.on("move",this.onMove,this);var E=Ext.EventManager;var a=this.anchor;var el=Ext.fly(a);if(Ext.isOpera){el.setStyle("text-decoration","none");}el.on("click",this.onClick,this);el.on("dblclick",this.onDblClick,this);el.on("contextmenu",this.onContextMenu,this);var _16=Ext.fly(this.iconNode);_16.on("click",this.onClick,this);_16.on("dblclick",this.onDblClick,this);_16.on("contextmenu",this.onContextMenu,this);E.on(this.ecNode,"click",this.ecClick,this,true);if(this.node.disabled){this.addClass("x-tree-node-disabled");}if(this.node.hidden){this.addClass("x-tree-node-disabled");}var ot=this.node.getOwnerTree();var dd=ot.enableDD||ot.enableDrag||ot.enableDrop;if(dd&&(!this.node.isRoot||ot.rootVisible)){Ext.dd.Registry.register(this.elNode,{node:this.node,handles:[this.iconNode,this.textNode],isHandle:false});}},hide:function(){if(this.rendered){this.wrap.style.display="none";}},show:function(){if(this.rendered){this.wrap.style.display="";}},onContextMenu:function(e){e.preventDefault();this.focus();this.fireEvent("contextmenu",this.node,e);},onClick:function(e){if(this.dropping){return;}if(this.fireEvent("beforeclick",this.node,e)!==false){if(!this.disabled&&this.node.attributes.href){this.fireEvent("click",this.node,e);return;}e.preventDefault();if(this.disabled){return;}if(this.node.attributes.singleClickExpand&&!this.animating&&this.node.hasChildNodes()){this.node.toggle();}this.fireEvent("click",this.node,e);}else{e.stopEvent();}},onDblClick:function(e){e.preventDefault();if(this.disabled){return;}if(!this.animating&&this.node.hasChildNodes()){this.node.toggle();}this.fireEvent("dblclick",this.node,e);},ecClick:function(e){if(!this.animating&&this.node.hasChildNodes()){this.node.toggle();}},startDrop:function(){this.dropping=true;},endDrop:function(){setTimeout(function(){this.dropping=false;}.createDelegate(this),50);},expand:function(){this.updateExpandIcon();this.ctNode.style.display="";},focus:function(){if(!this.node.preventHScroll){try{this.anchor.focus();}catch(e){}}else{if(!Ext.isIE){try{var _1d=this.node.getOwnerTree().el.dom;var l=_1d.scrollLeft;this.anchor.focus();_1d.scrollLeft=l;}catch(e){}}}},blur:function(){try{this.anchor.blur();}catch(e){}},animExpand:function(_1f){var ct=Ext.get(this.ctNode);ct.stopFx();if(!this.node.hasChildNodes()){this.updateExpandIcon();this.ctNode.style.display="";Ext.callback(_1f);return;}this.animating=true;this.updateExpandIcon();ct.slideIn("t",{callback:function(){this.animating=false;Ext.callback(_1f);},scope:this,duration:this.node.ownerTree.duration||0.25});},highlight:function(){var _21=this.node.getOwnerTree();Ext.fly(this.wrap).highlight(_21.hlColor||"C3DAF9",{endColor:_21.hlBaseColor});},collapse:function(){this.updateExpandIcon();this.ctNode.style.display="none";},animCollapse:function(_22){var ct=Ext.get(this.ctNode);ct.enableDisplayMode("block");ct.stopFx();this.animating=true;this.updateExpandIcon();ct.slideOut("t",{callback:function(){this.animating=false;Ext.callback(_22);},scope:this,duration:this.node.ownerTree.duration||0.25});},getContainer:function(){return this.ctNode;},getEl:function(){return this.wrap;},appendDDGhost:function(_24){_24.appendChild(this.elNode.cloneNode(true));},getDDRepairXY:function(){return Ext.lib.Dom.getXY(this.iconNode);},onRender:function(){this.render();},render:function(_25){var n=this.node;var _27=n.parentNode?n.parentNode.ui.getContainer():n.ownerTree.container.dom;if(!this.rendered){this.rendered=true;var a=n.attributes;this.indentMarkup="";if(n.parentNode){this.indentMarkup=n.parentNode.ui.getChildIndent();}var buf=["<li class=\"x-tree-node\"><div class=\"x-tree-node-el ",n.attributes.cls,"\">","<span class=\"x-tree-node-indent\">",this.indentMarkup,"</span>","<img src=\"",this.emptyIcon,"\" class=\"x-tree-ec-icon\">","<img src=\"",a.icon||this.emptyIcon,"\" class=\"x-tree-node-icon",(a.icon?" x-tree-node-inline-icon":""),(a.iconCls?" "+a.iconCls:""),"\" unselectable=\"on\">","<a hidefocus=\"on\" href=\"",a.href?a.href:"#","\" tabIndex=\"1\" ",a.hrefTarget?" target=\""+a.hrefTarget+"\"":"","><span unselectable=\"on\">",n.text,"</span></a></div>","<ul class=\"x-tree-node-ct\" style=\"display:none;\"></ul>","</li>"];if(_25!==true&&n.nextSibling&&n.nextSibling.ui.getEl()){this.wrap=Ext.DomHelper.insertHtml("beforeBegin",n.nextSibling.ui.getEl(),buf.join(""));}else{this.wrap=Ext.DomHelper.insertHtml("beforeEnd",_27,buf.join(""));}this.elNode=this.wrap.childNodes[0];this.ctNode=this.wrap.childNodes[1];var cs=this.elNode.childNodes;this.indentNode=cs[0];this.ecNode=cs[1];this.iconNode=cs[2];this.anchor=cs[3];this.textNode=cs[3].firstChild;if(a.qtip){if(this.textNode.setAttributeNS){this.textNode.setAttributeNS("ext","qtip",a.qtip);if(a.qtipTitle){this.textNode.setAttributeNS("ext","qtitle",a.qtipTitle);}}else{this.textNode.setAttribute("ext:qtip",a.qtip);if(a.qtipTitle){this.textNode.setAttribute("ext:qtitle",a.qtipTitle);}}}this.initEvents();if(!this.node.expanded){this.updateExpandIcon();}}else{if(_25===true){_27.appendChild(this.wrap);}}},getAnchor:function(){return this.anchor;},getTextEl:function(){return this.textNode;},getIconEl:function(){return this.iconNode;},updateExpandIcon:function(){if(this.rendered){var n=this.node,c1,c2;var cls=n.isLast()?"x-tree-elbow-end":"x-tree-elbow";var _2f=n.hasChildNodes();if(_2f){if(n.expanded){cls+="-minus";c1="x-tree-node-collapsed";c2="x-tree-node-expanded";}else{cls+="-plus";c1="x-tree-node-expanded";c2="x-tree-node-collapsed";}if(this.wasLeaf){this.removeClass("x-tree-node-leaf");this.wasLeaf=false;}if(this.c1!=c1||this.c2!=c2){Ext.fly(this.elNode).replaceClass(c1,c2);this.c1=c1;this.c2=c2;}}else{if(!this.wasLeaf){Ext.fly(this.elNode).replaceClass("x-tree-node-expanded","x-tree-node-leaf");this.wasLeaf=true;}}var ecc="x-tree-ec-icon "+cls;if(this.ecc!=ecc){this.ecNode.className=ecc;this.ecc=ecc;}}},getChildIndent:function(){if(!this.childIndent){var buf=[];var p=this.node;while(p){if(!p.isRoot||(p.isRoot&&p.ownerTree.rootVisible)){if(!p.isLast()){buf.unshift("<img src=\""+this.emptyIcon+"\" class=\"x-tree-elbow-line\">");}else{buf.unshift("<img src=\""+this.emptyIcon+"\" class=\"x-tree-icon\">");}}p=p.parentNode;}this.childIndent=buf.join("");}return this.childIndent;},renderIndent:function(){if(this.rendered){var _33="";var p=this.node.parentNode;if(p){_33=p.ui.getChildIndent();}if(this.indentMarkup!=_33){this.indentNode.innerHTML=_33;this.indentMarkup=_33;}this.updateExpandIcon();}}};Ext.tree.RootTreeNodeUI=function(){Ext.tree.RootTreeNodeUI.superclass.constructor.apply(this,arguments);};Ext.extend(Ext.tree.RootTreeNodeUI,Ext.tree.TreeNodeUI,{render:function(){if(!this.rendered){var _35=this.node.ownerTree.container.dom;this.node.expanded=true;_35.innerHTML="<div class=\"x-tree-root-node\"></div>";this.wrap=this.ctNode=_35.firstChild;}},collapse:function(){},expand:function(){}});



Ext.tree.TreeLoader=function(_1){this.baseParams={};this.requestMethod="POST";Ext.apply(this,_1);this.addEvents({"beforeload":true,"load":true,"loadexception":true});};Ext.extend(Ext.tree.TreeLoader,Ext.util.Observable,{uiProviders:{},clearOnLoad:true,load:function(_2,_3){if(this.clearOnLoad){while(_2.firstChild){_2.removeChild(_2.firstChild);}}if(_2.attributes.children){var cs=_2.attributes.children;for(var i=0,_6=cs.length;i<_6;i++){_2.appendChild(this.createNode(cs[i]));}if(typeof _3=="function"){_3();}}else{if(this.dataUrl){this.requestData(_2,_3);}}},getParams:function(_7){var _8=[],bp=this.baseParams;for(var _a in bp){if(typeof bp[_a]!="function"){_8.push(encodeURIComponent(_a),"=",encodeURIComponent(bp[_a]),"&");}}_8.push("node=",encodeURIComponent(_7.id));return _8.join("");},requestData:function(_b,_c){if(this.fireEvent("beforeload",this,_b,_c)!==false){var _d=this.getParams(_b);var cb={success:this.handleResponse,failure:this.handleFailure,scope:this,argument:{callback:_c,node:_b}};this.transId=Ext.lib.Ajax.request(this.requestMethod,this.dataUrl,cb,_d);}else{if(typeof _c=="function"){_c();}}},isLoading:function(){return this.transId?true:false;},abort:function(){if(this.isLoading()){Ext.lib.Ajax.abort(this.transId);}},createNode:function(_f){if(this.applyLoader!==false){_f.loader=this;}if(typeof _f.uiProvider=="string"){_f.uiProvider=this.uiProviders[_f.uiProvider]||eval(_f.uiProvider);}return (_f.leaf?new Ext.tree.TreeNode(_f):new Ext.tree.AsyncTreeNode(_f));},processResponse:function(_10,_11,_12){var _13=_10.responseText;try{var o=eval("("+_13+")");for(var i=0,len=o.length;i<len;i++){var n=this.createNode(o[i]);if(n){_11.appendChild(n);}}if(typeof _12=="function"){_12(this,_11);}}catch(e){this.handleFailure(_10);}},handleResponse:function(_18){this.transId=false;var a=_18.argument;this.processResponse(_18,a.node,a.callback);this.fireEvent("load",this,a.node,_18);},handleFailure:function(_1a){this.transId=false;var a=_1a.argument;this.fireEvent("loadexception",this,a.node,_1a);if(typeof a.callback=="function"){a.callback(this,a.node);}}});



Ext.tree.TreeFilter=function(_1,_2){this.tree=_1;this.filtered={};Ext.apply(this,_2,{clearBlank:false,reverse:false,autoClear:false,remove:false});};Ext.tree.TreeFilter.prototype={filter:function(_3,_4,_5){_4=_4||"text";var f;if(typeof _3=="string"){var _7=_3.length;if(_7==0&&this.clearBlank){this.clearFilter();return;}_3=_3.toLowerCase();f=function(n){return n.attributes[_4].substr(0,_7).toLowerCase()==_3;};}else{if(_3.exec){f=function(n){return _3.test(n.attributes[_4]);};}else{throw "Illegal filter type, must be string or regex";}}this.filterBy(f,null,_5);},filterBy:function(fn,_b,_c){_c=_c||this.tree.root;if(this.autoClear){this.clearFilter();}var af=this.filtered,rv=this.reverse;var f=function(n){if(n==_c){return true;}if(af[n.id]){return false;}var m=fn.call(_b||n,n);if(!m||rv){af[n.id]=n;n.ui.hide();return false;}return true;};_c.cascade(f);if(this.remove){for(var id in af){if(typeof id!="function"){var n=af[id];if(n&&n.parentNode){n.parentNode.removeChild(n);}}}}},clear:function(){var t=this.tree;var af=this.filtered;for(var id in af){if(typeof id!="function"){var n=af[id];if(n){n.ui.show();}}}this.filtered={};}};



Ext.tree.TreeSorter=function(_1,_2){Ext.apply(this,_2);_1.on("beforechildrenrendered",this.doSort,this);_1.on("append",this.updateSort,this);_1.on("insert",this.updateSort,this);var _3=this.dir&&this.dir.toLowerCase()=="desc";var p=this.property||"text";var _5=this.sortType;var fs=this.folderSort;var cs=this.caseSensitive===true;var _8=this.leafAttr||"leaf";this.sortFn=function(n1,n2){if(fs){if(n1.attributes[_8]&&!n2.attributes[_8]){return 1;}if(!n1.attributes[_8]&&n2.attributes[_8]){return -1;}}var v1=_5?_5(n1):(cs?n1[p]:n1[p].toUpperCase());var v2=_5?_5(n2):(cs?n2[p]:n2[p].toUpperCase());if(v1<v2){return _3?+1:-1;}else{if(v1>v2){return _3?-1:+1;}else{return 0;}}};};Ext.tree.TreeSorter.prototype={doSort:function(_d){_d.sort(this.sortFn);},compareNodes:function(n1,n2){return (n1.text.toUpperCase()>n2.text.toUpperCase()?1:-1);},updateSort:function(_10,_11){if(_11.childrenRendered){this.doSort.defer(1,this,[_11]);}}};



if(Ext.dd.DropZone){Ext.tree.TreeDropZone=function(_1,_2){this.allowParentInsert=false;this.allowContainerDrop=false;this.appendOnly=false;Ext.tree.TreeDropZone.superclass.constructor.call(this,_1.container,_2);this.tree=_1;this.lastInsertClass="x-tree-no-status";this.dragOverData={};};Ext.extend(Ext.tree.TreeDropZone,Ext.dd.DropZone,{ddGroup:"TreeDD",expandDelay:1000,expandNode:function(_3){if(_3.hasChildNodes()&&!_3.isExpanded()){_3.expand(false,null,this.triggerCacheRefresh.createDelegate(this));}},queueExpand:function(_4){this.expandProcId=this.expandNode.defer(this.expandDelay,this,[_4]);},cancelExpand:function(){if(this.expandProcId){clearTimeout(this.expandProcId);this.expandProcId=false;}},isValidDropPoint:function(n,pt,dd,e,_9){if(!n||!_9){return false;}var _a=n.node;var _b=_9.node;if(!(_a&&_a.isTarget&&pt)){return false;}if(pt=="append"&&_a.allowChildren===false){return false;}if((pt=="above"||pt=="below")&&(_a.parentNode&&_a.parentNode.allowChildren===false)){return false;}if(_b&&(_a==_b||_b.contains(_a))){return false;}var _c=this.dragOverData;_c.tree=this.tree;_c.target=_a;_c.data=_9;_c.point=pt;_c.source=dd;_c.rawEvent=e;_c.dropNode=_b;_c.cancel=false;var _d=this.tree.fireEvent("nodedragover",_c);return _c.cancel===false&&_d!==false;},getDropPoint:function(e,n,dd){var tn=n.node;if(tn.isRoot){return tn.allowChildren!==false?"append":false;}var _12=n.ddel;var t=Ext.lib.Dom.getY(_12),b=t+_12.offsetHeight;var y=Ext.lib.Event.getPageY(e);var _16=tn.allowChildren===false||tn.isLeaf();if(this.appendOnly||tn.parentNode.allowChildren===false){return _16?false:"append";}var _17=false;if(!this.allowParentInsert){_17=tn.hasChildNodes()&&tn.isExpanded();}var q=(b-t)/(_16?2:3);if(y>=t&&y<(t+q)){return "above";}else{if(!_17&&(_16||y>=b-q&&y<=b)){return "below";}else{return "append";}}},onNodeEnter:function(n,dd,e,_1c){this.cancelExpand();},onNodeOver:function(n,dd,e,_20){var pt=this.getDropPoint(e,n,dd);var _22=n.node;if(!this.expandProcId&&pt=="append"&&_22.hasChildNodes()&&!n.node.isExpanded()){this.queueExpand(_22);}else{if(pt!="append"){this.cancelExpand();}}var _23=this.dropNotAllowed;if(this.isValidDropPoint(n,pt,dd,e,_20)){if(pt){var el=n.ddel;var cls;if(pt=="above"){_23=n.node.isFirst()?"x-tree-drop-ok-above":"x-tree-drop-ok-between";cls="x-tree-drag-insert-above";}else{if(pt=="below"){_23=n.node.isLast()?"x-tree-drop-ok-below":"x-tree-drop-ok-between";cls="x-tree-drag-insert-below";}else{_23="x-tree-drop-ok-append";cls="x-tree-drag-append";}}if(this.lastInsertClass!=cls){Ext.fly(el).replaceClass(this.lastInsertClass,cls);this.lastInsertClass=cls;}}}return _23;},onNodeOut:function(n,dd,e,_29){this.cancelExpand();this.removeDropIndicators(n);},onNodeDrop:function(n,dd,e,_2d){var _2e=this.getDropPoint(e,n,dd);var _2f=n.node;_2f.ui.startDrop();if(!this.isValidDropPoint(n,_2e,dd,e,_2d)){_2f.ui.endDrop();return false;}var _30=_2d.node||(dd.getTreeNode?dd.getTreeNode(_2d,_2f,_2e,e):null);var _31={tree:this.tree,target:_2f,data:_2d,point:_2e,source:dd,rawEvent:e,dropNode:_30,cancel:!_30};var _32=this.tree.fireEvent("beforenodedrop",_31);if(_32===false||_31.cancel===true||!_31.dropNode){_2f.ui.endDrop();return false;}_2f=_31.target;if(_2e=="append"&&!_2f.isExpanded()){_2f.expand(false,null,function(){this.completeDrop(_31);}.createDelegate(this));}else{this.completeDrop(_31);}return true;},completeDrop:function(de){var ns=de.dropNode,p=de.point,t=de.target;if(!(ns instanceof Array)){ns=[ns];}var n;for(var i=0,len=ns.length;i<len;i++){n=ns[i];if(p=="above"){t.parentNode.insertBefore(n,t);}else{if(p=="below"){t.parentNode.insertBefore(n,t.nextSibling);}else{t.appendChild(n);}}}n.ui.focus();if(this.tree.hlDrop){n.ui.highlight();}t.ui.endDrop();this.tree.fireEvent("nodedrop",de);},afterNodeMoved:function(dd,_3b,e,_3d,_3e){if(this.tree.hlDrop){_3e.ui.focus();_3e.ui.highlight();}this.tree.fireEvent("nodedrop",this.tree,_3d,_3b,dd,e);},getTree:function(){return this.tree;},removeDropIndicators:function(n){if(n&&n.ddel){var el=n.ddel;Ext.fly(el).removeClass(["x-tree-drag-insert-above","x-tree-drag-insert-below","x-tree-drag-append"]);this.lastInsertClass="_noclass";}},beforeDragDrop:function(_41,e,id){this.cancelExpand();return true;},afterRepair:function(_44){if(_44&&Ext.enableFx){_44.node.ui.highlight();}this.hideProxy();}});}



if(Ext.dd.DragZone){Ext.tree.TreeDragZone=function(_1,_2){Ext.tree.TreeDragZone.superclass.constructor.call(this,_1.getEl(),_2);this.tree=_1;};Ext.extend(Ext.tree.TreeDragZone,Ext.dd.DragZone,{ddGroup:"TreeDD",onBeforeDrag:function(_3,e){var n=_3.node;return n&&n.draggable&&!n.disabled;},onInitDrag:function(e){var _7=this.dragData;this.tree.getSelectionModel().select(_7.node);this.proxy.update("");_7.node.ui.appendDDGhost(this.proxy.ghost.dom);this.tree.fireEvent("startdrag",this.tree,_7.node,e);},getRepairXY:function(e,_9){return _9.node.ui.getDDRepairXY();},onEndDrag:function(_a,e){this.tree.fireEvent("enddrag",this.tree,_a.node,e);},onValidDrop:function(dd,e,id){this.tree.fireEvent("dragdrop",this.tree,this.dragData.node,dd,e);this.hideProxy();},beforeInvalidDrop:function(e,id){var sm=this.tree.getSelectionModel();sm.clearSelections();sm.select(this.dragData.node);}});}



Ext.tree.TreeEditor=function(_1,_2){_2=_2||{};var _3=_2.events?_2:new Ext.form.TextField(_2);Ext.tree.TreeEditor.superclass.constructor.call(this,_3);this.tree=_1;_1.on("beforeclick",this.beforeNodeClick,this);_1.el.on("mousedown",this.hide,this);this.on("complete",this.updateNode,this);this.on("beforestartedit",this.fitToTree,this);this.on("startedit",this.bindScroll,this,{delay:10});this.on("specialkey",this.onSpecialKey,this);};Ext.extend(Ext.tree.TreeEditor,Ext.Editor,{alignment:"l-l",autoSize:false,hideEl:false,cls:"x-small-editor x-tree-editor",shim:false,shadow:"frame",maxWidth:250,fitToTree:function(ed,el){var td=this.tree.el.dom,nd=el.dom;if(td.scrollLeft>nd.offsetLeft){td.scrollLeft=nd.offsetLeft;}var w=Math.min(this.maxWidth,(td.clientWidth>20?td.clientWidth:td.offsetWidth)-Math.max(0,nd.offsetLeft-td.scrollLeft)-5);this.setSize(w,"");},triggerEdit:function(_9){this.completeEdit();this.editNode=_9;this.startEdit(_9.ui.textNode,_9.text);},bindScroll:function(){this.tree.el.on("scroll",this.cancelEdit,this);},beforeNodeClick:function(_a){if(this.tree.getSelectionModel().isSelected(_a)){this.triggerEdit(_a);return false;}},updateNode:function(ed,_c){this.tree.el.un("scroll",this.cancelEdit,this);this.editNode.setText(_c);},onSpecialKey:function(_d,e){var k=e.getKey();if(k==e.ESC){this.cancelEdit();e.stopEvent();}else{if(k==e.ENTER&&!e.hasModifier()){this.completeEdit();e.stopEvent();}}}});



Ext.form.Field=function(_1){Ext.form.Field.superclass.constructor.call(this,_1);this.addEvents({focus:true,blur:true,specialkey:true,change:true,invalid:true,valid:true});};Ext.extend(Ext.form.Field,Ext.Component,{invalidClass:"x-form-invalid",invalidText:"The value in this field is invalid",focusClass:"x-form-focus",validationEvent:"keyup",validateOnBlur:true,validationDelay:250,defaultAutoCreate:{tag:"input",type:"text",size:"20",autocomplete:"off"},fieldClass:"x-form-field",msgTarget:"qtip",msgFx:"normal",inputType:undefined,isFormField:true,hasFocus:false,value:undefined,getName:function(){return this.rendered&&this.el.dom.name?this.el.dom.name:(this.hiddenName||"");},applyTo:function(_2){this.target=_2;this.el=Ext.get(_2);this.render(this.el.dom.parentNode);return this;},onRender:function(ct,_4){if(this.el){this.el=Ext.get(this.el);if(!this.target){ct.dom.appendChild(this.el.dom);}}else{var _5=this.getAutoCreate();if(!_5.name){_5.name=this.name||this.id;}if(this.inputType){_5.type=this.inputType;}if(this.tabIndex!==undefined){_5.tabIndex=this.tabIndex;}this.el=ct.createChild(_5,_4);}var _6=this.el.dom.type;if(_6){if(_6=="password"){_6="text";}this.el.addClass("x-form-"+_6);}if(!this.customSize&&(this.width||this.height)){this.setSize(this.width||"",this.height||"");}if(this.readOnly){this.el.dom.readOnly=true;}this.el.addClass([this.fieldClass,this.cls]);this.initValue();},initValue:function(){if(this.value!==undefined){this.setValue(this.value);}else{if(this.el.dom.value.length>0){this.setValue(this.el.dom.value);}}},afterRender:function(){Ext.form.Field.superclass.afterRender.call(this);this.initEvents();},fireKey:function(e){if(e.isNavKeyPress()){this.fireEvent("specialkey",this,e);}},reset:function(){this.setValue(this.originalValue);this.clearInvalid();},initEvents:function(){this.el.on(Ext.isIE?"keydown":"keypress",this.fireKey,this);this.el.on("focus",this.onFocus,this);this.el.on("blur",this.onBlur,this);this.originalValue=this.getValue();},onFocus:function(){if(!Ext.isOpera){this.el.addClass(this.focusClass);}this.hasFocus=true;this.startValue=this.getValue();this.fireEvent("focus",this);},onBlur:function(){this.el.removeClass(this.focusClass);this.hasFocus=false;if(this.validationEvent!==false&&this.validateOnBlur&&this.validationEvent!="blur"){this.validate();}var v=this.getValue();if(v!=this.startValue){this.fireEvent("change",this,v,this.startValue);}this.fireEvent("blur",this);},setSize:function(w,h){if(!this.rendered||!this.el){this.width=w;this.height=h;return;}if(w){w=this.adjustWidth(this.el.dom.tagName,w);this.el.setWidth(w);}if(h){this.el.setHeight(h);}var h=this.el.dom.offsetHeight;},isValid:function(_b){if(this.disabled){return true;}var _c=this.preventMark;this.preventMark=_b===true;var v=this.validateValue(this.getRawValue());this.preventMark=_c;return v;},validate:function(){if(this.disabled||this.validateValue(this.getRawValue())){this.clearInvalid();return true;}return false;},validateValue:function(_e){return true;},markInvalid:function(_f){if(!this.rendered||this.preventMark){return;}this.el.addClass(this.invalidClass);_f=_f||this.invalidText;switch(this.msgTarget){case "qtip":this.el.dom.qtip=_f;this.el.dom.qclass="x-form-invalid-tip";break;case "title":this.el.dom.title=_f;break;case "under":if(!this.errorEl){var elp=this.el.findParent(".x-form-element",5,true);this.errorEl=elp.createChild({cls:"x-form-invalid-msg"});this.errorEl.setWidth(elp.getWidth(true)-20);}this.errorEl.update(_f);Ext.form.Field.msgFx[this.msgFx].show(this.errorEl,this);break;case "side":if(!this.errorIcon){var elp=this.el.findParent(".x-form-element",5,true);this.errorIcon=elp.createChild({cls:"x-form-invalid-icon"});}this.alignErrorIcon();this.errorIcon.dom.qtip=_f;this.errorIcon.dom.qclass="x-form-invalid-tip";this.errorIcon.show();break;default:var t=Ext.getDom(this.msgTarget);t.innerHTML=_f;t.style.display=this.msgDisplay;break;}this.fireEvent("invalid",this,_f);},alignErrorIcon:function(){this.errorIcon.alignTo(this.el,"tl-tr",[2,0]);},clearInvalid:function(){if(!this.rendered||this.preventMark){return;}this.el.removeClass(this.invalidClass);switch(this.msgTarget){case "qtip":this.el.dom.qtip="";break;case "title":this.el.dom.title="";break;case "under":if(this.errorEl){Ext.form.Field.msgFx[this.msgFx].hide(this.errorEl,this);}break;case "side":if(this.errorIcon){this.errorIcon.dom.qtip="";this.errorIcon.hide();}break;default:var t=Ext.getDom(this.msgTarget);t.innerHTML="";t.style.display="none";break;}this.fireEvent("valid",this);},getRawValue:function(){return this.el.getValue();},getValue:function(){var v=this.el.getValue();if(v==this.emptyText||v===undefined){v="";}return v;},setRawValue:function(v){return this.el.dom.value=v;},setValue:function(v){this.value=v;if(this.rendered){this.el.dom.value=v;this.validate();}},adjustWidth:function(tag,w){tag=tag.toLowerCase();if(typeof w=="number"&&Ext.isStrict&&!Ext.isSafari){if(Ext.isIE&&(tag=="input"||tag=="textarea")){if(tag=="input"){return w+2;}if(tag="textarea"){return w-2;}}else{if(Ext.isGecko&&tag=="textarea"){return w-6;}else{if(Ext.isOpera){if(tag=="input"){return w+2;}if(tag="textarea"){return w-2;}}}}}return w;}});Ext.form.Field.msgFx={normal:{show:function(_18,f){_18.setDisplayed("block");},hide:function(_1a,f){_1a.setDisplayed(false).update("");}},slide:{show:function(_1c,f){_1c.slideIn("t",{stopFx:true});},hide:function(_1e,f){_1e.slideOut("t",{stopFx:true,useDisplay:true});}},slideRight:{show:function(_20,f){_20.fixDisplay();_20.alignTo(f.el,"tl-tr");_20.slideIn("l",{stopFx:true});},hide:function(_22,f){_22.slideOut("l",{stopFx:true,useDisplay:true});}}};



Ext.form.TextField=function(_1){Ext.form.TextField.superclass.constructor.call(this,_1);this.addEvents({autosize:true});};Ext.extend(Ext.form.TextField,Ext.form.Field,{grow:false,growMin:30,growMax:800,vtype:null,maskRe:null,disableKeyFilter:false,allowBlank:true,minLength:0,maxLength:Number.MAX_VALUE,minLengthText:"The minimum length for this field is {0}",maxLengthText:"The maximum length for this field is {0}",selectOnFocus:false,blankText:"This field is required",validator:null,regex:null,regexText:"",emptyText:null,emptyClass:"x-form-empty-field",initEvents:function(){Ext.form.TextField.superclass.initEvents.call(this);if(this.validationEvent=="keyup"){this.validationTask=new Ext.util.DelayedTask(this.validate,this);this.el.on("keyup",this.filterValidation,this);}else{if(this.validationEvent!==false){this.el.on(this.validationEvent,this.validate,this,{buffer:this.validationDelay});}}if(this.selectOnFocus||this.emptyText){this.on("focus",this.preFocus,this);if(this.emptyText){this.on("blur",this.postBlur,this);this.applyEmptyText();}}if(this.maskRe||(this.vtype&&this.disableKeyFilter!==true&&(this.maskRe=Ext.form.VTypes[this.vtype+"Mask"]))){this.el.on("keypress",this.filterKeys,this);}if(this.grow){this.el.on("keyup",this.onKeyUp,this,{buffer:50});this.el.on("click",this.autoSize,this);}},filterValidation:function(e){if(!e.isNavKeyPress()){this.validationTask.delay(this.validationDelay);}},onKeyUp:function(e){if(!e.isNavKeyPress()){this.autoSize();}},reset:function(){Ext.form.TextField.superclass.reset.call(this);this.applyEmptyText();},applyEmptyText:function(){if(this.rendered&&this.emptyText&&this.getRawValue().length<1){this.setRawValue(this.emptyText);this.el.addClass(this.emptyClass);}},preFocus:function(){if(this.emptyText){if(this.getRawValue()==this.emptyText){this.setRawValue("");}this.el.removeClass(this.emptyClass);}if(this.selectOnFocus){this.el.dom.select();}},postBlur:function(){this.applyEmptyText();},filterKeys:function(e){var k=e.getKey();if(!Ext.isIE&&(e.isNavKeyPress()||k==e.BACKSPACE||(k==e.DELETE&&e.button==-1))){return;}var c=e.getCharCode();if(!this.maskRe.test(String.fromCharCode(c)||"")){e.stopEvent();}},setValue:function(v){if(this.emptyText&&v!==undefined&&v!==null&&v!==""){this.el.removeClass(this.emptyClass);}Ext.form.TextField.superclass.setValue.apply(this,arguments);},validateValue:function(_8){if(_8.length<1||_8===this.emptyText){if(this.allowBlank){this.clearInvalid();return true;}else{this.markInvalid(this.blankText);return false;}}if(_8.length<this.minLength){this.markInvalid(String.format(this.minLengthText,this.minLength));return false;}if(_8.length>this.maxLength){this.markInvalid(String.format(this.maxLengthText,this.maxLength));return false;}if(this.vtype){var vt=Ext.form.VTypes;if(!vt[this.vtype](_8)){this.markInvalid(this.vtypeText||vt[this.vtype+"Text"]);return false;}}if(typeof this.validator=="function"){var _a=this.validator(_8);if(_a!==true){this.markInvalid(_a);return false;}}if(this.regex&&!this.regex.test(_8)){this.markInvalid(this.regexText);return false;}return true;},selectText:function(_b,_c){var v=this.getRawValue();if(v.length>0){_b=_b===undefined?0:_b;_c=_c===undefined?v.length:_c;var d=this.el.dom;if(d.setSelectionRange){d.setSelectionRange(_b,_c);}else{if(d.createTextRange){var _f=d.createTextRange();_f.moveStart("character",_b);_f.moveEnd("character",v.length-_c);_f.select();}}}},autoSize:function(){if(!this.grow||!this.rendered){return;}if(!this.metrics){this.metrics=Ext.util.TextMetrics.createInstance(this.el);}var el=this.el;var v=el.dom.value+"&#160;";var w=Math.min(this.growMax,Math.max(this.metrics.getWidth(v)+10,this.growMin));this.el.setWidth(w);this.fireEvent("autosize",this,w);}});


/* ======================================================================================= */
/* merged js/extjs/fix-defer.js                                                            */
/* ======================================================================================= */
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     js
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

(function(){
    var eDefer = Function.prototype.defer;
    Function.prototype.defer = function() {
        var argLen = arguments.length;
        if (argLen==0 || (argLen==1 && arguments[0]==1)) {
            //common for Prototype Ajax requests
            return this.delay.curry(0.01).apply(this, arguments);
        }

        return eDefer.apply(this, arguments);
    }
})();
/* ======================================================================================= */
/* merged js/extjs/ext-tree-checkbox.js                                                    */
/* ======================================================================================= */
/**
 * Retrieve an array of ids of checked nodes
 * @return {Array} array of ids of checked nodes
 */
Ext.tree.TreePanel.prototype.getChecked = function(node){
    var checked = [], i;
    if( typeof node == 'undefined' ) {
        //node = this.rootVisible ? this.getRootNode() : this.getRootNode().firstChild;
        node = this.getRootNode();
    }

    if( node.attributes.checked ) {
        checked.push(node.id);
    }
    if( node.childNodes.length ) {
        for( i = 0; i < node.childNodes.length; i++ ) {
            checked = checked.concat( this.getChecked(node.childNodes[i]) );
        }
    }

    return checked;
};

/**
 * @class Ext.tree.CustomUITreeLoader
 * @extends Ext.tree.TreeLoader
 * Overrides createNode to force uiProvider to be an arbitrary TreeNodeUI to save bandwidth
 */
Ext.tree.CustomUITreeLoader = function() {
    Ext.tree.CustomUITreeLoader.superclass.constructor.apply(this, arguments);
};

Ext.extend(Ext.tree.CustomUITreeLoader, Ext.tree.TreeLoader, {
    createNode : function(attr){
        Ext.apply(attr, this.baseAttr || {});

        if(this.applyLoader !== false){
            attr.loader = this;
        }

        if(typeof attr.uiProvider == 'string'){
            attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
        }

        return(attr.leaf ?
            new Ext.tree.TreeNode(attr) :
                new Ext.tree.AsyncTreeNode(attr));
    }
});


/**
 * @class Ext.tree.CheckboxNodeUI
 * @extends Ext.tree.TreeNodeUI
 * Adds a checkbox to all nodes
 */
Ext.tree.CheckboxNodeUI = function() {
    Ext.tree.CheckboxNodeUI.superclass.constructor.apply(this, arguments);
};

Ext.extend(Ext.tree.CheckboxNodeUI, Ext.tree.TreeNodeUI, {
    /**
     * This is virtually identical to Ext.tree.TreeNodeUI.render, modifications are indicated inline
     */
    render : function(bulkRender){
        var n = this.node;
        var targetNode = n.parentNode ?
            n.parentNode.ui.getContainer() : n.ownerTree.container.dom; /* in later svn builds this changes to n.ownerTree.innerCt.dom */
        if(!this.rendered){
            this.rendered = true;
            var a = n.attributes;

            // add some indent caching, this helps performance when rendering a large tree
            this.indentMarkup = "";
            if(n.parentNode){
                this.indentMarkup = n.parentNode.ui.getChildIndent();
            }

            // modification: added checkbox
            var buf = ['<li class="x-tree-node"><div class="x-tree-node-el ', n.attributes.cls,'">',
                '<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
                '<img src="', this.emptyIcon, '" class="x-tree-ec-icon">',
                '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on">',
                '<input class="l-tcb" '+ (n.disabled ? 'disabled="disabled" ' : '') +' type="checkbox" ', (a.checked ? "checked>" : '>'),
                '<a hidefocus="on" href="',a.href ? a.href : "#",'" ',
                 a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '>',
                 '<span unselectable="on">',n.text,"</span></a></div>",
                '<ul class="x-tree-node-ct" style="display:none;"></ul>',
                "</li>"];

            if(bulkRender !== true && n.nextSibling && n.nextSibling.ui.getEl()){
                this.wrap = Ext.DomHelper.insertHtml("beforeBegin",
                                                            n.nextSibling.ui.getEl(), buf.join(""));
            }else{
                this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf.join(""));
            }
            this.elNode = this.wrap.childNodes[0];
            this.ctNode = this.wrap.childNodes[1];
            var cs = this.elNode.childNodes;
            this.indentNode = cs[0];
            this.ecNode = cs[1];
            this.iconNode = cs[2];
            this.checkbox = cs[3]; // modification: inserted checkbox
            this.anchor = cs[4];
            this.textNode = cs[4].firstChild;
            if(a.qtip){
             if(this.textNode.setAttributeNS){
                 this.textNode.setAttributeNS("ext", "qtip", a.qtip);
                 if(a.qtipTitle){
                     this.textNode.setAttributeNS("ext", "qtitle", a.qtipTitle);
                 }
             }else{
                 this.textNode.setAttribute("ext:qtip", a.qtip);
                 if(a.qtipTitle){
                     this.textNode.setAttribute("ext:qtitle", a.qtipTitle);
                 }
             }
            } else if(a.qtipCfg) {
                a.qtipCfg.target = Ext.id(this.textNode);
                Ext.QuickTips.register(a.qtipCfg);
            }

            this.initEvents();

            // modification: Add additional handlers here to avoid modifying Ext.tree.TreeNodeUI
            Ext.fly(this.checkbox).on('click', this.check.createDelegate(this, [null]));
            n.on('dblclick', function(e) {
                if( this.isLeaf() ) {
                    this.getUI().toggleCheck();
                }
            });

            if(!this.node.expanded){
                this.updateExpandIcon();
            }
        }else{
            if(bulkRender === true) {
                targetNode.appendChild(this.wrap);
            }
        }
    },

    checked : function() {
        return this.checkbox.checked;
    },

    /**
     * Sets a checkbox appropriately.  By default only walks down through child nodes
     * if called with no arguments (onchange event from the checkbox), otherwise
     * it's assumed the call is being made programatically and the correct arguments are provided.
     * @param {Boolean} state true to check the checkbox, false to clear it. (defaults to the opposite of the checkbox.checked)
     * @param {Boolean} descend true to walk through the nodes children and set their checkbox values. (defaults to false)
     */
    check : function(state, descend, bulk) {
        if (this.node.disabled) {
            return;
        }
        var n = this.node;
        var tree = n.getOwnerTree();
        var parentNode = n.parentNode;n
        if( !n.expanded && !n.childrenRendered ) {
            n.expand(false, false, this.check.createDelegate(this, arguments));
        }

        if( typeof bulk == 'undefined' ) {
            bulk = false;
        }
        if( typeof state == 'undefined' || state === null ) {
            state = this.checkbox.checked;
            descend = !state;
            if( state ) {
                n.expand(false, false);
            }
        } else {
            this.checkbox.checked = state;
        }
        n.attributes.checked = state;

        // do we have parents?
        if( parentNode !== null && state ) {
            // if we're checking the box, check it all the way up
            if( parentNode.getUI().check ) {
                //parentNode.getUI().check(state, false, true);
            }
        }
        if( descend && !n.isLeaf() ) {
            var cs = n.childNodes;
      for(var i = 0; i < cs.length; i++) {
        //cs[i].getUI().check(state, true, true);
      }
        }
        if( !bulk ) {
            tree.fireEvent('check', n, state);
        }
    },

    toggleCheck : function(state) {
        this.check(!this.checkbox.checked, true);
    }

});


/**
 * @class Ext.tree.CheckNodeMultiSelectionModel
 * @extends Ext.tree.MultiSelectionModel
 * Multi selection for a TreePanel containing Ext.tree.CheckboxNodeUI.
 * Adds enhanced selection routines for selecting multiple items
 * and key processing to check/clear checkboxes.
 */
Ext.tree.CheckNodeMultiSelectionModel = function(){
   Ext.tree.CheckNodeMultiSelectionModel.superclass.constructor.call(this);
};

Ext.extend(Ext.tree.CheckNodeMultiSelectionModel, Ext.tree.MultiSelectionModel, {
    init : function(tree){
        this.tree = tree;
        tree.el.on("keydown", this.onKeyDown, this);
        tree.on("click", this.onNodeClick, this);
    },

    /**
     * Handle a node click
     * If ctrl key is down and node is selected will unselect the node.
     * If the shift key is down it will create a contiguous selection
     * (see {@link Ext.tree.CheckNodeMultiSelectionModel#extendSelection} for the limitations)
     */
    onNodeClick : function(node, e){
        if (node.disabled) {
            return;
        }
        if( e.shiftKey && this.extendSelection(node) ) {
            return true;
        }
        if( e.ctrlKey && this.isSelected(node) ) {
            this.unselect(node);
        } else {
            this.select(node, e, e.ctrlKey);
        }
    },

    /**
     * Selects all nodes between the previously selected node and the one that the user has just selected.
     * Will not span multiple depths, so only children of the same parent will be selected.
     */
    extendSelection : function(node) {
        var last = this.lastSelNode;
        if( node == last || !last ) {
            return false; /* same selection, process normally normally */
        }

        if( node.parentNode == last.parentNode ) {
            var cs = node.parentNode.childNodes;
            var i = 0, attr='id', selecting=false, lastSelect=false;
            this.clearSelections(true);
            for( i = 0; i < cs.length; i++ ) {
                // We have to traverse the entire tree b/c don't know of a way to find
                // a numerical representation of a nodes position in a tree.
                if( cs[i].attributes[attr] == last.attributes[attr] || cs[i].attributes[attr] == node.attributes[attr] ) {
                    // lastSelect ensures that we select the final node in the list
                    lastSelect = selecting;
                    selecting = !selecting;
                }
                if( selecting || lastSelect ) {
                    this.select(cs[i], null, true);
                    // if we're selecting the last node break to avoid traversing the entire tree
                    if( lastSelect ) {
                        break;
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    },

    /**
     * Traps the press of the SPACE bar and sets the check state of selected nodes to the opposite state of
     * the selected or last selected node.  Assume you have the following five Ext.tree.CheckboxNodeUIs:
     * [X] One, [X] Two, [X] Three, [ ] Four, [ ] Five
     * If you select them in this order: One, Two, Three, Four, Five and press the space bar they all
     * will be <b>checked</b> (the opposite of the checkbox state of Five).
     * If you select them in this order: Five, Four, Three, Two, One and press the space bar they all
     * will be <b>unchecked</b> which is the opposite of the checkbox state of One.
     */
    onKeyDown : Ext.tree.DefaultSelectionModel.prototype.onKeyDown.createInterceptor(function(e) {
        var s = this.selNode || this.lastSelNode;
        // undesirable, but required
        var sm = this;
        if(!s){
            return;
        }
        var k = e.getKey();
        switch(k){
                case e.SPACE:
                    e.stopEvent();
                    var sel = this.getSelectedNodes();
                    var state = !s.getUI().checked();
                    if( sel.length == 1 ) {
                        s.getUI().check(state, !s.isLeaf());
                    } else {
                        for( var i = 0; i < sel.length; i++ ) {
                            sel[i].getUI().check(state, !sel[i].isLeaf() );
                        }
                    }
            break;
        }

        return true;
    })
});

