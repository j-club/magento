<?php
/**
 * @category   Oro
 * @package    Oro_GoogleTrustedStore
 */

/**
 * Custom renderer for subscriptin to group
 *
 */
class Oro_GoogleTrustedStore_Block_Adminhtml_System_Config_Subscribe
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $subscribeLabel = $this->__('Subscribe');
        return <<<HTML
<input id="trustedstore_subscription_for_updates_value" type="text" value="" name="groups[trustedstore][fields][subscription_for_updates][value]" class="input-text validate-email" style="width:190px">
<button type="submit" id="trustedstore_subscription_for_updates_submit" class="disabled" disabled="disabled">$subscribeLabel</button>
<script type="text/javascript">
    document.observe('dom:loaded', function () {
        var trstdSubmit = $('trustedstore_subscription_for_updates_submit');
        var trstdValue = $('trustedstore_subscription_for_updates_value');
        Event.observe('trustedstore_subscription_for_updates_value', 'input', function (e) {
            if (trstdValue.getValue()) {
                enableElement(trstdSubmit);
            } else {
                disableElement(trstdSubmit);
            }
        });
    });
</script>
HTML;
    }
}
