<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Model_Backend_FeedFilename extends Mage_Core_Model_Config_Data
{
    /**
     * Validates value before saving.  The values must not contain non-word characters, except for a period (.)
     *
     */
    protected function _beforeSave()
    {
        if (preg_match('/[^a-z0-9_.]+/i', $this->getValue())) {
            throw new Exception(Mage::helper('googletrustedstore')->__(
                'Please use only letters (a-z or A-Z), numbers (0-9), underscore (_) or dot (.) in feed filename field. No spaces or other characters are allowed.'
            ));
        }
    }
}
