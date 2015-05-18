<?php
/**
 * Data Upgrade
 *
 * @category   Oro
 * @package    Oro_TopSellers
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$content = <<< END
<p>{{block type="oro_category/landingswitch" name="category_landing_switch" template="catalog/category/landing/switch.phtml"}} {{block type="oro_advslider/home" template="advslider/home.phtml"}} {{block type="catalog/navigation" template="catalog/category/landing/categoryblocks.phtml"}}
{{block type="oro_topsellers/topsellers" template="catalog/product/product-topsellers.phtml"}}
</p>
END;

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$connection->update('cms_block', array('content' => $content), "identifier='categorylanding'");
$installer->endSetup();
?>