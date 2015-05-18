<?php
/**
 * Earn Points Rate
 *
 * @category   Oro
 * @package    Oro_EarnPoints
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
 
class Oro_EarnPoints_Block_Rate extends Mage_Core_Block_Template
{
    public function _construct()
    {
        $this->setTemplate('catalog/product/earning-points.phtml');
        parent::_construct();
    }

    /**
     * Get points by product
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int
     */
    public function getPointsByProduct(Mage_Catalog_Model_Product $product)
    {
        $price = $product->getFinalPrice();
        return $this->_calculatePoints($price);
    }

    /**
     * Get points for totals
     *
     * @param array $totals
     * @return int
     */
    public function getPointsForTotals($totals)
    {
        $price = 0;
        foreach($totals as $total) {
            if($total->getCode()=='grand_total'){
                $price = $total->getValue();
                break;
            }
        }
        return $this->_calculatePoints($price);
    }

    /**
     * @param float $price
     * @return int
     */
    protected function _calculatePoints($price)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return Mage::getModel('enterprise_reward/reward')
            ->setCustomer($customer)
            ->getRateToPoints()
            ->calculateToPoints($price);
    }
}
