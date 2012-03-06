<?php

class Ae_Table_Column_Reorder extends Ae_Table_Column {

    var $canOrderUpMethod = false;
    var $canOrderDownMethod = false;
    
    function getOrderUpTask() {
        if (isset($this->settings['orderUpTask'])) $res = $this->settings['orderUpTask'];
            else $res = 'orderup';
        return $res;
    }
    
    function getOrderDownTask() {
        if (isset($this->settings['orderDownTask'])) $res = $this->settings['orderDownTask'];
            else $res = 'orderdown';
        return $res;
    }
    
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('colspan' => '2', 'align' => 'center', 'width' => '5%');
        return $res;
    }
    
    function getCellAttribs() {
        if (isset($this->settings['cellAttribs'])) $res = $this->settings['cellAttribs'];
            else $res = array('align' => 'center', 'width' => '2%');
        return $res;
    }
    
    function getTitle() {
        if (isset($this->settings['title'])) $res = $this->settings['title'];
            else $res = ACLT_ORDERING;
        return $res;
    }    
    
    function getNextOrderProp ($rowNo) {
        $recList = $this->_table->listRecords();
        if (($this->getOrderProperty() !== false) && ($rowNo >= 0) && ($rowNo + 1 < count($recList))) {
            $nextRec = $this->_table->getRecord($recList[$rowNo + 1]);
            $res = $this->getOrderPropertyValue($nextRec, $rowNo + 1);
        } else {
            $res = false;
        }
        return $res;
    }
    
    function getPrevOrderProp ($rowNo) {
        $recList = $this->_table->listRecords();
        if (($this->getOrderProperty() !== false) && ($rowNo >= 1) && $rowNo < count($recList)) {
            $prevRec = $this->_table->getRecord($recList[$rowNo - 1]);
            $res = $this->getOrderPropertyValue($prevRec, $rowNo - 1);
        } else {
            $res = false;
        }
        return $res;
    }
    
    function getOrderProperty () {
        if (isset($this->settings['orderProperty'])) $res = $this->settings['orderProperty'];
            else $res = false;
        return $res;
    }

    function getOrderPropertyValue(& $record, $rowNo) {
        $op = $this->getOrderProperty ();
        if ($op !== false) {
            $res = $this->getData($record, $rowNo, $op);
            //$res = $record->$op;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function canOrderUp (& $record, $rowNo) {
        if (strlen($m = $this->canOrderUpMethod)) $res = $record->$m();
        else {
            $res = $rowNo > 0 && $this->getOrderPropertyValue($record, $rowNo) === $this->getPrevOrderProp($rowNo);
        }
        return $res;
    }
    
    function canOrderDown (& $record, $rowNo) {
        if (strlen($m = $this->canOrderDownMethod)) $res = $record->$m();
        else {
            $res = ($rowNo < count($this->_table->listRecords()) - 1) 
                && $this->getOrderPropertyValue($record, $rowNo) === $this->getNextOrderProp($rowNo);
        }
        return $res;
    }
    
    
    /**
     * Renders (echo's) column cell
     */
    function showCell(& $record, $rowNo) {
        echo "<td ".Ae_Util::mkAttribs($this->getCellAttribs()).">".$this->_table->_pageNav->orderUpIcon($rowNo, $this->canOrderUp($record, $rowNo), $this->getOrderUpTask())."</td>";
        echo "<td ".Ae_Util::mkAttribs($this->getCellAttribs()).">".$this->_table->_pageNav->orderDownIcon($rowNo, count($this->_table->listRecords()), $this->canOrderDown($record, $rowNo), $this->getOrderDownTask())."</td>";
    }
}
?>