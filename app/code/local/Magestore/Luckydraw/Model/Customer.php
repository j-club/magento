<?php

class Magestore_Luckydraw_Model_Customer extends Mage_Customer_Model_Customer
{
    public function validate() {
        $errors = array();
        $customerHelper = Mage::helper('customer');
        $configHelper = Mage::helper('luckydraw');
        
        if (!Zend_Validate::is( trim($this->getFirstname()) , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The first name cannot be empty.');
        }

        if (!Zend_Validate::is( trim($this->getLastname()) , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The last name cannot be empty.');
        }

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = $customerHelper->__('Invalid email address "%s".', $this->getEmail());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The password cannot be empty.');
        }
        if ($password && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = $customerHelper->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getConfirmation();
        if ($password != $confirmation) {
            $errors[] = $customerHelper->__('Please make sure your passwords match.');
        }

        if (('req' === $configHelper->getRegisterConfig('dob_show'))
            && '' == trim($this->getDob())) {
            $errors[] = $customerHelper->__('The Date of Birth is required.');
        }
        if (('req' === $configHelper->getRegisterConfig('taxvat_show'))
            && '' == trim($this->getTaxvat())) {
            $errors[] = $customerHelper->__('The TAX/VAT number is required.');
        }
        if (('req' === $configHelper->getRegisterConfig('gender_show'))
            && '' == trim($this->getGender())) {
            $errors[] = $customerHelper->__('Gender is required.');
        }
        
        if ('req' === $configHelper->getRegisterConfig('national_id_show')){
            if ('' == trim($this->getNationalId())) {
                $errors[] = $configHelper->__('National Identification is required.');
            } else {
                $this->setNationalId(trim($this->getNationalId()));
                /* check existed value */
                $collection = $this->getCollection()
                        ->addFieldToFilter('national_id',$this->getNationalId());
                if ($this->getId()) {
                    $collection->addFieldToFilter('entity_id',array('neq' => $this->getId()));
                }
                if ($this->getSharingConfig()->isWebsiteScope()) {
                    $collection->addFieldToFilter('website_id',
                            Mage::app()->getWebsite($this->getWebsiteId())->getId());
                }
                if ($collection->getFirstItem() && $collection->getFirstItem()->getId()) {
                    $errors[] = $configHelper->__('National Identification is already assigned to other customer.');
                }
            }
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}
