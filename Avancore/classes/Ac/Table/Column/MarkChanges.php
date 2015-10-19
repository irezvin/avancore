<?php

class Ac_Table_Column_MarkChanges extends Ac_Table_Column {
    
    var $staticAttribs = false;
    
    var $groupField = false;
    
    var $changedAttribs = array('class' => 'changed');
    
    var $notChangedAttribs = array();
    
    var $currCellAttribs = array();
    
    var $changedTrAttribs = array();
    
    var $notChangedTrAttribs = array();    
    
    var $strict = false;
    
    protected $lastValue = null;
    
    protected $hasLastValue = false;
    
    protected $lastGroupValue = null;
    
    function showHeader($rowCount, $rowNo = 1) {
        parent::showHeader($rowCount, $rowNo);
        $this->lastValue = null;
        $this->hasLastValue = false;
        $this->lastGroupValue = null;
    }
  
    function showCell($record, $rowNo) {
        $value = $this->getData($record, $rowNo);
        $changed = false;
        
        if (strlen($this->groupField)) {
            $groupValue = $this->getData($record, $rowNo, $this->groupField);
            if ($this->_table->currentRowNo > 0) {
                $groupChanged = $this->strict? $groupValue !== $this->lastGroupValue : $groupValue != $this->lastGroupValue;
                if ($groupChanged) {
                    $this->hasLastValue = false;
                }
            }
            $this->lastGroupValue = $groupValue;
        }
        
        if ($this->hasLastValue) {
            $changed = $this->strict? $this->lastValue !== $value : $this->lastValue != $value;
        }
        $this->currCellAttribs = $this->getCellAttribs();
        Ac_Util::ms($this->currCellAttribs, $changed? $this->changedAttribs : $this->notChangedAttribs);
        if ($changed) {
            if ($this->changedTrAttribs) Ac_Util::ms($this->_table->currentRowAttribs, $this->changedTrAttribs);
        } else {
            if ($this->notChangedTrAttribs) Ac_Util::ms($this->_table->currentRowAttribs, $this->notChangedTrAttribs);
        }
        parent::showCell($record, $rowNo);
        $this->hasLastValue = true;
        $this->lastValue = $value;
    }
    
    function updateAttribs() {
        $this->_cellAttribs = Ac_Util::mkAttribs($this->currCellAttribs);
    }
    
}