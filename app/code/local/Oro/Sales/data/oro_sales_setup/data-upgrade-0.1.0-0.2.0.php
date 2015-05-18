<?php
/**
 * Data upgrade
 *
 * @category   Oro
 * @package    Oro_Sales
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */


$data = array(
    'title'           => 'Subscribe Popup',
    'identifier'      => 'subscribe-popup',
    'content'         => "Replace this text for real text",
    'is_active'       => 1,
    'stores'          => 0
);

Mage::getModel('cms/block')->setData($data)->save();