<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE-CE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE-CE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Magpleasure does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Magpleasure does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Magpleasure
 * @package    Magpleasure_Forms
 * @version    1.0.5
 * @copyright  Copyright (c) 2011-2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE-CE.txt
 */

class Magpleasure_Forms_Model_Mysql4_Structure_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('forms/structure');
    }
    
    /**
     * Add form filter
     *
     * @param Magpleasure_Forms_Model_Form|int $form
     * @return Magpleasure_Forms_Model_Mysql4_Structure_Collection
     */
    public function addFormFilter($form)
    {
        if ($form instanceof Magpleasure_Forms_Model_Form){
            $form = $form->getId();
        }
        $this->getSelect()->where('main_table.form_id = ?', $form);
        return $this;
    }

    /**
     * Add direction to collection
     *
     * @param string $direction ASC|DESC
     * @return Magpleasure_Forms_Model_Mysql4_Structure_Collection
     */
    public function addOrdering($direction = 'ASC')
    {
        $this->getSelect()->order("main_table.sort_order {$direction}");
        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach($this->getItems() as $item){
            if ($item->hasAdditionalData() && $item->getValues()){
                $item->setValues(Zend_Json::decode($item->getValues()));
            }
        }
        return $this;
    }

}


