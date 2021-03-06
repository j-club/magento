/* ======================================================================================= */
/* merged js/itoris/layerednavigation/layerednavigation.js                                 */
/* ======================================================================================= */
LayNav = {

	isIE : function(version) {
		return parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) <= version;
	},

	addObserversToCheckboxes : function() {
		var checkboxes = $$('.itoris_laynav_checkbox');
		checkboxes.each(function(item){
			if (this.isIE(8)) {
				item.observe('propertychange',this.send.bindAsEventListener(this));
			} else {
				item.observe('change',this.send.bindAsEventListener(this));
			}

		}, this);
	},

	send : function() {
		var parameters = this.formSerialize(this._getForm());
		parameters['itoris_layerednavigation'] = 'true';
		parameters['closed_filters[]'] = this.closedFilters;
		if (this.additionalParams != null) {
			parameters = $H(parameters).merge(this.additionalParams);
			this.additionalParams = null;
		}

		this.customSend(parameters);
	},

	customSend : function(parameters) {
		requestUrl = document.location.href
		if (requestUrl.indexOf('#') >= 0) {
			var requestUrl = requestUrl.substring(0,requestUrl.indexOf('#'));
		}
		if (LayNav.canUseCache) {
			if (typeof parameters == 'string') {
				var queryParams = parameters;
			} else {
				var queryParams = Hash.toQueryString(parameters);
			}
			if (!queryParams.length) {
				/** we need our json cache **/
				queryParams = 'itoris_layerednavigation=true';
			}
			requestUrl += (requestUrl.indexOf('?') >= 0 ? '&' : '?') + queryParams;
			/** post parameter enabled our engine **/
			parameters = {itoris_layerednavigation: 'true'};
		} else {
			if (requestUrl.indexOf('?') >= 0) {
				requestUrl = requestUrl.replace('?', '?no_cache=true&');
			} else {
				requestUrl = requestUrl + '?no_cache=true';
			}
		}
		requestUrl = this.replaceToolbarParams(requestUrl);

		this.showLoading();
		new Ajax.Request(requestUrl, {
			method : 'post',
			parameters  : parameters,
			onSuccess: this.onSuccessSend.bindAsEventListener(this),
			onFailure: this.onFailureSend.bindAsEventListener(this)
		});
	},

	sendWithAdditionalParams : function(parameters) {
		var paramsKeys = Object.keys(parameters);
		for (var i = 0; i < paramsKeys.length; i++) {
			this.toolbarParams[paramsKeys[i]] = parameters[paramsKeys[i]];
		}
		this.additionalParams = this.toolbarParams;
		this.send();
	},
	replaceToolbarParams : function(requestUrl) {
		if (this.toolbarParams) {
			var paramsKeys = Object.keys(this.toolbarParams);
			for (var i = 0; i < paramsKeys.length; i++) {
				requestUrl = requestUrl.replace(new RegExp(paramsKeys[i] + '=[^&]*', 'i'), paramsKeys[i] + '=' + this.toolbarParams[paramsKeys[i]]);
			}
		}
		return requestUrl;
	},

	onSuccessSend : function(transport) {
		//console.log(transport);
		//var response = transport.responseJSON;
		var response = transport.responseText.evalJSON();

		$('itoris_layerednavigation_anchor').up().update(response['content_html']);
		$$('#itoris_layered_navigation_form .itoris_laynav .block-content')[0]
				.update(response['layered_navigation_html']);

		if (response.hasOwnProperty('price_range_config')) {
			if ($('laynav-filter-price').next().visible()) {
				LayNav.PriceRange.init(response['price_range_config']);
			} else {
				LayNav.delayedPriceConfig = response['price_range_config'];
			}
		}

		this.updateUrlFragment();
		this.hideLoading();
        document.body.fire('layerednavigation:onSuccessSend');

		if (smartLogin) {
			smartLogin.initialize();
		}
	},

	updateUrlFragment : function() {
		var parameters = this.formSerialize(this._getForm(), true);

		var href = document.location.href;
		if (href.indexOf('#') >= 0) {
			href = href.substr(0, href.indexOf('#'));
		}
		var toolbarParameters = '';
		if (this.toolbarParams) {
			var paramsKeys = Object.keys(this.toolbarParams);
			for (var i = 0; i < paramsKeys.length; i++) {
				toolbarParameters += paramsKeys[i] + '=' + this.toolbarParams[paramsKeys[i]];
				if (i != paramsKeys.length - 1) {
					toolbarParameters += '&';
				}
			}
		}
		if (parameters.length) {
			toolbarParameters += '&';
		}
		href += '#' + toolbarParameters + parameters;

		document.location.href = href;
	},

	onFailureSend : function(transport) {
		this.hideLoading();
		alert('Unable to connect to the server. Please try again.');
	},

	_getForm : function() {
		return document.forms.itoris_layered_navigation_form;
	},

	onPageLoad : function() {
		var href = document.location.href;

		var params;
		if (href.indexOf('#') >= 0) {
			params = href.substr(href.indexOf('#') + 1);
			params += '&itoris_layerednavigation=true';
			this.customSend(params);
			//document.observe("dom:loaded", this.showLoading.bind(this));
		}

	},

	showLoading : function() {
		$$('.ln-loader-back').each(function(div) {
			Element.show(div);
		});
	},

	hideLoading : function() {
		$$('.ln-loader-back').each(function(div) {
			Element.hide(div);
		});
	},

	evObj : function(args) {
		var ev;
		if (typeof(args[0]) != 'undefined') {
			ev =  args[0];
		} else {
			ev = window.event;
		}

		return Event.extend(ev);
	},

	categoryClick : function(anchor) {
		var url = anchor.href;

		var params = this.formSerialize(this._getForm());
		params['restore_fragment'] = 'true';

		function post_to_url(path, params) {
		    var method = "post";

		    var form = document.createElement("form");
		    form.setAttribute("method", method);
		    form.setAttribute("action", path);

			for(var key in params) {
			    if(params.hasOwnProperty(key)) {

					var values = params[key];

					if (!Object.isArray(values)) {
						values = new Array(values);
					}

					$A(values).each(function(value) {
						var hiddenField = document.createElement("input");
						hiddenField.setAttribute("type", "hidden");
						hiddenField.setAttribute("name", key);
						hiddenField.setAttribute("value", value);
						form.appendChild(hiddenField);
					});
				}
			};

		    document.body.appendChild(form);
		    form.submit();
		}

		post_to_url(url,params);

	},

	formSerialize : function(form, asQueryString) {
		if (typeof(asQueryString) == 'undefined') {
			asQueryString = false;
		}
		return this._serializeElements(Form.getElements(form), !asQueryString);
	},

	_serializeElements: function(elements, getHash) {
	    var data = elements.inject({}, function(result, element) {
	      if (!element.disabled && element.name && (element.className != 'not-use-in-request' || !getHash)) {
	        var key = element.name, value = $(element).getValue();
	        if (value != undefined && value != '') {
	          if (result[key]) {
	            if (result[key].constructor != Array) result[key] = [result[key]];
	            result[key].push(value);
	          }
	          else result[key] = value;
	        }
	      }
	      return result;
	    });

	    return getHash ? data : Hash.toQueryString(data);
	  },

	defaultToolbarParams : {
		'limit' : 9,
		'mode'  : 'grid',
		'order' : 'name',
		'dir'   : 'asc',
		'p'     : 1
	},

	additionalParams : null,
	closedFilters : $A(new Array()),
	delayedPriceConfig : null
};

LayNav.PriceRange = {
	init : function(config) {
		this.config = config;
		Event.observe(document.body, 'mousemove', this.onMouseMove.bindAsEventListener(this));
		Event.observe(document.body, 'mouseup', this.onMouseUp.bindAsEventListener(this));
		Event.observe(this.getLeftPointer(), 'mousedown', this.onMouseDown.bindAsEventListener(this, this.getLeftPointer()));
		Event.observe(this.getRightPointer(), 'mousedown', this.onMouseDown.bindAsEventListener(this, this.getRightPointer()));

		this.getRightPointer().style.right = '';
		this.initPointerPositions();
	},

	initPointerPositions : function() {
		this.moveLeftPointerToPrice($('laynav_price_pointer_left_input').value);
		this.moveRightPointerToPrice($('laynav_price_pointer_right_input').value);
		this.updateRangePosition();
	},

	onMouseMove : function(ev) {
		if (this.currentlyDragged == null) {
			return;
		}

		var dragCurrentOffset  = Event.pointerX(ev);

		if (this.currentlyDragged == this.getLeftPointer()
				&& !this.isMouseInLeftPointerDraggableArea(dragCurrentOffset)) {

			return;

		} else if (this.currentlyDragged == this.getRightPointer()
				&& !this.isMouseInRightPointerDraggableArea(dragCurrentOffset)) {

			return;
		}

		var delta = dragCurrentOffset - this.dragLastOffset;
		var newPosition = this.getPointerOffset(this.currentlyDragged) + delta;

		this.currentlyDragged.style.left = newPosition + 'px';
		this.dragLastOffset = dragCurrentOffset;
		Event.stop(ev);

		this.setPointerPriceValue(this.currentlyDragged);
		this.updateRangePosition();
		//console.log('pointer scale position : ', this.getPointerScalePosition(this.currentlyDragged) );
	},

	isMouseInLeftPointerLeftMarginDraggableArea : function(mousePos) {
		return mousePos >= this.getScale().cumulativeOffset().left + this.currentlyDragged.mouseInsideOffset;
	},

	isMouseInLeftPointerRightMarginDraggableArea : function(mousePos) {
		return mousePos <= + this.currentlyDragged.mouseInsideOffset
													+ this.getRightPointer().cumulativeOffset().left
													- this.getLeftPointer().getDimensions().width / 2;
	},

	isMouseInLeftPointerDraggableArea : function(mousePos) {
		return this.isMouseInLeftPointerLeftMarginDraggableArea(mousePos)
					&& this.isMouseInLeftPointerRightMarginDraggableArea(mousePos);
	},

	//******//

	isMouseInRightPointerLeftMarginDraggableArea : function(mousePos) {
		return mousePos >= this.getLeftPointer().cumulativeOffset().left
													+ this.getLeftPointer().getDimensions().width
													+ this.currentlyDragged.mouseInsideOffset
													+ this.getRightPointer().getDimensions().width / 2
													+ 1;
	},



	isMouseInRightPointerRightMarginDraggableArea : function(mousePos) {
		return mousePos <= this.getScale().cumulativeOffset().left
													+ this.getScale().getDimensions().width
													+ this.currentlyDragged.mouseInsideOffset;
	},

	isMouseInRightPointerDraggableArea : function(mousePos) {
		return this.isMouseInRightPointerLeftMarginDraggableArea(mousePos)
				&& this.isMouseInRightPointerRightMarginDraggableArea(mousePos);
	},

	getMouseInPointerOffset : function(mousePos) {
		var mouseOffsetInsidePointer = mousePos -this.currentlyDragged.cumulativeOffset().left;
		return mouseOffsetInsidePointer - Math.round(this.currentlyDragged.getDimensions().width / 2);
	},

	onMouseUp : function(ev) {
		if (this.currentlyDragged !== null)
			this.storePointerValue(this.currentlyDragged);
		this.currentlyDragged = null;
		Event.stop(ev);
	},

	onMouseDown : function(ev, pointer) {
		//console.log('mouse down', ev, arguments);
		this.currentlyDragged = pointer;
		this.dragLastOffset = Event.pointerX(ev);
		this.currentlyDragged.mouseInsideOffset = this.getMouseInPointerOffset(this.dragLastOffset);
		Event.stop(ev);
	},

	getLeftPointer : function() {
		return $('laynav_price_pointer_left');
	},

	getRightPointer : function() {
		return $('laynav_price_pointer_right');
	},

	getPointerOffset : function(pointer) {
		return parseInt(pointer.style.left);
	},

	getScale : function() {
		return $('laynav_price_scale');
	},

	getPointerScalePosition : function(pointer) {
		var position =  (pointer.cumulativeOffset().left
						- this.getScale().cumulativeOffset().left
						+ pointer.getDimensions().width / 2)
							/
				this.getScale().getDimensions().width;

		position = this.correctPointerScalePosition(position);

		return position;
	},

	correctPointerScalePosition : function(position) {
		if (position < 0) {
			position = 0;
		}

		if (position > 0.996) {
			position = 1;
		}

		return position;
	},

	setPointerPriceValue : function(pointer, position) {
		if (typeof(position) == 'undefined') {
			position = this.getPointerScalePosition(pointer);
		}

		var result = (this.config.max_price - this.config.min_price) * position
				+ this.config.min_price;
		$(pointer.id + '_label').update(Math.round(result));
	},

	getPointerPrice : function(pointer) {
		var result = (this.config.max_price - this.config.min_price) * this.getPointerScalePosition(pointer)
						+ this.config.min_price;
		result = Math.round(result);
		return result;
	},

	storePointerValue : function(pointer) {
		var value = this.getPointerPrice(pointer);
		$(pointer.id + '_input').value = value;
		LayNav.send();
	},

	movePointerToPosition : function(pointer, position) {
		var left = this.getScale().getDimensions().width * position;

		pointer.style.left = left + 'px';
		this.setPointerPriceValue(pointer, position);
	},

	moveLeftPointerToPrice : function(price) {
		if (price == '') {
			price = this.config.min_price;
		}
		var position = (price -this.config.min_price) / (this.config.max_price - this.config.min_price);
		position = this.correctPointerScalePosition(position);
		this.movePointerToPosition(this.getLeftPointer(), position);
	},

	moveRightPointerToPrice : function(price) {
		if (price == '') {
			price = this.config.max_price;
		}
		var position = (price -this.config.min_price) / (this.config.max_price - this.config.min_price);
		position = this.correctPointerScalePosition(position);
		this.movePointerToPosition(this.getRightPointer(), position);
	},

	updateRangePosition: function() {
		var scaleLeft = this.getScale().up().cumulativeOffset().left;
		var left = this.getLeftPointer().cumulativeOffset().left + this.getLeftPointer().getDimensions().width / 2 - scaleLeft;
		var right = this.getRightPointer().cumulativeOffset().left + this.getRightPointer().getDimensions().width / 2 - scaleLeft;
		var width = right - left;

		this.getRange().style.left = left + 'px';
		this.getRange().style.width = width + 'px';
	},

	getRange : function() {
		return $('laynav_price_range');
	},

	currentlyDragged : null,
	dragLastOffset : 0,
	config : null
};

LayNav.Toggler = {

	toggle:function (elem, requestVar) {
		var div = $(elem).next();
		Effect.toggle(div, 'slide', {
			delay:0.01,
			duration:0.3,
			afterFinish:this.onAfterToggle.bindAsEventListener(this, requestVar)
		});
	},

	onAfterToggle:function (effectData, requestVar) {
		var elm = effectData.element.previous();
		if (effectData.factor > 0) {
			elm.removeClassName('ln-closed');
			elm.addClassName('ln-opened');

			var index = LayNav.closedFilters.indexOf(requestVar);
			if (index >= 0) {
				LayNav.closedFilters.splice(index, 1);
			}

			if (requestVar == 'price' && LayNav.delayedPriceConfig != null) {
				LayNav.PriceRange.init(LayNav.delayedPriceConfig);
				LayNav.delayedPriceConfig = null;
			}

		} else {
			elm.addClassName('ln-closed');
			elm.removeClassName('ln-opened');

			if (LayNav.closedFilters.indexOf(requestVar) == -1) {
				LayNav.closedFilters.push(requestVar);
			}
		}
	}
};
document.observe('dom:loaded', function(){LayNav.onPageLoad();});
/* ======================================================================================= */
/* merged js/varien/product.js                                                             */
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
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
if(typeof Product=='undefined') {
    var Product = {};
}

/********************* IMAGE ZOOMER ***********************/

Product.Zoom = Class.create();
/**
 * Image zoom control
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
Product.Zoom.prototype = {
    initialize: function(imageEl, trackEl, handleEl, zoomInEl, zoomOutEl, hintEl){
        this.containerEl = $(imageEl).parentNode;
        this.imageEl = $(imageEl);
        this.handleEl = $(handleEl);
        this.trackEl = $(trackEl);
        this.hintEl = $(hintEl);

        this.containerDim = Element.getDimensions(this.containerEl);
        this.imageDim = Element.getDimensions(this.imageEl);

        this.imageDim.ratio = this.imageDim.width/this.imageDim.height;

        this.floorZoom = 1;

        if (this.imageDim.width > this.imageDim.height) {
            this.ceilingZoom = this.imageDim.width / this.containerDim.width;
        } else {
            this.ceilingZoom = this.imageDim.height / this.containerDim.height;
        }

        if (this.imageDim.width <= this.containerDim.width
            && this.imageDim.height <= this.containerDim.height) {
            this.trackEl.up().hide();
            this.hintEl.hide();
            this.containerEl.removeClassName('product-image-zoom');
            return;
        }

        this.imageX = 0;
        this.imageY = 0;
        this.imageZoom = 1;

        this.sliderSpeed = 0;
        this.sliderAccel = 0;
        this.zoomBtnPressed = false;

        this.showFull = false;

        this.selects = document.getElementsByTagName('select');

        this.draggable = new Draggable(imageEl, {
            starteffect:false,
            reverteffect:false,
            endeffect:false,
            snap:this.contain.bind(this)
        });

        this.slider = new Control.Slider(handleEl, trackEl, {
            axis:'horizontal',
            minimum:0,
            maximum:Element.getDimensions(this.trackEl).width,
            alignX:0,
            increment:1,
            sliderValue:0,
            onSlide:this.scale.bind(this),
            onChange:this.scale.bind(this)
        });

        this.scale(0);

        Event.observe(this.imageEl, 'dblclick', this.toggleFull.bind(this));

        Event.observe($(zoomInEl), 'mousedown', this.startZoomIn.bind(this));
        Event.observe($(zoomInEl), 'mouseup', this.stopZooming.bind(this));
        Event.observe($(zoomInEl), 'mouseout', this.stopZooming.bind(this));

        Event.observe($(zoomOutEl), 'mousedown', this.startZoomOut.bind(this));
        Event.observe($(zoomOutEl), 'mouseup', this.stopZooming.bind(this));
        Event.observe($(zoomOutEl), 'mouseout', this.stopZooming.bind(this));
    },

    toggleFull: function () {
        this.showFull = !this.showFull;

        //Hide selects for IE6 only
        if (typeof document.body.style.maxHeight == "undefined")  {
            for (i=0; i<this.selects.length; i++) {
                this.selects[i].style.visibility = this.showFull ? 'hidden' : 'visible';
            }
        }
        val_scale = !this.showFull ? this.slider.value : 1;
        this.scale(val_scale);

        this.trackEl.style.visibility = this.showFull ? 'hidden' : 'visible';
        this.containerEl.style.overflow = this.showFull ? 'visible' : 'hidden';
        this.containerEl.style.zIndex = this.showFull ? '1000' : '9';

        return this;
    },

    scale: function (v) {
        var centerX  = (this.containerDim.width*(1-this.imageZoom)/2-this.imageX)/this.imageZoom;
        var centerY  = (this.containerDim.height*(1-this.imageZoom)/2-this.imageY)/this.imageZoom;
        var overSize = (this.imageDim.width > this.containerDim.width || this.imageDim.height > this.containerDim.height);

        this.imageZoom = this.floorZoom+(v*(this.ceilingZoom-this.floorZoom));

        if (overSize) {
            if (this.imageDim.width > this.imageDim.height) {
                this.imageEl.style.width = (this.imageZoom*this.containerDim.width)+'px';
            } else {
                this.imageEl.style.height = (this.imageZoom*this.containerDim.height)+'px';
            }
            if (this.containerDim.ratio) {
                if (this.imageDim.width > this.imageDim.height) {
                    this.imageEl.style.height = (this.imageZoom*this.containerDim.width*this.containerDim.ratio)+'px'; // for safari
                } else {
                    this.imageEl.style.width = (this.imageZoom*this.containerDim.height*this.containerDim.ratio)+'px'; // for safari
                }
            }
        } else {
            this.slider.setDisabled();
        }

        this.imageX = this.containerDim.width*(1-this.imageZoom)/2-centerX*this.imageZoom;
        this.imageY = this.containerDim.height*(1-this.imageZoom)/2-centerY*this.imageZoom;

        this.contain(this.imageX, this.imageY, this.draggable);

        return true;
    },

    startZoomIn: function()
    {
        if (!this.slider.disabled) {
            this.zoomBtnPressed = true;
            this.sliderAccel = .002;
            this.periodicalZoom();
            this.zoomer = new PeriodicalExecuter(this.periodicalZoom.bind(this), .05);
        }
        return this;
    },

    startZoomOut: function()
    {
        if (!this.slider.disabled) {
            this.zoomBtnPressed = true;
            this.sliderAccel = -.002;
            this.periodicalZoom();
            this.zoomer = new PeriodicalExecuter(this.periodicalZoom.bind(this), .05);
        }
        return this;
    },

    stopZooming: function()
    {
        if (!this.zoomer || this.sliderSpeed==0) {
            return;
        }
        this.zoomBtnPressed = false;
        this.sliderAccel = 0;
    },

    periodicalZoom: function()
    {
        if (!this.zoomer) {
            return this;
        }

        if (this.zoomBtnPressed) {
            this.sliderSpeed += this.sliderAccel;
        } else {
            this.sliderSpeed /= 1.5;
            if (Math.abs(this.sliderSpeed)<.001) {
                this.sliderSpeed = 0;
                this.zoomer.stop();
                this.zoomer = null;
            }
        }
        this.slider.value += this.sliderSpeed;

        this.slider.setValue(this.slider.value);
        this.scale(this.slider.value);

        return this;
    },

    contain: function (x,y,draggable) {

        var dim = Element.getDimensions(draggable.element);

        var xMin = 0, xMax = this.containerDim.width-dim.width;
        var yMin = 0, yMax = this.containerDim.height-dim.height;

        x = x>xMin ? xMin : x;
        x = x<xMax ? xMax : x;
        y = y>yMin ? yMin : y;
        y = y<yMax ? yMax : y;

        if (this.containerDim.width > dim.width) {
            x = (this.containerDim.width/2) - (dim.width/2);
        }

        if (this.containerDim.height > dim.height) {
            y = (this.containerDim.height/2) - (dim.height/2);
        }

        this.imageX = x;
        this.imageY = y;

        this.imageEl.style.left = this.imageX+'px';
        this.imageEl.style.top = this.imageY+'px';

        return [x,y];
    }
}

/**************************** CONFIGURABLE PRODUCT **************************/
Product.Config = Class.create();
Product.Config.prototype = {
    initialize: function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        this.settings   = $$('.super-attribute-select');
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;

        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if(i==0){
                this.fillSelect(this.settings[i])
            }
            else {
                this.settings[i].disabled=true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // Set default values - from config and overwrite them by url values
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }

        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex != -1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            var urlValues = paramsStr.toQueryParams();
            if (!this.values) {
                this.values = {};
            }
            for (var i in urlValues) {
                this.values[i] = urlValues[i];
            }
        }

        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
    },

    configureForValues: function () {
        if (this.values) {
            this.settings.each(function(element){
                var attributeId = element.attributeId;
                element.value = (typeof(this.values[attributeId]) == 'undefined')? '' : this.values[attributeId];
                this.configureElement(element);
            }.bind(this));
        }
    },

    configure: function(event){
        var element = Event.element(event);
        this.configureElement(element);
    },

    configureElement : function(element) {
        this.reloadOptionLabels(element);
        if(element.value){
            this.state[element.config.id] = element.value;
            if(element.nextSetting){
                element.nextSetting.disabled = false;
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
            }
        }
        else {
            this.resetChildren(element);
        }
        this.reloadPrice();
//      Calculator.updatePrice();
    },

    reloadOptionLabels: function(element){
        var selectedPrice;
        if(element.options[element.selectedIndex].config){
            selectedPrice = parseFloat(element.options[element.selectedIndex].config.price)
        }
        else{
            selectedPrice = 0;
        }
        for(var i=0;i<element.options.length;i++){
            if(element.options[i].config){
                element.options[i].text = this.getOptionLabel(element.options[i].config, element.options[i].config.price-selectedPrice);
            }
        }
    },

    resetChildren : function(element){
        if(element.childSettings) {
            for(var i=0;i<element.childSettings.length;i++){
                element.childSettings[i].selectedIndex = 0;
                element.childSettings[i].disabled = true;
                if(element.config){
                    this.state[element.config.id] = false;
                }
            }
        }
    },

    fillSelect: function(element){
        var attributeId = element.id.replace(/[a-z]*/, '');
        var options = this.getAttributeOptions(attributeId);
        this.clearSelect(element);
        element.options[0] = new Option('', '');
        element.options[0].innerHTML = this.config.chooseText;

        var prevConfig = false;
        if(element.prevSetting){
            prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
        }

        if(options) {
            var index = 1;
            for(var i=0;i<options.length;i++){
                var allowedProducts = [];
                if(prevConfig) {
                    for(var j=0;j<options[i].products.length;j++){
                        if(prevConfig.config.allowedProducts
                            && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                            allowedProducts.push(options[i].products[j]);
                        }
                    }
                } else {
                    allowedProducts = options[i].products.clone();
                }

                if(allowedProducts.size()>0){
                    options[i].allowedProducts = allowedProducts;
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    element.options[index].config = options[i];
                    index++;
                }
            }
        }
    },

    getOptionLabel: function(option, price){
        var price = parseFloat(price);
        if (this.taxConfig.includeTax) {
            var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
            var excl = price - tax;
            var incl = excl*(1+(this.taxConfig.currentTax/100));
        } else {
            var tax = price * (this.taxConfig.currentTax / 100);
            var excl = price;
            var incl = excl + tax;
        }

        if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
            price = incl;
        } else {
            price = excl;
        }

        var str = option.label;
        if(price){
            if (this.taxConfig.showBothPrices) {
                str+= ' ' + this.formatPrice(excl, true) + ' (' + this.formatPrice(price, true) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= ' ' + this.formatPrice(price, true);
            }
        }
        return str;
    },

    formatPrice: function(price, showSign){
        var str = '';
        price = parseFloat(price);
        if(showSign){
            if(price<0){
                str+= '-';
                price = -price;
            }
            else{
                str+= '+';
            }
        }

        var roundedPrice = (Math.round(price*100)/100).toString();

        if (this.prices && this.prices[roundedPrice]) {
            str+= this.prices[roundedPrice];
        }
        else {
            str+= this.priceTemplate.evaluate({price:price.toFixed(2)});
        }
        return str;
    },

    clearSelect: function(element){
        for(var i=element.options.length-1;i>=0;i--){
            element.remove(i);
        }
    },

    getAttributeOptions: function(attributeId){
        if(this.config.attributes[attributeId]){
            return this.config.attributes[attributeId].options;
        }
    },

    reloadPrice: function(){
        var price    = 0;
        var oldPrice = 0;
        for(var i=this.settings.length-1;i>=0;i--){
            var selected = this.settings[i].options[this.settings[i].selectedIndex];
            if(selected.config){
                price    += parseFloat(selected.config.price);
                oldPrice += parseFloat(selected.config.oldPrice);
            }
        }

        optionsPrice.changePrice('config', {'price': price, 'oldPrice': oldPrice});
        optionsPrice.reload();

        return price;

        if($('product-price-'+this.config.productId)){
            $('product-price-'+this.config.productId).innerHTML = price;
        }
        this.reloadOldPrice();
    },

    reloadOldPrice: function(){
        if ($('old-price-'+this.config.productId)) {

            var price = parseFloat(this.config.oldPrice);
            for(var i=this.settings.length-1;i>=0;i--){
                var selected = this.settings[i].options[this.settings[i].selectedIndex];
                if(selected.config){
                    var parsedOldPrice = parseFloat(selected.config.oldPrice);
                    price += isNaN(parsedOldPrice) ? 0 : parsedOldPrice;
                }
            }
            if (price < 0)
                price = 0;
            price = this.formatPrice(price);

            if($('old-price-'+this.config.productId)){
                $('old-price-'+this.config.productId).innerHTML = price;
            }

        }
    }
}


/**************************** SUPER PRODUCTS ********************************/

Product.Super = {};
Product.Super.Configurable = Class.create();

Product.Super.Configurable.prototype = {
    initialize: function(container, observeCss, updateUrl, updatePriceUrl, priceContainerId) {
        this.container = $(container);
        this.observeCss = observeCss;
        this.updateUrl = updateUrl;
        this.updatePriceUrl = updatePriceUrl;
        this.priceContainerId = priceContainerId;
        this.registerObservers();
    },
    registerObservers: function() {
        var elements = this.container.getElementsByClassName(this.observeCss);
        elements.each(function(element){
            Event.observe(element, 'change', this.update.bindAsEventListener(this));
        }.bind(this));
        return this;
    },
    update: function(event) {
        var elements = this.container.getElementsByClassName(this.observeCss);
        var parameters = Form.serializeElements(elements, true);

        new Ajax.Updater(this.container, this.updateUrl + '?ajax=1', {
                parameters:parameters,
                onComplete:this.registerObservers.bind(this)
        });
        var priceContainer = $(this.priceContainerId);
        if(priceContainer) {
            new Ajax.Updater(priceContainer, this.updatePriceUrl + '?ajax=1', {
                parameters:parameters
            });
        }
    }
}

/**************************** PRICE RELOADER ********************************/
Product.OptionsPrice = Class.create();
Product.OptionsPrice.prototype = {
    initialize: function(config) {
        this.productId          = config.productId;
        this.priceFormat        = config.priceFormat;
        this.includeTax         = config.includeTax;
        this.defaultTax         = config.defaultTax;
        this.currentTax         = config.currentTax;
        this.productPrice       = config.productPrice;
        this.showIncludeTax     = config.showIncludeTax;
        this.showBothPrices     = config.showBothPrices;
        this.productOldPrice    = config.productOldPrice;
        this.priceInclTax       = config.priceInclTax;
        this.priceExclTax       = config.priceExclTax;
        this.skipCalculate      = config.skipCalculate; /** @deprecated after 1.5.1.0 */
        this.duplicateIdSuffix  = config.idSuffix;
        this.specialTaxPrice    = config.specialTaxPrice;
        this.tierPrices         = config.tierPrices;
        this.tierPricesInclTax  = config.tierPricesInclTax;

        this.oldPlusDisposition = config.oldPlusDisposition;
        this.plusDisposition    = config.plusDisposition;
        this.plusDispositionTax = config.plusDispositionTax;

        this.oldMinusDisposition = config.oldMinusDisposition;
        this.minusDisposition    = config.minusDisposition;

        this.exclDisposition     = config.exclDisposition;

        this.optionPrices   = {};
        this.customPrices   = {};
        this.containers     = {};

        this.displayZeroPrice   = true;

        this.initPrices();
    },

    setDuplicateIdSuffix: function(idSuffix) {
        this.duplicateIdSuffix = idSuffix;
    },

    initPrices: function() {
        this.containers[0] = 'product-price-' + this.productId;
        this.containers[1] = 'bundle-price-' + this.productId;
        this.containers[2] = 'price-including-tax-' + this.productId;
        this.containers[3] = 'price-excluding-tax-' + this.productId;
        this.containers[4] = 'old-price-' + this.productId;
    },

    changePrice: function(key, price) {
        this.optionPrices[key] = price;
    },

    addCustomPrices: function(key, price) {
        this.customPrices[key] = price;
    },
    getOptionPrices: function() {
        var price = 0;
        var nonTaxable = 0;
        var oldPrice = 0;
        var priceInclTax = 0;
        var currentTax = this.currentTax;
        $H(this.optionPrices).each(function(pair) {
            if ('undefined' != typeof(pair.value.price) && 'undefined' != typeof(pair.value.oldPrice)) {
                price += parseFloat(pair.value.price);
                oldPrice += parseFloat(pair.value.oldPrice);
            } else if (pair.key == 'nontaxable') {
                nonTaxable = pair.value;
            } else if (pair.key == 'priceInclTax') {
                priceInclTax += pair.value;
            } else if (pair.key == 'optionsPriceInclTax') {
                priceInclTax += pair.value * (100 + currentTax) / 100;
            } else {
                price += parseFloat(pair.value);
                oldPrice += parseFloat(pair.value);
            }
        });
        var result = [price, nonTaxable, oldPrice, priceInclTax];
        return result;
    },

    reload: function() {
        var price;
        var formattedPrice;
        var optionPrices = this.getOptionPrices();
        var nonTaxable = optionPrices[1];
        var optionOldPrice = optionPrices[2];
        var priceInclTax = optionPrices[3];
        optionPrices = optionPrices[0];

        $H(this.containers).each(function(pair) {
            var _productPrice;
            var _plusDisposition;
            var _minusDisposition;
            var _priceInclTax;
            if ($(pair.value)) {
                if (pair.value == 'old-price-'+this.productId && this.productOldPrice != this.productPrice) {
                    _productPrice = this.productOldPrice;
                    _plusDisposition = this.oldPlusDisposition;
                    _minusDisposition = this.oldMinusDisposition;
                } else {
                    _productPrice = this.productPrice;
                    _plusDisposition = this.plusDisposition;
                    _minusDisposition = this.minusDisposition;
                }
                _priceInclTax = priceInclTax;

                if (pair.value == 'old-price-'+this.productId && optionOldPrice !== undefined) {
                    price = optionOldPrice+parseFloat(_productPrice);
                } else if (this.specialTaxPrice == 'true' && this.priceInclTax !== undefined && this.priceExclTax !== undefined) {
                    price = optionPrices+parseFloat(this.priceExclTax);
                    _priceInclTax += this.priceInclTax;
                } else {
                    price = optionPrices+parseFloat(_productPrice);
                    _priceInclTax += parseFloat(_productPrice) * (100 + this.currentTax) / 100;
                }

                if (this.specialTaxPrice == 'true') {
                    var excl = price;
                    var incl = _priceInclTax;
                } else if (this.includeTax == 'true') {
                    // tax = tax included into product price by admin
                    var tax = price / (100 + this.defaultTax) * this.defaultTax;
                    var excl = price - tax;
                    var incl = excl*(1+(this.currentTax/100));
                } else {
                    var tax = price * (this.currentTax / 100);
                    var excl = price;
                    var incl = excl + tax;
                }

                var subPrice = 0;
                var subPriceincludeTax = 0;
                Object.values(this.customPrices).each(function(el){
                    if (el.excludeTax && el.includeTax) {
                        subPrice += parseFloat(el.excludeTax);
                        subPriceincludeTax += parseFloat(el.includeTax);
                    } else {
                        subPrice += parseFloat(el.price);
                        subPriceincludeTax += parseFloat(el.price);
                    }
                });
                excl += subPrice;
                incl += subPriceincludeTax;

                if (typeof this.exclDisposition == 'undefined') {
                    excl += parseFloat(_plusDisposition);
                }

                incl += parseFloat(_plusDisposition) + parseFloat(this.plusDispositionTax);
                excl -= parseFloat(_minusDisposition);
                incl -= parseFloat(_minusDisposition);

                //adding nontaxlable part of options
                excl += parseFloat(nonTaxable);
                incl += parseFloat(nonTaxable);

                if (pair.value == 'price-including-tax-'+this.productId) {
                    price = incl;
                } else if (pair.value == 'price-excluding-tax-'+this.productId) {
                    price = excl;
                } else if (pair.value == 'old-price-'+this.productId) {
                    if (this.showIncludeTax || this.showBothPrices) {
                        price = incl;
                    } else {
                        price = excl;
                    }
                } else {
                    if (this.showIncludeTax) {
                        price = incl;
                    } else {
                        price = excl;
                    }
                }

                if (price < 0) price = 0;

                if (price > 0 || this.displayZeroPrice) {
                    formattedPrice = this.formatPrice(price);
                } else {
                    formattedPrice = '';
                }

                if ($(pair.value).select('.price')[0]) {
                    $(pair.value).select('.price')[0].innerHTML = formattedPrice;
                    if ($(pair.value+this.duplicateIdSuffix) && $(pair.value+this.duplicateIdSuffix).select('.price')[0]) {
                        $(pair.value+this.duplicateIdSuffix).select('.price')[0].innerHTML = formattedPrice;
                    }
                } else {
                    $(pair.value).innerHTML = formattedPrice;
                    if ($(pair.value+this.duplicateIdSuffix)) {
                        $(pair.value+this.duplicateIdSuffix).innerHTML = formattedPrice;
                    }
                }
            };
        }.bind(this));

        for (var i = 0; i < this.tierPrices.length; i++) {
            $$('.price.tier-' + i).each(function (el) {
                var price = this.tierPrices[i] + parseFloat(optionPrices);
                el.innerHTML = this.formatPrice(price);
            }, this);
            $$('.price.tier-' + i + '-incl-tax').each(function (el) {
                var price = this.tierPricesInclTax[i] + parseFloat(optionPrices);
                el.innerHTML = this.formatPrice(price);
            }, this);
            $$('.benefit').each(function (el) {
                var parsePrice = function (html) {
                    return parseFloat(/\d+\.?\d*/.exec(html));
                };
                var container = $(this.containers[3]) ? this.containers[3] : this.containers[0];
                var price = parsePrice($(container).innerHTML);
                var tierPrice = $$('.tier-price.tier-' + i+' .price');
                tierPrice = tierPrice.length ? parsePrice(tierPrice[0].innerHTML, 10) : 0;
                var $percent = Selector.findChildElements(el, ['.percent.tier-' + i]);
                $percent.each(function (el) {
                    el.innerHTML = Math.ceil(100 - ((100 / price) * tierPrice));
                });
            }, this);
        }

    },
    formatPrice: function(price) {
        return formatCurrency(price, this.priceFormat);
    }
};

/* ======================================================================================= */
/* merged js/varien/configurable.js                                                        */
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
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
if (typeof Product == 'undefined') {
    var Product = {};
}

/**************************** CONFIGURABLE PRODUCT **************************/
Product.Config = Class.create();
Product.Config.prototype = {
    initialize: function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        if (config.containerId) {
            this.settings   = $$('#' + config.containerId + ' ' + '.super-attribute-select');
        } else {
            this.settings   = $$('.super-attribute-select');
        }
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;

        // Set default values from config
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }

        // Overwrite defaults by url
        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex != -1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            var urlValues = paramsStr.toQueryParams();
            if (!this.values) {
                this.values = {};
            }
            for (var i in urlValues) {
                this.values[i] = urlValues[i];
            }
        }

        // Overwrite defaults by inputs values if needed
        if (config.inputsInitialized) {
            this.values = {};
            this.settings.each(function(element) {
                if (element.value) {
                    var attributeId = element.id.replace(/[a-z]*/, '');
                    this.values[attributeId] = element.value;
                }
            }.bind(this));
        }

        // Put events to check select reloads
        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if (i == 0){
                this.fillSelect(this.settings[i])
            } else {
                this.settings[i].disabled = true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // Set values to inputs
        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
    },

    configureForValues: function () {
        if (this.values) {
            this.settings.each(function(element){
                var attributeId = element.attributeId;
                element.value = (typeof(this.values[attributeId]) == 'undefined')? '' : this.values[attributeId];
                this.configureElement(element);
            }.bind(this));
        }
    },

    configure: function(event){
        var element = Event.element(event);
        this.configureElement(element);
    },

    configureElement : function(element) {
        this.reloadOptionLabels(element);
        if(element.value){
            this.state[element.config.id] = element.value;
            if(element.nextSetting){
                element.nextSetting.disabled = false;
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
            }
        }
        else {
            this.resetChildren(element);
        }
        this.reloadPrice();
    },

    reloadOptionLabels: function(element){
        var selectedPrice;
        if(element.options[element.selectedIndex].config && !this.config.stablePrices){
            selectedPrice = parseFloat(element.options[element.selectedIndex].config.price)
        }
        else{
            selectedPrice = 0;
        }
        for(var i=0;i<element.options.length;i++){
            if(element.options[i].config){
                element.options[i].text = this.getOptionLabel(element.options[i].config, element.options[i].config.price-selectedPrice);
            }
        }
    },

    resetChildren : function(element){
        if(element.childSettings) {
            for(var i=0;i<element.childSettings.length;i++){
                element.childSettings[i].selectedIndex = 0;
                element.childSettings[i].disabled = true;
                if(element.config){
                    this.state[element.config.id] = false;
                }
            }
        }
    },

    fillSelect: function(element){
        var attributeId = element.id.replace(/[a-z]*/, '');
        var options = this.getAttributeOptions(attributeId);
        this.clearSelect(element);
        element.options[0] = new Option('', '');
        element.options[0].innerHTML = this.config.chooseText;

        var prevConfig = false;
        if(element.prevSetting){
            prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
        }

        if(options) {
            var index = 1;
            for(var i=0;i<options.length;i++){
                var allowedProducts = [];
                if(prevConfig) {
                    for(var j=0;j<options[i].products.length;j++){
                        if(prevConfig.config.allowedProducts
                            && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                            allowedProducts.push(options[i].products[j]);
                        }
                    }
                } else {
                    allowedProducts = options[i].products.clone();
                }

                if(allowedProducts.size()>0){
                    options[i].allowedProducts = allowedProducts;
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    if (typeof options[i].price != 'undefined') {
                        element.options[index].setAttribute('price', options[i].price);
                    }
                    element.options[index].config = options[i];
                    index++;
                }
            }
        }
    },

    getOptionLabel: function(option, price){
        var price = parseFloat(price);
        if (this.taxConfig.includeTax) {
            var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
            var excl = price - tax;
            var incl = excl*(1+(this.taxConfig.currentTax/100));
        } else {
            var tax = price * (this.taxConfig.currentTax / 100);
            var excl = price;
            var incl = excl + tax;
        }

        if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
            price = incl;
        } else {
            price = excl;
        }

        var str = option.label;
        if(price){
            if (this.taxConfig.showBothPrices) {
                str+= ' ' + this.formatPrice(excl, true) + ' (' + this.formatPrice(price, true) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= ' ' + this.formatPrice(price, true);
            }
        }
        return str;
    },

    formatPrice: function(price, showSign){
        var str = '';
        price = parseFloat(price);
        if(showSign){
            if(price<0){
                str+= '-';
                price = -price;
            }
            else{
                str+= '+';
            }
        }

        var roundedPrice = (Math.round(price*100)/100).toString();

        if (this.prices && this.prices[roundedPrice]) {
            str+= this.prices[roundedPrice];
        }
        else {
            str+= this.priceTemplate.evaluate({price:price.toFixed(2)});
        }
        return str;
    },

    clearSelect: function(element){
        for(var i=element.options.length-1;i>=0;i--){
            element.remove(i);
        }
    },

    getAttributeOptions: function(attributeId){
        if(this.config.attributes[attributeId]){
            return this.config.attributes[attributeId].options;
        }
    },

    reloadPrice: function(){
        if (this.config.disablePriceReload) {
            return;
        }
        var price    = 0;
        var oldPrice = 0;
        for(var i=this.settings.length-1;i>=0;i--){
            var selected = this.settings[i].options[this.settings[i].selectedIndex];
            if(selected.config){
                price    += parseFloat(selected.config.price);
                oldPrice += parseFloat(selected.config.oldPrice);
            }
        }

        optionsPrice.changePrice('config', {'price': price, 'oldPrice': oldPrice});
        optionsPrice.reload();

        return price;

        if($('product-price-'+this.config.productId)){
            $('product-price-'+this.config.productId).innerHTML = price;
        }
        this.reloadOldPrice();
    },

    reloadOldPrice: function(){
        if (this.config.disablePriceReload) {
            return;
        }
        if ($('old-price-'+this.config.productId)) {

            var price = parseFloat(this.config.oldPrice);
            for(var i=this.settings.length-1;i>=0;i--){
                var selected = this.settings[i].options[this.settings[i].selectedIndex];
                if(selected.config){
                    price+= parseFloat(selected.config.price);
                }
            }
            if (price < 0)
                price = 0;
            price = this.formatPrice(price);

            if($('old-price-'+this.config.productId)){
                $('old-price-'+this.config.productId).innerHTML = price;
            }

        }
    }
};

/* ======================================================================================= */
/* merged skin/frontend/enterprise/jewelersclub/js/amasty/amconf/formList.js               */
/* ======================================================================================= */
var optionsPrice = [];
var confData = [];

function createForm(url, product, keys)
{
    var form = document.createElement("form");
    form.action = url;
    form.id = 'product_addtocart_form' + '-' + product;
    form.enctype="multipart/form-data";
    form.method="post";
    
    var input = document.createElement("input");
    input.type = "hidden";
    input.name = 'product';
    input.value = product;
    form.appendChild(input);
    
    var input = document.createElement("input");
    input.type = "hidden";
    input.name = 'qty';
    input.value = 1;
    form.appendChild(input);
    
    var valid = true;
    var selectId = '';
    var isValid = 1;
    for ( keyVar in keys ) {
         if ($('attribute' + keys[keyVar] + '-' + product) && isValid){
            var input = document.createElement("input");
            input.type = "hidden";
            $('attribute' + keys[keyVar] + '-' + product).childElements().each(function(element) {
                if(element.selected){
                    if (parseInt(element.value) > 0){
                         input.value = element.value;
                    }
                    else{
                         valid = false;
                         selectId = 'attribute' + keys[keyVar] + '-' + product;
                         isValid = 0;
                    }   
                }
            });
            input.name = $('attribute' + keys[keyVar] + '-' + product).name;
            form.appendChild(input);
         }
    }
    var submit = document.createElement("input");
    submit.type = "submit";
    submit.style.display = 'none';
    form.appendChild(submit);
    $('insert').appendChild(form);
    if(valid){
        form.addClassName('isValid');
    }
    else{
        var attr = document.createAttribute('selectId')
        attr.nodeValue =  selectId; 
        form.attributes.setNamedItem(attr);    
    }
    return form;    
}

function formValidation(form)
{
    selectId = form.getAttribute('selectId');
        if ('' != selectId){
            $(selectId).parentNode.style.border = '1px dashed red';
            $(selectId).parentNode.style.margin = '0';
            $$('.dashed-red').each(function(elem){
               elem.removeClassName('dashed-red');
               elem.style.border = '0';
            });
            $(selectId).parentNode.addClassName('dashed-red');
             if(amRequaredField)
                $('requared-' + selectId).innerHTML = amRequaredField;
            $$('.required-field').each(function(elem){
               elem.removeClassName('required-field');
               elem.innerHTML = '';
            });
            $('requared-' + selectId).addClassName('required-field');
        } 
	form.remove();   
}

function formSubmit(button,url,product,keys)
{
    var form = createForm(url, product, keys); 
    if (form.hasClassName('isValid')) {
            var e = null;
            try {
                form.submit();
                form.remove();
            } catch (e) {
            }
            if (e) {
                throw e;
            }
            if (button && button != undefined) {
                button.disabled = true;
            }
    }
    else{
        formValidation(form);    
    }
};
/* ======================================================================================= */
/* merged skin/frontend/enterprise/jewelersclub/js/amasty/amconf/configurableList.js       */
/* ======================================================================================= */
// extension Code

AmConfigurableData = Class.create();
AmConfigurableData.prototype = 
{
    textNotAvailable : "",
    
    mediaUrlMain : "",
    
    currentIsMain : "",
    
    optionProducts : null,
    
    optionDefault : new Array(),
    
    oneAttributeReload : false,
    
    amlboxInstalled : false,
    
    initialize : function(optionProducts)
    {
        this.optionProducts = optionProducts;
    },
    
    hasKey : function(key)
    {
        return ('undefined' != typeof(this.optionProducts[key]));
    },
    
    getData : function(key, param)
    {
        if (this.hasKey(key) && 'undefined' != typeof(this.optionProducts[key][param]))
        {
            return this.optionProducts[key][param];
        }
        return false;
    },
    
    saveDefault : function(param, data)
    {
        this.optionDefault['set'] = true;
        this.optionDefault[param] = data;
    },
    
    getDefault : function(param)
    {
        if ('undefined' != typeof(this.optionDefault[param]))
        {
            return this.optionDefault[param];
        }
        return false;
    }
}

prevNextSetting = [];
// extension Code End
Product.Config.prototype.initialize = function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        if (config.containerId) {
            this.settings   = $$('#' + config.containerId + ' ' + '.super-attribute-select' + '-' + config.productId);
        } else {
            this.settings   = $$('.super-attribute-select' + '-' + config.productId);
        }
     
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;
        
        // Set default values from config
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }
        //hide all labels
         this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
             $('label-' + attributeId).hide();
         }.bind(this))
        
        // Overwrite defaults by url
        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex != -1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            var urlValues = paramsStr.toQueryParams();
            if (!this.values) {
                this.values = {};
            }
            for (var i in urlValues) {
                this.values[i] = urlValues[i];
            }
        }
        
        // Overwrite defaults by inputs values if needed
        if (config.inputsInitialized) {
            this.values = {};
            this.settings.each(function(element) {
                if (element.value) {
                    var attributeId = element.id.replace(/[a-z]*/, '');
                    this.values[attributeId] = element.value;
                }
            }.bind(this));
        }
            
        // Put events to check select reloads 
        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            var pos = attributeId.indexOf('-');
            if ('-1' != pos)
                attributeId = attributeId.substring(0, pos);
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))
   //If Ajax Cart     
    if('undefined' != typeof(AmAjaxObj)) {
            var length = this.settings.length;
            for (var i = 0; i < length-1; i++) {
              var element = this.settings[i];
              if(element  && element.config){
                   for (var j = i+1; j < length; j++) {
                       var elementNext = this.settings[j];
                       if(elementNext  && elementNext.config && (elementNext.config['id'] == element.config['id'])){
                            this.settings.splice (i,1);
                            i--;
                            break;    
                       }    
                   }    
              }
            }    
         }  
            
        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if (i == 0){
                this.fillSelect(this.settings[i])
            } else {
                this.settings[i].disabled = true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            prevNextSetting[this.settings[i].config.id] = [prevSetting, nextSetting];
            var optionId = this.settings[i].id;
            var pos = optionId.indexOf('-');
            if ('-1' != pos){
                optionId = optionId.substring(pos+1, optionId.lenght);
                id = parseInt(optionId);
                prevNextSetting[id] = [];
                prevNextSetting[id][this.settings[i].config.id] = [prevSetting, nextSetting];
            }
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }
        // Set values to inputs
        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
}
 
    
resetChildren = function(element){
    if(element.childSettings) {
        for(var i=0;i<element.childSettings.length;i++){
            element.childSettings[i].selectedIndex = 0;
            element.childSettings[i].disabled = true;
            if(element.config){
                this.state[element.config.id] = false;
            }
        }
    }
    // extension Code Begin
    this.processEmpty();
    // extension Code End
}

Product.Config.prototype.fillSelect = function(element){
    var attributeId = element.id.replace(/[a-z]*/, '');
    var pos = attributeId.indexOf('-');
    if ('-1' != pos)
        attributeId = attributeId.substring(0, pos);
    var options = this.getAttributeOptions(attributeId);
    this.clearSelect(element);
    element.options[0] = new Option(this.config.chooseText, '');

    var prevConfig = false;
    if(element.prevSetting){
        prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
    }
    if(options) {
        // extension Code
        if (this.config.attributes[attributeId].use_image)
        {
            if ($('amconf-images-' + attributeId + '-' + this.config.productId))
            {
                $('amconf-images-' + attributeId + '-' + this.config.productId).parentNode.removeChild($('amconf-images-' + attributeId + '-' + this.config.productId));
            }
            holder = element.parentNode;
            $('label-' + attributeId + '-' + this.config.productId).show();
            var holderDiv = document.createElement('div');
            holderDiv = $(holderDiv); // fix for IE
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId + '-' + this.config.productId;
            holder.insertBefore(holderDiv, element);
        }
        // extension Code End
        
        var index = 1;
        for(var i=0;i<options.length;i++){
            var allowedProducts = [];
            if(prevConfig) {
                for(var j=0;j<options[i].products.length;j++){
                    if(prevConfig.config && prevConfig.config.allowedProducts
                        && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                        allowedProducts.push(options[i].products[j]);
                    }
                }
            } else {
                allowedProducts = options[i].products.clone();
            }

            if(allowedProducts.size()>0)
            {
                // extension Code
                if (this.config.attributes[attributeId].use_image)
                {
                    var imgContainer = document.createElement('div');
                    imgContainer = $(imgContainer); // fix for IE
                    imgContainer.addClassName('amconf-image-container');
                    imgContainer.id = 'amconf-images-container-' + options[i].id + '-' + this.config.productId;
                    holderDiv.appendChild(imgContainer);
            
                    image = document.createElement('img');
                    image = $(image); // fix for IE
                    image.id = 'amconf-image-' + options[i].id + '-' + this.config.productId;
		            image.src   = options[i].image;
                    image.width = this.config.size;
                    image.height = this.config.size;
                    image.addClassName('amconf-image');
		            image.alt = options[i].label;
		            image.title = options[i].label;
                    image.observe('click', this.configureImage.bind(this));
		    		if('undefined' != typeof(buble)){
                         image.observe('mouseover', buble.showToolTip)
                         image.observe('mouseout', buble.hideToolTip)       
                    }
                    imgContainer.appendChild(image);
                }
                // extension Code End
                
                options[i].allowedProducts = allowedProducts;
                element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                element.options[index].config = options[i];
                index++;
            }
        }
        if(index > 1 && this.config.attributes[attributeId].use_image) {
            var amcart  = document.createElement('div');
            amcart = $(amcart); // fix for IE
            amcart.id = 'amconf-amcart-' + this.config.productId;
            holderDiv.appendChild(amcart);
        }
        if(this.config.attributes[attributeId].use_image) {
            var lastContainer = document.createElement('div');
            lastContainer = $(lastContainer); // fix for IE
            lastContainer.setStyle({clear : 'both'});
            holderDiv.appendChild(lastContainer);    
        }
    }
}

Product.Config.prototype.configureElement = function(element) 
{
    // extension Code
    var me = this;
    var optionId = element.value;
   
    this.reloadOptionLabels(element);
//Ajax cart
    if(element.value){
        if (element.config.id){
            this.state[element.config.id] = element.value;
        }
        var elId = element.id;
        var pos = elId.indexOf('-');
        if ('-1' != pos){
            elId = elId.substring(pos+1, elId.lenght);
            elId = 	parseInt(elId);
            if(prevNextSetting[elId] && prevNextSetting[elId][element.config.id] && prevNextSetting[elId][element.config.id][1] || element.nextSetting){
                 if(prevNextSetting[elId] && prevNextSetting[elId][element.config.id] && prevNextSetting[elId][element.config.id][1]){
                    element.nextSetting = prevNextSetting[elId][element.config.id][1]
                }
                element.nextSetting.disabled = false;
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
            }
        }
    }
    else {
        // extension Code
        if(element.childSettings) {
            for(var i=0;i<element.childSettings.length;i++){
                attributeId = element.childSettings[i].id.replace(/[a-z-]*/, '');
                if ($('amconf-images-' + attributeId + '-' + this.config.productId))
                {
                    $('amconf-images-' + attributeId + '-' + this.config.productId).parentNode.removeChild($('amconf-images-' + attributeId + '-' + this.config.productId));
                }
            }
        }
        // extension Code End
        
        this.resetChildren(element);
        
        // extension Code
        if (this.settings[0].hasClassName('no-display'))
        {
            this.processEmpty();
        }
        // extension Code End
    }
    
    if ($('amconf-image-' + optionId + '-' + this.config.productId))
    {
        this.selectImage($('amconf-image-' + optionId + '-' + this.config.productId));
    } 
    else {
        attributeId = element.id.replace(/[a-z-]*/, '');
        if ($('amconf-images-' + attributeId))
        {
            $('amconf-images-' + attributeId).childElements().each(function(child){
                child.removeClassName('amconf-image-selected');
            });
        }
    }
    
    // for compatibility with custom stock status extension:
    if ('undefined' != typeof(stStatus) && 'function' == typeof(stStatus.onConfigure))
    {
        stStatus.onConfigure(key, this.settings); // todo: key is undefined
    }
    // extension Code End
}
    
Product.Config.prototype.reloadSimplePrice = function(parentId, key)
{
    if ('undefined' == typeof(confData) || 'undefined' == typeof(confData[parentId]['optionProducts'][key]['price_html']))
    {
        return false;
    }
    
    var result = false;
    var childConf = confData[parentId]['optionProducts'][key];
    
    result = childConf['price_html'];

    var elmExpr = '.price-box';// span#product-price-'+parentId+' span.price';
    $$(elmExpr).each(function(container)
    {
        if(container.select('#product-price-'+parentId) != 0 || container.select('#parent-product-price-'+parentId) != 0) {
            var tmp = document.createElement('div');
            tmp = $(tmp); // fix for IE
            tmp.style.display = "none";
            tmp.innerHTML = result;
            container.appendChild(tmp);
            
            var parent = document.createElement('div');
            parent = $(parent); // fix for IE
            parent.id = 'parent-product-price-'+parentId;
            var tmp1 = tmp.childElements()[0];
            tmp1.appendChild(parent);
            
            container.innerHTML = tmp1.innerHTML; 
        }
    }.bind(this));
    
    return result; // actually the return value is never used
}

// these are new methods introduced by the extension
// extension Code
Product.Config.prototype.configureImage = function(event){
    var element = Event.element(event);
    attributeId = element.parentNode.parentNode.id.replace(/[a-z-]*/, '');
    var optionId = element.id.replace(/[a-z-]*/, '');
    var pos = optionId.indexOf('-');
    if ('-1' != pos)
        optionId = optionId.substring(0, pos);
    //this.selectImage(element);
    $$('#attribute' + attributeId).each(function(select){
        select.value = optionId;    
    });
    this.configureElement($('attribute' + attributeId));
}

Product.Config.prototype.selectImage = function(element)
{
    attributeId = element.parentNode.parentNode.id.replace(/[a-z-]*/, '');
    $('amconf-images-' + attributeId).childElements().each(function(child){
        var childr = child.childElements();
        if(childr[0]) {
            $(childr[0]).removeClassName('amconf-image-selected');    
        }
    });
    element.addClassName('amconf-image-selected');
    
    var pos = attributeId.indexOf('-');
    if ('-1' == pos) return;
        
    var optionId = attributeId.substring(0, pos);
    var parentId = attributeId.substring(pos+1, attributeId.length);

    var key = '';
    this.settings.each(function(select){
        if (parseInt(select.value))
        {
           key += select.value + ',';
        }
    });
    key = key.substr(0, key.length - 1);

    if('undefined' != typeof(confData[parentId]['optionProducts'][key]['small_image'])){ 
         var parUrl = confData[parentId]['optionProducts'][key]['parent_image'];
         var possl = parUrl.lastIndexOf('/');
         $$('.product-image img').each(function(img){
              var posslImg = img.src.lastIndexOf('/');
              if(img.src.substr(posslImg, img.src.length) == parUrl.substr(possl, parUrl.length) || img.hasClassName('amconf-parent-'+parentId)){
                  img.src = confData[parentId]['optionProducts'][key]['small_image'];
                  img.addClassName('amconf-parent-'+parentId);
                  
              }
         });              
                    
      }
      
    if ('undefined' != typeof(confData[parentId]) && confData[parentId].useSimplePrice == "1")
    {
        this.reloadSimplePrice(parentId, key);
    }
    else
    {
        // default behaviour
        this.reloadPrice();
    }
}

Product.Config.prototype.processEmpty = function()
{
    if ('undefined' == typeof(this.config)) return true;
    var me = this;
    $$('.super-attribute-select').each(function(select) {
        if (select.disabled)
        {
            var attributeId = select.id.replace(/[a-z]*/, '');
            if ($('amconf-images-' + attributeId + '-' + this.config.productId))
            {
                $('amconf-images-' + attributeId + '-' + this.config.productId).parentNode.removeChild($('amconf-images-' + attributeId + '-' + this.config.productId));
            }
            holder = select.parentNode;
            holderDiv = document.createElement('div');
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId + '-' + this.config.productId;
            if ('undefined' != typeof(confData[me.config.productId]))
            {
            	holderDiv.innerHTML = confData[me.config.productId].textNotAvailable;
            } else 
            {
            	holderDiv.innerHTML = "";
            }
            holder.insertBefore(holderDiv, select);
        }
    }.bind(this));
}

Product.Config.prototype.reloadPrice = function(){
        if (this.config.disablePriceReload) {
            return;
        }
        var price    = 0;
        var oldPrice = 0;
        for(var i=this.settings.length-1;i>=0;i--){
            var selected = this.settings[i].options[this.settings[i].selectedIndex];
            if(selected.config){
                price    += parseFloat(selected.config.price);
                oldPrice += parseFloat(selected.config.oldPrice);
            }
        }

        optionsPrice[this.config.productId].changePrice('config', {'price': price, 'oldPrice': oldPrice});
        optionsPrice[this.config.productId].reload();
        return price;
        if($('product-price-'+this.config.productId)){
            $('product-price-'+this.config.productId).innerHTML = price;
        }
        this.reloadOldPrice();
}


Event.observe(window, 'load', function(){
     imageObj = new Image();
     for ( keyVar in confData ) {
         if( parseInt(keyVar) > 0){
             for ( keyImg in confData[keyVar]['optionProducts'] ) {
                 imageObj.src = confData[keyVar]['optionProducts'][keyImg]['small_image'];
             }
         } 
     }
});
/* ======================================================================================= */
/* merged skin/frontend/enterprise/jewelersclub/js/amasty/amconf/bubleTooltip.js           */
/* ======================================================================================= */
function findTopLeft(obj)
{
    var curleft = curtop = 0;
    if (obj.offsetParent) {
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
    }
    return [curleft,curtop];
}

//class for onmouseover showing option name
Buble = Class.create();
Buble.prototype = 
{    
    isCreated : false,
    
    bubleTooltip : null,
    
    text : null, 
    
     initialize : function()
    {
        var me = this;    
    },  
        
    showToolTip : function(event)
    {
         if( !this.isCreated ){
            var element = Event.element(event);
            var bubleTooltip = $('bubble');
            var bubleMiddle = $('buble_middle');
            var parent  =  element.parentNode;
            parent.appendChild(bubleTooltip);
            this.text = element.alt; 
                   
			bubleTooltip.style.opacity = 0;
            new Effect.Opacity('bubble', { from: 0, to: 1, duration: 0.2 });
			
            bubleMiddle.innerHTML = this.text;
            bubleTooltip.style.display = 'block'; 
            var offset = findTopLeft(element);
            bubleTooltip.style.left =  10 - bubleTooltip.getWidth() + "px";
            bubleTooltip.style.top =   - bubleTooltip.getHeight() + 5 + 'px';

            this.isCreated = true;
            this.bubleTooltip = bubleTooltip;
            if(!this.text){
				$('bubble').hide();
                this.isCreated = false;    
            }
        }
    },
    
    hideToolTip : function()
    {
        if(this.isCreated){
	    $('bubble').hide();
	    $$('body')[0].appendChild($('bubble'));
            this.isCreated = false;   
        }
    }
}
 var buble = new Buble();
 
