/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Advancednewsletter
 * @copyright  Copyright (c) 2003-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var Advancednewsletter = Class.create();
Advancednewsletter.prototype = {
	url: '',
    initialize: function(ctrl, url) {
        this.url = url;
        ctrl.observe('click', function(event){this.display();Event.stop(event);}.bind(this));
        $('an-content').observe('click', (function(event) {if (event.element().id == 'advancednewsletter-cancel') this.deactivate();}).bind(this));
	},
    display: function(){
        if ($('advancednewsletter-subscribe-ajax') == undefined)
        {this.sendResponse()}
        else
        { 
            $('advancednewsletter-overlay').show();
            $('an-content').show();
        }
    },
    deactivate: function(){
        $('advancednewsletter-overlay').hide();
        $('an-content').hide();
    },
    sendResponse: function(){
        this.displayWait();
        new Ajax.Request(this.url, {
          onSuccess: function(resp){
				$('subscribe-please-wait').hide();
                $('an-content').update(resp.responseText.stripScripts());
                this.alignBlockAn($('an-content'), 400, 200);
                advancednewsletterForm = new VarienForm('advancednewsletter-form');
			}.bind(this)
        });
    },
    displayWait: function(){
        $('advancednewsletter-overlay').show();
        this.alignBlockAn($('subscribe-please-wait'), 120, 20);
    },
    alignBlockAn: function(block, width, height){
        block.style.display = 'block';
        block.style.width = width + 'px';
        block.style.height = height + 'px';
        block.style.left = (document.viewport.getWidth()/2 - width/2) + 'px';
        block.style.top = (document.viewport.getHeight()/2 - height/2) + 'px';
    }
}

