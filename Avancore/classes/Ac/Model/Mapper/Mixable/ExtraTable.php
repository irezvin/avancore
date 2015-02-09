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
     * Reference mapping: slaveColName => ownerColName
     * @var array
     */
    protected $colMap = array();

    /**
     * If slave records should be deleted when owners are deleted
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
     * @var bool
     */
    protected $overwriteModelFields = false;

    function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    function getTableName() {
        return $this->tableName;
    }

    function setFieldNames(array $fieldNames) {
        $this->fieldNames = $fieldNames;
        $this->dataFieldNames = false;
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
        return $this->restrictions;
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
     * @return Ac_Model_Relation
     */
    protected function getImplRelation() {
        if ($this->implRelation === false) {
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
     * @return Ac_Model_Mapper
     */
    protected function getImplMapper() {
        if ($this->implMapper === false && $this->mixin) {
            $this->implMapper = new Ac_Model_Mapper(array(
                'id' => 'extraTable_'.$this->mixin->getId().$this->tableName,
                'tableName' => $this->tableName,
                'application' => $this->mixin->getApplication(),
            ));
        }
        return $this->implMapper;
    }
    
    protected function getMappedData(array $recordRows) {
        $rel = $this->getImplRelation();
        $data = $rel->getSrc($recordRows, AMR_ORIGINAL_KEYS);
        if ($this->fieldNames)
            $data = Ac_Model_Mapper::mapRows($data, $this->fieldNames);
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
                $rows[$k] = array_merge($rows[$k], array_diff_key($extra, $rows[$k]));
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
    
    protected function getExtraRecord($data, & $bindData = array()) {
        $bindData = array();
        $colMap = array_merge(array_combine($def = array_keys($this->getDefaults()), $def), $this->fieldNames);
        foreach (array_intersect_key($colMap, $data) as $colName => $fieldName) {
            if (strlen($fieldName)) {
                $bindData[$colName] = $data[$fieldName];
            }
        }
        if ($this->restrictions) 
            $bindData = array_merge($bindData, $this->restrictions);
        
        foreach ($this->colMap as $slaveCol => $ownerCol) {
            $bindData[$slaveCol] = $data[$ownerCol];
        }
        
        $record = $this->getImplMapper()->createRecord();
        $record->bind($bindData);
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
                    if ($this->extraIsReferenced) 
                        $hyData[$ownerCol] = $val;
                    $newData[$ownerCol] = $val;
                }
            }
        } else {
            $res = false;
            $error = $res->getError();
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
            $extraRecord = $this->getExtraRecord($hyData);
            if ($extraRecord->hasFullPrimaryKey()) {
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
        
}