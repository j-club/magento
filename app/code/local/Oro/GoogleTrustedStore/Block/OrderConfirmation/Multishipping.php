<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Block_OrderConfirmation_Multishipping extends Mage_Core_Block_Template
{
    /**
     * Returns all order placed during multishipping ordering process
     *
     * @return array
     */
    protected function _getAllOrders()
    {
        $allOrders = array();
        $ids = Mage::getSingleton('checkout/session')->getMultishippingOrderIds(false);
        if ($ids && is_array($ids)) {
            $allOrders = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $ids));
        }

        return $allOrders;
    }

    /**
     * Render block HTML if only extension is enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        if (Mage::getSingleton('googletrustedstore/config')->isEnabled(Mage::app()->getStore())) {
            foreach ($this->_getAllOrders() as $order) {
                $html .= $this->getChild('googletrustedstore.item.success')->setOrder($order)->toHtml();
                // leave only first order on the success page. May be will be changed in future, when Google starts
                // support multishipping orders.
                break;
            }
        }

        return $html;
    }

}
