<?php

/**
 * This class shows checkbox to select record for some action and "checkAll" checkbox in the header
 * 
 * @package Avancore Lite
 * @copyright Copyright &copy; 2007, Ilya Rezvin, Avansite (I.Rezvin@avansite.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

class Ac_Table_Column_Checked extends Ac_Table_Column {
    function getTitle() {
        $attribs = array(
            'type' => 'checkbox',
            'name' => 'toggle',
            'value' => '',
            'onclick' => 'checkAll('.$this->_table->countRecords().');',
        );
        $res  =  "<input ".Ac_Util::mkAttribs($attribs)." />"
                ."<input ".Ac_Util::mkAttribs(array('type' => 'hidden', 'name' => 'boxchecked', 'value' => '0'))." />";
        return $res;
    }
    
    function getIdPropName() {
        if (isset($this->settings['idPropName'])) $res = $this->settings['idPropName'];
            else $res = 'id';
        return $res;
    }
    
    function getData($record, $rowNo) {
        if ($this->getCheckoutProcessing()) {
            $res = $this->checkedOutProcessing ($record, $rowNo);
        } else {
            $id = parent::getData($record, $rowNo, $this->getIdPropName());
            $res = $this->getIdBox ($rowNo, $id);  
            
            //mosHTML::idBox( $rowNo, parent::getData ($record, $rowNo, $this->getIdPropName()), false );   
        }
        
        return $res;
    }    
    
    function getIdBox($rowNo, $id, $checkedOut = false) {
        if ($checkedOut) $res = ''; else $res = '<input type="checkbox" id="cb'.$rowNo.'" name="cid[]" value="'.$id.'" onclick="isChecked(this.checked);" />';
        return $res;
    }
    
    function checkedOut($row) {
        if (class_exists('mosCommonHTML')) $res = mosCommonHTML::checkedOut($row);
            else $res = 'Checked out';
        return $res;
    }
    
    function checkedOutProcessing ($record, $rowNo) {
        $disp = Ac_Dispatcher::getInstance();
        if ( $record->checked_out) {
            $checked = $this->checkedOut($record);
        } else {
            $user = $disp->getUser();
            $checked = $this->getIdBox( $i, $row->id, ($row->checked_out && $row->checked_out != $user->id ) );
        }
        return $checked;
    }
    
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('align' => 'left', 'width' => '10');
        $res['rowspan'] = $this->getHeaderRowspan($rowCount, $rowNo);
        return $res;
    }
    
    function getCheckoutProcessing() {
        if (isset($this->settings['checkoutProcessing'])) $res = $this->settings['checkoutProcessing'];
            else $res = false;
        return $res;
    }
}

?>