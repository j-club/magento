<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Sociable
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
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD
);
$productTypes = join(',', $productTypes);

$installer->addAttribute('catalog_product', 'visible_in_social', array(
    'group'             => 'General',
    'backend'           => 'catalog/product_attribute_backend_boolean',
    'label'             => 'Visible in Social Networks',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'type'              => 'int',
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => 0,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => $productTypes,
    'is_configurable'   => false,
    'used_in_product_listing' => 1,
    'note'              => 'Yes - product info will be published to social networks (see "BE Sociable" settings)',
));

$installer->endSetup();
