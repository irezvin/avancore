<?php

/**
 * Locates items with given identifiers. 
 * If setMapper() first, the identifiers will be retrieved using $this->getMapper()->getIdentifier($rec),
 * otherwise using $rec->getIdentifier(). In general, setMapper() is recommended for more optimal approach.
 * If setAreByIds(true), filter($records) will assume that keys of $records is a list of identifiers.
 */
class Ac_Model_Criterion_Identifier extends Ac_Model_Criterion_FieldEquals implements Ac_I_Search_Criterion_Bulk {
    
    protected $prepared = false;

    /**
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;

    /**
     * @var bool
     */
    protected $areByIds = false;
    
    protected $useIdGetter = false;
    
    protected $strict = false;
    
    function test($record, $name, $value, $adHoc) {
        if (!$this->prepared) $this->prepare();
        $res = parent::test($record, $name, $value, $adHoc);
        return $res;
    }
    
    protected function prepare() {
        $this->prepared = true;
        $idF = false;
        if ($this->mapper) {
            $idF = $this->mapper->getIdentifierField();
            if (!strlen($idF))
                $idF = $this->mapper->getIdentifierPublicField();
        }
        if (strlen($idF)) {
            $this->field = $idF;
            // won't use getFieldValue, will use one of record's fields
            $this->useGFV = false;
            $this->useIdGetter = false;
        } else {
            // will use getFieldValue
            $this->useGFV = true;
            $this->useIdGetter = true;
        }
    }
    
    protected function getFieldValue($record, $field) {
        if ($this->useIdGetter) 
            $res = $this->mapper? $this->mapper->getIdentifier($record) : $record->getIdentifier();
        else 
            $res = parent::getFieldValue($record, $field);
        return $res;
    }

    function setField($field) {
        trigger_error(__METHOD__." has no effect", E_USER_NOTICE);
    }

    function setStrict($strict) {
        trigger_error(__METHOD__." has no effect", E_USER_NOTICE);
    }

    function filter (array $records, $name, $value, $adHoc) {
        if (!$this->prepared) $this->prepare();
        if ($this->valueIsSet) {
            $value = $this->value;
        } else {
            if ($adHoc) throw new Ac_E_InvalidUsage("Please setValue() before using test() when applying ".get_class($this)." \$adHoc");
        }
        if ($this->areByIds) { // keys are IDs
            $value = array_unique(Ac_Util::toArray($value));
            $kk = array_flip($value);
            $res = array_intersect_key($records, $kk);
        } else {
            $res = array();
            $tmp = array($this->value, $this->valueIsSet);
            $this->value = $value;
            $this->valueIsSet = true;
            foreach ($records as $k => $v) {
                if (parent::test($v, null, $value, false)) {
                    $res[$k] = $v;
                }
            }
            list($this->value, $this->valueIsSet) = $tmp;
        }
        return $res;
    }

    function setMapper(Ac_Model_Mapper $mapper = null) {
        $this->mapper = $mapper;
        $this->prepared = false;
    }

    /**
     * @return Ac_Model_Mapper
     */
    function getMapper() {
        return $this->mapper;
    }

    /**
     * @param bool $areByIds
     */
    function setAreByIds($areByIds) {
        $this->areByIds = $areByIds;
    }

    /**
     * @return bool
     */
    function getAreByIds() {
        return $this->areByIds;
    }    
    
    
}