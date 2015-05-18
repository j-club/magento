<?php

/**
 * PDF creation model rewrite
 *
 * @category   Oro
 * @package    Oro_Sales
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */

class Oro_Sales_Model_Pdf_Shipment extends Unirgy_Dropship_Model_Pdf_Shipment
{
    /**
     * Place vendor logo into page
     * @param $page
     */
    public function drawLogo($page)
    {
        $vendorId = Mage::getSingleton('udropship/session')->getId();
        $vendor = Mage::helper('udropship')->getVendor($vendorId);
        $img=Mage::getBaseDir('media').DS.$vendor->getLogo();

        if(is_file($img)){
            $image = Zend_Pdf_Image::imageWithPath($img);
            $page->drawImage($image, 25, 805, 125, 830);
        }
    }

    /**
     * CreatePDF document for shipments
     *
     * @param array $shipments
     * @return Zend_Pdf
     */
    public function getPdf($shipments = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        if (method_exists($this, '_setPdf')) $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            $this->_currentShipment = $shipment;

            $order = $shipment->getOrder();

            //$this->drawLogo($page);

            /* Add image */
            $this->insertLogo($page, $shipment->getStore());

            /* Add address */
            $this->insertAddress($page, $shipment->getStore());

            $top = $this->y ? $this->y : 815;
            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId()));

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);
            $page->drawText(Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId(), 35, ($top-=25), 'UTF-8');

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);


            /* Add table head */
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $page->drawText(Mage::helper('sales')->__('Qty'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Products'), 60, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'), 470, $this->y, 'UTF-8');

            $this->y -=15;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
            foreach ($shipment->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y<65+10*count($this->getCustomTextArray($shipment))) {
                    $this->drawCustomText($shipment, $page);
                    $page = $this->newPage(array('table_header' => true));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
            }
            $this->drawCustomText($shipment, $page);
        }

        $this->_afterGetPdf();

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

}
