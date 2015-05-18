<?php
/**
 * Upgrade
 *
 * @category   Oro
 * @package    Oro_Category
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$connection->update('cms_block', array('content' => '<p>'), "identifier='categorylanding'");
$installer->endSetup();
?>