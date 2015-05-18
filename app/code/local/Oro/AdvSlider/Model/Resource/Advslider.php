<?php

/**
 * AdvSlider Resource
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_AdvSlider_Model_Resource_Advslider extends Mage_Core_Model_Resource_Db_Abstract
{

    protected $_categoryTable;

    protected function _construct()
    {
        $this->_init('oro_advslider/advslider', 'banner_id');
        $this->_categoryTable = $this->getTable('oro_advslider/category_banner');
    }


    public function bindBannersToCategory($categoryId, $banners)
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($banners as $bannerId => $banner) {
            if (isset($banner['position'])) {
                $adapter->insertOnDuplicate(
                    $this->_categoryTable,
                    array('banner_id' => $bannerId, 'category_id' => $categoryId, 'position' => $banner['position']),
                    array('category_id')
                );
            }
        }

        if (empty($banners)) {
            $banners = array(0);
        }

        $adapter->delete($this->_categoryTable,
            array('category_id = ?' => $categoryId, 'banner_id NOT IN (?)' => array_keys($banners))
        );
        return $this;
    }

    /**
     * Get banners that associated to category
     *
     * @param int $categoryId
     * @return array
     */
    public function getRelatedBannersByCategoryId($categoryId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_categoryTable, array('banner_id','position'))
            ->where('category_id = ?', $categoryId)
            ->order('position');
        return $adapter->fetchAll($select);
    }
}
