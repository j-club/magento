<?php
/**
 * Data Upgrade
 *
 * @category   Oro
 * @package    Oro_TopSellers
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$blockContent = '
<div class="footer-wrapper">
    <div class="col">
        <h3>' . Mage::helper('core')->__('Customer Care') . '</h3>
        <ul>
            <li><a href="' . Mage::getModel('core/url')->getUrl('contacts') . '">' . Mage::helper('core')->__('Contact Us') . '</a></li>
            <li><a href="' . Mage::getModel('core/url')->getUrl('shipping') . '">' . Mage::helper('core')->__('Shipping Info') . '</a></li>
            <li><a href="' . Mage::getModel('core/url')->getUrl('returns') . '">' . Mage::helper('core')->__('Returns') . '</a></li>
            <li><a href="' . Mage::getModel('core/url')->getUrl('privacy-policy') . '">' . Mage::helper('core')->__('Privacy Policy') . '</a></li>
        </ul>
    </div>
    <div class="col">
        <h3>' . Mage::helper('core')->__('Specials') . '</h3>
        <ul>
            <li><a href="#">' . Mage::helper('core')->__('Clearance') . '</a></li>
            <li><a href="#">' . Mage::helper('core')->__('Arrivals') . '</a></li>
        </ul>
    </div>
    <div class="col">
        <h3>' . Mage::helper('core')->__('News and Articles') . '</h3>
        <ul>
            <li><a href="#">' . Mage::helper('core')->__('Articles') . '</a></li>
            <li><a href="#">' . Mage::helper('core')->__('News') . '</a></li>
            <li><a href="#">' . Mage::helper('core')->__('Buying Guides') . '</a></li>
        </ul>
    </div>
    <div class="col">
        <h3>' . Mage::helper('core')->__('Connect With Us') . '</h3>
        <ul class="social-list">
            <li class="pinterest"><a href="http://www.pinterest.com/jewelersclub/">' . Mage::helper('core')->__('Pinterest') . '</a></li>
            <li class="twitter"><a href="https://twitter.com/jewelersclub">' . Mage::helper('core')->__('Twitter') . '</a></li>
            <li class="facebook"><a href="https://www.facebook.com/JewelersClub">' . Mage::helper('core')->__('Facebook') . '</a></li>
        </ul>
    </div>
    <div class="col norton-block"><a href="#"><img src="' . Mage::getDesign()->getSkinUrl('images/img-norton.png') . '" alt="norton"/></a></div>
</div>
';

$blockData = array(
    'title'         => 'Footer block',
    'identifier'    => 'footer-block',
    'content'       => $blockContent,
    'is_active'     => 1,
    'stores'        => 0
);
Mage::getModel('cms/block')->load('footer-block','identifier')->setData($blockData)->save();