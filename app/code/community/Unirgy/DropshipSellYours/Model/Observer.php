<?php

class Unirgy_DropshipSellYours_Model_Observer
{
    public function umicrosite_check_permission($observer)
    {
        if (!Mage::getStoreConfig('udropship/microsite/use_basic_pro_accounts')
            || !$observer->getVendor() || !$observer->getVendor()->getId()
        ) {
            return $this;
        }
        switch ($observer->getAction()) {
            case 'microsite':
            case 'adminhtml':
            case 'new_product':
                if ($observer->getVendor()->getAccountType()!='pro') {
                    $observer->getTransport()->setRedirect(
                        Mage::app()->getStore()->getUrl('udsell/index/becomePro')
                    );
                    $observer->getTransport()->setAllowed(false);
                }
                break;
        }
        return $this;
    }
    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        $id = $observer->getEvent()->getId();
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('vendor_form');

        $fieldset->addField('is_featured', 'select', array(
            'name'      => 'is_featured',
            'label'     => Mage::helper('udsell')->__('Is Featured'),
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
        ));

        if (Mage::getStoreConfig('udropship/microsite/use_basic_pro_accounts')) {
            $fieldset->addField('account_type', 'select', array(
                'name'      => 'account_type',
                'label'     => Mage::helper('udsell')->__('Account Type'),
                'options'   => Mage::getSingleton('udsell/source')->setPath('account_type')->toOptionHash(),
            ));
        }
    }

    public function controller_front_init_before($observer)
    {
        if (Mage::getStoreConfigFlag('udropship/customer/sell_yours_show_disabled')) {
        }
    }
}