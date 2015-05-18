<?php
/**
 * Source model for tax filling info
 *
 * @category   MageCore
 * @package    Oro_Dropship
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_Dropship_Model_Taxfillinginfo
{
    const FORM_1099 = 1;
    const FORM_W9 = 2;
    /**
     * Get types as a source model result
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::FORM_1099 => Mage::helper('oro_dropship')->__('1099 Form'),
            self::FORM_W9   => Mage::helper('oro_dropship')->__('W-9 Form'),
        );
    }

}