<?php 
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_LayeredNavigation
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_LayeredNavigation_Block_Layer_View extends Itoris_LayeredNavigation_Block_Layer_View
{
    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }
        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter');
        }
        if ($this->getChild('brand_filter')) {
            $filters[] = $this->getChild('brand_filter');
        }
        return $filters;
    }
    /**
     * Handles sequence of the layered navigation initialization, filters impact to the collection
     * and calculation of filter items to show.
     */
    protected function _prepareLayout() {
        $thirdEngineEnabled = $this->getDataHelper()->isEnabledThirdEngineSearch();
        Mage::app()->setUseSessionInUrl(false);

        $stateBlock = $this->getLayout()->createBlock($this->_stateBlockName)
            ->setLayer($this->getLayer());

        /** @var $categoryBlock Itoris_LayeredNavigation_Block_Layer_MultiFilter_Category */
        $categoryBlock = $this->getLayout()->createBlock($this->_categoryBlockName)
            ->setLayer($this->getLayer())
            ->init();
        $brandBlock = $this->getLayout()->createBlock('oro_layerednavigation/layer_multiFilter_brand')
            ->setLayer($this->getLayer())
            ->init();
        $this->setChild('layer_state', $stateBlock);
        $this->setChild('category_filter', $thirdEngineEnabled ? $categoryBlock->addFacetCondition() : $categoryBlock);
        $this->setChild('brand_filter', $brandBlock);
        $blocks = array();
        if ($categoryBlock->getFilter()) {
            $blocks[] = $categoryBlock;
        }

        if ($brandBlock->getFilter()) {
           $blocks[] = $brandBlock;
        }

        $this->getLayer()->getProductCollection()->cloneSelect();
        $hasItems = count($this->getLayer()->getProductCollection()->getAllIds());

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            if ($attribute->getAttributeCode() == 'price') {
                $block = $this->getLayout()->createBlock($this->_priceFilterBlockName, $this->_priceBlockNameInLayout);
            } elseif ($attribute->getBackendType() == 'decimal') {
                $block = $this->getLayout()->createBlock($this->_decimalFilterBlockName);
            } else {
                $block = $this->getLayout()->createBlock($this->_attributeFilterBlockName);
            }

            $block->setLayer($this->getLayer())
                ->setAttributeModel($attribute)
                ->init();

            $this->setChild($attribute->getAttributeCode() . '_filter', $thirdEngineEnabled ? $block->addFacetCondition() : $block);

            if ($block->getFilter()) {
                $blocks[] = $block;
            }
        }
        $applyAfter = array();
        foreach ($blocks as $block) {
            $filter = $block->getFilter();

            if (!$this->isFilterCleared($filter)) {
                if ($filter->getRequestVar() == 'price') {
                    $applyAfter[] = $filter;
                    continue;
                }
                $filter->apply($this->getRequest(),$filter);
            }
        }

        foreach ($applyAfter as $filter) {
            $filter->apply($this->getRequest(),$filter);
        }

        if ($hasItems && !count($this->getLayer()->getProductCollection()->getAllIds())) {
            $this->getLayer()->getProductCollection()->useClonedSelect();
            $this->getDataHelper()->setNotUseFilter(true);
        }

        foreach ($blocks as $block) {
            $block->getFilter()->getItems();
        }

        foreach ($blocks as $block) {
            $block->getFilter()->updateStateItemsStatus();
        }

        $this->getLayer()->apply();
    }
}
