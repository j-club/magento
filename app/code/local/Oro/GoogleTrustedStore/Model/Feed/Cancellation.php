<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

/**
 * Cancellation feed model
 */

class Oro_GoogleTrustedStore_Model_Feed_Cancellation extends Oro_GoogleTrustedStore_Model_Feed_Abstract
{
    /**
     * Initializes header, adds data to feed
     *
     * @param Mage_Sales_Model_Resource_Order_Collection      $orders     canceled orders collection
     */
    public function __construct($orders)
    {
        $this->_setHeader(
            array(
                'merchant order id',
                'reason'
            )
        );
        foreach ($orders as $order) {
            $this->_addCanceledOrder($order);
        }
    }

     /**
     * Adds canceled order to feed
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function _addCanceledOrder(Mage_Sales_Model_Order $order)
    {
        if ($order->getCancellationReason()) {
            $this->_addRow(
                array(
                    $order->getIncrementId(),
                    $order->getCancellationReason()
                )
            );
        }
    }
}
