<?php
/**
 * Data upgrade
 *
 * @category   Oro
 * @package    Oro_Sales
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */


$data = array(
    'title'           => 'Subscribe Popup Body',
    'identifier'      => 'subscribe-popup-body',
    'content'         => '<h3>GET 20% OFF</h3>
                                <strong class="title">
                                    Just for joining our mailing list, you will get 20% OFF your next purchase.
                                </strong>',
    'is_active'       => 1,
    'stores'          => 0
);

Mage::getModel('cms/block')->setData($data)->save();
