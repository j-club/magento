<?php

class Unirgy_DropshipMultiPrice_Block_ProductVendors extends Mage_Catalog_Block_Product_View_Description
{
    public function addToParentGroup($groupName)
    {
        if ($this->getParentBlock()) {
            $this->getParentBlock()->addToChildGroup($groupName, $this);
        }
        return $this;
    }
}