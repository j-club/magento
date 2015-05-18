<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_ProductQuickView
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_ProductQuickView_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $productId = $this->getRequest()->getParam('product');
        $product = Mage::getModel('catalog/product')->load($productId);
        $html = '';
        if ($product->getId()) {
            $this->loadLayout('productquickview_index_index');
            $popupBlock = $this->getLayout()->getBlock('productquickview_popup');
            $html = $popupBlock->setProduct($product)->toHtml();
        }
        $this->getResponse()->setBody($html);
    }
}
