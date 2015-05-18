<?php

class Magestore_Luckydraw_Block_Luckydraw_Players extends Mage_Core_Block_Template
{
    protected function _construct(){
        parent::_construct();
        
        $collection = Mage::getResourceModel('luckydraw/code_collection')
                ->addFieldToFilter('program_id',$this->getProgram()->getId())
                ->addFieldToFilter('status',array('neq' => Magestore_Luckydraw_Model_Code::STATUS_INACTIVE))
                ->setIsGroupCountSql(true);
        /*$collection->getSelect()
                ->columns('GROUP_CONCAT(`draw_code`) AS draw_codes')
                ->group('customer_id')
                ->order('created_time DESC');*/
        $collection->getSelect()
                ->columns('GROUP_CONCAT(`draw_code` SEPARATOR ", ") AS draw_codes')
                ->group('customer_id')
                ->order('created_time DESC');
        $this->setCollection($collection);
    }
    
	public function _prepareLayout(){
		parent::_prepareLayout();
        $this->setTemplate('luckydraw/luckydraw/players.phtml');
        
        $pager = $this->getLayout()->createBlock('luckydraw/luckydraw_pager','luckydraw_pager')
                ->setCollection($this->getCollection());
        $this->setChild('luckydraw_pager', $pager);
        
        return $this;
	}
	
	/**
	 * get Current Lucky Draw Program
	 * 
	 * @return Magestore_Luckydraw_Model_Program
	 */
	public function getProgram(){
		$program = Mage::registry('luckydraw_program');
		if (!$program){
			$program = Mage::getModel('luckydraw/program');
			$program->load(Mage::app()->getRequest()->getParam('id'));
			Mage::register('luckydraw_program',$program);
		}
		return $program;
	}
    
    public function isConfirmationEnable() {
        if (!$this->hasData('is_confirmation_enable')) {
            $this->setData('is_confirmation_enable',Mage::helper('luckydraw')->getRegisterConfig('verify'));
        }
        return $this->getData('is_confirmation_enable');
    }
    
    public function formatShowDate($createTime) {
        $delta = time() - strtotime($createTime);
        if ($delta < 60) {
            return $this->__('%s second(s) ago',$delta);
        }
        $delta = intval($delta / 60);
        if ($delta < 60) {
            return $this->__('%s minute(s) ago',$delta);
        }
        $delta = intval($delta / 60);
        if ($delta < 24) {
            return $this->__('%s hour(s) ago',$delta);
        }
        $delta = intval($delta / 24);
        return $this->__('%s day(s) ago',$delta);
    }
}