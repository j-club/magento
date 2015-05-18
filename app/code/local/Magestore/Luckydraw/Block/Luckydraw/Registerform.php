<?php

class Magestore_Luckydraw_Block_Luckydraw_Registerform extends Magestore_Luckydraw_Block_Luckydraw
{
	public function _prepareLayout(){
		$this->getFormData();
		return parent::_prepareLayout();
	}
	
	public function getFormData(){
		if ($this->hasData('form_data')) return $this->getData('form_data');
		$formData = new Varien_Object();
		if ($data = Mage::getSingleton('core/session')->getRegisterLuckydraw()){
			$formData->setData($data);
			Mage::getSingleton('core/session')->setRegisterLuckydraw(null);
		} elseif ($this->getIsCustomerLogin()) {
            $formData->setData($this->getCustomer()->getData());
        }
		$this->setData('form_data',$formData);
		return $this->getData('form_data');
	}
	
	public function isCaptchaEnabled(){
		return Mage::helper('luckydraw')->getRegisterConfig('captcha');
	}
    
    public function getCustomer(){
        return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    /**
     * URL to check existed email address
     * 
     * @return string
     */
    public function getCheckCustomerEmailUrl(){
        return $this->getUrl('*/*/checkemailregister',array('id' => $this->getProgram()->getId()));
    }
    
    public function getNationalIdField(){
        if (!$this->hasData('national_id_field')) {
            $this->setData('national_id_field',Mage::helper('luckydraw')->getRegisterConfig('national_id_show'));
        }
        return $this->getData('national_id_field');
    }
    
    public function reqNationalIdField(){
        return (bool) ($this->getNationalIdField() === 'req');
    }
    
    public function getAddress(){
        if ($this->getFormData()->getData('account')) {
            $address = Mage::getModel('customer/address');
            $address->setData($this->getFormData()->getData('account'));
        } elseif ($this->getIsCustomerLogin()) {
            if ($this->customerHasAddresses()){
                return $this->getCustomer()->getAddressesCollection()->getFirstItem();
            } else {
                return Mage::getModel('customer/address')
                        ->setFirstname($this->getCustomer()->getFirstname())
                        ->setLastname($this->getCustomer()->getLastname());
            }
        }
        return Mage::getModel('customer/address');
    }
    
    public function customerHasAddresses(){
        return $this->getCustomer()->getAddressesCollection()->getSize();
    }
    
    public function getAddressesHtmlSelect($type){
        if ($this->getIsCustomerLogin()){
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                );
            }
            $addressId = $this->getAddress()->getId();
            if (empty($addressId)) {
				$address = $this->getCustomer()->getPrimaryBillingAddress();
            }
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange=lsRequestTrialNewAddress(this.value);')
                ->setValue($addressId)
                ->setOptions($options);
            $select->addOption('', Mage::helper('checkout')->__('New Address'));
            return $select->getHtml();
        }
        return '';
    }
    
    public function getCountryHtmlSelect($type){
        $countryId = $this->getAddress()->getCountryId();
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        return $select->getHtml();
    }
    
    public function getRegionCollection(){
        if (!$this->_regionCollection){
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }
    
    public function getRegionHtmlSelect($type){
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[region]')
            ->setId($type.':region')
            ->setTitle(Mage::helper('checkout')->__('State/Province'))
            ->setClass('required-entry validate-state')
            ->setValue($this->getAddress()->getRegionId())
            ->setOptions($this->getRegionCollection()->toOptionArray());
        return $select->getHtml();
    }
    
    public function getCountryCollection(){
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getSingleton('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }
    
    public function getCountryOptions(){
        $options    = false;
        $useCache   = Mage::app()->useCache('config');
        if ($useCache) {
            $cacheId    = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
            $cacheTags  = array('config');
            if ($optionsCache = Mage::app()->loadCache($cacheId)) {
                $options = unserialize($optionsCache);
            }
        }
        if ($options == false) {
            $options = $this->getCountryCollection()->toOptionArray();
            if ($useCache) {
                Mage::app()->saveCache(serialize($options), $cacheId, $cacheTags);
            }
        }
        return $options;
    }
}