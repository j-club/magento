<?php

class Magestore_Luckydraw_Block_Luckydraw_Pager extends Mage_Page_Block_Html_Pager
{
	public function _prepareLayout(){
		parent::_prepareLayout();
        $this->setTemplate('luckydraw/luckydraw/pager.phtml');
        return $this;
	}
}
