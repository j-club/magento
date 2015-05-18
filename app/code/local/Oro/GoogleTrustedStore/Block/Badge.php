<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

class Oro_GoogleTrustedStore_Block_Badge extends Mage_Core_Block_Template
{
    /**
     * @var Mage_GoogleShopping_Model_Item|null|false
     */
    private $_gsItem = false;

    /**
     * Returns Account ID entered in admin panel to use in template
     *
     * @return number
     */
    public function getAccountId()
    {
        return Mage::getSingleton('googletrustedstore/config')->getAccountId(Mage::app()->getStore());
    }

    /**
     * Returns true is Mage_GoogleShopping is active and product was published
     * in Google shopping in current store
     *
     * @return bool
     */
    public function hasGoogleShoppingItem()
    {
        return $this->_getGoogleShoppingAdapter()->isActive() && $this->getGoogleShoppingItemId();
    }

    /**
     * Returns Google shopping item ID or null if product was published in current store
     *
     * @return string|null
     * @throws RuntimeException If Mage_GoogleShopping is not active
     */
    public function getGoogleShoppingItemId()
    {
        if (false === $this->_gsItem) {
            $this->_gsItem = ($product = Mage::registry('current_product'))
                ? $this->_getGoogleShoppingAdapter()->getItemId($product)
                : null;
        }

        return $this->_gsItem;
    }

    /**
     * Returns Google shopping account ID for current store
     *
     * @return string
     * @throws RuntimeException If Mage_GoogleShopping is not active
     */
    public function getGoogleShoppingAccountId()
    {
        return $this->_getGoogleShoppingAdapter()->getAccountId();
    }

    /**
     * Returns ISO code of target country's language in Google shopping for current store
     *
     * @return string
     * @throws RuntimeException If Mage_GoogleShopping is not active
     */
    public function getGoogleShoppingLanguage()
    {
        return $this->_getGoogleShoppingAdapter()->getTargetLanguage();
    }

    /**
     * Returns ISO code of target country in Google shopping for current store
     *
     * @return string
     * @throws RuntimeException If Mage_GoogleShopping is not active
     */
    public function getGoogleShoppingCountry()
    {
        return $this->_getGoogleShoppingAdapter()->getTargetCountry();
    }

    /**
     * Returns adapter model to Google shopping extension
     *
     * @return Oro_GoogleTrustedStore_Helper_GoogleShopping
     */
    protected function _getGoogleShoppingAdapter()
    {
        return Mage::getSingleton('googletrustedstore/googleShoppingAdapter');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::getSingleton('googletrustedstore/config')->isEnabled(Mage::app()->getStore())) {
            return parent::_toHtml();
        }
    }
}
