<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento enterprise edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento enterprise edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancednewsletter
 * @version    2.3.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * DEPRICATED. Was used in AW_Advancednewsletter < 2.0. Now used for sync with 2.0 
 * version and compatibility with other extensions
 */
class AW_Advancednewsletter_Model_Cache extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('advancednewsletter/cache');
    }

    public function loadByCode($code) {
        return $this->load($code, 'code');
    }

    public function saveCache($value, $code) {
        return $this
                        ->loadByCode($code)
                        ->setData('code', $code)
                        ->setData('value', $value)
                        ->save();
    }

    public function loadCache($code) {
        $this->setData(array());
        return $this->load($code, 'code')->getValue();
    }

    public function removeCache($code) {
        $this
                ->loadByCode($code)
                ->delete();
    }

}