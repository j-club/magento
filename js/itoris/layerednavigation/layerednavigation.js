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