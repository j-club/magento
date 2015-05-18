<?php
/**
 * Banners Grid
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_AdvSlider_Block_Adminhtml_Catalog_Category_Tab_Banners_Grid
    extends Enterprise_Banner_Block_Adminhtml_Banner_Grid
{
    /**
     * @var array
     */
    protected $_categoryBanners = array();

    /**
     * Initialize grid, set promo catalog rule grid ID
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('related_category_banners_grid');
        $this->setVarNameFilter('related_category_banners_filter');
        if ($this->_getCategory()->getId()) {
            $this->setDefaultFilter(array('in_banners'=>1));
        }
    }

    /**
     * Create grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_banners', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_banners',
            'values'    => $this->_getSelectedBanners(),
            'align'     => 'center',
            'index'     => 'banner_id'
        ));
        parent::_prepareColumns();
        $this->removeColumn('banner_types');
        $this->addColumn('position', array(
            'header'            => Mage::helper('catalog')->__('Position'),
            'name'              => 'position',
            'index'              => 'position',
            'width'             => 60,
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'editable'          => true,
            'filter'    => false,
            'sortable'  => false,
            'renderer' => 'oro_advslider/adminhtml_catalog_category_tab_banners_renderer_item',
            ''
        ));
        return $this;
    }

    /* Set custom filter for in banner flag
     *
     * @param string $column
     * @return Oro_AdvSlider_Block_Adminhtml_Catalog_Category_Tab_Banners_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_banners') {
            $bannerIds = $this->_getSelectedBanners();
            if (empty($bannerIds)) {
                $bannerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.banner_id', array('in'=>$bannerIds));
            } else {
                if ($bannerIds) {
                    $this->getCollection()->addFieldToFilter('main_table.banner_id', array('nin'=>$bannerIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Disable massaction functioanality
     *
     * @return Oro_AdvSlider_Block_Adminhtml_Catalog_Category_Tab_Banners_Grid
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Ajax grid URL getter
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/advslider/categoryBannersGrid', array('_current'=>true));
    }

    /**
     * Define row click callback
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Get selected banners ids for in banner flag
     *
     * @return array
     */
    protected function _getSelectedBanners()
    {
        /*$banners = $this->getSelectedCategoryBanners();
        if (is_null($banners)) {
            $banners = $this->getRelatedBannersByCategory();
        }*/
        return $this->getRelatedBannerIds();
    }

    /**
     * Get related banners by current category
     *
     * @return array
     */
    public function getRelatedBannersByCategory()
    {
        $categoryId = Mage::registry('current_category')->getId();
        if (isset($this->_categoryBanners[$categoryId])) {
            return $this->_categoryBanners[$categoryId];
        }
        $banners = Mage::getModel('oro_advslider/advslider')->getRelatedBannersByCategoryId($categoryId);
        $result = array();
        foreach ($banners as $banner) {
            $result[$banner['banner_id']] = array('position' => $banner['position']);
        }
        $this->_categoryBanners[$categoryId] = $result;
        return $result;
    }

    public function getRelatedBannerIds()
    {
        $ids = array_keys($this->getRelatedBannersByCategory());
        return $ids;
    }

    /**
     * Get current category model
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _getCategory()
    {
        return Mage::registry('current_category');
    }
}

