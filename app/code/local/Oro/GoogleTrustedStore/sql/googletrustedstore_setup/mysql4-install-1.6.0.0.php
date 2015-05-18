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
$installer->getConnection()->addColumn(
        $installer->getTable('sales/order'),
        'cancellation_reason',
        'varchar(64) DEFAULT NULL COMMENT "Order Cancellation Reason"'
);
