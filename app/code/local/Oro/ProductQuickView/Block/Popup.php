<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_ProductQuickView
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_ProductQuickView_Block_Popup extends Mage_Catalog_Block_Product_View
{
    protected function _prepareLayout()
    {
        return $this;
    }

    protected function _toHtml()
    {
        if ($this->getProduct()) {
            foreach ($this->getChild() as $childBlock) {
                $childBlock->setProduct($this->getProduct());
            }
            return parent::_toHtml();
        }
        return '';
    }

    public function getProduct()
    {
        return $this->getData('product');
    }

    /**
     * Retrieve block cache tags
     *
     * @return array
     */
    public function getCacheTags()
    {
        return array_merge(parent::getCacheTags(), array('POPUP_BLOCK'));
    }
}
