<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_SimpleMsrp
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Add new product attribute 
 */
$productTypes = array(
    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
    Mage_Catalog_Model_Product_Type::TYPE_GROUPED
);
$productTypes = join(',', $productTypes);

$installer->addAttribute('catalog_product', 'simple_msrp', array(
    'group'             => 'Prices',
    'backend'           => 'catalog/product_attribute_backend_price',
    'label'             => 'Simple MSRP',
    'input'             => 'price',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'type'              => 'decimal',
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => $productTypes,
    'is_configurable'   => false,
    'used_in_product_listing' => 1,
    'note'              => 'Should be higher than regular price, otherwise will be ignored',
));

$installer->endSetup();
