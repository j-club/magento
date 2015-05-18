<?php

class Unirgy_DropshipBatch_Model_Adapter_Default
    extends Unirgy_DropshipBatch_Model_Adapter_Abstract
{
    public function addPO($po)
    {
        if (!$this->preparePO($po)) {
            return $this;
        }

        if (!$this->getItemsArr()) {
            $this->setItemsArr(array());
        }
        $itemsFooter = $itemsFooterTpl = '';
        $itemTpl = $tpl = $this->getExportTemplate();

        if (($useItemTemplate = $this->getUseItemExportTemplate())) {
            $itemTpl = $this->getItemExportTemplate();
            $itemsFooterTpl = $this->getItemFooterExportTemplate();
        }

        $idx = 0;
        foreach ($po->getItemsCollection() as $item) {
            if (!$this->preparePOItem($item)) {
                continue;
            }
            $itemKey = $this->getVars('po_id').'-'.$item->getId();
            if ($useItemTemplate) {
                if (0==$idx++) {
                    $this->_data['items_arr'][$this->getVars('po_id').'-0'] = $this->renderTemplate($tpl, $this->getVars());
                }
                $itemsFooter = $this->renderTemplate($itemsFooterTpl, $this->getVars());
            }
            $this->_data['items_arr'][$itemKey] = $this->renderTemplate($itemTpl, $this->getVars());
            $this->getBatch()->addRowLog($this->getOrder(), $this->getPo(), $this->getPoItem());
            $this->restoreItem();
        }
        if ($useItemTemplate) {
            $this->_data['items_arr'][$this->getVars('po_id').'-99999'] = $itemsFooter;
        }

        $this->setHasOutput(true);
        return $this;
    }

    public function renderOutput()
    {
        $batch = $this->getBatch();
        $header = $batch->getBatchType()=='export_orders' ? $batch->getVendor()->getBatchExportOrdersHeader() : '';

        $this->setHasOutput(false);
        return ($header ? $header."\n" : '') . join("\n", $this->getItemsArr());
    }

    public function getPerPoOutput()
    {
        $batch = $this->getBatch();
        $rows = array();
        $rows['header'] = $batch->getBatchType()=='export_orders' ? $batch->getVendor()->getBatchExportOrdersHeader() : '';

        foreach ($this->getItemsArr() as $iKey => $iRow) {
            $poId = substr($iKey, 0, strpos($iKey, '-'));
            if (empty($rows[$poId])) {
                $rows[$poId] = '';
            } else {
                $rows[$poId] .= "\n";
            }
            $rows[$poId] .= $iRow;
        }

        $this->setHasOutput(false);

        return $rows;
    }

}
