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


class AW_Advancednewsletter_Model_Segment extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('advancednewsletter/segment');
    }

    /**
     * Returns if segment must be selected at the frontend
     * @param int $categoryId
     * @return bool
     */
    public function isSelected($categoryId) {
        return ($this->getDefaultCategory() == $categoryId || $this->getDefaultCategory() == AW_Advancednewsletter_Helper_Data::ANY_CATEGORY_VALUE);
    }

    /**
     * Mass segments creation. All segments params are set to default, segments 
     * codes = segments titles = items of segmentsArray
     * @param array $segmentsArray 
     */
    public function massCreation($segmentsArray) {
        $segmentsCodes = array_keys($this->getSegmentOptionArray());
        foreach ($segmentsArray as $segment) {
            if (!in_array($segment, $segmentsCodes)) {
                $this->createNewSegment($segment);
            }
        }
    }

    /**
     * Deletion of segment
     */
    public function delete() {
        AW_Advancednewsletter_Model_Sync_Mailchimpclient::$disableAutosync = true;
        $segmentCode = $this->getCode();
        parent::delete();
        Mage::getModel('advancednewsletter/subscriber')->getCollection()->removeSegment($segmentCode);
        Mage::dispatchEvent('an_segment_delete', array('segment_code' => $segmentCode));
    }

    /**
     * Creation of segment code with default values. Segment code = Segment Title.
     * @param string $segmentCode 
     */
    public function createNewSegment($segmentCode) {
        $newSegment = new self;
        $data = array(
            'code' => $segmentCode,
            'title' => $segmentCode,
            'default_store' => 0,
            'default_category' => AW_Advancednewsletter_Helper_Data::ANY_CATEGORY_VALUE,
            'display_in_store' => 0,
            'display_in_category' => AW_Advancednewsletter_Helper_Data::ANY_CATEGORY_VALUE,
            'frontend_visibility' => 0,
            'checkout_visibility' => 0
        );
        try {
            $newSegment
                    ->addData($data)
                    ->save();
        } catch (Exception $ex) {
            
        }
    }

    /**
     * Getting Segments Array as array('label' => .., 'value' => ..)
     * @return array
     */
    public function getSegmentArray() {
        $segmentsArray = array();
        foreach ($this->getCollection() as $item) {
            $segmentsArray[] = array('label' => $item->getTitle(), 'value' => $item->getCode());
        }
        return $segmentsArray;
    }

    /**
     * Getting Segments Array as array('label' => value)
     * @return array
     */
    public function getSegmentOptionArray() {
        $segmentsArray = array();
        foreach ($this->getCollection() as $item) {
            $segmentsArray[$item->getCode()] = $item->getTitle();
        }
        return $segmentsArray;
    }

}