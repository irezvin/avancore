<?php

class Ac_Table_Sequential extends Ac_Table {
    
    /**
     * @var Ac_Model_Collection
     */
    var $_collection = false;
    
    var $_eof = false;
    var $_ptr = 0;

    function Ac_Table_Sequential ($columnSettings, & $collection, & $pageNav, $tableAttribs = array(), $recordClass = false) {
        $this->_columnSettings = $columnSettings;
        $this->_collection = & $collection;
        $this->_pageNav = & $pageNav;
        $this->tableAttribs = $tableAttribs;
        $this->recordClass = $recordClass;
        if (!isset($this->tableAttribs['class'])) $this->tableAttribs['class'] = 'adminlist';
    }
    
    function show() {
        echo "<table ".Ac_Util::mkAttribs($this->tableAttribs)." >";
        
        $headerRowCount = $this->getHeaderRowCount();
        
        foreach(range(0, $headerRowCount - 1) as $headerRowNo) {
            echo "<tr>"; 
        
            foreach($this->listColumns() as $colName) {
                $col = & $this->getColumn($colName);
                if ($headerRowNo < $col->getHeaderRowCount()) 
                    $col->showHeader($headerRowCount, $headerRowNo);
            }
            
            echo "</tr>";
        }
        
        $row = 0;
        
        $cols = array();
        foreach($this->listColumns() as $colName) {
            $cols[] = & $this->getColumn($colName);
        }
        $nCols = count($cols);
        
        $coll = & $this->_collection;
        $coll->rewind();
        
        while($record = & $this->_fetchNextRecord()) {
            $rMod = $row % 2;
            $trAttribs = array('class' => 'row'.$rMod);
            echo "<tr ".Ac_Util::mkAttribs($this->_trAttribs($record, $trAttribs)).">"; 
            for ($i = 0; $i < $nCols; $i++) {
                $cols[$i]->showCell($record, $row);
            }
            echo "</tr>";
            
            $row++;
        }
        
        echo "</table>";
    }
    
    /**
     * @return Ac_Model_Object
     */
    function & _fetchNextRecord() {
        if ($this->_ptr >= count($this->_records)) {
            $res = false;
            if (!$this->_eof) {
		        $res = & $this->_collection->getNext();
		        if ($res) $this->_records[] = & $res;
		            else $this->_eof = true;
            }            
        } else {
            $r = array_slice($this->_records, $this->_ptr, 1, false);
            $res = $r[0];
        }
        $this->_ptr++;
        return $res;
    }
    
    function _fetchAll() {
        if (!$this->_eof) {
            while ($rec = & $this->_collection->getNext()) {
                $this->_records[] = & $rec;
                unset($rec);
            }            
            $this->_eof = true;
        }
    }
    
    function countRecords() {
        return $this->_collection->countRecords();
    }

    function listRecords() {
        if (!$this->_eof) $this->_fetchAll();
        return parent::listRecords();
    }
    
    function getRecord($key) {
        if (!$this->_eof) $this->_fetchAll();
        $res = & parent::getRecord($key);
        return $res;
    }
    
}

?>