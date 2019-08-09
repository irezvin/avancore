<?php

/**
 * Class for rendering admin's tables. Works with a set of Ac_Table_Column instances. 
 */

class Ac_Table {
    
    var $recordClass = false;
    
    /**
     * @var array Column presets 
     * @access private
     */
    var $_columnSettings = array();
    
    /**
     * @var array Array of Ac_Table_Column instances 
     * @access private
     */
    var $_columns = array();
    
    /**
     * @var array Records to render (array of object)
     * @access private
     */
    var $_records = array();
    
    /**
     * @var Ac_Page_List_Navigation mosPageNav instance
     * @access private
     */
    var $_pageNav = null;
    
    /**
     * @var array TABLE tag attribs array (to pass to the ormAe_Util::mkAttribs)
     */
    var $tableAttribs = array();
    
    /**
     * Prototype of record to retrieve static property info
     * @var Ac_Model_Data
     */
    var $_recordPrototype = false;
    
    var $_colNames = false;

    /**
     * Render-time variable - current record
     */
    var $currentRecord = null;
    
    /**
     * Render-time variable - current row attribs (may be modified from inside of the cell)
     */
    var $currentRowAttribs = array();
    
    /**
     * Render-time variable - ## of processed records. -1 for header row
     */
    var $currentRecordNo = -1;
    
    /**
     * Render-time variable - ## of rendered rows at the moment (except the header) -1 for header row
     */
    var $currentRowNo = -1;
    
    /**
     * Render-time variable - whether to skip current row (if set from a cell, the rendering will stop and we will continue to next row)
     * @var type 
     */
    var $currentRowSkip = false;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * @param array columnSettings Array 'column name' => array('class' => 'column class', ...other settings...)
     * @param array records Records to render
     * @param object pageNav mosPageNav instance for displaying the pager
     * @param array tableAttribs Attributes of the TABLE tag
     */
    function __construct($columnSettings, $records, $pageNav, $tableAttribs = array(), $recordClass = false) {
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
        $res = $this->_columns[$colName1]->order - $this->_columns[$colName2]->order;
        if ($res > 0) return 1;
        if ($res < 0) return -1;
        return 0;
    }
    
    /**
     * @param string name Column name (must match to key in columnSettings param that was passed to the constructor)
     */
    function getColumn($name) { 
        
        if (!isset($this->_columns[$name])) {
            if (!isset($this->_columnSettings[$name])) trigger_error (__FILE__."::".__FUNCTION__." - column '$name' missing ", E_USER_ERROR);
            $settings = $this->_columnSettings[$name];
            
            if (!isset($settings['class'])) $columnClass = "Ac_Table_Column";
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

    protected function resetState() {
        $this->currentRecord = null;
        $this->currentRecordNo = -1;
        $this->currentRowNo = -1;
        $this->currentRowAttribs = array();
        $this->currentRowSkip = false;
    }
    
    /**
     * @return Ac_Model_Object
     */
    function _fetchNextRecord() {
        $items = array_slice($this->records, $this->currentRecordNo, 1);
        if (count($items)) $res = $items[0];
            else $res = null;
        return $res;
    }
    
    /**
     * Renders (echo's) the table
     */
    function show() {
        
        $this->resetState();
        
        echo "<table class='adminlist' ".Ac_Util::mkAttribs($this->tableAttribs)." >";
        
        $headerRowCount = $this->getHeaderRowCount();
        
        foreach(range(0, $headerRowCount - 1) as $headerRowNo) {
            ob_start();
        
            foreach($this->listColumns() as $colName) {
                $col = $this->getColumn($colName);
                if ($headerRowNo < $col->getHeaderRowCount()) 
                    $col->showHeader($headerRowCount, $headerRowNo);
            }
            echo Ac_Util::mkElement("tr", ob_get_clean(), $this->currentRowAttribs);
        }
        
        $cols = array();
        foreach($this->listColumns() as $colName) {
            $cols[] = $this->getColumn($colName);
        }
        $nCols = count($cols);
        
        $this->currentRecordNo = 0;
        $this->currentRowNo = 0;
        
        while($this->currentRecord = $this->_fetchNextRecord()) {
            
            $rMod = $this->currentRowNo % 2;
            Ac_Decorator::pushModel($this->currentRecord);
            
            $this->currentRowAttribs = array('class' => 'row'.$rMod);
            
            ob_start();
            for ($i = 0; $i < $nCols; $i++) {
                $cols[$i]->showCell($this->currentRecord, $this->currentRecordNo);
                if ($this->currentRowSkip) break;
            }
            $cells = ob_get_clean();
            if (!$this->currentRowSkip) {
                echo Ac_Util::mkElement("tr", $cells,  $this->currentRowAttribs);
                $this->currentRowNo++;
            }
            Ac_Decorator::popModel();
            $this->currentRecordNo++;
            $this->currentRecord = null;
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
     * @return Ac_Model_Data
     */
    function getRecordPrototype() {
        if ($this->_recordPrototype === false) {
            if ($this->recordClass) {
                $this->_recordPrototype = new $this->recordClass();
            } else $this->_recordPrototype = null;
        }
        return $this->_recordPrototype;
    }
    
    function createAutoColumn ($propName) {
        // TODO
    }
    
    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        if ($this->application === false) {
            $this->application = Ac_Application::getDefaultInstance();
        }
        return $this->application;
    }    
    
}

