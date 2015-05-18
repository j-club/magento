<?php
/**
* Source model for business type
*
* @category   MageCore
* @package    Oro_Dropship
* @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
*/
class Oro_Dropship_Model_Businesstype
{
    const OTHER = 1;
    const INDIVIDUAL = 2;
    const CORPORATION = 3;
    const PARTNERSHIP = 4;
    const LIMITED = 5;
    const LIABILITY = 6;

    /**
     * Get types as a source model result
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::INDIVIDUAL    => Mage::helper('oro_dropship')->__('Individual/Sole Proprietor'),
            self::CORPORATION   => Mage::helper('oro_dropship')->__('Corporation'),
            self::PARTNERSHIP   => Mage::helper('oro_dropship')->__('Partnership'),
            self::LIMITED       => Mage::helper('oro_dropship')->__('Limited'),
            self::LIABILITY     => Mage::helper('oro_dropship')->__('Liability Company'),
            self::OTHER         => Mage::helper('oro_dropship')->__('Other'),
        );
    }

}