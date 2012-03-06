<?php

/**
 * Implements values list from the records
 */
/**
 * Implements values list from the records
 */
class Ae_Model_Values_Records extends Ae_Model_Values {
    
    /**
     * @var Ae_Model_Mapper
     */
    var $_mapper = false;
    
    var $_cpk = true;
    
    var $mapperClass = false;
    
    var $where = false;
    
    var $ordering = '?';
    
    var $extraJoins = false;
    
    var $titleFieldName = false;
    
    var $titleIsProperty = '?';
    
    var $valueFieldName = false;
    
    var $valueIsProperty = false;
    
    /**
     * If primary key is compound, return md5 as value
     */
    var $md5pk = true;
    
    function Ae_Model_Values_Records (& $data, $propName = false, $options = true, $isStatic = false) {
        parent::Ae_Model_Values($data, $propName, $options);
        if (!$this->mapperClass) trigger_error ('$mapperClass property must be provided', E_USER_ERROR);
        $this->_mapper = & Ae_Model_Mapper::getMapper($this->mapperClass);
        if ($this->ordering === '?') {
            $this->ordering = $this->_mapper->getDefaultOrdering();
            if ($this->ordering === false && $this->_mapper->getTitleFieldName()) {
                $this->ordering = 't.'.$this->_mapper->getTitleFieldName();
            }
        }
        if ($this->valueFieldName) {
            $this->_cpk = count($this->valueFieldName) > 1;
        } else {
           $this->_cpk = count($this->_mapper->listPkFields()) > 1;
        }
    }
    
    function _doDefaultGetValueList() {
        $ttls = $this->_mapper->getRecordTitles($this->where, $this->ordering, $this->extraJoins, $this->titleFieldName, $this->titleIsProperty, $this->valueFieldName, $this->valueIsProperty);
        //    if ($this->tableName === '#__element_versions')
        
        $res = array();
        if (is_array($ttls)) {
            if ($this->_cpk) {
                if ($this->md5pk) {
                    foreach ($ttls as $ttl) {
                        $res[md5(implode("-", $ttl[0]))] = $ttl[1];             
                    }
                } else {
                    foreach ($ttls as $ttl) {
                        $res[implode("-", $ttl[0])] = $ttl[1];              
                    }
                }
            } else {
                foreach ($ttls as $ttl) {
                    $res[$ttl[0]] = $ttl[1];                
                }
            }
        }
        
        if ($this->cache) $this->_cachedValueList = $res;
        return $res;
    }

    function __sleep() {
        $this->_mapper = false;
        return array_keys(get_object_vars($this));
    }
    
    function __wakeup() {
        $this->_mapper = & Ae_Model_Mapper::getMapper($this->mapperClass);        
    }
    
    // TODO: make getCaption() and check() methods
    
}

?>