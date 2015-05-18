<?php
/**
 * Upgrade
 *
 * @category   Oro
 * @package    Oro_FeaturedProducts
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, 'is_featured',
    array(
        'type'                     => 'int',
        'label'                    => 'Featured product',
        'input'                    => 'select',
        'source'                   => 'eav/entity_attribute_source_boolean',
        'required'                 => false,
        'sort_order'               => 15,
        'global'                   => true,
        'group'                    => 'General'
    )
);

$installer->endSetup();