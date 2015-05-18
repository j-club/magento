<?php
/**
 * Package
 *
 * @category   Oro
 * @package    Oro_Membership
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Membership_Model_Package extends Magestore_Membership_Model_Package
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('membership/package');
    }

    /*
		get list product of a package.
		The order is sorted by the total of money saved.
	*/
    public function getSortedProductByMembershipPrice()
    {
        $productIds = $this->getAllProductIds();
        $listToSort = array();
        if(count($productIds)){
            foreach($productIds as $productId)
            {
                $product = Mage::getModel("catalog/product")->load($productId);
                $listToSort[$productId] = Mage::helper('membership')->getMembershipPrice($product, $this) - $product->getPrice();
            }
        }
        asort($listToSort, SORT_NUMERIC);

        $productIds = array_keys($listToSort);

        $products = array();
        if(count($productIds)){
            foreach($productIds as $productId)
            {
                $products[] = Mage::getModel("catalog/product")->load($productId);
            }
        }
        return $products;
    }
}