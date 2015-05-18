<?php
/**
 * Helper
 *
 * @category   Oro
 * @package    Oro_SimpleMsrp
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */
class Oro_SimpleMsrp_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get product regular price for rendering on frontend
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getRegularPrice($product)
    {
        $price = $product->getPrice();
        if ($msrp = $product->getSimpleMsrp()) {
            if ($msrp > $price) {
                $price = $msrp;
            }
        }
        return $price;
    }
}
