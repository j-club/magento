<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Sociable
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sociable/cron'), 'post_type', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'default'   => 'random',
        'length'    => 15,
        'comment'   => 'Post Type',
    ));

$installer->endSetup();
