<?php
/**
 * Helper
 *
 * @category   Oro
 * @package    Oro_Membership
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Membership_Helper_Data extends Magestore_Membership_Helper_Data
{
    public function getMembershipPrice($product, $package)
    {
        $result_price = 0;
        $base_price = $product->getPrice();

        $packageproduct_collection = Mage::getModel('membership/packageproduct')->getCollection()
            ->addFieldToFilter('package_id',$package->getId())
            ->addFieldToFilter('product_id',$product->getId());

        if (count($packageproduct_collection)) {
            $productPrice = $package->getPackageProductPrice();
            $pos = strpos($productPrice, '%');

            if ($pos === false) {
                $result_price = $productPrice;
            } else {
                $result_price = $base_price*floatval($productPrice)/100;
            }
        }


        $groupIdsFromProduct = $this->getGroupIdsFromProduct($product->getId());
        $groupIdsFromPackage = $this->getGroupIdsFromPackage($package->getId());

        $groupIds = array_intersect($groupIdsFromPackage, $groupIdsFromProduct);

        if (count($groupIds)) {
            //get a group with max priority
            $priority = 0;

            foreach ($groupIds as $groupId) {
                $group_temp = Mage::getModel('membership/group')->load($groupId);
                if ($group_temp->getPriority() >= $priority ) {
                    $priority = $group_temp->getPriority();
                    $group = $group_temp;
                }
            }

            $productPrice = $group->getGroupProductPrice();
            $pos = strpos($productPrice, '%');

            if ($pos === false) {
                $result_price = $productPrice;
            } else {
                $result_price = $base_price*floatval($productPrice)/100;
            }
        }
        return $result_price;
    }

    public function createMembershipProduct($name, $description, $price, $status, $productId, $storeId)
    {
        if ($productId) {
            $product  =  Mage::getModel('catalog/product')->load($productId);

        } else {
            $product = Mage::getModel('catalog/product');
            $attributeSetName = 'Membership';
            $entityType = Mage::getSingleton('eav/entity_type')->loadByCode('catalog_product');
            $entityTypeId = $entityType->getId();
            $setId = Mage::getResourceModel('catalog/setup', 'core_setup')->getAttributeSetId($entityTypeId, $attributeSetName);
            $product->setAttributeSetId($setId);
            $product->setTypeId('virtual');
            $product->setSku('ms_' . $name);
            $product->setWebsiteIDs(array(1));

            $product->setTaxClassId(0);
            $product->setStockData(array(
                'is_in_stock' => 1,
                'qty' => 99999
            ));
            $product->setCreatedAt(now());
        }
        $product->setStoreId($storeId);
        $product->setPrice($price);
        $product->setName($name);
        $product->setDescription($description);
        $product->setShortDescription($description);
        $product->setStatus((int) $status);

        $product->save();
        return $product->getId();
    }
}