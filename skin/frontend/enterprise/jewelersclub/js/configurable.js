/**
 * Modify the thumbnail container from .product-img-box to .product-img-holder
 * to work with JClub theme
 */
Product.Config.prototype.updateData = function(key)
{
    var imageClassName = '.product-img-holder';
    if(!$$(imageClassName)[0]) var imageClassName = '.img-box';
    if(!$$(imageClassName)[0]) var imageClassName = '.product-img-column';
    if ('undefined' == typeof(confData))
    {
        return false;
    }
    if (confData.hasKey(key))
    {
        // getting values of selected configuration
        if (confData.getData(key, 'name'))
        {
            $$('.product-name h1').each(function(container){
                if (!confData.getDefault('name'))
                {
                    confData.saveDefault('name', container.innerHTML);
                }
                container.innerHTML = confData.getData(key, 'name');
            }.bind(this));
        }
        if (confData.getData(key, 'short_description'))
        {
            $$('.short-description div').each(function(container){
                if (!confData.getDefault('short_description'))
                {
                    confData.saveDefault('short_description', container.innerHTML);
                }
                container.innerHTML = confData.getData(key, 'short_description');
            }.bind(this));
        }
        if (confData.getData(key, 'description'))
        {
            $$('.box-description div').each(function(container){
                if (!confData.getDefault('description'))
                {
                    confData.saveDefault('description', container.innerHTML);
                }
                container.innerHTML = confData.getData(key, 'description');
            }.bind(this));
        }
        if (confData.getData(key, 'media_url'))
        {
            // should reload images
            tmpContainer = $$(imageClassName)[0];
            new Ajax.Updater(tmpContainer, confData.getData(key, 'media_url'), {
                evalScripts: true,
                onSuccess: function(transport)
                {

                },
                onCreate: function()
                {
                    confData.saveDefault('media', tmpContainer.innerHTML);
                    confData.currentIsMain = false;
                },
                onComplete: function()
                {
                    if('undefined' != typeof(AmZoomerObj)) {
                        if($$('.zoomContainer')[0]) $$('.zoomContainer')[0].remove();
                        AmZoomerObj.loadZoom();
                    }
                    jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
                }
            });
        } else if (confData.getData(key, 'noimg_url'))
        {
            noImgInserted = false;
            $$(imageClassName + ' img').each(function(img){
                if (!noImgInserted)
                {
                    img.src = confData.getData(key, 'noimg_url');
                    $(img).stopObserving('click');
                    $(img).stopObserving('mouseover');
                    $(img).stopObserving('mousemove');
                    $(img).stopObserving('mouseout');
                    noImgInserted = true;
                }
            });
        }
    } else
    {
        // setting values of default product
        if (true == confData.getDefault('set'))
        {
            if (confData.getDefault('name'))
            {
                $$('.product-name h1').each(function(container){
                    container.innerHTML = confData.getDefault('name');
                }.bind(this));
            }
            if (confData.getDefault('short_description'))
            {
                $$('.short-description div').each(function(container){
                    container.innerHTML = confData.getDefault('short_description');
                }.bind(this));
            }
            if (confData.getDefault('description'))
            {
                $$('.box-description div').each(function(container){
                    container.innerHTML = confData.getDefault('description');
                }.bind(this));
            }
            if (confData.getDefault('media') && !confData.currentIsMain)
            {
                var tmpContainer = $$(imageClassName)[0];
                new Ajax.Updater(tmpContainer, confData.mediaUrlMain, {
                    evalScripts: true,
                    onSuccess: function(transport) {
                        confData.saveDefault('media', tmpContainer.innerHTML);
                        confData.currentIsMain = true;
                    },
                    onComplete: function()
                    {
                        if('undefined' != typeof(AmZoomerObj)) {
                            if($$('.zoomContainer')[0]) $$('.zoomContainer')[0].remove();
                            AmZoomerObj.loadZoom();
                        }
                        jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
                    }
                });
            }
        }
    }
}
