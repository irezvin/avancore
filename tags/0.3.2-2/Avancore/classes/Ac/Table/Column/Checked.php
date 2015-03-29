<?php

/**
 * This class shows checkbox to select record for some action and "checkAll" checkbox in the header
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
        $id = parent::getData($record, $rowNo, $this->getIdPropName());
        $res = $this->getIdBox ($rowNo, $id);  
        
        return $res;
    }    
    
    function getIdBox($rowNo, $id) {
        $res = '<input type="checkbox" id="cb'.$rowNo.'" name="cid[]" value="'.$id.'" onclick="isChecked(this.checked);" />';
        return $res;
    }
    
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('align' => 'left', 'width' => '10');
        $res['rowspan'] = $this->getHeaderRowspan($rowCount, $rowNo);
        return $res;
    }
    
}

