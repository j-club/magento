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


/**
 * DEPRICATED. Was used in AW_Advancednewsletter < 2.0. Now used for sync with 2.0 
 * version and compatibility with other extensions
 */
class AW_Advancednewsletter_Model_Subscriptions extends Mage_Core_Model_Abstract {

    protected $subscriber;

    /**
     * DEPRICATED
     */
    public function _construct() {
        parent::_construct();
        $this->_init('advancednewsletter/subscriptions');
    }

    /**
     * DEPRICATED
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param int $salutation
     * @param string $phone
     * @param array $segments_array
     */
    public function subscribe($email, $firstname, $lastname, $salutation, $phone, $segments_array) {
        if (in_array(AW_Advancednewsletter_Helper_Data::AN_SEGMENTS_ALL, $segments_array)) {
            $segments_array = Mage::getModel('advancednewsletter/segment')->toOptionArray();
        }
        $params = array('first_name' => $firstname, 'last_name' => $lastname, 'phone' => $phone, 'salutation' => $salutation);
        return Mage::getModel('advancednewsleter/subscriber')->subscribe($email, $segments_array, $params);
    }

    /**
     * DEPRICATED
     * @param string $email
     */
    public function getSubscriber($email) {
        $this->subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($email);
        return $this;
    }

    /**
     * DEPRICATED
     * @return string 
     */
    public function getSegmentsCodes() {
        if ($this->subscriber)
            return implode(',', $this->subscriber->getSegmentsCodes());
        return '';
    }

}