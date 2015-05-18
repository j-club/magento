<?php
/**
 * Video Plugin for Magento
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Niveus
 * @package    Niveus_ProductVideo
 * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Niveus Solutions <support@niveussolutions.com>
 */



class Niveus_ProductVideo_Model_Resource_Eav_Mysql4_Setup extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
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

                    'youtube_video_one' => array(
                        'group'             => 'Videos',
                        'type'              => 'varchar',
                        'required'          => false,
                        'label'             => 'Video 1',
                        'input'             => 'text',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'sort_order'        => 10,
                    ),
                    'youtube_video_two' => array(
                        'group'             => 'Videos',
                        'type'              => 'varchar',
                        'required'          => false,
                        'label'             => 'Video 2',
                        'input'             => 'text',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'sort_order'        => 20,
                    ),
                    'youtube_video_three' => array(
                        'group'             => 'Videos',
                        'type'              => 'varchar',
                        'required'          => false,
                        'label'             => 'Video 3',
                        'input'             => 'text',
                        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'sort_order'        => 30,
                    ),
                )
            )
        );
    }
}
