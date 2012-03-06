<?php

/**
 * Class for rendering admin's tables. Works with a set of Ae_Table_Column instances. 
 *  
 * @package Avancore Lite
 * @copyright Copyright &copy; 2007, Ilya Rezvin, Avansite (I.Rezvin@avansite.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

class Ae_Table {
    
    var $trAttribsCallback = false;
    
    var $recordClass = false;
    
    /**
     * @var array Column presets 
     * @access private
     */
    var $_columnSettings = array();
    
    /**
     * @var array Array of Ae_Table_Column instances 
     * @access private
     */
    var $_columns = array();
    
    /**
     * @var array Records to render (array of object)
     * @access private
     */
    var $_records = array();
    
    /**
     * @var Ae_Page_List_Navigation mosPageNav instance
     * @access private
     */
    var $_pageNav = null;
    
    /**
     * @var array TABLE tag attribs array (to pass to the ormAe_Util::mkAttribs)
     */
    var $tableAttribs = array();
    
    /**
     * Prototype of record to retrieve static property info
     * @var Ae_Model_Data
     */
    var $_recordPrototype = false;
    
    var $_colNames = false;
    
    /**
     * @param array columnSettings Array 'column name' => array('class' => 'column class', ...other settings...)
     * @param array records Records to render
     * @param object pageNav mosPageNav instance for displaying the pager
     * @param array tableAttribs Attributes of the TABLE tag
     */
    function Ae_Table($columnSettings, $records, & $pageNav, $tableAttribs = array(), $recordClass = false) {
        $this->_columnSettings = $columnSettings;
        $this->_records = $records;
        $this->_pageNav = $pageNav;
        $this->tableAttribs = $tableAttribs;
        $this->recordClass = $recordClass;
    }
    
    function getHeaderRowCount() {
        $rowCount = 1;
        foreach ($this->listColumns() as $colId) {
            $column = $this->getColumn($colId);
            $rowCount = max($rowCount, $column->getHeaderRowCount());
        }
        return $rowCount;
    }
    
    /**
     * @returns array names of table's columns (to return them via getColumn() function)
     */
    function listColumns() {
        if ($this->_colNames === false) {
            $this->_colNames = array();
            foreach (array_keys($this->_columnSettings) as $k) {
                $col = $this->getColumn($k);
                if (!$col->disabled) $this->_colNames[] = $k;
            }
            usort($this->_colNames, array(& $this, '_sortColumns'));
        }
        return $this->_colNames;
    }
    
    function _sortColumns($colName1, $colName2) {
        return $this->_columns[$colName1]->order - $this->_columns[$colName2]->order;
    }
    
    /**
     * @param string name Column name (must match to key in columnSettings param that was passed to the constructor)
     */
    function getColumn($name) { 
        
        if (!isset($this->_columns[$name])) {
            if (!isset($this->_columnSettings[$name])) trigger_error (__FILE__."::".__FUNCTION__." - column '$name' missing ", E_USER_ERROR);
            $settings = $this->_columnSettings[$name];
            
            if (!isset($settings['class'])) $columnClass = "Ae_Table_Column";
                else $columnClass = $settings['class'];
            
            $className = $columnClass;
            
            $this->_columns[$name] = new $className ($this, $name, $settings);
            
            if ($this->_columns[$name]->order === false) {
                $this->_columns[$name]->isAutoOrder = true;
                $maxAutoOrder = 100;
                foreach (array_keys($this->_columns) as $i) if (!$this->_columns[$i]->disabled) {
                    if ($this->_columns[$i]->isAutoOrder && ($this->_columns[$i]->order >= $maxAutoOrder))
                        $maxAutoOrder = $this->_columns[$i]->order + 10;
                }
                $this->_columns[$name]->order = $maxAutoOrder;
            }
        }
        $res = $this->_columns[$name];
        return $res;
    }
    
    /**
     * @return array Array of available record identifiers (to pass to the getRecord() method)
     */
    function listRecords() {
        $res = array_keys($this->_records);
        return $res;
    }
    
    /**
     * @return int Records count (that will be displayed in the table)
     */
    function countRecords() {
        $res = count($this->_records);
        return $res;
    }   
    
    /**
     * @param string name Record identifier in $this->_records array (don't confuse with primary key)
     * @return object Record object
     */
    function getRecord($name) {
        if (!isset($this->_records[$name])) trigger_error (__FILE__."::".__FUNCTION__." - record '$name' missing ", E_USER_ERROR);
        $res = $this->_records[$name];
        return $res;
    }
    
    /**
     * Renders (echo's) the table
     */
    function show() {
        echo "<table class='adminlist' ".Ae_Util::mkAttribs($this->tableAttribs)." >";
        
        $headerRowCount = $this->getHeaderRowCount();
        
        foreach(range(0, $headerRowCount - 1) as $headerRowNo) {
            echo "<tr>";
        
            foreach($this->listColumns() as $colName) {
                $col = $this->getColumn($colName);
                if ($headerRowNo < $col->getHeaderRowCount()) 
                    $col->showHeader($headerRowCount, $headerRowNo);
            }
            
            echo "</tr>";
        }
        
        $row = 0;
        
        $cols = array();
        foreach($this->listColumns() as $colName) {
            $cols[] = $this->getColumn($colName);
        }
        $nCols = count($cols);
        
        foreach($this->listRecords() as $recordName) {
            $rMod = $row % 2;
            $record = $this->getRecord($recordName);
            $trAttribs = array('class' => 'row'.$rMod);
            echo "<tr ".Ae_Util::mkAttribs($this->_trAttribs($record, $trAttribs)).">"; 
            
            //$record->makeHtmlSafe();
            
            for ($i = 0; $i < $nCols; $i++) {
                $cols[$i]->showCell($record, $row);
            }
            echo "</tr>";
            
            $row++;
        }
        
        echo "</table>";
    }
    
    /**
     * Renders (echo's) table legend (may be not used in the template yet)
     */
    function showHints() {
        foreach($this->listColumns() as $colName) {
            $col = $this->getColumn($colName);
            $col->showHint();
        }
    }
    
    /**
     * @return Ae_Model_Data
     */
    function getRecordPrototype() {
        if ($this->_recordPrototype === false) {
            if ($this->recordClass) {
                $this->_recordPrototype = new $this->recordClass();
            } else $this->_recordPrototype = null;
        }
        return $this->_recordPrototype;
    }
    
    function & createAutoColumn ($propName) {
        // TODO
    }
    
    function _trAttribs(& $record, & $attribs) {
        if ($this->trAttribsCallback) call_user_func_array($this->trAttribsCallback, array(& $record, & $attribs));
        return $attribs;
    }
}

?>