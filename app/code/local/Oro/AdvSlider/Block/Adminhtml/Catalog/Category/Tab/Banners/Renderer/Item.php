<?php

class Oro_AdvSlider_Block_Adminhtml_Catalog_Category_Tab_Banners_Renderer_Item
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $item
     * @return string
     */
    public function render(Varien_Object $item)
    {
        $bannerId = $item->getData('banner_id');
        $grid = $this->getColumn()->getGrid();
        $banners = $grid->getRelatedBannersByCategory();
        $item->setPosition(isset($banners[$bannerId]['position']) ? $banners[$bannerId]['position'] : '');
        return parent::$this->_getInputValueElement($item);
    }
}
