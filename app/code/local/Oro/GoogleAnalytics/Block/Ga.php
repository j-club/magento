<?php
/**
 * Google Analytics Block
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_GoogleAnalytics_Block_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Render information about specified orders and their items
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/ecommerce
     * @return string
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();
        $result[] = "ga('require', 'ecommerce', 'ecommerce.js');";

        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("ga('ecommerce:addTransaction', {'id': '%s', 'affiliation': '%s', 'revenue': '%s', 'shipping': '%s', 'tax': '%s'});",
                                $order->getIncrementId(),
                                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                                $order->getBaseGrandTotal(),
                                $order->getBaseShippingAmount(),
                                $order->getBaseTaxAmount()
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("ga('ecommerce:addItem', {'id': '%s', 'name': '%s', 'sku': '%s', 'category': '%s', 'price': '%s', 'quantity': '%s'});",
                                    $order->getIncrementId(),
                                    $this->jsQuoteEscape($item->getName()),
                                    $this->jsQuoteEscape($item->getSku()),
                                    null, // there is no "category" defined for the order item
                                    $item->getBasePrice(),
                                    $item->getQtyOrdered()
                );
            }
            $result[] = "ga('ecommerce:send');";
        }
        return implode("\n", $result);
    }

}