<?php
/**
 * @category   Oro
 * @package    Oro_Asset
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

/**
 * Oro Asset Style System Config Source Model
 */
class Oro_Asset_Model_System_Config_Source_Style
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Oro_Asset_Helper_Data::STYLE_DEFAULT,
                'label' => Mage::helper('oro_asset')->__('Default'),
            ),
            array(
                'value' => Oro_Asset_Helper_Data::STYLE_MERGE_MIN,
                'label' => Mage::helper('oro_asset')->__('Merged and Compressed'),
            ),
            array(
                'value' => Oro_Asset_Helper_Data::STYLE_MERGE,
                'label' => Mage::helper('oro_asset')->__('Only Merged'),
            ),
        );
    }
}
