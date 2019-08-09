<?php

class Ac_Model_Typer_ExtraTable extends Ac_Model_Typer_Abstract {
    
    /**
     * field in the rows that contains record identifiers
     * @var string
     */
    protected $objectTypeField = false;

    /**
     * typeId to use instead of value in objectTypeField
     * @var string
     */
    protected $uniformTypeId = false;

    /**
     * allow mappers outside of $typeMap (mapper ID matches typeId)
     * @var bool
     */
    protected $detectMappers = true;

    /**
     * try to find mixables if they are not provided in $mixables
     * @var bool
     */
    protected $detectMixables = true;

    /**
     * $tableName will be used to automatically detect the mixables in the
     * concrete mappers, if they are not provided
     */
    protected $tableName = false;
    
    /**
     * Mixables are used to
     * -    detect mappers if we didn't that already
     * -    detect column maps
     * -    pre-populate rows to avoid loading of already requested data
     * 
     * @var array $typeId => $extraTableMixableIdOrExtraMixable
     */
    protected $extraMixables = array();

    /**
     * @var array $typeId => $mapperClass
     */
    protected $typeMap = array();
    
    protected $myBaseClass = 'Ac_Model_Typer_ExtraTable';
    
    function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    function getTableName() {
        return $this->tableName;
    }   
    
    function setExtraMixables (array $extraMixables = array(), $override = false) {
        if ($override && ($i = array_intersect_key($this->extraMixables, $extraMixables))) {
            throw Ac_E_InvalidCall::alreadySuchItem('extraMixables', implode(', ', array_keys($i)));
        }
        foreach ($extraMixables as $typeId => $mixable) {
            if (is_object($mixable)) {
                if ($mixable instanceof Ac_Model_Mixable_ExtraTable) {
                    $this->typeMap[$typeId] = $mixable->getMixin();
                } else {
                    throw Ac_E_InvalidCall::wrongType("\$extraMixables['{$typeId}']", $mixable, 'Ac_Model_Mixable_ExtraTable');
                }
            }
        }
        if (!$override) $this->extraMixables = $extraMixables;
        else {
            foreach ($extraMixables as $k => $v) {
                $this->extraMixables[$k] = $v;
            }
        }
        $this->extraMixables = $extraMixables;
    }
    
    /**
     * @return array
     */
    function getExtraMixables() {
        return $this->extraMixables;
    }
    
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
        $extra = $this->getExtraTable($typeId, $mapper);
        if (!$extra) throw new Ac_E_InvalidUsage("Cannot retrieve ExtraTable mixable for type '{$typeId}'");
        $colMap = $extra->getColMap();
        // now we need to create proper criteria for Mapper
        $myCols = array_keys($colMap);
        $mapperCols = array_values($colMap);
        $extra->pushPreloadedRows($rows);
        $crit = $extra->getExtraLinkCrit();
        $vv = array();
        if (count($myCols) == 1) {
            $myCol = $myCols[0];
            foreach ($rows as $k => $r) {
                $vv[$k] = $r[$myCol];
            }
            if (strlen($crit)) $query = array($crit => $vv);
            $query = array($mapperCols[0] => $vv);
        } else {
            $mc = array_flip($mapperCols);
            foreach ($rows as $k => $r) {
                foreach ($mapperCols as $c) $vv[$k][] = $r[$c];
            }
            if (strlen($crit)) $query = array($crit => $vv);
            else {
                $query = array(
                    implode('_', $mapperCols) => new Ac_Model_Criterion_MultiField(array(
                        'fields' => $mapperCols,
                        'values' => $vv,
                    ))
                );
            }
        }
        $unsorted = $mapper->find($query);
        $res = array();
        // now we have to sort the rows back to get the same order as in $rows
        if (count($myCols) == 1) {
            $byFk = Ac_Util::indexArray($unsorted, $mapperCols[0], true);
            foreach ($vv as $k => $v)
                if (isset($byFk[$v])) $res[$k] = $byFk[$v];
        } else { // crude, but this case should occur quite rare
            $map = array();
            $orig = $vv;
            foreach ($unsorted as $uk => $obj) {
                $pattern = array_values(Ac_Util::getObjectProperty($obj, $mapperCols));
                foreach ($orig as $k => $keys) if ($pattern == $keys) {
                    $map[$k] = $uk;
                    unset($orig[$k]);
                }
                foreach (array_keys($vv) as $k) {
                    if (isset($map[$k])) $res[$k] = $unsorted[$map[$k]];
                }
            }
        }
        $extra->popPreloadedRows($rows);
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
                $this->typeMap[$typeId] = $this->getApplication()->getMapper($this->typeMap[$typeId]);
            }
            $res = $this->typeMap[$typeId];
        } else {
            if ($this->detectMappers) {
                if (($m = $this->getApplication()->getMapper($typeId, true))) {
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
    
    /**
     * @param type $typeId
     * @param Ac_Model_Mapper $mapper
     * @return Ac_Model_Mapper_Mixable_ExtraTable
     */
    protected function getExtraTable($typeId, Ac_Model_Mapper $mapper) {
        $res = null;
        if (isset($this->extraMixables[$typeId])) {
            if (is_string($this->extraMixables[$typeId])) {
                $this->extraMixables[$typeId] = $mapper->getMixable($typeId);
            }
            $res = $this->extraMixables[$typeId];
        } elseif ($this->detectMixables) {
            if ($this->objectTypeField !== false || $this->tableName !== false) {
                foreach ($mapper->listMixables('Ac_Model_Mapper_Mixable_ExtraTable') as $i) {
                    $mix = $mapper->getMixable($i);
                    $ok = true;
                    if ($this->objectTypeField !== false && $mix->getObjectTypeField() !== $this->objectTypeField)
                        $ok = false;
                    if ($ok && $this->tableName !== false && $mix->getTableName() !== $this->tableName) {
                        $ok = false;
                    }
                    if ($ok) {
                        $res = $this->extraMixables[$typeId] = $mix;
                    }
                }
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
        if ($this->objectTypeField !== false && $this->uniformTypeId !== false) {
            trigger_error("Setting both \$objectTypeField and \$uniformTypeId doesn't make much sense; \$uniformTypeId is ignored", E_USER_NOTICE);
        }
        if ($this->objectTypeField !== false) {
            $res = array();
            foreach ($rows as $k => $r) {
                $res[$r[$this->objectTypeField]][$k] = $r;
            }
        } elseif ($this->uniformTypeId !== false) {
            $res = array($this->uniformTypeId => $rows);
        } else {
            throw new Ac_E_InvalidUsage("Either \$objectTypeField or \$uniformTypeId must be set");
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

    /**
     * Sets typeId to use instead of value in objectTypeField
     * @param string $uniformTypeId
     */
    function setUniformTypeId($uniformTypeId) {
        if (!strlen($uniformTypeId)) $uniformTypeId = false;
        $this->uniformTypeId = $uniformTypeId;
    }

    /**
     * Returns typeId to use instead of value in objectTypeField
     * @return string
     */
    function getUniformTypeId() {
        return $this->uniformTypeId;
    }
    
    function onGetIdentifier(Ac_Model_Object $record, &$result) {
        // TODO: unfuck this mess
        if ($this->mapperHandlerEnabled && ($recordMapper = $record->getMapper()) !== $this->mixin) {
            foreach ($this->typeMap as $typeId => $mapper) {
                if (!is_object($mapper)) $mapper = $this->getMapper($typeId);
                if ($mapper === $recordMapper) {
                    $tMix = $this->getExtraTable($typeId, $mapper);
                    if ($tMix && $rMixId = $tMix->getModelMixableId()) {
                        $field = $this->mixin->getRowIdentifierField();
                        $result = $record->getMixable($rMixId)->{$field};
                    }
                }
            }
        }
    }
    
}