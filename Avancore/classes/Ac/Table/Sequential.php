<?php

class Ac_Table_Sequential extends Ac_Table {
    
    /**
     * @var Ac_Model_Collection
     */
    var $_collection = false;
    
    var $_eof = false;
    var $_ptr = 0;

    function Ac_Table_Sequential ($columnSettings, $collection, $pageNav, $tableAttribs = array(), $recordClass = false) {
        $this->_columnSettings = $columnSettings;
        $this->_collection = $collection;
        $this->_pageNav = $pageNav;
        $this->tableAttribs = $tableAttribs;
        $this->recordClass = $recordClass;
        if (!isset($this->tableAttribs['class'])) $this->tableAttribs['class'] = 'adminlist';
    }
 
    function resetState() {
        parent::resetState();
        $coll = $this->_collection;
        $coll->rewind();
    }
    
    /**
     * @return Ac_Model_Object
     */
    function _fetchNextRecord() {
        if ($this->_ptr >= count($this->_records)) {
            $res = false;
            if (!$this->_eof) {
		        $res = $this->_collection->getNext();
		        if ($res) $this->_records[] = $res;
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
            while ($rec = $this->_collection->getNext()) {
                $this->_records[] = $rec;
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
        $res = parent::getRecord($key);
        return $res;
    }
    
}

