<?php
/**
 * Upgrade
 *
 * @category   Oro
 * @package    Oro_Category
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$installer = $this;
$installer->getConnection()->insert($installer->getTable('cms/block'), array(
    'title' => 'categorylanding',
    'identifier' => 'categorylanding',
    'content' => '<p>{{block type="oro_category/landingswitch" name="category_landing_switch" template="catalog/category/landing/switch.phtml"}} {{block type="oro_advslider/home" template="advslider/home.phtml"}} {{block type="catalog/navigation" template="catalog/category/landing/categoryblocks.phtml"}}</p>',
    'is_active' => 1
));

$blockId = $installer->getConnection()->lastInsertId();

$installer->getConnection()->insert(
    $this->getTable('cms/block_store'),
    array(
        'block_id' => $blockId,
        'store_id' => 0
    )
);

$installer->endSetup();
?>