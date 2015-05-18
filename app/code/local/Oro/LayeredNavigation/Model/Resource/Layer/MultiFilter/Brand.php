<?php 
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_LayeredNavigation
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_LayeredNavigation_Model_Resource_Layer_MultiFilter_Brand
    extends Itoris_LayeredNavigation_Model_Mysql4_Layer_MultiFilter_Attribute
{
    /**
     * Add condition to the collection.
     *
     * @param $filter
     * @param $value
     * @return Oro_LayeredNavigation_Model_Resource_Layer_MultiFilter_Brand
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $connection = $this->_getReadAdapter();
        $vendorProductTable = $this->_getVendorProductTable();
        $vendorProductTableAlias = $this->_getVendorProductTableAlias();
        $vendorTable = $this->_getVendorTable();
        $vendorTableAlias = $this->_getVendorTableAlias();
        $conditions = array(
            "{$vendorProductTableAlias}.product_id = e.entity_id",
            $connection->quoteInto("{$vendorProductTableAlias}.vendor_id in (?)", $value)
        );
        $collection->getSelect()->distinct();
        $collection->getSelect()->join(
            array($vendorProductTableAlias => $vendorProductTable),
            implode(' AND ', $conditions),
            array()
        );
        $collection->setFlag('add_group', true);
        //Mage::log($collection->getSelect()->__toString());
        return $this;
    }
    protected function _getVendorProductTable()
    {
        return $this->getTable('udropship/vendor_product');
    }
    protected function _getVendorProductTableAlias()
    {
        return 'uvp';
    }
    protected function _getVendorTable()
    {
        return $this->getTable('udropship/vendor');
    }
    protected function _getVendorTableAlias()
    {
        return 'uv';
    }
    /**
     * Get attribute items count in the collection.
     *
     * @param $filter
     * @return array
     */
    public function getCount($filter) {
        /** @var $select Varien_Db_Select */
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $connection = $this->_getReadAdapter();
        $vendorProductTable = $this->_getVendorProductTable();
        $vendorProductTableAlias = $this->_getVendorProductTableAlias();
        $vendorTable = $this->_getVendorTable();
        $vendorTableAlias = $this->_getVendorTableAlias();

        $from = $select->getPart(Zend_Db_Select::FROM);
        unset($from[$vendorProductTableAlias]);
        unset($from[$vendorTableAlias]);
        $select->setPart(Zend_Db_Select::FROM, $from);
        $columns = array(
            array ($vendorProductTableAlias, 'vendor_id', null)
        );
        $select->setPart(Zend_Db_Select::COLUMNS, $columns);
        $conditions = array(
            "{$vendorProductTableAlias}.product_id = e.entity_id",
        );

        $select->join(
                array($vendorProductTableAlias => $vendorProductTable),
                join(' AND ', $conditions),
                array('count' => new Zend_Db_Expr("COUNT(distinct {$vendorProductTableAlias}.product_id)"))
            )->group("{$vendorProductTableAlias}.vendor_id");
        $select->joinLeft(
            array($vendorTableAlias => $vendorTable),
            "{$vendorTableAlias}.vendor_id = {$vendorProductTableAlias}.vendor_id",
            array('vendor_name')
        );
        //Mage::log($select->__toString());
        return $connection->fetchAll($select);
    }

    public function getBrands()
    {
        $select = $select = $this->_getReadAdapter()->select();
        $select->reset()
            ->from($this->_getVendorTable(), array('vendor_id', 'vendor_name'));
        return $this->_getReadAdapter()
            ->fetchAssoc($select);
    }
}
