<?php

class Ac_Model_Mapper_Mixable_ExtraTable extends Ac_Mixable {

    protected $myBaseClass = 'Ac_Model_Mapper_Mixable_ExtraTable';
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mixin = false;
    
    /**
     * Name of extra table
     * @var string
     */
    protected $tableName = false;

    /**
     * Mapping of tableColName => slaveFieldNames
     * @var array
     */
    protected $fieldNames = array();

    /**
     * Reference mapping: extraTableColName => modelObjectColName
     * @var array
     */
    protected $colMap = array();

    /**
     * If extra records should be deleted when owners are deleted
     * @var bool
     */
    protected $deleteWithOwner = true;
    
    /**
     * Base class of mixin mapper (if other, error will be triggered during mixing)
     * @var string
     */
    protected $mapperBaseClass = 'Ac_Model_Mapper';
    
    /**
     * Class or prototype of model mixin
     * @var type 
     */
    protected $modelMixable = false;

    /**
     * ID of model mixable to prefix conflicting properties during hydration
     * (will be used when $overwriteModelFields === false)
     */
    protected $modelMixableId = false;
    
    /**
     * @var Ac_Model_Relation
     */
    protected $implRelation = false;

    /**
     * @var Ac_Model_Mapper
     */
    protected $implMapper = false;
    
    protected $defaults = false;
    
    /**
     * If extra is referenced by the owner and should be saved first
     * @var bool
     */
    protected $extraIsReferenced = false;
    
    /**
     * @var array SQL column => value
     */
    protected $restrictions = array();

    /**
     * pre-populated rows from the extra table - in case when we already have them before loading main records
     */
    protected $preloadedRows = array();
    
    /**
     * old values of $preloadedRows (saved between pushPreloadedRows() and popPreloadedRows() calls
     */
    protected $prStack = array();
    
    /**
     * Number of hits of \$preloadedRows
     * @var int
     */
    protected $preloadedHits = 0;
    
    protected $lastPreloadedHits = 0;
    
    /**
     * @var bool
     */
    protected $overwriteModelFields = false;
    
    protected $objectTypeField = false;
    /**
     * name of mapper criterion that links to the extra table
     * @var string
     */
    protected $extraLinkCrit = false;

    function setTableName($tableName) {
        if ($tableName !== $this->tableName) {
            $this->tableName = $tableName;
            if ($this->implMapper) {
                if ($this->tableName && $this->implMapper->tableName !== $this->tableName) {
                    throw new Ac_E_InvalidUsage("\$tableName, if set, must not be different from \$implMapper->tableName");
                }
            }
        }
    }

    function getTableName() {
        if ($this->implMapper) return $this->getImplMapper()->tableName;
        return $this->tableName;
    }

    function setFieldNames(array $fieldNames) {
        $this->fieldNames = $fieldNames;
    }

    /**
     * @return array
     */
    function getFieldNames() {
        return $this->fieldNames;
    }

    function setColMap(array $colMap) {
        $this->colMap = $colMap;
    }

    /**
     * @return array
     */
    function getColMap() {
        return $this->colMap;
    }

    /**
     * @param bool $deleteWithOwner
     */
    function setDeleteWithOwner($deleteWithOwner) {
        $this->deleteWithOwner = $deleteWithOwner;
    }

    /**
     * @return bool
     */
    function getDeleteWithOwner() {
        return $this->deleteWithOwner;
    }

    function setModelMixable($modelMixable) {
        $this->modelMixable = $modelMixable;
    }

    function getModelMixable() {
        return $this->modelMixable;
    }    
    
    /**
     * Sets ID of model mixable to prefix conflicting properties during hydration
     * (will be used when $overwriteModelFields === false)
     */
    function setModelMixableId($modelMixableId) {
        $this->modelMixableId = $modelMixableId;
    }

    /**
     * Returns ID of model mixable to prefix conflicting properties during hydration
     * (will be used when $overwriteModelFields === false)
     */
    function getModelMixableId() {
        return $this->modelMixableId;
    }    
 
    function setMapperBaseClass($mapperBaseClass) {
        $this->mapperBaseClass = $mapperBaseClass;
    }

    function getMapperBaseClass() {
        return $this->mapperBaseClass;
    }
    
    function setRestrictions(array $restrictions) {
        $this->restrictions = $restrictions;
    }

    /**
     * @return array
     */
    function getRestrictions() {
        $res = $this->restrictions;
        if (strlen($this->objectTypeField) && $this->mixin) {
            $res[$this->objectTypeField] = $this->mixin->getId();
        }
        return $res;
    }    

    /**
     * @param bool $extraIsReferenced
     */
    function setExtraIsReferenced($extraIsReferenced) {
        $this->extraIsReferenced = $extraIsReferenced;
    }

    /**
     * @return bool
     */
    function getExtraIsReferenced() {
        return $this->extraIsReferenced;
    }    
    
    /**
     * Field in restrictions that will be set to mapper Id (FALSE to ignore this feature)
     * @param string $objectTypeField
     */
    function setObjectTypeField($objectTypeField) {
        $this->objectTypeField = $objectTypeField;
    }

    /**
     * Field in restrictions that will be set to mapper Id (FALSE to ignore this feature)
     * @return string
     */
    function getObjectTypeField() {
        return $this->objectTypeField;
    }    
    
    
    /**
     * @return Ac_Model_Relation
     */
    protected function getImplRelation() {
        if ($this->implRelation === false) {
            $this->getImplMapper();
            $this->implRelation = new Ac_Model_Relation(array(
                'fieldLinks' => $this->colMap,
                'srcTableName' => $this->tableName,
                'srcIsUnique' => true,
                'destIsUnique' => true,
                'db' => $this->mixin->getDb(),
            ));
        }
        return $this->implRelation;
    }
    
    /**
     * Sets class, prototype or instance of Ac_Model_Mapper that interacts with extra table
     */
    function setImplMapper($implMapper) {
        if (!$this->implMapper || $this->implMapper !== $implMapper) {
            if ($this->implMapper) throw Ac_E_InvalidCall::canRunMethodOnce($this, __METHOD__);
            $this->implMapper = $implMapper;
        }
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    protected function getImplMapper() {
        if ($this->implMapper !== false && !is_object($this->implMapper) && $this->mixin) {
            $def = array();
            if ($this->mixin) 
                $def['application'] = $this->mixin->getApplication();
            $this->implMapper = Ac_Prototyped::factory($this->implMapper, 'Ac_Model_Mapper', $def);
            if ($this->tableName && $this->implMapper->tableName !== $this->tableName) {
                throw new Ac_E_InvalidUsage("\$tableName, if set, must not be different from \$implMapper->tableName");
            }
            $this->tableName = $this->implMapper->tableName;
        }
        if ($this->implMapper === false && $this->mixin && strlen($this->tableName)) {
            $this->implMapper = new Ac_Model_Mapper(array(
                'id' => 'extraTable_'.$this->mixin->getId().$this->tableName,
                'tableName' => $this->tableName,
                'application' => $this->mixin->getApplication(),
            ));
        }
        return $this->implMapper;
    }
    
    function pushPreloadedRows(array $rows) {
        array_push($this->prStack, array($this->preloadedRows, $this->preloadedHits));
        $this->lastPreloadedHits = $this->preloadedHits;
        $this->preloadedHits = 0;
        $this->preloadedRows = $rows;
    }
    
    function popPreloadedRows() {
        $this->lastPreloadedHits = $this->preloadedHits;
        list($this->preloadedRows, $this->preloadedHits) = array_pop($this->prStack);
    }
    
    /**
     * @return int
     */
    function getPreloadedHits($last = false) {
        return $last? $this->lastPreloadedHits : $this->preloadedHits;
    }
    
    protected function pickPreloaded($recordRows) {
        $myKeys = array_keys($this->colMap);
        $otherKeys = array_values($this->colMap);
        // num(colMap) == 1
        if (count($myKeys) == 1) {
            $a = Ac_Util::indexArray($this->preloadedRows, $myKeys[0], true);
            $b = Ac_Util::indexArray($recordRows, $otherKeys[0], false, false, true);
        }
        // num(colMap) > 1
        if (count($myKeys) > 1) { // crude and not 100% accurate, but I'm almost sure composite FKs won't be used
            $a = array();
            $b = array();
            $myKeys1 = array_flip($myKeys);
            $otherKeys1 = array_flip($otherKeys);
            foreach ($this->preloadedRows as $v) {
                $a[implode('___', array_intersect_key($v, $myKeys1))] = $v;
            }
            foreach ($recordRows as $k => $r) {
                $b[implode('___', array_intersect_key($r, $otherKeys1))][$k] = $r;
            }
        }
        $res = array();
        foreach (array_intersect_key($a, $b) as $aKey => $preloadedRow) {
            foreach (array_keys($b[$aKey]) as $bk) $res[$bk] = $preloadedRow;
        }
        $this->preloadedHits += count($res);
        return $res;
    }
    
    protected function getMappedData(array $recordRows) {
        
        // only part of the rows may be preloaded
        
        if ($this->preloadedRows) {
            $preloaded = $this->pickPreloaded($recordRows);
            if ($preloaded) {
                $recordRows = array_diff_key($recordRows, array_flip(array_keys($preloaded)));
                Ac_Model_Mapper::mapRows($preloaded, $this->fieldNames);
            }
        } else {
            $preloaded = array();
        }
        
        if ($recordRows) {

            // now get mapped data for $recordRows that didn't had preloaded rows

            $rel = $this->getImplRelation();
            $data = $rel->getSrc($recordRows, Ac_Model_Relation_Abstract::RESULT_ORIGINAL_KEYS);
            if ($this->fieldNames)
                $data = Ac_Model_Mapper::mapRows($data, $this->fieldNames);
            
            if ($preloaded) {
                foreach ($preloaded as $k => $v) {
                    $data[$k] = $v;
                }
            }
            
        } else {
            $data = $preloaded;
        }
        return $data;
    }
    
    function getDefaults() {
        if ($this->defaults === false) {
            $this->defaults = $this->getImplMapper()->getDefaults();
            if ($this->fieldNames) {
                $this->defaults = Ac_Model_Mapper::mapRows(array($this->defaults), $this->fieldNames);
                $this->defaults = $this->defaults[0];
            }
        }
        return $this->defaults;
    }
    
    function onBeforeLoadFromRows(array & $rows, array & $records) {
        $md = $this->getMappedData($rows);
        foreach ($rows as $k => $row) {
            if (isset($md[$k])) {
                $extra = $md[$k];
            } else {
                $extra = $this->getDefaults();
            }
            if ($this->overwriteModelFields) 
                $rows[$k] = array_merge($rows[$k], $extra);
            else {
                if (strlen($this->modelMixableId)) {
                    foreach (array_intersect_key($extra, $rows[$k]) as $key => $value) {
                        $extra[$this->modelMixableId.'::'.$key] = $value;
                        unset($extra[$key]);
                    }
                    $rows[$k] = array_merge($rows[$k], $extra);
                } else {
                    // here we need to prefix the model with mixable ID
                    $rows[$k] = array_merge($rows[$k], array_diff_key($extra, $rows[$k]));
                }
                
            }
        }
    }
    
    function onPeLoad(& $data, $primaryKey, Ac_Model_Object $record, & $error) {
        $rows = array($data);
        $md = $this->getMappedData($rows);
        if (isset($md[0])) $md = $md[0];
            else $md = $this->getDefaults ();
        $data = array_merge($data, $md);
    }
    
    protected function getExtraRecord($data, & $bindData = array(), $loadNotBind = false) {
        $bindData = array();
        $colMap = array_merge(array_combine($def = array_keys($this->getDefaults()), $def), $this->fieldNames);
        foreach (array_intersect_key($colMap, $data) as $colName => $fieldName) {
            if (strlen($fieldName)) {
                $bindData[$colName] = $data[$fieldName];
            }
        }
        if ($restrictions = $this->getRestrictions()) 
            $bindData = array_merge($bindData, $this->getRestrictions());
        
        foreach ($this->colMap as $slaveCol => $ownerCol) {
            $bindData[$slaveCol] = $data[$ownerCol];
        }
        $record = $this->getImplMapper()->createRecord();
        if ($loadNotBind) {
            $record->load($bindData, true);
        } else {
            $record->bind($bindData);
        }
        return $record;
    }
    
    protected function storeExtra($record, array & $hyData, & $newData, & $error) {
        $data = $hyData;
        if (is_array($newData) && $newData)
            $data = array_merge($data, $newData);
        
        $record = $this->getExtraRecord($data, $myData);
        
        if ($record->hasFullPrimaryKey()) {
            $record->load();
            $record->bind($myData);
        }
        if ($record->store()) {
            $res = $record;
            foreach ($this->colMap as $slaveCol => $ownerCol) {
                $val = $record->$slaveCol;
                if ($val !== $myData[$slaveCol]) {
                    if (!is_array($newData)) $newData = array();
                    if ($this->extraIsReferenced) {
                        $hyData[$ownerCol] = $val;
                        $newData[$ownerCol] = $val;
                    }
                }
            }
        } else {
            $res = false;
            $error = $res->getErrors(false, true);
        }
        return $res;
    }
    
    function onBeforeStoreRecord($record, array & $hyData, & $newData, & $exists, & $result, & $error) {
        if ($this->extraIsReferenced && $result !== false) {
            $saved = $this->storeExtra($record, $hyData, $newData, $error);
            if (!$saved) $result = false;
        }
    }
    
    function onAfterStoreRecord($record, array & $hyData, & $newData, & $exists, & $result, & $error) {
        if (!$this->extraIsReferenced && $result) {
            $saved = $this->storeExtra($record, $hyData, $newData, $error);
            if (!$saved) $result = false;
        }
    }
    
    function onAfterDeleteRecord($record, array & $hyData, & $error, & $result) {
        if ($result) {
            $extraRecord = $this->getExtraRecord($hyData, $bindData, true);
            if ($extraRecord->isPersistent()) {
                if (!$extraRecord->delete()) { // there may be the case when extra record does not exist
                    if ($xError = $extraRecord->getError()) {
                        $result = false;
                        $error = $xError;
                    }
                }
            }
        }
    }
    
    function onAfterCreateRecord(Ac_Model_Object & $record) {
        if ($this->modelMixable) {
            $mix = Ac_Prototyped::factory($this->modelMixable, 'Ac_I_Mixable');
            if ($mix instanceof Ac_Model_Mixable_ExtraTable) $mix->setMapperExtraTable($this);
            if (!$mix->getMixableId() && strlen($this->mixableId)) $mix->setMixableId ($this->getMixableId());
            $record->addMixable($mix);
        }
    }

    /**
     * @param bool $overwriteModelFields
     */
    function setOverwriteModelFields($overwriteModelFields) {
        $this->overwriteModelFields = $overwriteModelFields;
    }

    /**
     * @return bool
     */
    function getOverwriteModelFields() {
        return $this->overwriteModelFields;
    }
    
    protected function doGetRelationPrototypes() {
        return array();
    }
    
    protected function doGetAssociationPrototypes() {
        return array();
    }
    
    function onGetRelationPrototypes(& $relationPrototypes) {
        if ($p = $this->doGetRelationPrototypes()) 
            Ac_Util::ms($relationPrototypes, $p);
    }
    
    function onGetAssociationPrototypes(& $associationPrototypes) {
        if ($p = $this->doGetAssociationPrototypes()) 
            Ac_Util::ms($associationPrototypes, $p);
    }
    
    /**
     * @return Ac_Model_Relation
     */
    protected function getRelation($relId) {
        return $this->mixin->getRelation($relId);
    }
    
    function onGetSqlTable($alias, $prevAlias, Ac_Sql_Select_TableProvider $tableProvider, & $result) {
        if (!$result && $this->mixin) {
            $sel = $tableProvider->getSqlSelect();
            $this->getImplMapper();
            if ($sel && $sel->hasTable($prevAlias) && $sel->getTable($prevAlias)->name === $this->mixin->tableName) {
                if (strlen($this->mixableId) && $alias === 'extra__'.$this->mixableId && !$result) {
                    $result = array(
                        'class' => 'Ac_Sql_Select_Table',
                        'name' => $this->tableName,
                        'joinsAlias' => $prevAlias,
                        'joinsOn' => $this->colMap,
                        'joinType' => 'LEFT JOIN',
                    );
                }
            }
        }
    }
    
    function onListDataProperties(array & $dataProperties) {
        if (!$this->modelMixable) {
            $dataProperties = array_unique(array_merge($dataProperties, array_keys($this->getDefaults())));
        }
    }

    /**
     * Sets name of mapper criterion that links to the extra table
     * @param string $extraLinkCrit
     */
    function setExtraLinkCrit($extraLinkCrit) {
        $this->extraLinkCrit = $extraLinkCrit;
    }

    /**
     * Returns name of mapper criterion that links to the extra table
     * @return string
     */
    function getExtraLinkCrit() {
        return $this->extraLinkCrit;
    }    
    
    
}