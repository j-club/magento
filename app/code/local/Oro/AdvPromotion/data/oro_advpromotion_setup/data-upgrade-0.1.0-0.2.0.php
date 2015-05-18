<?php
/**
 * Upgrade
 *
 * @category   Oro
 * @package    Oro_AdvPromotion
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$pageContentArray = array(
    '<a href="#">BLACK FRIDAY PROMOTION: 50% OFF</a>',
    '<a href="#">Rewards / Club Points</a>',
    '<a href="#">Free Shipping over $50</a>'
);

for ($i=0; $i < 3; $i++) {
    $data = array(
        'title'           => 'Promotion'.($i+1),
        'identifier'      => 'homepromotion'.($i+1),
        'content'         => $pageContentArray[$i],
        'is_active'       => 1,
    );

    Mage::getModel('cms/block')->setData($data)->save();
}