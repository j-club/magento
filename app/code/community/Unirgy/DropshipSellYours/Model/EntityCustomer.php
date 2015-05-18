<?php

class Unirgy_DropshipSellYours_Model_EntityCustomer extends Mage_Customer_Model_Entity_Customer
{
    protected function _beforeSave(Varien_Object $customer)
    {
        parent::_beforeSave($customer);

        if ($customer->getUsername()) {
            $select = $this->_getWriteAdapter()->select()
                ->from($this->getEntityTable(), array($this->getEntityIdField()))
                ->where('username=?', $customer->getUsername());
            if ($customer->getId()) {
                $select->where('entity_id !=?', $customer->getId());
            }
            if ($this->_getWriteAdapter()->fetchOne($select)) {
                Mage::throwException(Mage::helper('udsell')->__('This customer username already exists.'));
            }
        }

        return $this;
    }
}