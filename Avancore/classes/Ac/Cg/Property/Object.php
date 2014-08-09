<?php

class Ac_Cg_Property_Object extends Ac_Cg_Property {

    var $isPrivateVar = true;
    
    /**
     * @var bool Whether this property is based on incoming relation
     * 
     */
    var $isIncoming = false;
    
    /**
     * @var string
     * Name of Ac_Dbs_Relation in schema that corresponds to this association
     */
    var $relation = false;
    
    /**
     * @var string
     */
    var $otherRelation = false;
    
    /**
     * @var bool
     */
    var $isOtherIncoming = false;
    
    var $className = false;
    
    var $thisIsUnique = false;
    
    var $otherIsUnique = false;
    
    var $idrSuffixSingle = '';
    var $idrSuffixPlural = '';
    
    var $otherModelIdInMethodsSingle = false;
    var $otherModelIdInMethodsPlural = false;
    var $otherModelIdInMethodsPrefix = false;
    
    var $mapperClass = false;
    
    /**
     * @var Ac_Sql_Dbi_Relation
     */
    var $_rel = false;
    
    /**
     * @var Ac_Sql_Dbi_Relation
     */
    var $_otherRel = false;
    
    /**
     * Other model that is associated with one of current relation 
     * @var Ac_Cg_Model
     */
    var $_other = false;
    
    function listPassthroughVars() {
        return array_merge(array(
            'className', 
            'mapperClass', 
            'otherModelIdInMethodsSingle', 
            'otherModelIdInMethodsPlural', 
            'otherModelIdInMethodsPrefix'
        ), parent::listPassthroughVars());
    }
    
    function _init() {
        if ($this->relation) {
            if ($this->isIncoming)
                $this->_rel = $this->_model->tableObject->getIncomingRelation($this->relation);
            else 
               $this->_rel = $this->_model->tableObject->getRelation($this->relation);
               
            if ($this->otherRelation) {
                if ($this->isIncoming) $tbl = $this->_rel->ownTable;
                    else $tbl = $this->_rel->getForeignTable();
                if ($this->isOtherIncoming) $this->_otherRel = $tbl->getIncomingRelation($this->otherRelation);
                    else $this->_otherRel = $tbl->getRelation($this->otherRelation);
            }
            
        }
        
        if ($this->_rel) {
            if ($this->_otherRel) {
                $tblName = $this->isOtherIncoming? $this->_otherRel->ownTable->name : $this->_otherRel->table;
                $this->_other = $this->_model->_domain->searchModelByTable($tblName);
                $this->thisIsUnique = $this->isIncoming? $this->_rel->isOtherRecordUnique() : $this->_rel->isThisRecordUnique(); 
                $this->otherIsUnique = $this->isOtherIncoming? $this->_otherRel->isThisRecordUnique() : $this->_otherRel->isOtherRecordUnique();
            } else {
                $tblName = $this->isIncoming? $this->_rel->ownTable->name : $this->_rel->table;
                $this->_other = $this->_model->_domain->searchModelByTable($tblName);
                $this->thisIsUnique = $this->isIncoming? $this->_rel->isOtherRecordUnique() : $this->_rel->isThisRecordUnique(); 
                $this->otherIsUnique = $this->isIncoming? $this->_rel->isThisRecordUnique() : $this->_rel->isOtherRecordUnique();
            }
        } else {
            var_dump("$this->name: Cannot find relation ", $this->relation);
        }
        
        if (!$this->className) $this->className = $this->getDefaultClassName();
        
        if (!$this->varName) $this->varName = $this->getDefaultVarName();
        
        if (!$this->pluralForList) $this->pluralForList = $this->getDefaultPluralForList();
        
        if (!$this->caption) $this->caption = $this->getDefaultCaption();
        
        if ($this->_other) $this->mapperClass = $this->_other->getMapperClass();
        
    }
    
    function varNameHasConflicts() {
        $res = false;
        $pdn =  $this->getPreferredDefaultVarName();
        if ($this->varName === $pdn) {
            foreach ($this->_model->listProperties() as $i) {
                $p = $this->_model->getProperty($i);
                if (!Ac_Util::sameObject($p, $this) && is_a($p, 'Ac_Cg_Property_Object') && $p->getPreferredDefaultVarName() == $pdn) $res = true;
            }
        }
        return $res;
    }
    
    
    function resolveConflicts() {
        if ($this->varNameHasConflicts()) {
            $this->varName = substr($this->name, 5); // strip out "_rel_" prefix from the name
        }
    }
    
    function getTargetModelName() {
        $res = $this->_other? $this->_other->name : false;
    }
    
    function getDefaultClassName() {
        if ($this->_other) return $this->_other->className;
    }
    
    function isList() {
        if ($this->_rel) {
            return $this->isIncoming? !$this->_rel->isThisRecordUnique() : !$this->_rel->isOtherRecordUnique();
        }
    }
    
    function onShow() {
        return array(
            'otherEntityNameSingle' => $this->getOtherEntityName(),
            'otherEntityNamePlural' => $this->getOtherEntityName(false),
            'commonPrefixes' => implode(", ", $this->_model->getCommonSubsystemPrefixes($this->_other)),
            'otherPrefixes' => implode(", ", $this->_other->subsystemPrefixes),
        );
    }
    
    function getOtherEntityName($single = true) {
        if (!$single) 
            $res = $this->_other->plural;
        else 
            $res = $this->_other->single;
        
        if (count ($cp = $this->_model->getCommonSubsystemPrefixes($this->_other))) {
            $prefixes = array_slice($this->_other->subsystemPrefixes, count($cp));
        } else {
            $prefixes = $this->_other->subsystemPrefixes;
        }
        if (count($prefixes)) {
            $res = implode(' ', $prefixes).' '.$res;
        }
        if (in_array($res, $this->_model->tableObject->listColumns())) {
            $this->idrSuffixSingle = 'Object';
            $this->idrSuffixPlural = 'Objects';
            
            $res .= ' '.($this->idrSuffix = !$single? $this->idrSuffixPlural : $this->idrSuffixSingle);
        }
        $res = Ac_Cg_Inflector::camelize($res);
        return $res;
    }
    
    function getPreferredDefaultVarName() {
        return $this->getOtherEntityName(!$this->isList());
    }
    
    function getDefaultVarName($forceSingle = false) {
        if ($this->isList() || $forceSingle) {
            $res = $this->otherModelIdInMethodsPlural;
        } else {
            $res = $this->otherModelIdInMethodsSingle;
        }
        if (!strlen($res))
            $res = $this->getOtherEntityName(!$this->isList() || $forceSingle);
        if (strlen($this->otherModelIdInMethodsPrefix)) $res = $this->otherModelIdInMethodsPrefix.ucfirst($res);
        return $res;
    }
    
    function getDefaultPluralForList() {
        $res = false;
        if ($this->_rel) {
            $plur = $this->isIncoming? !$this->_rel->isThisRecordUnique() : !$this->_rel->isOtherRecordUnique();
            if ($plur) {
                if ($this->otherModelIdInMethodsPlural) $res = $this->otherModelIdInMethodsPlural; 
                else {
                    if ($this->_other) $res = $this->getOtherEntityName(false);
                    else $res = $this->varName;
                }
                    
            }
        }
        if (strlen($this->otherModelIdInMethodsPrefix) && strlen($res)) $res = $this->otherModelIdInMethodsPrefix.ucfirst($res);
        return $res;
    }
    
    function isManyToMany() {
        return $this->_otherRel;
    }
    
    function getDefaultCaption() {
        if ($this->_rel && $this->_other) {
            if ($this->isList()) $res = $this->_other->pluralCaption;
            else $res = $this->_other->singleCaption;
        }
        return $res;
    }
    
    /**
     * Gets corresponding property from other model that is built on the same relation 
     * @return Ac_Cg_Property_Object
     */
    function getMirrorProperty() {
        $res = null;
        foreach ($this->_other->listProperties() as $name) {
            $prop = $this->_other->getProperty($name);
            if (is_a($prop, 'Ac_Cg_Property_Object')) {
                if ($this->_otherRel) {
                    if ($prop->_otherRel && ($prop->isIncoming == !$this->isOtherIncoming) && ($prop->isOtherIncoming == !$this->isIncoming) 
                        && Ac_Util::sameObject($this->_rel, $prop->_otherRel) && Ac_Util::sameObject($this->_otherRel, $prop->_rel)) 
                    {
                        $res = $prop;
                        break;
                    }
                } else {
                    if (($prop->isIncoming == !$this->isIncoming) && Ac_Util::sameObject($this->_rel, $prop->_rel)) {
                        $res = $prop;
                        break;
                    }
                }
            }
        }
        return $res;
    }
    
    function getAeModelRelationPrototype() {
        $res = array();
        $res['srcMapperClass'] = $this->_model->getMapperClass();
        $res['destMapperClass'] = $this->_other->getMapperClass();
        $res['srcVarName'] = $this->getClassMemberName();
        if ($this->isManyToMany()) $res['srcNNIdsVarName'] = $this->getIdsMemberName();
        if ($cmn  = $this->getCountMemberName()) $res['srcCountVarName'] = $cmn;
        if (($mirrorProp = $this->getMirrorProperty()) && $mirrorProp->isEnabled()) {
            $res['destVarName'] = $mirrorProp->getClassMemberName();
            if ($cmn  = $mirrorProp->getCountMemberName()) $res['destCountVarName'] = $cmn;
            if ($this->isManyToMany()) $res['destNNIdsVarName'] = $mirrorProp->getIdsMemberName();
        }
        if ($this->isIncoming) {
            $res['fieldLinks'] = array_flip($this->_rel->columns);
            $res['srcIsUnique'] = $this->_rel->isOtherRecordUnique();
            $res['destIsUnique'] = $this->_rel->isThisRecordUnique();      
        } else {
            $res['fieldLinks'] = $this->_rel->columns;
            $res['srcIsUnique'] = $this->_rel->isThisRecordUnique();
            $res['destIsUnique'] = $this->_rel->isOtherRecordUnique();
            $res['srcOutgoing'] = true;
        }
        if ($this->_otherRel) {
            if ($this->isIncoming) {
                $res['midTableName'] = $this->_rel->ownTable->name;
            } else {
                $res['midTableName'] = $this->_rel->table;
            }
            if ($this->isOtherIncoming) {
                
                $res['fieldLinks2'] = array_flip($this->_otherRel->columns);
                $res['destIsUnique'] = $this->_otherRel->isThisRecordUnique();      
            } else {
                $res['fieldLinks2'] = $this->_otherRel->columns;
                $res['destIsUnique'] = $this->_otherRel->isOtherRecordUnique();
            }
            
            // workaround for many-to-many relations
            // TODO: figure why srcIsUnique and destIsUnique are true 
            if (isset($res['midTableName']) && strlen($res['midTableName'])) {
                $res['srcIsUnique'] = false;
                $res['destIsUnique'] = false;
            }
        }
         
        return $res;
    }
    
    function getCountMemberName() {
        if ($this->pluralForList) {
            $res = '_'.$this->pluralForList.'Count';
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * Name of member that holds foreign IDs for many-to-many relations
     */
    function getIdsMemberName() {
        $p = $this->getIdsPropertyName();
        if (strlen($p)) {
            $res = '_'.$p;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function getIdsPropertyName() {
        if ($this->isManyToMany()) {
            if (strlen($this->otherModelIdInMethodsSingle)) 
                $res = $this->otherModelIdInMethodsSingle.'Ids';
            else $res = $this->getOtherEntityName(true).'Ids';
            if (strlen($this->otherModelIdInMethodsPrefix)) $res = $this->otherModelIdInMethodsPrefix.ucfirst($res);        
        } else $res = false;
        return $res;
    }
    
    /**
     * @return Ac_Cg_Model
     */
    function getOtherModel() {
        return $this->_other;
    }
    
    /**
     * @return Ac_Sql_Dbi_Relationc
     */
    function getRelation() {
        return $this->_rel;
    }
    
    function getAllClassMembers() {
        $res = parent::getAllClassMembers();
        if ($cmn = $this->getCountMemberName()) $res[$cmn] = false;
        if ($imn = $this->getIdsMemberName()) $res[$imn] = false;
        return $res;
    }
    
    function isEnabled() {
        if ($this->enabled === false) return false;
        if ($this->enabled === true) return true;
        foreach ($this->_model->_domain->dontLinkSubsystems as $s) {
            if (in_array($s[0], $this->_model->listSystems()) && in_array($s[1], $this->_other->listSystems())) {
                return false;
            }
        }
        return true;
        
    }
    
    function hasSeveralProperties() {
        return $this->getIdsMemberName() !== false;
    }
    
    function getAeModelPropertyInfo() {
        if ($this->getIdsMemberName() !== false) {
            $many = true;
            $res = array(
                $this->varName => parent::getAeModelPropertyInfo(),
                $this->getIdsPropertyName() => array(
                    'dataType' => 'int',
                    'arrayValue' => true,
                    //'caption' => $this->_other->pluralCaption,
                    'controlType' => 'selectList',
                    'values' => array(
                        'class' => 'Ac_Model_Values_Records',
                        'mapperClass' => $this->_other->getMapperClass(),
                    ),
                    'showInTable' => false,
                ),
            );
        } else {
            $many = false;
            $res = parent::getAeModelPropertyInfo();
        }
        $relId = $this->_model->searchRelationIdByProperty($this);
        if ($relId !== false) {
            $prot = $this->_model->getAeModelRelationPrototype($relId);
            if (isset($prot['srcVarName'])) {
                if ($many) {
                    $res[$this->varName]['relationId'] = $prot['srcVarName'];
                    if (strlen($m = $this->getCountMemberName())) $res[$this->varName]['countVarName'] = $m;
                    if (strlen($m = $this->getIdsMemberName())) $res[$this->varName]['nnIdsVarName'] = $m;
                    $res[$this->varName]['referenceVarName'] = $prot['srcVarName'];
                }
                else {
                    $res['relationId'] = $prot['srcVarName'];
                    if (strlen($m = $this->getCountMemberName())) $res['countVarName'] = $m;
                    if (strlen($m = $this->getIdsMemberName())) $res['nnIdsVarName'] = $m;
                    $res['referenceVarName'] = $prot['srcVarName'];
                }
            }
        }
        return $res;
    }
    
    function getForeignKeyFieldName() {
        $res = false;
        if (!$this->isList()) {
            $proto = $this->getAeModelRelationPrototype();
            $fl = $proto['fieldLinks'];
            if (count($fl) == 1) {
                $tmp = array_keys($fl);
                $res = $tmp[0];
            }
        }
        return $res;
    }
    
}

