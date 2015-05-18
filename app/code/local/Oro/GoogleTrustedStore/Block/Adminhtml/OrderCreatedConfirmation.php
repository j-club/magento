<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

/**
 * Adds Google provided JavaScript to admin panel order success page
 *
 */
class Oro_GoogleTrustedStore_Block_Adminhtml_OrderCreatedConfirmation
    extends Oro_GoogleTrustedStore_Block_OrderConfirmation_Onepage
{
    /**
     * Placed order
     *
     * @var Mage_Sales_Model_Order
     */
    private $_order;

    /**
     * Returns order placed
     *
     * @return Mage_Sales_Model_Order
     * @throws RuntimeException If unable to load order
     */
    protected function _getOrder()
    {
        if (!$this->_order) {
            $orderId = Mage::getSingleton('adminhtml/session')->getLastAdminOrderId();
            $order = Mage::getModel('sales/order')->load($orderId);
            Mage::getSingleton('adminhtml/session')->unsLastAdminOrderId();
            if (!$order->getId()) {
                throw new RuntimeException('Unable to load last order.');
            }
            $this->_order = $order;
        }

        return $this->_order;
    }

    /**
     * Return true if session contains ID of recently created order
     *
     * @return bool
     */
    protected function _hasOrder()
    {
        return Mage::getSingleton('adminhtml/session')->hasLastAdminOrderId();
    }

    /**
     * Returns Account ID entered in admin panel to use in template
     *
     * @return number
     */
    public function getAccountId()
    {
        $store = $this->_getOrder()->getStoreId();
        return Mage::getSingleton('googletrustedstore/config')->getAccountId($store);
    }

    /**
     * Render block HTML if only extension is enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_hasOrder()) {
            $store = $this->_getOrder()->getStoreId();
            if (Mage::getSingleton('googletrustedstore/config')->isEnabled($store)) {
                return parent::_toHtml();
            }
        }
    }

    /**
     * Returns Google shopping account ID
     *
     * @return string
     */
    public function getGoogleShoppingAccountId()
    {
        return Mage::getSingleton('googletrustedstore/googleShoppingAdapter')->getAccountId();
    }
}
