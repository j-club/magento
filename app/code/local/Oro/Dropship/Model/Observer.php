<?php
/**
 * Oro Dropship Model Observer
 *
 * @category   Oro
 * @package    Oro_Sitemap
 * @copyright  Copyright (c) 2014 Oro Inc. DBA MageCore (http://www.magecore.com)
 */
class Oro_Dropship_Model_Observer
{
    /**
     * Add more fields to Vendor Registration Form
     *
     * @param $observer
     * @return $this
     */
    public function addVendorFields($observer)
    {
        $hlp = Mage::helper('oro_dropship');

        if (isset($observer['form'])) {
            $fieldset = $observer['form']->getElement('vendor_form');
        } else {
            return $this;
        }

        $fieldset->addField('fein', 'text', array(
            'name'      => 'fein',
            'label'     => $hlp->__('Federal Tax ID Number (FEIN)')
        ));

        $fieldset->addField('business_type', 'select', array(
            'name'      => 'business_type',
            'label'     => $hlp->__('Business Type'),
            'values'    => Mage::getModel('oro_dropship/businesstype')->toOptionArray()
        ));

        $fieldset->addField('other_business_type', 'text', array(
            'name'      => 'other_business_type',
            'label'     => $hlp->__('Other Business Type')
        ));

        $fieldset->addField('organization', 'text', array(
            'name'      => 'organization',
            'label'     => $hlp->__('State of Incorporation or Organization')
        ));

        $fieldset->addField('tax_filling_info', 'select', array(
            'name'      => 'tax_filling_info',
            'label'     => $hlp->__('Tax Filling Information'),
            'values'    => Mage::getModel('oro_dropship/taxfillinginfo')->toOptionArray()
        ));

        $fieldset->addField('business_address', 'text', array(
            'name'      => 'business_address',
            'label'     => $hlp->__('Business Address')
        ));

        $fieldset->addField('company_website', 'text', array(
            'name'      => 'company_website',
            'label'     => $hlp->__('Company Website')
        ));

        $fieldset->addField('contact_name', 'text', array(
            'name'      => 'contact_name',
            'label'     => $hlp->__('Contact Name')
        ));

        $fieldset->addField('contact_title', 'text', array(
            'name'      => 'contact_title',
            'label'     => $hlp->__('Title')
        ));

        $fieldset->addField('telephone_evening', 'text', array(
            'name'      => 'telephone_evening',
            'label'     => $hlp->__('Telephone (Evening)')
        ));

        $fieldset->addField('bank_name', 'text', array(
            'name'      => 'bank_name',
            'label'     => $hlp->__('Bank Name')
        ));

        $fieldset->addField('bank_account_type', 'text', array(
            'name'      => 'bank_account_type',
            'label'     => $hlp->__('Bank account type')
        ));

        $fieldset->addField('bank_address', 'text', array(
            'name'      => 'bank_address',
            'label'     => $hlp->__('Bank address')
        ));

        $fieldset->addField('bank_contact_name', 'text', array(
            'name'      => 'bank_contact_name',
            'label'     => $hlp->__('Bank contact name')
        ));

        $fieldset->addField('bank_contact_phone', 'text', array(
            'name'      => 'bank_contact_phone',
            'label'     => $hlp->__('Bank contact phone')
        ));

        $fieldset->addField('bank_contact_email', 'text', array(
            'name'      => 'bank_contact_email',
            'label'     => $hlp->__('Bank contact email')
        ));

        $fieldset->addField('bank_account_number', 'text', array(
            'name'      => 'bank_account_number',
            'label'     => $hlp->__('Bank account no.')
        ));

        $fieldset->addField('bank_routing_number', 'text', array(
            'name'      => 'bank_routing_number',
            'label'     => $hlp->__('Bank routing no.')
        ));

        $fieldset->addField('sign', 'text', array(
            'name'      => 'sign',
            'label'     => $hlp->__('Sign')
        ));

        $fieldset->addField('date', 'date', array(
            'name'      => 'date',
            'label'     => $hlp->__('Date'),
            'image'     => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'format'    => 'yyyy-MM-dd'
        ));

        $fieldset->addField('authorized_person', 'text', array(
            'name'      => 'authorized_person',
            'label'     => $hlp->__('Authorized Person')
        ));

        $fieldset->addField('filing_number', 'text', array(
            'name'      => 'filing_number',
            'label'     => $hlp->__('Filling No.')
        ));

        $fieldset->addField('received_by', 'text', array(
            'name'      => 'received_by',
            'label'     => $hlp->__('Form received by')
        ));

        $fieldset->addField('received_date', 'date', array(
            'name'      => 'received_date',
            'label'     => $hlp->__('Received date'),
            'image'     => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'format'    => 'yyyy-MM-dd'
        ));

        $fieldset->addField('approved_by', 'text', array(
            'name'      => 'approved_by',
            'label'     => $hlp->__('Form approved by')
        ));

        $fieldset->addField('approved_date', 'date', array(
            'name'      => 'approved_date',
            'label'     => $hlp->__('Approved date'),
            'image'     => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
            'format'    => 'yyyy-MM-dd'
        ));

        $fieldset->addField('remarks', 'text', array(
            'name'      => 'remarks',
            'label'     => $hlp->__('Remarks')
        ));

        return $this;
    }
}