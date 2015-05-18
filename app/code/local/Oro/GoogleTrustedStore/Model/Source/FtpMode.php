<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Model_Source_FtpMode
{
    private $_options;
    /**
     * Prepares array value=>label for available ftp modes
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!is_array($this->_options)) {
            $this->_options = array(
                array('value' => 0, 'label' => Mage::helper('googletrustedstore')->__('Active')),
                array('value' => 1, 'label' => Mage::helper('googletrustedstore')->__('Passive')),
            );
        }
        return $this->_options;
    }
}
