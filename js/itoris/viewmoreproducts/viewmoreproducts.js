if (!Itoris) {
    var Itoris = {};
}

Itoris.ViewMoreProducts = Class.create({
    initialize : function(text, method) {
        this.viewMoreText = text;
        this.countPages = 1;
        this.method = method;
        this.viewMoreTop = true;
        var productTimer  = null;
        productTimer = new PeriodicalExecuter(function() {
            this.moveElements(productTimer);
        }.bind(this), 0.2);
        if (method == 1) {
            Event.observe(window, 'scroll', function(){
                if ($$('.itoris_view_more_products')[0]) {
                    var viewMore = Element.viewportOffset($$('.itoris_view_more_products')[0]).top;
                    var windowHeight = document.viewport.getHeight();
                    if (viewMore != 0 && this.viewMoreTop && viewMore - 30 <= windowHeight) {
                        this.loadMoreProducts();
                    }
                }
            }.bind(this));
        }
    },
    moveElements : function(t) {
        var toolbar = $$('.category-products .toolbar');
        if (toolbar.length) {
            t.stop();
        }
        for (var i = 0; i < toolbar.length; i++) {
            var pager = $$('.category-products .pager')[i];
            var sorter = $$('.category-products .sorter')[i];
            var amount = pager ? pager.select('.amount')[0] : null;
            var viewMode = sorter ? sorter.select('.view-mode')[0] : null;
            if (amount && sorter && viewMode) {
                sorter.insertBefore(amount, viewMode);
                amount.setStyle({float: 'left', paddingRight: '30px', margin: '0'});
            }
            if (pager) {
                pager.hide();
            }
        }
        this.addButtonViewMore();
    },
    addButtonViewMore : function() {
        var toolbarBottom = $$('.category-products .toolbar-bottom')[0];
        var pagesTop = $$('.category-products .pages')[0];
        var pagesBottom = $$('.category-products .pages')[1];
        if (toolbarBottom && (pagesTop || pagesBottom) && !$$('.category-products .itoris_view_more_products')[0]) {
            var viewMore = document.createElement('div');
            Element.extend(viewMore);
            viewMore.addClassName('itoris_view_more_products');
            $$('.category-products')[0].insertBefore(viewMore, toolbarBottom);
            var span = document.createElement('span');
            Element.extend(span);
            span.addClassName('itoris_viewmore_text');
            span.update(this.viewMoreText);
            viewMore.appendChild(span);
            viewMore.appendChild($$('.itoris_viewmoreproducts_loader')[0]);
            if (this.method == 0) {
                Event.observe(viewMore, 'click', this.loadMoreProducts.bind(this));
            }
            this.countPages = $$('.category-products .toolbar-bottom .pages ol li').length - 1;
        }
    },
    loadMoreProducts : function() {
        var next = $$('.category-products .pager a.next')[0];
        if (next &&  this.viewMoreTop) {
            var nextUrl = next.href;
            this.viewMoreTop = false;
            new Ajax.Request(nextUrl, {
                method     : 'post',
                onLoading : function() {
                    $$('.itoris_viewmoreproducts_loader')[0].show();
                    $$('.itoris_viewmore_text')[0].hide();
                }.bind(this),
                onSuccess: function(response){
                    this.viewMoreTop = true;
                    $$('.itoris_viewmoreproducts_loader')[0].hide();
                    $$('.itoris_viewmore_text')[0].show();
                    var div = new Element('div');
                    div.innerHTML = response.responseText;
                    var productUls = div.select('.category-products ul.products-grid');
                    var productList = div.select('.category-products ol#products-list .item');
                    if (productUls.length) {
                        var products = productUls;
                        var mode = 'grid';
                        var productsByMode = $$('.category-products ul.products-grid');
                    } else if (productList.length) {
                        var products = productList;
                        var mode = 'list';
                        var productsByMode = $$('.category-products ol#products-list .item');
                    }
                    var countProductsBeforeAdd = productsByMode.length;
                    productsByMode[countProductsBeforeAdd-1].removeClassName('last');
                    for (var i = 0; i < products.length; i++) {
                        if (mode == 'grid') {
                            $$('.category-products')[0].insertBefore(products[i], $$('.category-products .itoris_view_more_products')[0]);
                        } else if (mode == 'list') {
                            $$('.category-products ol#products-list')[0].appendChild(products[i]);
                        }
                    }

                    var amountElement = $$('.category-products .sorter .amount')[0];
                    if (amountElement && amountElement.textContent) {
                        var amountText = amountElement.textContent;
                        var textAsArray = amountText.match('([^0-9]*[0-9]+[^0-9]*)([0-9]*)(.*)');
                        if (mode == 'grid') {
                            var countProductsAfterAdd = $$('.category-products ul.products-grid .item').length;
                        } else if (mode == 'list') {
                            var countProductsAfterAdd = $$('.category-products ol#products-list .item').length;
                        }

                        var newTextForAmount = textAsArray[1] + countProductsAfterAdd + textAsArray[3];
                        $$('.category-products .sorter .amount')[0].update(newTextForAmount);
                        $$('.category-products .sorter .amount')[1].update(newTextForAmount);
                    }

                    var currentNextUrl = nextUrl.split('p=');
                    var numberCurrentNextPage = currentNextUrl[1] ? parseInt(currentNextUrl[1]) : 1;
                    if (numberCurrentNextPage < this.countPages) {
                        var numberNextPage = numberCurrentNextPage + 1;
                        next.href = nextUrl.replace('p=' + numberCurrentNextPage, 'p=' + numberNextPage);
                    } else {
                        $$('.category-products .itoris_view_more_products')[0].hide();
                    }
                    //document.body.fire('layerednavigation:onSuccessSend');
                    jQuery(".rating-holder").each(function(index, el) {
                        eval(jQuery(el).find('script').text());
                    });
                }.bind(this)
            });
        }
    }
});
