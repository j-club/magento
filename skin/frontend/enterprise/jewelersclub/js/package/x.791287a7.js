/* ======================================================================================= */
/* merged skin/frontend/enterprise/default/js/oro/productquickview/binding.js              */
/* ======================================================================================= */
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_ProductQuickView
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */
var oroProductQuickView = {
    openedPopup:null,
    init : function() {
        this._originLeft = 35;
        this._originBottom = 40;
        this._transformedLeft = -485;
        this._transformedBottom = -420;
        var self = this;
        $$("[id^=popup-gallery-]").each(function(element){
            Event.observe(element, 'click', self.switchGallery.bind(self, element));
        });
        $$("[id^=popup-open-]").each(function(element){
            Event.observe(element, 'click', self.openPopup.bind(self, element));
        });
        $$("[id^=popup-close-]").each(function(element){
            Event.observe(element, 'click', self.closePopup.bind(self, element));
        });
        $$("[id*=-tab-open-]").each(function(element){
            Event.observe(element, 'click', self.openTab.bind(self, element));
        });

        var tag = document.createElement('script');

        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    },
    switchGallery : function(element) {
        var id = this.parseId(element.id);
        var productId = this.parseProductIdImage(element.id);
        var vid = this.parseProductId(element.id);
        $('image-wrap').addClassName('product-image-wrap');
        if ($('popup-image-' + productId)) {
            $('popup-image-' + productId).show();
        }
        jQuery('div.zoomPad').show();

        if(typeof window.ytPlayer == 'object') {
            try {
                window.ytPlayer.destroy();
            } catch (TypeError) {

            }
        }

        if(parseInt($('popup-gallery-' + id).readAttribute('isvideo'))) {
            jQuery('div.zoomPad').hide();
            function onPlayerReady(event) {
                event.target.playVideo();
            }

            window.ytPlayer = new YT.Player('popup-image-' + productId, {
                height: '315',
                width: '560',
                videoId: vid,
                events: {
                    'onReady': onPlayerReady
                }
            });
            $('image-wrap').removeClassName('product-image-wrap');
            jQuery('.youtube-video').fitVids();
        }
    },

    openPopup : function(element) {
        setTimeout(function(){
            var productId = this.parseProductId(element.id);
            var el = $('quick-view-' + productId)
            $$("[id^=quick-view-]").each(function(element){
                element.hide();
            });

            var opener = $('popup-open-'+productId).up();
            el.show();
            $$("[id$=-tab-content-" + productId + "]").each(function(element, index) {
                if (index > 0) {
                    element.hide();
                }
            });
            if ($('ajax-container-' + productId).innerHTML.length == 0) {
                new Ajax.Request('/quickview/index', {
                    method: 'get',
                    parameters: {product: productId},
                    onSuccess: function(transport) {
                        $('ajax-container-' + productId).innerHTML = transport.responseText;
                        twttr.widgets.load();
                    }
                });
            }
            this.centerPopup(el, opener);
            this.openedPopup = element;

            $('popup-open-'+productId).addClassName("selected");
        }.bind(this),50);

    },

    centerPopup : function(element, opener) {
        $$(".col-main").each(function(e) {
            var layout = e.getLayout();
            var offset = opener.viewportOffset();
            var left = offset.left - parseInt(opener.parentNode.getStyle('width'));
            var top = offset.top + parseInt(opener.parentNode.getStyle('height'))/3;
            var width = layout.get('width');
            var height = layout.get('height');
            var elementWidth = parseInt(element.getStyle('width'));
            var elementHeight = parseInt(element.getStyle('height'));
            var elementLeft =  (width < left + elementWidth) ? this._transformedLeft : this._originLeft;
            var elementBottom = (parseInt(top) < elementHeight) ? -(elementHeight + this._originBottom) : this._originBottom;
            var cornerElementId = 'corner-quick-' + this.parseId(element.id);
            if (elementLeft == this._transformedLeft && offset.left < elementWidth) {
                elementLeft = parseInt(document.body.clientWidth/2 - elementWidth/2) - offset.left;
                $(cornerElementId).hide();
            } else {
                var className = 'corner';
                if (elementLeft == this._transformedLeft && parseInt(top) < elementHeight) {
                    className = 'corner bottom';
                } else if (elementLeft == this._transformedLeft && elementBottom == this._originBottom) {
                    className = 'corner right-side';
                } else if (elementLeft == this._originLeft && parseInt(top) < elementHeight) {
                    className = 'corner bottom-right-side';
                }
                $(cornerElementId).writeAttribute('class', className);
            }
            element.setStyle({
                left: elementLeft + 'px',
                bottom: elementBottom + 'px'
            });
            element.focus();
        }, this);
    },

    openTab : function(element) {
        var elementId = element.id;
        var id = this.parseProductId(element.id);
        var tabType = this.parseTabType(element.id);
        $$("[id$=-tab-content-" + id + "]").each(function(element){
            element.hide();
        });
        $$("[id$=-tab-open-" + id + "]").each(function(element){
            Element.removeClassName(element,'selected');
        });
        Element.addClassName($(elementId),'selected');
        $(tabType + '-tab-content-' + id).show();
    },
    closePopup : function(element) {
        var productId = this.parseProductId(element.id);
        $('quick-view-' + productId).hide();
        $('popup-open-'+productId).removeClassName('selected');
        this.openedPopup = null;
    },

    checkMouseEvent : function(event){
        if(!this.openedPopup) return;

        var productId = this.parseProductId(this.openedPopup.id);
        var el = $('quick-view-' + productId);
        var vp = el.viewportOffset();
        var layout = el.getLayout();
        var width = layout.get('width');
        var height = layout.get('height');
        if(event.clientX < vp.left || event.clientX > vp.left + width || event.clientY < vp.top || event.clientY > vp.top + height){
            this.closePopup(this.openedPopup);
        }
    },

    parseId : function(str) {
        var idData = str.split('-');
        var l = idData.length;
        return idData[l-2] + '-' + idData[l-1];
    },
    parseProductIdImage : function(str) {
        var idData = str.split('-');
        var l = idData.length;
        return idData[l-2];
    },
    parseProductId : function(str) {
        var idData = str.split('-');
        var l = idData.length;
        return idData[l-1];
    },
    parseTabType : function(str) {
        var idData = str.split('-');
        return idData[0];
    }
};

document.observe("dom:loaded", function() {
    oroProductQuickView.init();
});

document.observe("layerednavigation:onSuccessSend", function() {
    oroProductQuickView.init();
});

document.observe("click", function(event){
    oroProductQuickView.checkMouseEvent(event);
})

document.observe('keyup', function(event){
    if(oroProductQuickView.openedPopup != null && event.keyCode == Event.KEY_ESC){
        oroProductQuickView.closePopup(oroProductQuickView.openedPopup);
    }
});
