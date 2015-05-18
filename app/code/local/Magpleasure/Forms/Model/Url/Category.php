<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE-CE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE-CE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * Magpleasure does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Magpleasure does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Magpleasure
 * @package    Magpleasure_Forms
 * @version    1.0.5
 * @copyright  Copyright (c) 2011-2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE-CE.txt
 */

class Magpleasure_Forms_Model_Url_Category extends Magpleasure_Forms_Model_Url_Abstract
{
    /**
     * Category URL Suffix
     *
     * @return string
     */
    public function getCategoryUrlSuffix()
    {
        return Mage::getStoreConfig('catalog/seo/product_url_suffix');
    }

    public function getUrl($entityId = null)
    {
        /** @var $catalogCategory Mage_Catalog_Model_Category */
        $catalogCategory = Mage::getModel('catalog/category')->load($entityId);

        if ($catalogCategory->getId()){
            return $this->getBaseUrl().$catalogCategory->getUrlPath();
        }
        return false;
    }
}
