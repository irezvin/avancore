<?php

/**
 * Implements values list from the records
 */
/**
 * Implements values list from the records
 * @deprecated
 * 
 * Use Ac_Model_Values_Mapper instead
 */
class Ac_Model_Values_Records extends Ac_Model_Values {
    
    /**
     * @var Ac_Model_Mapper
     */
    var $mapper = false;
    
    var $multiKey = true;
    
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
    
    function __construct (array $prototype = array()) {
        parent::__construct($prototype);
        if (!$this->mapperClass) trigger_error ('$mapperClass property must be provided', E_USER_ERROR);
        $this->mapper = Ac_Model_Mapper::getMapper($this->mapperClass);
        if (!strlen($this->titleFieldName)) {
            $this->titleFieldName = $this->mapper->getTitleFieldName();
        }
        if ($this->titleIsProperty === '?') {
            if (method_exists($this->mapper, 'isTitleAProperty')) {
                $this->titleIsProperty = $this->mapper->isTitleAProperty();
            } else {
                $this->titleIsProperty = !in_array($this->titleFieldName, $this->mapper->listDataProperties());
            }
        }
        if ($this->ordering === '?') {
            if (method_exists($this->mapper, 'getDefaultOrdering')) {
                $this->ordering = $this->mapper->getDefaultOrdering();
                if ($this->ordering === false && $this->mapper->getTitleFieldName() && !$this->titleIsProperty) {
                    $this->ordering = 't.'.$this->mapper->getTitleFieldName();
                }
            } else {
                $this->ordering = false;
            }
        }
        if ($this->valueFieldName) {
            $this->multiKey = count($this->valueFieldName) > 1;
        } else {
           $this->multiKey = false;
        }
    }

    function hasPublicVars() {
        return true;
    }
    
    protected function doDefaultGetValueList() {
        $where = $this->where;
        if ($this->whereMap) {
            $kc = array();
            foreach ($this->whereMap as $src => $dest) {
                if (is_numeric($src)) {
                    $src = $dest;
                }
                $kc[$dest] = $this->data->getField($src);
            }
            $sKc = $this->mapper->getDb()->valueCriterion($kc);
            if (strlen($where)) {
                $where = "($where) AND ({$sKc})";
            } else {
                $where = $sKc;
            }
        }
        $ttls = $this->getRecordTitles($where, $this->ordering, $this->extraJoins, $this->titleFieldName, $this->titleIsProperty, $this->valueFieldName, $this->valueIsProperty);
        $res = array();
        if (is_array($ttls)) {
            if ($this->multiKey) {
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
        
        if ($this->cache) $this->cachedValueList = $res;
        return $res;
    }
    
    
    /**
     * @deprecated
     * @see Ac_Model_Mapper::getTitles
     * @return array (array($pk1, $title1), array($pk2, $title2), ...)
     */
    protected function getRecordTitles($where = false, $ordering = false, $extraJoins = false, $titleFieldName = false, $titleIsProperty = '?', $valueFieldName = false, $valueIsProperty = false) {
        
        $mapper = $this->mapper;
        $db = $mapper->getDb();
        
        if ($titleFieldName === false) {
            $titleFieldName = $mapper->getTitleFieldName();
        }
        if ($titleIsProperty === '?') $titleIsProperty = $mapper->isTitleAProperty();
        if (!$titleFieldName) {
            $titleFieldName = $mapper->getStorage()->getPrimaryKey();
        }
        $qpkf = array();
        if ($valueFieldName === false)
            $qpkf[] = $db->n('t').'.'.$db->n($mapper->getStorage()->getPrimaryKey());
        else {
            $vf = $valueFieldName;
            foreach (Ac_Util::toArray($vf) as $pkf) $qpkf[] = $db->n('t').'.'.$db->n($pkf);
        }
        $spk = count($qpkf) == 1;
        $qpkf = implode(", ", $qpkf);
        $res = array();
        if (!$titleIsProperty && !$valueIsProperty) {
            $sql = "SELECT DISTINCT t.".$titleFieldName." AS _title_, ".$qpkf." FROM ".$db->n($mapper->tableName)." AS t";
            if ($extraJoins) $sql .= " ".$extraJoins;
            if ($where) $sql .= " WHERE ".$where;
            if ($ordering) $sql .= " ORDER BY ".$ordering;
            foreach ($db->fetchArray($sql) as $row) {
                $title = $row['_title_'];
                $pk = Ac_Util::array_values(array_slice($row, 1));
                if ($spk) $pk = $pk[0];
                $res[] = array($pk, $title);
            }
        } else {
            $coll = new Ac_Model_Collection(get_class($mapper), false, $where, $ordering, $extraJoins);
            $coll->setSequential();
            $coll->useCursor();
            while ($rec = $coll->getNext()) {
                if ($valueFieldName === false) $pk = $rec->getPrimaryKey();
                else {
                    if (is_array($valueFieldName)) {
                        $pk = array();
                        if ($valueIsProperty) foreach ($vf as $f) $pk[] = $rec->getField($f);
                            else foreach ($vf as $f) $pk[] = $rec->$f;
                    } else {
                        $pk = $valueIsProperty? $rec->getField($valueFieldName) : $rec->{$valueFieldName};
                    }
                }
                $title = $rec->getField($titleFieldName);
                $res[] = array($pk, $title);
            }
        }
        return $res;
    }
    

    protected function _sleep() {
        $this->mapper = false;
        return array_keys(get_object_vars($this));
    }
    
    protected function _wakeup() {
        $this->mapper = Ac_Model_Mapper::getMapper($this->mapperClass);        
    }
    
    function getCaptions(array $values) {
        $this->cachedValueList = $this->getValueList ();
        return parent::getCaptions($values);
    }
    
    /**
     * Removes all values not in the list. Default implementation filters out all non-scalar values
     * @param array $values List of values to check
     * @return array Array without improper values
     */
    function filterValuesArray(array $values) {
        $res = array();
        if ($this->cache && is_array($this->cachedValueList)) {
            $res = parent::filterValuesArray($values);
        } else {
            $res = $valuesToCheck = array_values($values);
            $db = $this->mapper->getDb();
            if ($this->cache && $this->cachedExistingValues) {
                foreach ($valuesToCheck as $i => $v) {
                    if (isset($this->cachedExistingValues[$v])) {
                        if (!$this->cachedExistingValues[$v]) unset($res[$i]);
                        unset($valuesToCheck[$i]);
                    }
                }
            }
            if ($valuesToCheck) {
                $m = $this->mapper;
                if ($m->hasAllRecords()) {
                    $existing = $m->getAllRecords($valuesToCheck);
                    if ($this->valueFieldName) $existing = Ac_Util::indexArray($existing, $this->valueFieldName, true);
                } else {
                    $db = $m->getDb();
                    $tmpWhere = $this->where;
                    $tmpList = $this->cachedValueList;
                    if (!$this->valueIsProperty)  {
                        if (!$this->valueFieldName) $k = $m->pk;
                            else $k = $this->valueFieldName;
                        $crit = $db->n(array('t', $k), false).$db->eqCriterion($valuesToCheck);
                        if (strlen($this->where)) 
                            $this->where = '('.$this->where.') AND '.$crit;
                        else 
                            $this->where = $crit;
                    }
                    $existing = $this->doDefaultGetValueList();
                    $this->where = $tmpWhere;
                    $this->cachedValueList = $tmpList;
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
        if (($oldData !== $newData) && $this->whereMap) $this->resetCache();
    }
    
    protected function doDefaultCheck($value) {
        $res = count($this->filterValuesArray(array($value)));
        return $res;
    }
    
    function resetCache() {
        parent::resetCache();
        $this->cachedExistingValues = array();
    }
    
}

