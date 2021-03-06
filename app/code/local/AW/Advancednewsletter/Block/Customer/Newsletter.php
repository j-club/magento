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


class AW_Advancednewsletter_Block_Customer_Newsletter extends Mage_Customer_Block_Newsletter {

    protected $subscriber;

    public function __construct() {
        Mage_Customer_Block_Account_Dashboard::__construct();
        $this->setTemplate('advancednewsletter/customer/subscriptions.phtml');
    }

    public function getSubscriber() {
        if (!$this->subscriber) {
            $customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());
            $this->subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($customer->getEmail());
        }
        return $this->subscriber;
    }

    public function getUpdateLink() {
        $url = $this->getUrl('advancednewsletter/index/updateStatus');
        if (Mage::app()->getRequest()->getScheme() == 'https') {
            if (preg_match("#http://#is", $url)) {
                return preg_replace("#http://#is", "https://", $url);
            }
        }
        return $url;
    }

    public function getSegments() {
        return Mage::getModel('advancednewsletter/segment')
                        ->getCollection()
                        ->addStoreFilter(Mage::app()->getStore()->getId())
                        ->addFieldToFilter('frontend_visibility', array('eq' => array(1)));
    }

    public function isChecked($segment) {
        return in_array($segment->getCode(), $this->getSubscriber()->getSegmentsCodes());
    }

}