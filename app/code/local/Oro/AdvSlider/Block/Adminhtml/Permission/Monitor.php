<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Banner Permission Monitor block
 *
 * Removes certain blocks from layout if user do not have required permissions
 *
 * @category   Oro
 * @package    Oro_AdvSlider
 * @copyright  Copyright (c) 2013 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_AdvSlider_Block_Adminhtml_Permission_Monitor extends Mage_Adminhtml_Block_Template
{
    /**
     * Preparing layout
     *
     * @return Oro_AdvSlider_Block_Adminhtml_Permission_Monitor
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!Mage::getSingleton('admin/session')->isAllowed('cms/enterprise_banner')) {
            /** @var $layout Mage_Core_Model_Layout */
            $layout = $this->getLayout();
            if ($layout->getBlock('category.related.banners') !== false) {
                /** @var $promoQouteEditTabsBlock Mage_Adminhtml_Block_Widget_Tabs */
                $promoQuoteEditTabsBlock = $layout->getBlock('promo_quote_edit_tabs');
                if ($promoQuoteEditTabsBlock !== false) {
                    $promoQuoteEditTabsBlock->removeTab('banners_section');
                    $layout->unsetBlock('category.related.banners');
                }
            }
        }
        return $this;
    }
}
