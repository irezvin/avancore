<?php

/**
 * This class shows current row number - that's all
 */

class Ac_Table_Column_Number extends Ac_Table_Column {
    
    function setOffset($offset) {
        $this->settings['offset'] = $offset;
    }
    
    function getOffset() {
        if (isset($this->settings['offset'])) $res = $this->settings['offset'];
            else $res = 0;
        return $res;
    }
    
    function getTitle() {
        if (isset($this->settings['title'])) $res = $this->settings['title'];
            else $res = "#";
        return $res;
    }
    
    function getData($record, $rowNo, $fieldName = null) {
        if ($this->_table->_pageNav) {
            $res = $this->_table->_pageNav->rowNumber($rowNo);
        } else {
            $res = $this->getOffset() + $rowNo + 1;
        }
        return $res;
    }    
    
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('align' => 'left', 'width' => '10');
        $res['rowspan'] = $this->getHeaderRowspan($rowCount, $rowNo);
        return $res;
    }
}

