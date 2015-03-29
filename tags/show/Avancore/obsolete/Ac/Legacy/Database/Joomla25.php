<?php

class Ac_Legacy_Database_Joomla25 extends Ac_Legacy_Database_Joomla15 {
	
    function loadResult() {
        $this->_debugBeforeQuery($this->_db->getQuery());
        $res = $this->_db->loadResult();
        $this->_debugAfterQuery($this->_db->getQuery(), count($res));
        return $res;
    }
    
    function loadResultArray($numInArray = 0) {
        $this->_debugBeforeQuery($this->_db->getQuery());
        $res = $this->_db->loadColumn($numInArray);
        $this->_debugAfterQuery($this->_db->getQuery(), count($res));
        return $res;
    }
    
    function loadAssocList($id = '') {
        $this->_debugBeforeQuery($this->_db->getQuery());
        $res = $this->_db->loadAssocList($id);
        $this->_debugAfterQuery($this->_db->getQuery(), count($res));
        return $res;
    }
    
    function loadObjectList($id = '') {
        $this->_debugBeforeQuery($this->_db->getQuery());
        $res = $this->_db->loadObjectList($id);
        $this->_debugAfterQuery($this->_db->getQuery(), count($res));
        return $res;
    }
    
    function query($query = false) {
        if ($query !== false) $this->_db->setQuery($query);
        $this->_debugBeforeQuery($this->_db->getQuery());
        $res = $this->_db->query();
        $this->_debugAfterQuery($this->_db->getQuery(), false);
        return $res;
    }
    
    function getLastInsertId() {
        return $this->_db->insertid();
    }
    
    function getResultResource($unbuffered = false) {
        $this->_debugBeforeQuery($this->_db->getQuery());
        $res = $this->_db->query();
        $this->_debugAfterQuery($this->_db->getQuery(), false, true);
        return $res;
    }
    
    function fetchObject($resultResource, $className = null) {
        return $className? mysql_fetch_object($resultResource, $className) : mysql_fetch_object($resultResource);
    }
    
    function _pushQuery() {
        $this->_qBuf[] = array($this->_db->getQuery(), null, null);
    }
    
}