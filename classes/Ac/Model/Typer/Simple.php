<?php

class Ac_Model_Typer_Simple extends Ac_Model_Typer_Abstract {
    
    /**
     * field in the rows that contains record identifiers
     * @var string
     */
    protected $objectTypeField = false;

    /**
     * allow mappers outside of $typeMap (mapper ID matches typeId)
     * @var bool
     */
    protected $detectMappers = true;

    protected $detectMixables = true;

    /**
     * @var array $typeId => $mapperClass
     */
    protected $typeMap = array();
    
    protected $myBaseClass = 'Ac_Model_Typer_Simple';
    
    function setTypeMap(array $typeMap, $override = false) {
        if ($override && ($i = array_intersect_key($this->typeMap, $typeMap))) {
            throw Ac_E_InvalidCall::alreadySuchItem('typeMap', implode(', ', array_keys($i)));
        }
        foreach ($typeMap as $typeId => $mapper) {
            if (is_object($mapper) && !$mapper instanceof Ac_Model_Mapper) {
                throw Ac_E_InvalidCall::wrongType("\$typeMap['{$typeId}']", $mapper, 'Ac_Model_Mapper');
            }
        }
        if (!$override) $this->typeMap = $typeMap;
        else {
            foreach ($typeMap as $k => $v) {
                $this->typeMap[$k] = $v;
            }
        }
        $this->typeMap = $typeMap;
    }
    
    /**
     * @return array
     */
    function getTypeMap() {
        return $this->typeMap;
    }
    
    protected function instantiateSubset(array $rows, $typeId) {
        $mapper = $this->getMapper($typeId);
        if (!$mapper) throw new Ac_E_InvalidUsage("Cannot retrieve Mapper for type '{$typeId}'");
        $res = $mapper->loadFromRows($rows);
        return $res;
    }

    /**
     * @return Ac_Model_Mapper
     */
    public function getMapper($typeId) {
        if (isset($this->typeMap[$typeId])) {
            if (!$this->typeMap[$typeId] instanceof Ac_Model_Mapper) {
                if (is_object($this->typeMap[$typeId])) 
                    throw new Ac_E_InvalidUsage("\$typeMap['{$typeId}'] must be either a Mapper identifier or Ac_Model_Mapper, but ".Ac_Util::typeClass ($typeMap[$typeId])." is provided");
                $this->typeMap[$typeId] = $this->getApp()->getMapper($this->typeMap[$typeId]);
            }
            $res = $this->typeMap[$typeId];
        } else {
            if ($this->detectMappers) {
                if (($m = $this->getApp()->getMapper($typeId, true))) {
                    $res = $this->typeMap[$typeId] = $m;
                } else {
                    $res = null;
                }
            } else {
                $res = null;
            }
        }
        return $res;
    }
    
    public function getRecordTypeId(Ac_Model_Object $record) {
        if ($this->objectTypeField !== false) {
            $res = $record->{$this->objectTypeField};
        } elseif ($this->uniformTypeId !== false) {
            $res = $this->uniformTypeId;
        } else {
            throw new Ac_E_InvalidUsage("Either \$objectTypeField or \$uniformTypeId must be set");
        }
        if ($this->objectTypeField !== false && $this->uniformTypeId !== false) {
            trigger_error("Setting both \$objectTypeField and \$uniformTypeId doesn't make much sense; \$uniformTypeId is ignored", E_USER_NOTICE);
        }
    }
    
    public function getRowTypeId($row) {
        if ($this->objectTypeField !== false) {
            $res = $row[$this->objectTypeField];
        } elseif ($this->uniformTypeId !== false) {
            $res = $this->uniformTypeId;
        } else {
            throw new Ac_E_InvalidUsage("Either \$objectTypeField or \$uniformTypeId must be set");
        }
        if ($this->objectTypeField !== false && $this->uniformTypeId !== false) {
            trigger_error("Setting both \$objectTypeField and \$uniformTypeId doesn't make much sense; \$uniformTypeId is ignored", E_USER_NOTICE);
        }
        return $res;
    }
    
    protected function classifyRows(array $rows) {
        if ($this->objectTypeField === false) 
            throw new Ac_E_InvalidUsage("Cannot classifyRows() without prior setObjectTypeField()");
        $res = array();
        foreach ($rows as $k => $r) {
            $res[$r[$this->objectTypeField]][$k] = $r;
        }
        return $res;
    }

    /**
     * Sets allow mappers outside of $typeMap (mapper ID matches typeId)
     * @param bool $detectMappers
     */
    function setDetectMappers($detectMappers) {
        $this->detectMappers = (bool) $detectMappers;
    }

    /**
     * Returns allow mappers outside of $typeMap (mapper ID matches typeId)
     * @return bool
     */
    function getDetectMappers() {
        return $this->detectMappers;
    }

    /**
     * Sets try to find mixables if they are not provided in $mixables
     * @param bool $detectMixables
     */
    function setDetectMixables($detectMixables) {
        $this->detectMixables = (bool) $detectMixables;
    }

    /**
     * Returns try to find mixables if they are not provided in $mixables
     * @return bool
     */
    function getDetectMixables() {
        return $this->detectMixables;
    }

    /**
     * Sets field in the rows that contains record identifiers
     * @param string $objectTypeField
     */
    function setObjectTypeField($objectTypeField) {
        if (!strlen($objectTypeField)) $objectTypeField = false;
        $this->objectTypeField = $objectTypeField;
    }

    /**
     * Returns field in the rows that contains record identifiers
     * @return string
     */
    function getObjectTypeField() {
        return $this->objectTypeField;
    }

}
