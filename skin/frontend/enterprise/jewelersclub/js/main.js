var reinitCategoryControls = function()
{
    jQuery('span.opener-view-all').click(function(){
        jQuery('div.section-category-second-part').show();
        jQuery('div.section-category-first-part').hide();
        jQuery('ul.category-grid').each(function(index, el) {
            var next = jQuery(el).parent('div.list_carousel').prev('a.next');
            next.attr('id', 'next-category-'+index);
            next.prev('a.prev').attr('id', 'prev-category-'+index);
            jQuery(el).carouFredSel({
                items :'variable',
                width : "100%",
                align: "left",
                circular: false,
                infinite: false,
                auto : false,
                prev: '#prev-category-' + index,
                next: '#next-category-' + index,
                mousewheel: true,
                swipe: {
                    onMouse: true,
                    onTouch: true
                }
            });
        });

        sidebarBg();
    });
}
jQuery(document).ready(function() {
    jQuery(".fancybox-thumb").fancybox();
});
jQuery(document).ready(function() {
    var offset = 220;
    var duration = 500;
    jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top').fadeIn(duration);
        } else {
            jQuery('.back-to-top').fadeOut(duration);
        }
    });

    jQuery('.back-to-top').click(function(event) {
        event.preventDefault();
        jQuery('html, body').animate({scrollTop: 0}, duration);
        return false;
    });
    jQuery('.dropdown-extra').each(function(){
        var _parent = jQuery(this);
        var _opener =jQuery(_parent).find('.dropdown-opener');
        var _dropContent =jQuery(_parent).find('.dropdown-content');
        jQuery(_opener).click(function(){
            jQuery(_dropContent).slideToggle('slow')
            setTimeout(function(){
                jQuery(document).one('click', function(e){
                    if(jQuery(_parent).has(e.target).length === 0 && jQuery(_parent).has(e.target).length === 0 ){
                        jQuery(_dropContent).slideToggle('slow');
                    }
                });
            }, 10);
        });
        document.observe('keyup',(function(e){
            if(e.keyCode == 27 && !jQuery(_dropContent).is(':hidden')){
                jQuery(_dropContent).slideToggle('slow');
            }
        }));
    });
    function layeredAccordion(){
        if(jQuery(window).width() > 768){
            jQuery('.itoris_laynav dd').each( function(){
                var _parent = jQuery(this);
                var _showBtn =jQuery(_parent).find('span.show');
                var _hideBtn =jQuery(_parent).find('span.hide');
                if( ( jQuery(_parent).find('li').length)  > 5){
                    jQuery(_showBtn).show();
                    jQuery(_parent).find('li').hide();
                    jQuery(_parent).find('li:lt(5)').show();
                    jQuery(_showBtn).click(function(){
                        jQuery(_parent).find('li').show();
                        jQuery(_hideBtn).show();
                        jQuery(_showBtn).hide();
                    });
                    jQuery(_hideBtn).click(function(){
                        jQuery(_parent).find('li').hide();
                        jQuery(_parent).find('li:lt(5)').show();
                        jQuery(_hideBtn).hide();
                        jQuery(_showBtn).show();
                    });
                }
            });
        }
        else{
            jQuery('.itoris_laynav dd').each( function(){
                var _parent = jQuery(this);
                var _showBtn =jQuery(_parent).find('span.show');
                var _hideBtn =jQuery(_parent).find('span.hide');
                jQuery(_showBtn).hide();
                jQuery(_hideBtn).hide();
                jQuery(_parent).find('li').show();
            });
        }
    }
    //layeredAccordion(); //@see manNavigationItemsShowHide();
    manNavigationItemsShowHide();
    if(jQuery('body').hasClass('catalog-category-view')){
        reinitCategoryControls();
    }
    initOpenClose();
    initInputs();
    initCarousel();

    jQuery(window).resize(function(){
        sidebarBg();
    });

    jQuery('#navigation li a span').each(function(index, el){
        var text = jQuery(el).text().toLocaleLowerCase();
        if(text == 'clearance'){
            jQuery(el).parent().addClass('clearance');
        }
    });
    if(jQuery('div.nav-container-tablet').length > 0){
        var listItems = jQuery('div.nav-container-tablet div.block-content-holder > ul > li')
        listItems.each(function(){
           var curentMenuItem = jQuery(this);
            curentMenuItem.find('> a').on('click', function(event){
                event.preventDefault();
                event.stopPropagation();
                listItems.removeClass('active');
                jQuery(this).parent().addClass('active');
            });
        });
    }
    jQuery('div.nav-container-tablet').each(function(){
        var _parent = jQuery(this);
        var _opener =jQuery(_parent).find('a.view-all');
        var _dropContent =jQuery(_parent).find('.block-content');
        jQuery(_opener).click(function(){
            jQuery(_dropContent).slideToggle('slow')
            setTimeout(function(){
                jQuery(document).one('click', function(e){
                    if(jQuery(_parent).has(e.target).length === 0 && jQuery(_parent).has(e.target).length === 0 ){
                        jQuery(_dropContent).slideToggle('slow');
                    }
                });
            }, 10);
        });
        document.observe('keyup',(function(e){
            if(e.keyCode == 27 && !jQuery(_dropContent).is(':hidden')){
                jQuery(_dropContent).slideToggle('slow');
            }
        }));
    });
});
jQuery(window).resize(function(){
    jQuery('div.nav-container-tablet .block-content').hide();
});
jQuery(window).ready(function(){
    sidebarBg();
});
jQuery(document).ready(function () {
    smartDrop();
    jQuery('#navigation li').hover(function () {
        jQuery(this).addClass('hover');
    }, function () {
        var el = jQuery(this);
        setTimeout(function () {
            el.removeClass('hover');
        }, 600)
    });
});
var itemsCount = 5;
function manNavigationItemsOpen(el, hasFilterInState){
    var _btnMore = el.find('.show');
    var _btnLess = el.find('.hide');
    var _items = el.find('li');
    if (hasFilterInState == 0) {
        jQuery(_items).hide();
        jQuery(_items).filter(':lt(' + itemsCount + ')').show();
        jQuery(_btnMore).show();
        jQuery(_btnLess).hide();
    } else {
        jQuery(_btnMore).hide();
        jQuery(_btnLess).show();
    }
    jQuery(_btnMore).on('click', function(){
        jQuery(_items).show();
        jQuery(this).hide();
        jQuery(_btnLess).show();
    })
    jQuery(_btnLess).on('click', function(){
        jQuery(this).hide();
        jQuery(_btnMore).show();
        jQuery(_items).hide();
        jQuery(_items).filter(':lt(' + itemsCount + ')').show();
    })
};
function manNavigationItemsShowHide(){
    if(jQuery('#narrow-by-list').length > 0){
        jQuery('#narrow-by-list .accordion-filter').each(function(){
            var _items = jQuery(this).find('li');
            jQuery(_items).hide();
            jQuery(_items).filter(':lt(' + itemsCount + ')').show();
            var _btnMore = jQuery(this).parent().find('.show');
            var _btnLess= jQuery(this).parent().find('.hide');
            jQuery(_btnMore).on('click', function(){
                jQuery(_items).show();
                jQuery(this).hide();
                jQuery(_btnLess).show();
            })
            jQuery(_btnLess).on('click', function(){
                jQuery(this).hide();
                jQuery(_btnMore).show();
                jQuery(_items).hide();
                jQuery(_items).filter(':lt(' + itemsCount + ')').show();
            })
        });
    }
};
function smartDrop() {
    var nav = jQuery('#navigation');
    var drop = nav.find('.drop');
    var rightClass = 'right-side';
    drop.each(function () {
        var $this = jQuery(this);
        $this.show();
        $this.data('dropWidth', $this.children().outerWidth());
        $this.removeAttr('style');
    });
    function checkViewport(winWidth) {
        drop.each(function () {
            var $this = jQuery(this);
            if ($this.parent().offset().left + $this.data('dropWidth') > winWidth) {
                $this.parent().addClass(rightClass)
            } else if ($this.parent().hasClass(rightClass)) {
                $this.parent().removeClass(rightClass);
            }
        });
    }
    checkViewport(jQuery(window).width());
    jQuery(window).resize(function () {
        checkViewport(jQuery(window).width());
    });
}
function sidebarBg(){
    var myBlock = jQuery('div.col-left.sidebar');
    jQuery(myBlock).height('auto');
    var myParentBlock = jQuery('div.main-section-container').height() + "px";
    if (jQuery(window).width() > 767){
        jQuery(myBlock).innerHeight(myParentBlock);
    }else{
        jQuery(myBlock).height('auto');
    }
    if( jQuery('div.col2-right-layout').length > 0){
        var myBlockRight = jQuery('div.col-right.sidebar');
        jQuery(myBlockRight).height('auto');
        if (jQuery(window).width() > 767){
            jQuery(myBlockRight).innerHeight(myParentBlock);
        }else{
            jQuery(myBlockRight).height('auto');
        }
    }
}
function initOpenClose() {
    jQuery('div.links-holder.slide').openClose({
        activeClass: 'expanded',
        opener: 'a.opener-mobile',
        slider: '.links',
        animSpeed: 500,
        effect: 'slide'
    });
    jQuery('div.block.slide').openClose({
        activeClass: 'expanded',
        opener: 'a.opener-mobile',
        slider: '.block-content',
        animSpeed: 500,
        effect: 'slide'
    });
    jQuery('div.block.itoris_laynav').openClose({
        activeClass: 'expanded',
        opener: 'a.opener-mobile',
        slider: '.block-content',
        animSpeed: 500,
        effect: 'slide'
    });
    /*jQuery('.opener-navigation').openClose({
        activeClass: 'expanded',
        opener: 'a.view-all',
        slider: '.block-content',
        animSpeed: 200,
        effect: 'slide'
    });*/
}
// clear inputs on focus
function initInputs() {
    PlaceholderInput.replaceByOptions({
        // filter options
        clearInputs: true,
        clearTextareas: true,
        clearPasswords: true,
        skipClass: 'default',

        // input options
        wrapWithElement: false,
        showUntilTyping: false,
        getParentByClass: false,
        placeholderAttr: 'value'
    });
}

// scroll gallery init
function initCarousel() {
    if (jQuery('div.carousel').length > 0){
        jQuery('.carousel').scrollAbsoluteGallery({
            mask: 'div.gmask',
            slider: '.slideset',
            slides: '.slide',
            btnPrev: 'a.btn-prev',
            btnNext: 'a.btn-next',
            generatePagination: '.pagination',
            stretchSlideToMask: true,
            maskAutoSize: true,
            autoRotation: true,
            switchTime: 5000,
            animSpeed: 500
        });
    }
}


// page init
jQuery(function(){
    initLightbox();
});

// fancybox modal popup init
function initLightbox() {
    jQuery('a.lightbox, a[rel*="lightbox"]').each(function(){
        var link = jQuery(this);
        link.fancybox({
            padding: 0,
            margin: 0,
            cyclic: false,
            autoScale: true,
            overlayShow: true,
            overlayOpacity: 0.65,
            overlayColor: '#ffffff',
            titlePosition: 'inside',
            onComplete: function(box) {
                if(link.attr('href').indexOf('#') === 0) {
                    jQuery('#fancybox-content').find('a.close').unbind('click.fb').bind('click.fb', function(e){
                        jQuery.fancybox.close();
                        e.preventDefault();
                    });
                }
            }
        });
    });
}

/* Fancybox overlay fix */
jQuery(function(){
    // detect device type
    var isTouchDevice = ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch;
    var isWinPhoneDevice = navigator.msPointerEnabled && /MSIE 10.*Touch/.test(navigator.userAgent);

    if(!isTouchDevice && !isWinPhoneDevice) {
        // create <style> rules
        var head = document.getElementsByTagName('head')[0],
            style = document.createElement('style'),
            rules = document.createTextNode('#fancybox-overlay'+'{'+
                'position:fixed;'+
                'top:0;'+
                'left:0;'+
                '}');

        // append style element
        style.type = 'text/css';
        if(style.styleSheet) {
            style.styleSheet.cssText = rules.nodeValue;
        } else {
            style.appendChild(rules);
        }
        head.appendChild(style);
    }
});

/*
 * FancyBox - jQuery Plugin
 * Simple and fancy lightbox alternative
 *
 * Examples and documentation at: http://fancybox.net
 *
 * Copyright (c) 2008 - 2010 Janis Skarnelis
 * That said, it is hardly a one-person project. Many people have submitted bugs, code, and offered their advice freely. Their support is greatly appreciated.
 *
 * Version: 1.3.4 (11/11/2010)
 * Requires: jQuery v1.3+
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
;(function(B){var L,T,Q,M,d,m,J,A,O,z,C=0,H={},j=[],e=0,G={},y=[],f=null,o=new Image(),i=/\.(jpg|gif|png|bmp|jpeg)(.*)?$/i,k=/[^\.]\.(swf)\s*$/i,p,N=1,h=0,t="",b,c,P=false,s=B.extend(B("<div/>")[0],{prop:0}),S=/MSIE 6/.test(navigator.userAgent)&&B.browser.version<7&&!window.XMLHttpRequest,r=function(){T.hide();o.onerror=o.onload=null;if(f){f.abort()}L.empty()},x=function(){if(false===H.onError(j,C,H)){T.hide();P=false;return}H.titleShow=false;H.width="auto";H.height="auto";L.html('<p id="fancybox-error">The requested content cannot be loaded.<br />Please try again later.</p>');n()},w=function(){var Z=j[C],W,Y,ab,aa,V,X;r();H=B.extend({},B.fn.fancybox.defaults,(typeof B(Z).data("fancybox")=="undefined"?H:B(Z).data("fancybox")));X=H.onStart(j,C,H);if(X===false){P=false;return}else{if(typeof X=="object"){H=B.extend(H,X)}}ab=H.title||(Z.nodeName?B(Z).attr("title"):Z.title)||"";if(Z.nodeName&&!H.orig){H.orig=B(Z).children("img:first").length?B(Z).children("img:first"):B(Z)}if(ab===""&&H.orig&&H.titleFromAlt){ab=H.orig.attr("alt")}W=H.href||(Z.nodeName?B(Z).attr("href"):Z.href)||null;if((/^(?:javascript)/i).test(W)||W=="#"){W=null}if(H.type){Y=H.type;if(!W){W=H.content}}else{if(H.content){Y="html"}else{if(W){if(W.match(i)){Y="image"}else{if(W.match(k)){Y="swf"}else{if(B(Z).hasClass("iframe")){Y="iframe"}else{if(W.indexOf("#")===0){Y="inline"}else{Y="ajax"}}}}}}}if(!Y){x();return}if(Y=="inline"){Z=W.substr(W.indexOf("#"));Y=B(Z).length>0?"inline":"ajax"}H.type=Y;H.href=W;H.title=ab;if(H.autoDimensions){if(H.type=="html"||H.type=="inline"||H.type=="ajax"){H.width="auto";H.height="auto"}else{H.autoDimensions=false}}if(H.modal){H.overlayShow=true;H.hideOnOverlayClick=false;H.hideOnContentClick=false;H.enableEscapeButton=false;H.showCloseButton=false}H.padding=parseInt(H.padding,10);H.margin=parseInt(H.margin,10);L.css("padding",(H.padding+H.margin));B(".fancybox-inline-tmp").unbind("fancybox-cancel").bind("fancybox-change",function(){B(this).replaceWith(m.children())});switch(Y){case"html":L.html(H.content);n();break;case"inline":if(B(Z).parent().is("#fancybox-content")===true){P=false;return}B('<div class="fancybox-inline-tmp" />').hide().insertBefore(B(Z)).bind("fancybox-cleanup",function(){B(this).replaceWith(m.children())}).bind("fancybox-cancel",function(){B(this).replaceWith(L.children())});B(Z).appendTo(L);n();break;case"image":P=false;B.fancybox.showActivity();o=new Image();o.onerror=function(){x()};o.onload=function(){P=true;o.onerror=o.onload=null;F()};o.src=W;break;case"swf":H.scrolling="no";aa='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+H.width+'" height="'+H.height+'"><param name="movie" value="'+W+'"></param>';V="";B.each(H.swf,function(ac,ad){aa+='<param name="'+ac+'" value="'+ad+'"></param>';V+=" "+ac+'="'+ad+'"'});aa+='<embed src="'+W+'" type="application/x-shockwave-flash" width="'+H.width+'" height="'+H.height+'"'+V+"></embed></object>";L.html(aa);n();break;case"ajax":P=false;B.fancybox.showActivity();H.ajax.win=H.ajax.success;f=B.ajax(B.extend({},H.ajax,{url:W,data:H.ajax.data||{},dataType:"text",error:function(ac,ae,ad){if(ac.status>0){x()}},success:function(ad,af,ac){var ae=typeof ac=="object"?ac:f;if(ae.status==200||ae.status===0){if(typeof H.ajax.win=="function"){X=H.ajax.win(W,ad,af,ac);if(X===false){T.hide();return}else{if(typeof X=="string"||typeof X=="object"){ad=X}}}L.html(ad);n()}}}));break;case"iframe":E();break}},n=function(){var V=H.width,W=H.height;if(V.toString().indexOf("%")>-1){V=parseInt((B(window).width()-(H.margin*2))*parseFloat(V)/100,10)+"px"}else{V=V=="auto"?"auto":V+"px"}if(W.toString().indexOf("%")>-1){W=parseInt((B(window).height()-(H.margin*2))*parseFloat(W)/100,10)+"px"}else{W=W=="auto"?"auto":W+"px"}L.wrapInner('<div style="width:'+V+";height:"+W+";overflow: "+(H.scrolling=="auto"?"auto":(H.scrolling=="yes"?"scroll":"hidden"))+';position:relative;"></div>');H.width=L.width();H.height=L.height();E()},F=function(){H.width=o.width;H.height=o.height;B("<img />").attr({id:"fancybox-img",src:o.src,alt:H.title}).appendTo(L);E()},E=function(){var W,V;T.hide();if(M.is(":visible")&&false===G.onCleanup(y,e,G)){B('.fancybox-inline-tmp').trigger('fancybox-cancel');P=false;return}P=true;B(m.add(Q)).unbind();B(window).unbind("resize.fb scroll.fb");B(document).unbind("keydown.fb");if(M.is(":visible")&&G.titlePosition!=="outside"){M.css("height",M.height())}y=j;e=C;G=H;if(G.overlayShow){Q.css({"background-color":G.overlayColor,opacity:G.overlayOpacity,cursor:G.hideOnOverlayClick?"pointer":"auto",height:B(document).height()});if(!Q.is(":visible")){if(S){B("select:not(#fancybox-tmp select)").filter(function(){return this.style.visibility!=="hidden"}).css({visibility:"hidden"}).one("fancybox-cleanup",function(){this.style.visibility="inherit"})}Q.show()}}else{Q.hide()}c=R();l();if(M.is(":visible")){B(J.add(O).add(z)).hide();W=M.position(),b={top:W.top,left:W.left,width:M.width(),height:M.height()};V=(b.width==c.width&&b.height==c.height);m.fadeTo(G.changeFade,0.3,function(){var X=function(){m.html(L.contents()).fadeTo(G.changeFade,1,v)};B('.fancybox-inline-tmp').trigger('fancybox-change');m.empty().removeAttr("filter").css({"border-width":G.padding,width:c.width-G.padding*2,height:H.autoDimensions?"auto":c.height-h-G.padding*2});if(V){X()}else{s.prop=0;B(s).animate({prop:1},{duration:G.changeSpeed,easing:G.easingChange,step:U,complete:X})}});return}M.removeAttr("style");m.css("border-width",G.padding);if(G.transitionIn=="elastic"){b=I();m.html(L.contents());M.show();if(G.opacity){c.opacity=0}s.prop=0;B(s).animate({prop:1},{duration:G.speedIn,easing:G.easingIn,step:U,complete:v});return}if(G.titlePosition=="inside"&&h>0){A.show()}m.css({width:c.width-G.padding*2,height:H.autoDimensions?"auto":c.height-h-G.padding*2}).html(L.contents());M.css(c).fadeIn(G.transitionIn=="none"?0:G.speedIn,v)},D=function(V){if(V&&V.length){if(G.titlePosition=="float"){return'<table id="fancybox-title-float-wrap" cellpadding="0" cellspacing="0"><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">'+V+'</td><td id="fancybox-title-float-right"></td></tr></table>'}return'<div id="fancybox-title-'+G.titlePosition+'">'+V+"</div>"}return false},l=function(){t=G.title||"";h=0;A.empty().removeAttr("style").removeClass();if(G.titleShow===false){A.hide();return}t=B.isFunction(G.titleFormat)?G.titleFormat(t,y,e,G):D(t);if(!t||t===""){A.hide();return}A.addClass("fancybox-title-"+G.titlePosition).html(t).appendTo("body").show();switch(G.titlePosition){case"inside":A.css({width:c.width-(G.padding*2),marginLeft:G.padding,marginRight:G.padding});h=A.outerHeight(true);A.appendTo(d);c.height+=h;break;case"over":A.css({marginLeft:G.padding,width:c.width-(G.padding*2),bottom:G.padding}).appendTo(d);break;case"float":A.css("left",parseInt((A.width()-c.width-40)/2,10)*-1).appendTo(M);break;default:A.css({width:c.width-(G.padding*2),paddingLeft:G.padding,paddingRight:G.padding}).appendTo(M);break}A.hide()},g=function(){if(G.enableEscapeButton||G.enableKeyboardNav){B(document).bind("keydown.fb",function(V){if(V.keyCode==27&&G.enableEscapeButton){V.preventDefault();B.fancybox.close()}else{if((V.keyCode==37||V.keyCode==39)&&G.enableKeyboardNav&&V.target.tagName!=="INPUT"&&V.target.tagName!=="TEXTAREA"&&V.target.tagName!=="SELECT"){V.preventDefault();B.fancybox[V.keyCode==37?"prev":"next"]()}}})}if(!G.showNavArrows){O.hide();z.hide();return}if((G.cyclic&&y.length>1)||e!==0){O.show()}if((G.cyclic&&y.length>1)||e!=(y.length-1)){z.show()}},v=function(){if(B.support.opacity===false){m.get(0).style.removeAttribute("filter");M.get(0).style.removeAttribute("filter")}if(H.autoDimensions){m.css("height","auto")}M.css("height","auto");if(t&&t.length){A.show()}if(G.showCloseButton){J.show()}g();if(G.hideOnContentClick){m.bind("click",B.fancybox.close)}if(G.hideOnOverlayClick){Q.bind("click",B.fancybox.close)}B(window).bind("resize.fb",B.fancybox.resize);if(G.centerOnScroll){B(window).bind("scroll.fb",B.fancybox.center)}if(G.type=="iframe"){B('<iframe id="fancybox-frame" name="fancybox-frame'+new Date().getTime()+'" frameborder="0" hspace="0" '+(window.attachEvent?'allowtransparency="true""':"")+' scrolling="'+H.scrolling+'" src="'+G.href+'"></iframe>').appendTo(m)}M.show();P=false;B.fancybox.center();G.onComplete(y,e,G);K()},K=function(){var V,W;if((y.length-1)>e){V=y[e+1].href;if(typeof V!=="undefined"&&V.match(i)){W=new Image();W.src=V}}if(e>0){V=y[e-1].href;if(typeof V!=="undefined"&&V.match(i)){W=new Image();W.src=V}}},U=function(W){var V={width:parseInt(b.width+(c.width-b.width)*W,10),height:parseInt(b.height+(c.height-b.height)*W,10),top:parseInt(b.top+(c.top-b.top)*W,10),left:parseInt(b.left+(c.left-b.left)*W,10)};if(typeof c.opacity!=="undefined"){V.opacity=W<0.5?0.5:W}M.css(V);m.css({width:V.width-G.padding*2,height:V.height-(h*W)-G.padding*2})},u=function(){return[B(window).width()-(G.margin*2),B(window).height()-(G.margin*2),B(document).scrollLeft()+G.margin,B(document).scrollTop()+G.margin]},R=function(){var V=u(),Z={},W=G.autoScale,X=G.padding*2,Y;if(G.width.toString().indexOf("%")>-1){Z.width=parseInt((V[0]*parseFloat(G.width))/100,10)}else{Z.width=G.width+X}if(G.height.toString().indexOf("%")>-1){Z.height=parseInt((V[1]*parseFloat(G.height))/100,10)}else{Z.height=G.height+X}if(W&&(Z.width>V[0]||Z.height>V[1])){if(H.type=="image"||H.type=="swf"){Y=(G.width)/(G.height);if((Z.width)>V[0]){Z.width=V[0];Z.height=parseInt(((Z.width-X)/Y)+X,10)}if((Z.height)>V[1]){Z.height=V[1];Z.width=parseInt(((Z.height-X)*Y)+X,10)}}else{Z.width=Math.min(Z.width,V[0]);Z.height=Math.min(Z.height,V[1])}}Z.top=parseInt(Math.max(V[3]-20,V[3]+((V[1]-Z.height-40)*0.5)),10);Z.left=parseInt(Math.max(V[2]-20,V[2]+((V[0]-Z.width-40)*0.5)),10);return Z},q=function(V){var W=V.offset();W.top+=parseInt(V.css("paddingTop"),10)||0;W.left+=parseInt(V.css("paddingLeft"),10)||0;W.top+=parseInt(V.css("border-top-width"),10)||0;W.left+=parseInt(V.css("border-left-width"),10)||0;W.width=V.width();W.height=V.height();return W},I=function(){var Y=H.orig?B(H.orig):false,X={},W,V;if(Y&&Y.length){W=q(Y);X={width:W.width+(G.padding*2),height:W.height+(G.padding*2),top:W.top-G.padding-20,left:W.left-G.padding-20}}else{V=u();X={width:G.padding*2,height:G.padding*2,top:parseInt(V[3]+V[1]*0.5,10),left:parseInt(V[2]+V[0]*0.5,10)}}return X},a=function(){if(!T.is(":visible")){clearInterval(p);return}B("div",T).css("top",(N*-40)+"px");N=(N+1)%12};B.fn.fancybox=function(V){if(!B(this).length){return this}B(this).data("fancybox",B.extend({},V,(B.metadata?B(this).metadata():{}))).unbind("click.fb").bind("click.fb",function(X){X.preventDefault();if(P){return}P=true;B(this).blur();j=[];C=0;var W=B(this).attr("rel")||"";if(!W||W==""||W==="nofollow"){j.push(this)}else{j=B('a[rel="'+W+'"], area[rel="'+W+'"]');C=j.index(this)}w();return});return this};B.fancybox=function(Y){var X;if(P){return}P=true;X=typeof arguments[1]!=="undefined"?arguments[1]:{};j=[];C=parseInt(X.index,10)||0;if(B.isArray(Y)){for(var W=0,V=Y.length;W<V;W++){if(typeof Y[W]=="object"){B(Y[W]).data("fancybox",B.extend({},X,Y[W]))}else{Y[W]=B({}).data("fancybox",B.extend({content:Y[W]},X))}}j=jQuery.merge(j,Y)}else{if(typeof Y=="object"){B(Y).data("fancybox",B.extend({},X,Y))}else{Y=B({}).data("fancybox",B.extend({content:Y},X))}j.push(Y)}if(C>j.length||C<0){C=0}w()};B.fancybox.showActivity=function(){clearInterval(p);T.show();p=setInterval(a,66)};B.fancybox.hideActivity=function(){T.hide()};B.fancybox.next=function(){return B.fancybox.pos(e+1)};B.fancybox.prev=function(){return B.fancybox.pos(e-1)};B.fancybox.pos=function(V){if(P){return}V=parseInt(V);j=y;if(V>-1&&V<y.length){C=V;w()}else{if(G.cyclic&&y.length>1){C=V>=y.length?0:y.length-1;w()}}return};B.fancybox.cancel=function(){if(P){return}P=true;B('.fancybox-inline-tmp').trigger('fancybox-cancel');r();H.onCancel(j,C,H);P=false};B.fancybox.close=function(){if(P||M.is(":hidden")){return}P=true;if(G&&false===G.onCleanup(y,e,G)){P=false;return}r();B(J.add(O).add(z)).hide();B(m.add(Q)).unbind();B(window).unbind("resize.fb scroll.fb");B(document).unbind("keydown.fb");if(G.type==="iframe"){m.find("iframe").attr("src",S&&/^https/i.test(window.location.href||"")?"javascript:void(false)":"about:blank")}if(G.titlePosition!=="inside"){A.empty()}M.stop();function V(){Q.fadeOut("fast");A.empty().hide();M.hide();B('.fancybox-inline-tmp').trigger('fancybox-cleanup');m.empty();G.onClosed(y,e,G);y=H=[];e=C=0;G=H={};P=false}if(G.transitionOut=="elastic"){b=I();var W=M.position();c={top:W.top,left:W.left,width:M.width(),height:M.height()};if(G.opacity){c.opacity=1}A.empty().hide();s.prop=1;B(s).animate({prop:0},{duration:G.speedOut,easing:G.easingOut,step:U,complete:V})}else{M.fadeOut(G.transitionOut=="none"?0:G.speedOut,V)}};B.fancybox.resize=function(){if(Q.is(":visible")){Q.css("height",B(document).height())}B.fancybox.center(true)};B.fancybox.center=function(){var V,W;if(P){return}W=arguments[0]===true?1:0;V=u();if(!W&&(M.width()>V[0]||M.height()>V[1])){return}M.stop().animate({top:parseInt(Math.max(V[3]-20,V[3]+((V[1]-m.height()-40)*0.5)-G.padding)),left:parseInt(Math.max(V[2]-20,V[2]+((V[0]-m.width()-40)*0.5)-G.padding))},typeof arguments[0]=="number"?arguments[0]:200)};B.fancybox.init=function(){if(B("#fancybox-wrap").length){return}B("body").append(L=B('<div id="fancybox-tmp"></div>'),T=B('<div id="fancybox-loading"><div></div></div>'),Q=B('<div id="fancybox-overlay"></div>'),M=B('<div id="fancybox-wrap"></div>'));d=B('<div id="fancybox-outer"></div>').append('<div class="fancybox-bg" id="fancybox-bg-n"></div><div class="fancybox-bg" id="fancybox-bg-ne"></div><div class="fancybox-bg" id="fancybox-bg-e"></div><div class="fancybox-bg" id="fancybox-bg-se"></div><div class="fancybox-bg" id="fancybox-bg-s"></div><div class="fancybox-bg" id="fancybox-bg-sw"></div><div class="fancybox-bg" id="fancybox-bg-w"></div><div class="fancybox-bg" id="fancybox-bg-nw"></div>').appendTo(M);d.append(m=B('<div id="fancybox-content"></div>'),J=B('<a id="fancybox-close"></a>'),A=B('<div id="fancybox-title"></div>'),O=B('<a href="javascript:;" id="fancybox-left"><span class="fancy-ico" id="fancybox-left-ico"></span></a>'),z=B('<a href="javascript:;" id="fancybox-right"><span class="fancy-ico" id="fancybox-right-ico"></span></a>'));J.click(B.fancybox.close);T.click(B.fancybox.cancel);O.click(function(V){V.preventDefault();B.fancybox.prev()});z.click(function(V){V.preventDefault();B.fancybox.next()});if(B.fn.mousewheel){M.bind("mousewheel.fb",function(V,W){if(P){V.preventDefault()}else{if(B(V.target).get(0).clientHeight==0||B(V.target).get(0).scrollHeight===B(V.target).get(0).clientHeight){V.preventDefault();B.fancybox[W>0?"prev":"next"]()}}})}if(B.support.opacity===false){M.addClass("fancybox-ie")}if(S){T.addClass("fancybox-ie6");M.addClass("fancybox-ie6");B('<iframe id="fancybox-hide-sel-frame" src="'+(/^https/i.test(window.location.href||"")?"javascript:void(false)":"about:blank")+'" scrolling="no" border="0" frameborder="0" tabindex="-1"></iframe>').prependTo(d)}};B.fn.fancybox.defaults={padding:10,margin:40,opacity:false,modal:false,cyclic:false,scrolling:"auto",width:560,height:340,autoScale:true,autoDimensions:true,centerOnScroll:false,ajax:{},swf:{wmode:"transparent"},hideOnOverlayClick:true,hideOnContentClick:false,overlayShow:true,overlayOpacity:0.7,overlayColor:"#777",titleShow:true,titlePosition:"float",titleFormat:null,titleFromAlt:false,transitionIn:"fade",transitionOut:"fade",speedIn:300,speedOut:300,changeSpeed:300,changeFade:"fast",easingIn:"swing",easingOut:"swing",showCloseButton:true,showNavArrows:true,enableEscapeButton:true,enableKeyboardNav:true,onStart:function(){},onCancel:function(){},onComplete:function(){},onCleanup:function(){},onClosed:function(){},onError:function(){}};B(document).ready(function(){B.fancybox.init()})})(jQuery);