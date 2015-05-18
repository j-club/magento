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
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AW_Advancednewsletter_Block_Adminhtml_Customer_Edit_Tab_Newsletter extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('advancednewsletter/customer/tab/newsletter.phtml');
    }

    public function initForm() {
        $customer = Mage::registry('current_customer');
        $subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($customer->getEmail());
        Mage::register('subscriber', $subscriber);
        return $this;
    }

    protected function _prepareLayout() {
        $this->setChild('grid', $this->getLayout()->createBlock('advancednewsletter/adminhtml_customer_edit_tab_newsletter_grid', 'advancednewsletter.grid')
        );
        return parent::_prepareLayout();
    }

    public function getSegments() {
        return Mage::getModel('advancednewsletter/segment')->getCollection();
    }

    public function isChecked($segment) {
        return in_array($segment->getCode(), $this->getSubscriber()->getSegmentsCodes());
    }

    public function getSubscriber() {
        if (!Mage::registry('subscriber')) {
            $customer = Mage::registry('current_customer');
            $subscriber = Mage::getModel('advancednewsletter/subscriber')->loadByEmail($customer->getEmail());
            Mage::register('subscriber', $subscriber);
        }
        return Mage::registry('subscriber');
    }

}
