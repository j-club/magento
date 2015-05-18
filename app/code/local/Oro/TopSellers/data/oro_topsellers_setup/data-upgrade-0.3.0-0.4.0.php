<?php
/**
 * Data Upgrade
 *
 * @category   Oro
 * @package    Oro_TopSellers
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$blockContent = '
<div class="banner">
    <a href="#"><img src="'.Mage::getDesign()->getSkinUrl('images/banner-1.gif').'" alt="jewelry Buying Guides Learn before you buy"/></a>
</div>
<div class="banner">
    <a href="#"><img src="'.Mage::getDesign()->getSkinUrl('images/banner-2.gif').'" alt="Shop Necklaces"/></a>
</div>
<div class="banner">
    <a href="#"><img src="'.Mage::getDesign()->getSkinUrl('images/banner-3.gif').'" alt="Precious Pearls Celebrating 20 years of giving happiness Shop Pearls"/></a>
</div>
';

$blockData = array(
    'title'         => 'Right sidebar block',
    'identifier'    => 'sidebar_block',
    'content'       => $blockContent,
    'is_active'     => 1,
    'stores'        => 0
);
Mage::getModel('cms/block')->load('sidebar_block','identifier')->setData($blockData)->save();