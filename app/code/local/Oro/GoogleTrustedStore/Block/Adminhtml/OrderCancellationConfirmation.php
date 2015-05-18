<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

/**
 * Form for order cancellation reason
 *
 */
class Oro_GoogleTrustedStore_Block_Adminhtml_OrderCancellationConfirmation extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Create form with one field for cancellation reason
     * and sets it to widget
     *
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('order_cancellation_');
        $fieldset = $form->addFieldset('base', array());
        $source = Mage::getSingleton('googletrustedstore/source_orderCancellationReason');
        $fieldset->addField('reason', 'select', array(
            'name'   => 'cancellation_reason',
            'label'  => Mage::helper('googletrustedstore')->__('Cancellation Reason'),
            'values' => $source->toOptionArray(),
            'value'  => $source->getDefaultCode(),
        ));
        $this->setForm($form);
    }
}
