<?php

class Be_Sociable_Model_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
	public function getDefaultEntities()
    {
    	return array(
            'catalog_product' => array(
                'entity_model'      => 'catalog/product',
                'attribute_model'   => 'catalog/resource_eav_attribute',
                'table'             => 'catalog/product',
                'additional_attribute_table' => 'catalog/eav_attribute',
                'entity_attribute_collection' => 'catalog/product_attribute_collection',
                'attributes'        => array(
	                   
                		'sociable_image'=> array(
                				'type'              => 'varchar',
                				'label'             => 'Image for Sociable',
                				'input'             => 'media_image',
                				'frontend'          => 'catalog/product_attribute_frontend_image',
                				'required'          => false,
                				'sort_order'        => 10,
                				'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                				'group'              => 'Images',
                		),
                		'facebook_text' => array(
                				'group'             => 'BE SOCIABLE',
                				'type'              => 'varchar',
                				'required'          => false,
                				'label'             => 'Facebook Page Message Text',
                				'input'             => 'select',
                				'source'            => 'sociable/attribute_source_text',
                				'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                				'sort_order'        => 10,
                		),
                		'twitter_text' => array(
                				'group'             => 'BE SOCIABLE',
                				'type'              => 'varchar',
                				'required'          => false,
                				'label'             => 'Twitter Message Text',
                				'input'             => 'select',
                				'source'            => 'sociable/attribute_source_text',
                				'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                				'sort_order'        => 20,
                		),
                		'pinterest_text' => array(
                				'group'             => 'BE SOCIABLE',
                				'type'              => 'varchar',
                				'required'          => false,
                				'label'             => 'Pinterest Message Text',
                				'input'             => 'select',
                				'source'            => 'sociable/attribute_source_text',
                				'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                				'sort_order'        => 30,
                		),
                		'gplus_text' => array(
                				'group'             => 'BE SOCIABLE',
                				'type'              => 'varchar',
                				'required'          => false,
                				'label'             => 'Google+ Page Message Text',
                				'input'             => 'select',
                				'source'            => 'sociable/attribute_source_text',
                				'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                				'sort_order'        => 40,
                		),
                    
                )
            )
        );
    }
}