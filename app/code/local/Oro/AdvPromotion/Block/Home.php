<?php
/**
 * Home Promotion
 *
 * @category   Oro
 * @package    Oro_AdvPromotion
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
 
class Oro_AdvPromotion_Block_Home extends Oro_Page_Block_Html_Topmenu
{
    /**
     * Get items collection
     *
     * @return Mage_Cms_Model_Resource_Block_Collection
     */
    public function getItemsCollection()
    {
        $collection = Mage::getModel('cms/block')->getCollection();
        $collection->addFieldToFilter('identifier', array('like' => 'homepromotion%'));
        $collection->addOrder('update_time');
        return $collection;
    }
}