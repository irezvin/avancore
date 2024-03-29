<?php

class Ac_Cg_Property_Simple extends Ac_Cg_Property {
    
    var $colName = false;
    
    var $dataType = false;
    
    var $controlType = false;
    
    var $maxLength = false;
    
    var $valueList = false;
    
    var $values = false;
    
    var $dummyCaption = false;
    
    var $isRte = false;

    var $required = false;
    
    var $default = false;
    
    var $extraPropInfo = array();
    
    var $attribs = array();
    
    var $isNullable = false;
    
    /**
     * @var Ac_Sql_Dbi_Column
     */
    var $_col = false;
    
    var $_assocPropertyName = false;
    
    protected $skipInit = false;
    
    function listPassthroughVars() {
        return array_merge(
            array(
                'dataType', 'controlType', 'maxLength', 'valueList', 'dummyCaption', 'isRte', 'required', 'values', 'attribs',
                'assocPropertyName', 'isNullable',
            ), 
            parent::listPassthroughVars()
        );
    }
    
    function init() {
        if ($this->skipInit) return;
        
        if (!$this->colName) $this->colName = $this->name;
        $this->_col = $this->_model->tableObject->getColumn($this->colName);
        if (!$this->varName) $this->varName = $this->getDefaultVarName();
        $this->processType();
        //if (!$this->maxLength) $this->maxLength = $this->getDefaultMaxLength();
        if (!$this->caption) $this->caption = $this->getDefaultCaption();
    }
    
    function _useMaxLength() {
        if ($this->_col->width && $this->maxLength === false) $this->maxLength = $this->_col->width;
    }
    
    function processType() {
        $dataType = false;
        $controlType = false;
        $valueList = [];
        
        $numeric = false;
        $default = false;
        
        $default = $this->_col->default;
        
        switch (strtoupper($this->_col->type)) {
            case 'VARCHAR':
            case 'CHAR':
                $this->_useMaxLength();
                if (!$this->_col->nullable && is_null($this->_col->default) && !in_array($this->_col->name, $this->_model->tableObject->listPkFields()))
                    $default = '';
                break;
            
            case 'TEXT':
            case 'TINYTEXT':
            case 'MEDIUMTEXT':
            case 'LONGTEXT':
            case 'JSON':
                $this->_useMaxLength();
                $controlType = $this->isRte? 'rte' : 'textArea';
                if (!$this->_col->nullable && is_null($this->_col->default) && !in_array($this->_col->name, $this->_model->tableObject->listPkFields()))
                    $default = ''; 
                break;
                 
            case 'INTEGER':
            case 'TINYINT':
            case 'INT':
            case 'MEDIUMINT':
            case 'SMALLINT':
            case 'BIGINT':
                $dataType = 'int';
                if ($this->_col->width == 1) {
                    $valueList = array(0 => 'No', 1 => 'Yes');
                    $controlType = 'selectList';
                    $dataType = 'bool';    
                }
                $this->_useMaxLength();
                $numeric = true;
                if (!is_null($default) && strlen($default)) $default = intval($default);
                if (!$this->_col->nullable && is_null($this->_col->default) && !in_array($this->_col->name, $this->_model->tableObject->listPkFields()))
                    $default = 0;
                break;
                
            case 'DATE':
                $dataType = 'date';
                $controlType = 'dateInput';
                if (!isset($this->extraPropertyInfo['internalDateFormat']))
                    $this->extraPropertyInfo['internalDateFormat'] = 'Y-m-d';
                if (!isset($this->extraPropertyInfo['outputDateFormat']))
                    $this->extraPropertyInfo['outputDateFormat'] = 'Y-m-d';
                    
                if ($default == 'CURRENT_TIMESTAMP') $default = false;
                break;
                
            case 'DATETIME':
                $dataType = 'dateTime';
                $controlType = 'dateInput';
                if (!isset($this->extraPropertyInfo['internalDateFormat']))
                    $this->extraPropertyInfo['internalDateFormat'] = 'Y-m-d H:i:s';
                if (!isset($this->extraPropertyInfo['outputDateFormat']))
                    $this->extraPropertyInfo['outputDateFormat'] = 'Y-m-d H:i:s';
                if (!is_null($default) && strlen($default)) $default = '0000-00-00 00:00:00'; 
                if ($default == 'CURRENT_TIMESTAMP') $default = false;
                break;
                
            case 'TIMESTAMP':
                $dataType = 'timestamp';
                $controlType = 'dateInput';
                if (!isset($this->extraPropertyInfo['internalDateFormat']))
                    $this->extraPropertyInfo['internalDateFormat'] = 'Y-m-d H:i:s';
                if (!isset($this->extraPropertyInfo['outputDateFormat']))
                    $this->extraPropertyInfo['outputDateFormat'] = 'Y-m-d H:i:s';
                if ($default == 'CURRENT_TIMESTAMP') $default = false;
                break;
                
            case 'TIME':
                $dataType = 'time';
                $controlType = 'dateInput';
                if (!isset($this->extraPropertyInfo['internalDateFormat']))
                    $this->extraPropertyInfo['internalDateFormat'] = 'H:i:s';
                if (!isset($this->extraPropertyInfo['internalDateFormat']))
                    $this->extraPropertyInfo['outputDateFormat'] = 'H:i:s';
                if ($default == 'CURRENT_TIMESTAMP') $default = false;
                break;
            
            case 'FLOAT':
            case 'DOUBLE':
            case 'DECIMAL':    
                $dataType = 'float';
                $numeric = true;
                if (!is_null($default) && strlen($default)) $default = floatval($default);
                if (!$this->_col->nullable && is_null($this->_col->default) && !in_array($this->_col->name, $this->_model->tableObject->listPkFields()))
                    $default = 0;
                break;
                
            case 'ENUM':
                if ($this->_col->enumValues) {
                    if (!count(array_diff($this->_col->enumValues, array('N', 'Y')))) $valueList = array('N' => 'No', 'Y' => 'Yes');
                    else foreach ($this->_col->enumValues as $val) $valueList[$val] = $val;
                    $controlType = 'selectList'; 
                }
                break;
             
            case 'YEAR':
            case 'TINYBLOB':
            case 'BLOB':
            case 'MEDIUMBLOB':
            case 'LONGBLOB':
            case 'SET':
            case 'BINARY':
            case 'VARBINARY':
                break;
                
            default:
                break;
        }
        
        if (!$this->dataType) $this->dataType = $dataType;
        if (!$this->valueList && $valueList) $this->valueList = $valueList;
        if ($this->default === false) $this->default = $default;
        $this->isNullable = $this->_col->nullable;
        
        if (!$this->values) {
            if ($sfc = $this->_col->pointsToSingleForeignColumn()) {
                if ($sfc->isUnique()) {
                    $foreignTbl = $sfc->_table;
                    if ($mod = $this->_model->_domain->searchModelByTable($foreignTbl->name)) {
                        if ($this->_col->nullable) $this->dummyCaption = new Ac_Cg_Php_Expression("''");
                        $this->values = array(
                            'class' => 'Ac_Model_Values_Mapper', 
                            'mapperClass' => $mod->getMapperClass()
                        );
                        // it's not a primary key so we have to specify what field do we reference
                        if (!$sfc->isPk()) {
                            $this->values['valueFieldName'] = $sfc->name;
                        }
                        $controlType = 'selectList';
                    }
                }
            }
        }
        
        if (!$this->controlType) $this->controlType = $controlType;
        
        if ($numeric && ($this->controlType !== 'selectList') && !isset($this->attribs['size']))
            $this->attribs['size'] = '6';
        
    }
    
    function getDefaultVarName() {
        return $this->colName? $this->colName : $this->name;
    }
    
    function getDefaultCaption() {
        if ($this->_col->comment) $c = $this->_col->comment; 
            else $c = Ac_Cg_Util::makeCaption($this->colName);
        return $c;        
    }
    
    function getAllClassMembers() {
        return array($this->getClassMemberName() => $this->default);
    }
    
    function getAssocPropertyName() {
        if ($this->_assocPropertyName === false) {
            if ($sfc = $this->_col->pointsToSingleForeignColumn()) {
                if ($sfc->isPk()) {
                    $foreignTbl = $sfc->_table;
                    if ($mod = $this->_model->_domain->searchModelByTable($foreignTbl->name)) {
                        foreach ($this->_model->listProperties() as $i) {
                            $p = $this->_model->getProperty($i);
                            if (is_a($p, 'Ac_Cg_Property_Object') && ($p->isEnabled()) && ($om = $p->getOtherModel())) {
                                if (Ac_Util::sameObject($om, $mod)) {
                                    $rel = $p->_rel;
                                    if (in_array($this->colName, array_keys($rel->columns))) {
                                        $this->_assocPropertyName = $p->varName;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->_assocPropertyName;
    }
    
    function serializeToArray() {
        $res = parent::serializeToArray();
        if ($this->_col) $res['_col'] = $this->refColumn($this->_col);
        return $res;
    }
    
    function unserializeFromArray($array) {
        parent::unserializeFromArray($array);
        if (isset($array['_col'])) $this->_col = $this->unrefColumn($array['_col']);
        $this->skipInit = true;
    }
    
    function getAeModelPropertyInfo() {
        $res = parent::getAeModelPropertyInfo();
        if ($op = $this->getAssocPropertyName()) {
            $res['assocPropertyName'] = $op;
        }
        return $res;
    }
    
}

