<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Model_Source_OrderCancellationReason
{
    private $_options;

    /**
     * Prepares array value=>label for available order cancellation reasons
     *
     * @return array
     * @throws RuntimeException If cannot read the reasons from the config
     */
    public function toOptionArray()
    {
        if (!is_array($this->_options)) {
            $this->_options = array();
            $reasons = Mage::getSingleton('googletrustedstore/config')->getCancellationReasons();
            foreach ($reasons as $code => $description) {
                $this->_options[] = array(
                    'value' => $code,
                    'label' => Mage::helper('googletrustedstore')->__((string)$description),
                );
            }
        }

        return $this->_options;
    }

    /**
     * Returns code of default cancelation reason
     *
     * @throws RuntimeException If cannot read the reason or reason is not listed
     * @return string Reason
     */
    public function getDefaultCode()
    {
        return Mage::getSingleton('googletrustedstore/config')->getDefaultCancellationReasonCode();
    }

    /**
     * Returns reason text description by specified reason code
     *
     * @param string $code
     * @return string
     */
    public function getDescriptionByCode($code)
    {
        return Mage::getSingleton('googletrustedstore/config')->getDescriptionOfCancellationReasonByCode($code);
    }

}
