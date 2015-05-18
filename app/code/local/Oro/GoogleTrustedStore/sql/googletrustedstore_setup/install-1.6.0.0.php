<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */
?>
<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/**
 * Prepare database for tables setup
 */
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'), 'cancellation_reason', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'    => '64',
            'nullable'  => true,
            'comment'   => 'Order Cancellation Reason',
        )
);
