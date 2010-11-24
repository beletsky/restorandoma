// dhtml library by rod.o2theory.com | soloplay@msn.com
// keep these two lines and you're free to use this code

// last updated: 06.16.2003

// fixed and added with functionality by Leo Tsvaigboim 18.03.2005

// lib namespace
var lib = {
    // browser check (needed for hacks and bug fixes for specific browsers)
    browsercheck: function()
    {
        this.ua = navigator.userAgent.toLowerCase();
        this.dom = document.getElementById ? 1 : 0;
        this.op7 = (this.dom && this.ua.indexOf('opera 7') > -1 || this.ua.indexOf('opera/7') > -1) ? 1 : 0;
        this.ie5 = (this.dom && this.ua.indexOf('msie 5') > -1) ? 1 : 0;
        this.ie6 = (this.dom && this.ua.indexOf('msie 6') > -1) ? 1 : 0;
        this.moz = (this.dom && this.ua.indexOf('mozilla') > -1 && this.ua.indexOf('gecko') > -1) ? 1 : 0;
        this.ie = (this.ie5 || this.ie6) ? 1 : 0;
    },

    // utilities
    px: function(n) { return (typeof n == 'number') ? n + 'px' : 0; },
    rand: function(x, y) { return (Math.round(Math.random() * (y - x)) + x); },
    
    // global reference to the dhtml object
    registry: new Array(),
    
    // dhtml object constructor
    dhtmlobject: function(id)
    {
        this.el = document.getElementById ? document.getElementById(id) : null;
        this.css = this.el.style;
        this.cname = this.el.className;
        this.i = lib.registry.length; lib.registry[this.i] = this;
        this.w = this.el.offsetWidth ? this.el.offsetWidth : 0;
        this.h = this.el.offsetHeight ? this.el.offsetHeight : 0;
        this.x = this.el.offsetLeft ? this.el.offsetLeft : 0;
        this.y = this.el.offsetTop ? this.el.offsetTop : 0;
        this.o = 100;
        this.fadetimer = this.slidetimer = this.resizetimer = 0;
        this.fading = this.sliding = this.resizing = false;
    },
    
    checkboxobject: function (text)
    {
        var prms = new Array();
        prms = text.split(":");
        this.id = parseInt(prms[0]);
        this.value = parseInt(prms[1]);
        this.active = parseInt(prms[2]);
    }
}

// lib.dhtmlobject dhtml methods
lib.dhtmlobject.prototype = {
    // update object values
    update: function()
    {
        this.w = this.el.offsetWidth ? this.el.offsetWidth : 0;
        this.h = this.el.offsetHeight ? this.el.offsetHeight : 0;
        this.x = this.el.offsetLeft ? this.el.offsetLeft : 0;
        this.y = this.el.offsetTop ? this.el.offsetTop : 0;
    },

    // moves a layer to (x, y) pixels
    moveto: function(x, y)
    {
        if (x != null && typeof x == 'number') { this.x = x; this.css.left = x + 'px'; }
        if (y != null && typeof y == 'number') { this.y = y; this.css.top = y + 'px'; }
    },

    // moves a layer by (x, y) pixels
    moveby: function(x, y) { this.moveto(this.x + x, this.y + y); },

    // visibility
    show: function() { this.css.visibility = 'visible'; },
    hide: function() { this.css.visibility = 'hidden'; },

    // resize layer to (width, height) pixels
    setsize: function(w, h)
    {
        if (w != null && typeof w == 'number') { this.w = w; this.css.width = w + 'px'; }
        if (h != null && typeof h == 'number') { this.h = h; this.css.height = h + 'px'; }
    },

    // write content to layer
    write: function(text)
    {
        if (typeof this.el.innerHTML != 'undefined')
        {
            this.el.innerHTML = text;
            this.update();
        }
    },

    // set layer opacity (in percent, between 0 and 100)
    setopacity: function(o)
    {
        if (typeof o == 'number')
        {
            if (typeof this.css.MozOpacity != 'undefined') this.css.MozOpacity = (o / 100);
            else if (this.el.filters) this.css.filter = 'alpha(opacity = ' + o + ')';
            this.o = o;
        }
    }

}
// end of lib.dhtmlobject dhtml methods

// event object
lib.event = {
    init: function(e)
    {
        lib.document.getcanvas();
        e = window.event ? window.event : e;
        this.mousex = typeof e.clientX != 'undefined' ? e.clientX + lib.document.scrollx : 0;
        this.mousey = typeof e.clientY != 'undefined' ? e.clientY + lib.document.scrolly : 0;
        this.layerx = typeof e.offsetX != 'undefined' ? e.offsetX : typeof e.layerX != 'undefined' ? e.layerX : 0;
        this.layery = typeof e.offsetY != 'undefined' ? e.offsetY : typeof e.layerY != 'undefined' ? e.layerY : 0;
        this.type = e.type;
        this.target = e.srcElement || e.target;
//      if (this.target.nodeType == 3 || this.target.tagName.toLowerCase() == 'img') this.target = this.target.parentNode;
    },

    // prevents event default action
    preventdefault: function(e)
    {
        if (window.event) window.event.returnValue = false;
        else if (e.preventDefault) e.preventDefault();
    },

    // cancels event bubbling to parent elements
    cancelbubble: function(e)
    {
        if (window.event) window.event.cancelBubble = true;
        else if (e.stopPropagation) e.stopPropagation();
    }
}

// document object
lib.document = {
    getcanvas: function()
    {
        if (document.documentElement && document.documentElement.scrollLeft) this.scrollx = document.documentElement.scrollLeft;
        else if (document.body && document.body.scrollLeft) this.scrollx = document.body.scrollLeft;
        else if (window.scrollX) this.scrollx = window.scrollX;
        else this.scrollx = 0;

        if (document.documentElement && document.documentElement.scrollTop) this.scrolly = document.documentElement.scrollTop;
        else if (document.body && document.body.scrollTop) this.scrolly = document.body.scrollTop;
        else if (window.scrollY) this.scrolly = window.scrollY;
        else this.scrolly = 0;
        
        if (document.documentElement && document.documentElement.clientWidth) this.width = document.documentElement.clientWidth;
        else if (document.body && document.body.clientWidth) this.width = document.body.clientWidth;
        else if (window.innerWidth) this.width = window.innerWidth;
        else this.width = 0;

        if (document.documentElement && document.documentElement.clientHeight) this.height = document.documentElement.clientHeight;
        else if (document.body && document.body.clientHeight) this.height = document.body.clientHeight;
        else if (window.innerHeight) this.height = window.innerHeight;
        else this.height = 0;
        
        this.w = this.width + this.scrollx;
        this.h = this.height + this.scrolly;
    }
}


// event listener
var listener = {
    // attach event to an element
    add: function(obj, et, fn, capture)
    {
        if (obj.addEventListener) { obj.addEventListener(et, fn, capture); return true; }
        else if (obj.attachEvent) { var ae = obj.attachEvent('on' + et, fn); return ae; }
        // else { obj['on' + et] = fn; }
    },
    // detach event from an element
    remove: function(obj, et, fn, capture)
    {
        if (obj.removeEventListener) { obj.removeEventListener(et, fn, capture); return true; }
        else if (obj.detachEvent) { var re = obj.detachEvent('on' + et, fn); return re; }
        // else { obj['on' + et] = null; }
    }
}

// browser check variable
var bw = new lib.browsercheck();

//modifying of document stylesheet
if (navigator.userAgent.indexOf("Gecko") != -1)
{
//    if (document.styleSheets[0].cssRules[0].selectorText = "ul")
//    { document.styleSheets[0].cssRules[0].style.marginLeft = "-24px"; }
    if (document.styleSheets[0].cssRules[1].selectorText = "ul li")
    { document.styleSheets[0].cssRules[1].style.listStyleImage = "url(../i/icn01_nc.gif)"; }
}

// getting horizontal position of element
function getX(el)
{
    var X=0;
    X=el.offsetLeft ? el.offsetLeft : 0
    if (el.offsetParent)
    {
        X=X+getX(el.offsetParent);
    }
    return X;
}

// getting vertical position of element
function getY(el)
{
    if (el)
    {
        var Y=0;
        Y=el.offsetTop ? el.offsetTop: 0
        if (el.offsetParent)
        {
            Y=Y+getY(el.offsetParent);
        }
        return Y;
    }
}

// getting element parent with name containing prefix
function getParent(el, prefix)
{
    return (el.id && el.id.indexOf(prefix)!=-1)?el.id:(el.parentNode)?getParent(el.parentNode, prefix):0;
}

//chanhing element class
function cc (el, overFlag, num)
{
//      el = el.parentNode;
    switch (num)
    {
        case 1:
        el = el.parentNode;
        break;
        case 2:
//      el = el.parentNode.parentNode.childNodes[0];
        el = el.parentNode.parentNode.parentNode.parentNode;
        break;
    }
    if (overFlag && el.className.indexOf("-over") == -1)
    {
        el.className += '-over';
    }
    else if (!overFlag && el.className.indexOf("-over") != -1)
    {
        re = /-over$/;
        el.className = el.className.replace(re, "");
    }
}

//changing element source
function sc (el, overFlag, num)
{
//  el = el.childNodes[0];
    switch (num)
    {
        case 1:
        el = el.childNodes[0];
        break;
        case 2:
            el = el;
        break;
        case 3:
            el = el.parentNode.parentNode.childNodes[1].childNodes[0].childNodes[0];
        break;
    }
    if (overFlag && el.src.indexOf("-over") == -1 && el.src.indexOf("-dis") == -1)
    {
        re = /(.+)(\.\w{3})/;
        el.src = el.src.replace(re, "$1-over$2");
    }
    else if (overFlag && el.src.indexOf("-over") == -1 && el.src.indexOf("-dis") != -1)
    {
        re = /(.+)(-dis\.\w{3})/;
        el.src = el.src.replace(re, "$1-over$2");
    }
    else if (!overFlag && el.src.indexOf("-over") != -1)
    {
//      re = /(.+)-over(\.\w{3})$/;
        re = /-over/;
        el.src = el.src.replace(re, "");
    }
}

function scdis (el, active)
{
    if (!active && el.src.indexOf("-dis") == -1)
    {
        re = /(.+)(\.\w{3})$/;
        el.src = el.src.replace(re, "$1-dis$2");
    }
    else if (active && el.src.indexOf("-dis") != -1)
    {
//      re = /(.+)-dis(\.\w{3})$/;
        re = /-dis/;
        el.src = el.src.replace(re, "");
    }
}

//open window
var opened = false;
function ow(pageSrc, width, height)
{
features="top=50,left=100,width=" + width + ",height=" + height + ",toolbar=no,menubar=no,location=no,directories=no,scrollbars=yes,resizable=yes";
if ((!opened) || (newWin.closed))
{
    opened = true;
    newWin = window.open(pageSrc , "newWindow" , features);
    newWin.focus();
    var w = (window.document.body.offsetWidth)?window.document.body.offsetWidth -  4:window.document.innerWidth;
    var h = (window.document.body.offsetHeight)?window.document.body.offsetHeight - 4:window.document.innerHeight;
    if ((w != width) || (h != height)) {newWin.resizeTo(width + 10, height + 29);}
}
else
{
    if ((opened) || (!newWin.closed))
    {
        newWin.location = pageSrc;
        newWin.focus();
    }
}
}

function p(value)
{
    return (!isNaN(parseInt(value)))?parseInt(value):0;
}

function empty(value)
{
    return (!(isNaN(value) || value == '' || value == 0))?true:false;
}

