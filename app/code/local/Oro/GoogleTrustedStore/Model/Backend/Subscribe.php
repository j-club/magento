<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Model_Backend_Subscribe extends Mage_Core_Model_Config_Data
{
    /**
     * Validates value before saving
     *
     */
    protected function _beforeSave()
    {
        if ($this->getValue()) {
            if (!Zend_Validate::is($this->getValue(), 'EmailAddress')) {
                throw new InvalidArgumentException(
                    Mage::helper('googletrustedstore')->__('Incorrect email for subscription.')
                );
            }
            Mage::helper('googletrustedstore')->subscribeForUpdate($this->getValue());
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('googletrustedstore')->__('Subscription request has been sent.')
            );
        }
    }
}
