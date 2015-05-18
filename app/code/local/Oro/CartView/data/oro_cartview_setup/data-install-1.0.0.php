<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_CartView
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */
$blockContent = "
<div class=\"have-questions\">
<h3>Have Questions?</h3>
Contact our Customer Care: (800) 123-1234 or 
<a href=\"mailto:{{config path=\"trans_email/ident_general/email\"}}\">{{config path=\"trans_email/ident_general/email\"}}</a>
</div>
";
$blockData = array(
    'title'         => 'Cart Help',
    'identifier'    => 'cart_help',
    'content'       => $blockContent,
    'is_active'     => 1,
    'stores'        => 0
);
//add to template $this->getChildHtml('cart-help');
Mage::getModel('cms/block')->setData($blockData)->save();
