<?php
/**
 * Home Slider
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_AdvSlider_Block_Home extends Mage_Core_Block_Template
{
    /**
     * Get items collection
     *
     * @return Mage_Cms_Model_Resource_Block_Collection
     */
    public function getItemsCollection()
    {
        $filter = $this->getData('block');
        $filter = !empty($filter) ? $filter : 'homeslider';

        $collection = Mage::getModel('cms/block')->getCollection();
        $collection->addFieldToFilter('identifier', array('like' => $filter.'%'));
        $collection->addFieldToFilter('is_active', 1);
        $collection->addOrder('update_time');

        return $collection;
    }
}