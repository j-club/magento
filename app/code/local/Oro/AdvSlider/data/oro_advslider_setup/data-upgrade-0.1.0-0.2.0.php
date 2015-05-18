<?php
/**
 * Data upgrade
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

$content = <<< END
<p>
{{block type="oro_advslider/home" template="page/html/gallery-main.phtml"}}
{{block type="oro_category/list" template="page/html/category-section-main.phtml"}}
{{block type="oro_featuredproducts/list" template="catalog/product/product-featured.phtml"}}
</p>
END;

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();
$connection->update('cms_page', array('content' => $content), "identifier='home' AND is_active=1");
$installer->endSetup();
?>