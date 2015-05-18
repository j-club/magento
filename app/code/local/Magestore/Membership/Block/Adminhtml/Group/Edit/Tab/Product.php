<?php
class Magestore_Membership_Block_Adminhtml_Group_Edit_Tab_Product
	 extends Mage_Adminhtml_Block_Widget_Grid
{
	
    public function __construct()
  	{
        parent::__construct();
        $this->setId('list_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getGroup() && $this->getGroup()->getId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }  
  	}
	
	
	/*Ham nay co tac dung de search theo tung truong mot*/
    protected function _addColumnFilterToCollection($column)
    {
		// Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    //return category collection filtered by store
	protected function _prepareCollection()
    {
		$membershipProductIds = Mage::helper('membership')->getMembershipProductIds();
		
        $collection = Mage::getModel('catalog/product')
			->getCollection()
            ->addFieldToFilter('status', 1)
            ->addAttributeToSelect('*');
		
		if(count($membershipProductIds)){
			$collection->addFieldToFilter('entity_id', array('nin'=>$membershipProductIds));
		}
		
		$this->setCollection($collection);
		
		return 	parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		$this->addColumn('in_products', array(
			  'header_css_class' => 'a-center',
			  'type'      => 'checkbox',
			  'name'      => 'in_products',
			  'align'     => 'center',
			  'index'     => 'entity_id',
			  'values'    => $this->_getSelectedProducts(),
		));
		
		$this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));
		
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '130px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '90px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '90px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'  => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));
		
		$this->addColumn('position', array(
            'header'            => Mage::helper('catalog')->__(''),
            'name'              => 'position',
           // 'validate_class'    => 'validate-number',
            'index'             => 'position',
            'width'             => 0,
            'editable'          => true,//!$this->isReadonly(),
			'filter'			=> false,
        ));
		
		 return parent::_prepareColumns();
		
    }
	
	protected function _getSelectedProducts()
    {
		$products = $this->getProducts();
		if(!is_array($products)) {
			$products = array_keys($this->getSelectedProducts());
		}
		
		return $products;
	}
	
	public function getSelectedProducts()
    {
		$products = array();
		$productIds = $this->getGroup()->getProductIds();
		if(count($productIds)){
			foreach($productIds as $productId) {
				$products[$productId] = array('position'=>0);
			}
		}
		return $products;
    }

    //return url
	public function getGridUrl()
    {
		return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/productsGrid', array('_current'=>true,'id'=>$this->getRequest()->getParam('id')));
	}

	
	//return Magestore_Affiliate_Model_Program
	public function getGroup()
	{
		return Mage::getModel('membership/group')
						->load($this->getRequest()->getParam('id'));
	}	
}