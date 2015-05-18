<?php
/**
 * @category   Oro
 * @package    Oro_SimpleMsrp
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/**
 * On Sale Label Class
 */
class Oro_SimpleMsrp_Block_Product_Label extends AW_Onsale_Block_Product_Label
{
    /**
     * Updated method
     * @return AW_Onsale_Block_Product_Label
     */
    public function setProduct($product)
    {
        # Fix #2111
        if (!$product->getCreatedAt()) {
            $product->setCreatedAt(Mage::helper('onsale')->getCustomAttributeValue('created_at', $product));
        }
        # End fix #2111

        $this->_product = $product;

        $this->_onSale = false;
        $this->_custom = false;
        $this->_new = false;
        $oroMsrpHelper = $this->helper('oro_simplemsrp');
        //Set up category for helper collection load
        Mage::helper('onsale')->setCategoryId($product->getCategoryId());

        //Onsale price calculations
        if ($product->getTypeId() == 'bundle') {
            list($_minimalPrice, $_maximalPrice) = $product->getPriceModel()->getPrices($product);
            $this->_price = $_minimalPrice;
            $this->_specialPrice = $_minimalPrice;
            if (!is_null($product->getData('special_price')) && ($product->getData('special_price') < 100)) {
                $this->_regularPrice = ($this->_specialPrice / $product->getData('special_price')) * 100;
            } else {
                $this->_regularPrice = $this->_specialPrice;
            }
        } else {
            $this->_price = 0;
            $this->_regularPrice = $oroMsrpHelper->getRegularPrice($product);
            $this->_specialPrice = $product->getFinalPrice();
        }

        if ($this->_specialPrice != $this->_regularPrice) {
            if ($this->_regularPrice > 0) {
                $this->_discountAmount = round((1 - $this->_specialPrice / $this->_regularPrice) * 100);
                $this->_saveAmount = $this->_regularPrice - $this->_specialPrice;

                if (Mage::helper('onsale')
                    ->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_ONSALE, 'display', $product) == 1
                ) {
                    $this->_onSale = true;
                }
                $threshold = Mage::helper('onsale')
                    ->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_ONSALE, 'threshold', $product);
                if ($threshold && $threshold > $this->_discountAmount) {
                    $this->_onSale = false;
                }
            }
        }

        //New calculations
        $days = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'days', $product);
        $isNew = $this->_isNewProduct($product->getCreatedAt(), $days);
        $isNativeNew = $this->_isNativeNewProduct($product);
        $overridesNativeNew = Mage::helper('onsale')
            ->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'overrides_native_new', $product);
        $nativeNewIsSettedUp = ($product->getNewsFromDate() || $product->getNewsToDate());
        if ($isNativeNew || $isNew) {
            if (
                ($nativeNewIsSettedUp && !$overridesNativeNew && $isNativeNew) ||
                (($isNew && $overridesNativeNew) || ($isNew && !$nativeNewIsSettedUp))
            ) {
                if (Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'display', $product) == 1) {
                    $this->_new = true;
                }
            }
        }

        if (Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_CUSTOM, 'display', $product)) {
            $this->_custom = true;
        }

        //Fill common of params
        $this->_inStock = (int)Mage::helper('onsale')->getStockAttribute('qty', $product);
        $this->_productSku = $product->getSku();
        $this->_daysAgo = $this->_getAbsDays($product->getCreatedAt());
        $this->_hoursAgo = $this->_getAbsHours($product->getCreatedAt());
        if ($this->_regularPrice) {
            $this->_regularPrice = strip_tags(Mage::app()->getStore()->convertPrice($this->_regularPrice, true));
        }
        if ($this->_specialPrice) {
            $this->_specialPrice = strip_tags(Mage::app()->getStore()->convertPrice($this->_specialPrice, true));
        }
        if ($this->_saveAmount) {
            $this->_saveAmount = strip_tags(Mage::app()->getStore()->convertPrice($this->_saveAmount, true));
        }
        if ($this->_discountAmount) {
            $this->_discountAmount = $this->_discountAmount . '%';
        }

        //Desides that we will show
        //Set up routes
        $overrides = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, AW_Onsale_Block_Product_Label::TYPE_NEW, 'overrides', $product);
        if ($this->_custom) {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_CUSTOM;
        } elseif ($this->_new && $overrides) {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_NEW;
        } elseif ($this->_onSale) {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_ONSALE;
        } else {
            $this->_type = AW_Onsale_Block_Product_Label::TYPE_NEW;
        }

        //Set Show params
        if ($this->isShow()) {
            if ($this->_custom) {
                $imageFile = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image', $product, true);
                $imagePath = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image_path', $product, true);
                if (!file_exists($imageFile)) {
                    $imageFile = 'uploaded' . DS . $imageFile;
                }
            } else {
                $imageFile = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image', $product);
                $imagePath = Mage::helper('onsale')->confGetCustomValue($this->_placeRoute, $this->_type, 'image_path', $product);
            }
            $defaultImage = $this->_getDefaultImage();
            $this->setImageFile($imageFile, $defaultImage, $imagePath);
        }
        return $this;
    }

}
