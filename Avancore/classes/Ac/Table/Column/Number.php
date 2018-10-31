<?php

/**
 * This class shows current row number - that's all
 */

class Ac_Table_Column_Number extends Ac_Table_Column {
    
    function getTitle() {
        if (isset($this->settings['title'])) $res = $this->settings['title'];
            else $res = "#";
        return $res;
    }
    
    function getData($record, $rowNo, $fieldName = null) {
        $res = $this->_table->_pageNav->rowNumber($rowNo);
        return $res;
    }    
    
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('align' => 'left', 'width' => '10');
        $res['rowspan'] = $this->getHeaderRowspan($rowCount, $rowNo);
        return $res;
    }
}

