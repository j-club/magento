<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento enterprise edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento enterprise edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancednewsletter
 * @version    2.3.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancednewsletter_Block_Subscribe extends Mage_Core_Block_Template {
    const STYLES_PATH = 'advancednewsletter/formconfiguration/segmentsstyle';

    const DISPLAY_NAMES = 'advancednewsletter/formconfiguration/displayname';
    const DISPLAY_PHONE = 'advancednewsletter/formconfiguration/displayphone';
    const DISPLAY_SALUTATION = 'advancednewsletter/formconfiguration/displaysalutation';
    const SALUTATION_FIRST = 'advancednewsletter/formconfiguration/salutation1';
    const SALUTATION_SECOND = 'advancednewsletter/formconfiguration/salutation2';

    protected $subscriber;
    protected static $uniqueId = 0;
    
    protected static $segments;

    protected function _tohtml() {
        
        if(!$this->getSegments()->count()) {
            return;
        }
         
        if ($this->getInsertedManually()) {
            $layout = Mage::getModel('core/layout');
            $checkboxes = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/options/checkboxes.phtml');
            $radio = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/options/radio.phtml');
            $none = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/options/none.phtml');
            $multiselect = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/options/multiselect.phtml');
            $select = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/options/select.phtml');
            $data = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/subscriber/data.phtml');
            $subscribeBlock = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/subscribe.phtml');
            $subscribeBlock
                    ->setChild('advancednewsletter.options.checkboxes', $checkboxes)
                    ->setChild('advancednewsletter.options.radio', $radio)
                    ->setChild('advancednewsletter.options.none', $none)
                    ->setChild('advancednewsletter.options.multiselect', $multiselect)
                    ->setChild('advancednewsletter.options.select', $select)
                    ->setChild('advancednewsletter.subscriber.data', $data);
            $subscribeLink = $layout->createBlock('advancednewsletter/subscribe')->setTemplate('advancednewsletter/subscribe_link.phtml');

            $main = $layout->createBlock('advancednewsletter/subscribe', 'advancednewsletter.subscribe.block')
                    ->setTemplate('advancednewsletter/subscribe_block.phtml')
                    ->setData('in_block_only', $this->getInBlockOnly() ? true : false);
            $main
                    ->setChild('advancednewsletter.subscribe', $subscribeBlock)
                    ->setChild('advancednewsletter.subscribe.link', $subscribeLink);
            return $main->renderView();
        }
        return parent::_toHtml();
    }

    public function getBlockUniqueId() {
        return self::$uniqueId;
    }

    public function setBlockUniqueId($id) {
        self::$uniqueId = $id;
    }

    public function getSubscriber() {
        if (!$this->subscriber) {
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            $this->subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($customer->getEmail());
        }
        return $this->subscriber;
    }

    public function getSegments()
    {
        if (!self::$segments) {

            self::$segments = Mage::getModel('advancednewsletter/segment')
                    ->getCollection()
                    ->addStoreFilter($this->getStoreId())
                    ->addCategoryFilter($this->getCategoryId())
                    ->addFieldToFilter('frontend_visibility', array('eq' => array(1)))
                    ->addOrder('display_order', Varien_Data_Collection::SORT_ORDER_ASC);
        }

        return self::$segments;
    }

    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function getCategoryId() {
        if (Mage::app()->getRequest()->getParam('an_category_id'))
            $category = Mage::app()->getRequest()->getParam('an_category_id');
        else
            $category = Mage::registry('current_category') ? Mage::registry('current_category')->getId() : 0;
        return $category;
    }

    public function displaySalutation() {
        return Mage::getStoreConfig(self::DISPLAY_SALUTATION);
    }

    public function displayNames() {
        return Mage::getStoreConfig(self::DISPLAY_NAMES) && !Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function displayPhone() {
        return Mage::getStoreConfig(self::DISPLAY_PHONE);
    }

    public function displayEmail() {
        return!Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getAjaxUrl() {
        return $this->getUrl("advancednewsletter/index/subscribeajax", array('an_category_id' => $this->getCategoryId(), '_secure' => Mage::app()->getStore(true)->isCurrentlySecure()));
    }

    public function checkDisplay() {
        return $this->getSegments()->getSize() > 0 ||
                Mage::getStoreConfig(self::STYLES_PATH) == AW_Advancednewsletter_Model_Source_Segmentsstyle::STYLE_NONE;
    }

    public function displayLabel() {
        if ($this->getDisplayLabel() == 'false')
            return false;
        return true;
    }

}