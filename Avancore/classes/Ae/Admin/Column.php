<?php

class Ae_Admin_Column extends Ae_Table_Column {
    /**
     * Reference to manager that holds table with this column
     *
     * @var Ae_Admin_Manager
     */
    var $manager = false;
    
    function Ae_Admin_Column(& $table, $name, $settings = array()) {
        parent::Ae_Table_Column($table, $name, $settings);
        if (!$this->manager && isset($table->_manager) && $table->_manager) $this->manager = $table->_manager;
    }
    
    function showHeader($rowCount, $rowNo = 1) {
        if ($this->staticAttribs) $this->updateAttribs();
        echo "<th ".Ae_Util::mkAttribs($this->getHeaderAttribs($rowCount, $rowNo)).">";
        $this->doShowHeader();
        echo "</th>";
    }
    
    function doShowHeader() {
        echo $this->getTitle();
    }
    
    function showCell(& $record, $rowNo) {
        if (!$this->staticAttribs) $this->updateAttribs();
        echo '<td ', $this->_cellAttribs, '>';
        $this->doShowCell($record, $rowNo);
        echo '</td>';
    }
    
    function doShowCell(& $record, $rowNo) {
        if (is_null($data = $this->getData($record, $rowNo, $this->fieldName))) $data = $this->nullText;
        echo $data;
    }
    
}

?>