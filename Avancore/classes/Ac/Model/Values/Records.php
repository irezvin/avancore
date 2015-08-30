<?php

/**
 * Implements values list from the records
 */
/**
 * Implements values list from the records
 */
class Ac_Model_Values_Records extends Ac_Model_Values {
    
    /**
     * @var Ac_Model_Mapper
     */
    var $_mapper = false;
    
    var $_multiKey = true;
    
    var $mapperClass = false;
    
    var $where = false;
    
    /**
     * Additional map of modelField => destTableField
     * @var array
     */
    var $whereMap = array();
    
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
    
    protected $cachedExistingValues = array();
    
    function __construct ($data, $propName = false, $options = true, $isStatic = false, array $optionsOverride = array()) {
        parent::__construct($data, $propName, $options, $isStatic, $optionsOverride);
        if (!$this->mapperClass) trigger_error ('$mapperClass property must be provided', E_USER_ERROR);
        $this->_mapper = Ac_Model_Mapper::getMapper($this->mapperClass);
        if ($this->titleIsProperty === '?') {
            $this->titleIsProperty = $this->_mapper->isTitleAProperty();
        }
        if ($this->ordering === '?') {
            $this->ordering = $this->_mapper->getDefaultOrdering();
            if ($this->ordering === false && $this->_mapper->getTitleFieldName() && !$this->titleIsProperty) {
                $this->ordering = 't.'.$this->_mapper->getTitleFieldName();
            }
        }
        if ($this->valueFieldName) {
            $this->_multiKey = count($this->valueFieldName) > 1;
        } else {
           $this->_multiKey = false;
        }
    }
    
    function _doDefaultGetValueList() {
        $where = $this->where;
        if ($this->whereMap) {
            $kc = array();
            foreach ($this->whereMap as $src => $dest) {
                if (is_numeric($src)) {
                    $src = $dest;
                }
                $kc[$dest] = $this->data->getField($src);
            }
            $sKc = $this->_mapper->getDb()->valueCriterion($kc);
            if (strlen($where)) {
                $where = "($where) AND ({$sKc})";
            } else {
                $where = $sKc;
            }
        }
        $ttls = $this->_mapper->getRecordTitles($where, $this->ordering, $this->extraJoins, $this->titleFieldName, $this->titleIsProperty, $this->valueFieldName, $this->valueIsProperty);
        $res = array();
        if (is_array($ttls)) {
            if ($this->_multiKey) {
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
        $this->_mapper = Ac_Model_Mapper::getMapper($this->mapperClass);        
    }
    
    function getCaption($value) {
        $this->_cachedValueList = $this->getValueList ();
        return parent::getCaption($value);
    }
    
    /**
     * Removes all values not in the list. Default implementation filters out all non-scalar values
     * @param array $values List of values to check
     * @return array Array without improper values
     */
    function filterValuesArray(array $values) {
        $res = array();
        if ($this->cache && is_array($this->_cachedValueList)) {
            $res = parent::filterValuesArray($values);
        } else {
            foreach ($values as $v) {
                if (is_scalar(($v))) $res[] = $v;
            }
            $valuesToCheck = $res;
            if ($this->cache && $this->cachedExistingValues) {
                foreach ($valuesToCheck as $i => $v) {
                    if (isset($this->cachedExistingValues[$v])) {
                        if (!$this->cachedExistingValues[$v]) unset($res[$i]);
                        unset($valuesToCheck[$i]);
                    }
                }
            }
            if ($valuesToCheck) {
                $m = $this->_mapper;
                if ($m->hasAllRecords()) {
                    $existing = $m->getAllRecords($valuesToCheck);
                    if ($this->valueFieldName) $existing = Ac_Util::indexArray($existing, $this->valueFieldName, true);
                } else {
                    $db = $m->getDb();
                    $tmpWhere = $this->where;
                    $tmpList = $this->_cachedValueList;
                    if (!$this->valueIsProperty)  {
                        if (!$this->valueFieldName) $k = $m->pk;
                            else $k = $this->valueFieldName;
                        $crit = $db->n(array('t', $k), false).$db->eqCriterion($valuesToCheck);
                        if (strlen($this->where)) 
                            $this->where = '('.$this->where.') AND '.$crit;
                        else 
                            $this->where = $crit;
                    }
                    $existing = $this->_doDefaultGetValueList();
                    $this->where = $tmpWhere;
                    $this->_cachedValueList = $tmpList;
                }
                foreach ($valuesToCheck as $i => $v) {
                    $has = isset($existing[$v]);
                    $this->cachedExistingValues[$v] = $has;
                    if (!$has) {
                        unset($res[$i]);
                    }
                }
            }
        }
        return $res;
    }
    
    protected function resetCacheOnDataChange($oldData, $newData) {
        if (($oldData !== $newData) && $this->whereMap) $this->_resetCache();
    }
    
    function _doDefaultCheck($value) {
        $res = count($this->filterValuesArray(array($value)));
        return $res;
    }
    
    function _resetCache() {
        parent::_resetCache();
        $this->cachedExistingValues = array();
    }
    
}

