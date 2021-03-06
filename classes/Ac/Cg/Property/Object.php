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
    
    var $canLoadSrc = true;
    var $canLoadDest = true;
    var $canCreateDest = true;
    
    var $mapperClass = false;
    
    var $relationOverrides = array();
    var $associationOverrides = array();
    var $relationProviderOverrides = array();
    
    /**
     * @var Ac_Cg_Model_Relation
     */
    var $modelRelation = false;
    
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
    
    var $_assocStrategy = false;
    
    protected $refsToFix = false;
    
    function listPassthroughVars() {
        return array_merge(array(
            'className', 
            'mapperClass', 
            'otherModelIdInMethodsSingle', 
            'otherModelIdInMethodsPlural', 
            'otherModelIdInMethodsPrefix'
        ), parent::listPassthroughVars());
    }
    
    function init() {
        if (is_array($this->refsToFix)) {
            $this->finishUnserialization ();
            return;
        }
        
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
            var_dump("Property '{$this->_model->name}'.'{$this->name}': cannot find relation '{$this->relation}'");
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
        return $res;
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
    
    function computeRelationData() {
        $res = array();
        $res['srcMapperClass'] = $this->_model->getMapperClass();
        $res['destMapperClass'] = $this->_other->getMapperClass();
        $res['srcVarName'] = $this->getClassMemberName();
        if ($this->isManyToMany()) $res['srcNNIdsVarName'] = $this->getIdsMemberName();
        if ($cmn  = $this->getCountMemberName()) $res['srcCountVarName'] = $cmn;
        if ($cmn  = $this->getLoadedMemberName()) $res['srcLoadedVarName'] = $cmn;
        if (($mirrorProp = $this->getMirrorProperty()) && $mirrorProp->isEnabled()) {
            $res['destVarName'] = $mirrorProp->getClassMemberName();
            if ($cmn  = $mirrorProp->getCountMemberName()) $res['destCountVarName'] = $cmn;
            if ($cmn  = $mirrorProp->getLoadedMemberName()) $res['destLoadedVarName'] = $cmn;
            if ($this->isManyToMany()) {
                $res['destNNIdsVarName'] = $mirrorProp->getIdsMemberName();
            }
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
        if ($this->isManyToMany()) {
            $mem = $this->getIdsMemberName();
            if (strlen ($mem)) {
                $res['srcLoadNNIdsMethod'] = array(true, "load".ucfirst(preg_replace("/^_/", "", $mem))."For");
            }
            if ($mp = $this->getMirrorProperty()) {
                $mem = $mp->getIdsMemberName();
                if (strlen($mem)) {
                    $res['destLoadNNIdsMethod'] = array(true, "load".ucfirst(preg_replace("/^_/", "", $mem))."For");
                }
            }
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
    
    function getAeModelRelationPrototype() {
        $res = $this->computeRelationData();
        if (is_array($this->relationOverrides))
            Ac_Util::ms($res, $this->relationOverrides);
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
    
    function getLoadedMemberName() {
        if ($this->pluralForList) {
            $res = '_'.$this->pluralForList.'Loaded';
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
        if ($lmn = $this->getLoadedMemberName()) $res[$lmn] = false;
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
                        'class' => 'Ac_Model_Values_Mapper',
                        'mapperClass' => $this->_other->getMapperClass(),
                    ),
                    'showInTable' => false,
                    'assocPropertyName' => $this->varName
                ),
            );
            $res[$this->varName]['idsPropertyName'] = $this->getIdsPropertyName();
        } else {
            $many = false;
            $res = parent::getAeModelPropertyInfo();
        }
        $relation = $this->modelRelation;
        if ($relation) {
            $prot = $this->_model->getAeModelRelationPrototype($relation);
            if (!$many) {
                if (count($prot['fieldLinks']) == 1) {
                    $kk = array_keys($prot['fieldLinks']);
                    $res['idPropertyName'] = $kk[0];
                }
            }
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
    
    function getAssociationPrototype() {
        $prot = $this->getAeModelRelationPrototype();
        $strat = $this->getAssocStrategy();
        $mm = $strat->getMethodNames();
        $res = array(
            'relationId' => $prot['srcVarName'],
            'useMapperMethods' => true,
            'useModelMethods' => true,
            'single' => $strat->single,
            'plural' => $strat->plural,
        );
        foreach (array(
            'canLoadDest' => 'canLoadDestObjects', 
            'canLoadSrc' => 'canLoadSrcObjects', 
            'canCreateDest' => 'canCreateDestObject'
        ) as $p => $op) {
            if (!$this->$p) $res[$op] = false;
        }
        if ($this->isList()) {
            if ($this->isManyToMany()) {
                $class = 'Ac_Model_Association_ManyToMany';
            } else {
                $class = 'Ac_Model_Association_Many';
                if (!$this->isIncoming) 
                    $res['isReferenced'] = true;
            }
        } else {
            $class = 'Ac_Model_Association_One';
            if ($this->isIncoming) 
                $res['isReferenced'] = false;
        }
        $res['class'] = $class;
        Ac_Util::ms($res, $mm);
        
        if (is_array($this->associationOverrides))
            Ac_Util::ms($res, $this->associationOverrides);
        
        return $res;
    }
    
    function getRelationProviderPrototype() {
        $rel = $this->computeRelationData();
        $res = array(); // by default return nothing
        $links = isset($rel['fieldLinks2'])? $rel['fieldLinks2'] : $rel['fieldLinks'];
        
        // study the relation structure
        
        $right = array_values($links);
        $rightPk = $this->_other->tableObject->listPkFields();

        $rightIsSingle = count($right) == 1;
        $rightMatchesPk = !array_diff($right, $rightPk) && count($right) == count($rightPk);
        $hasMidTable = isset($rel['midTableName']) && strlen($rel['midTableName']);
        
        // now let's produce providers
        if ($hasMidTable) {
            if ($rightMatchesPk) {
                $res = array(
                    'class' => 'Ac_Model_Relation_Provider_Sql_NN_Pk',
//                    ''
                );
            } elseif ($rightIsSingle) {
            } else {
                
            }
        }
        
        return $res;
    }
    
    function getAssocStrategy() {
        if ($this->_assocStrategy === false) {
            $prot = $this->getAeModelRelationPrototype();
            $relationId = $prot['srcVarName'];
            if ($this->isList() && $this->isManyToMany()) $class = 'Ac_Cg_Template_Assoc_Strategy_ManyToMany';
            elseif ($this->isList()) $class = 'Ac_Cg_Template_Assoc_Strategy_Many';
            else $class = 'Ac_Cg_Template_Assoc_Strategy_One';

            //$class = 'Ac_Cg_Template_Assoc_Strategy';
            $this->_assocStrategy = new $class (array(
                'relationId' => $relationId, 
                'prop' => $this, 
                'model' => $this->_model,
                'domain' => $this->_model->_domain
            ));
        }
        return $this->_assocStrategy;
    }
    
    function setOtherModel(Ac_Cg_Model $otherModel = null, $resetProps = false) {
        $this->_other = $otherModel;
        
        if ($resetProps) {
            $this->className = false;
            $this->varName = false;
            $this->pluralForList = false;
            $this->caption = false;
        }
        
        if (!$this->className) $this->className = $this->getDefaultClassName();
        
        if (!$this->varName) $this->varName = $this->getDefaultVarName();
        
        if (!$this->pluralForList) $this->pluralForList = $this->getDefaultPluralForList();
        
        if (!$this->caption) $this->caption = $this->getDefaultCaption();
        
        if ($this->_other) $this->mapperClass = $this->_other->getMapperClass();
        
    }
    
    function __clone() {
        $this->_assocStrategy = false;
    }
    
    function getSerializationMap() {
        $res = array(
            'modelRelation' => array('modelRelation', 'Ac_Cg_Model_Relation', array())
        );
        return $res;
    }
    
    function serializeToArray() {
        $res = parent::serializeToArray();
        if ($this->_other) $res['_other'] = $this->refModel($this->_other);
        if ($this->_rel) $res['_rel'] = $this->refRelation($this->_rel);
        if ($this->_otherRel) $res['_otherRel'] = $this->refRelation($this->_otherRel);
        return $res;
    }
    
    function unserializeFromArray($array) {
        parent::unserializeFromArray($array);
        $this->refsToFix = array();
        foreach (array('_other', '_rel', '_otherRel') as $k) if (isset($array[$k])) $this->refsToFix[$k] = $array[$k];
    }
    
    protected function finishUnserialization() {
        $array = $this->refsToFix;
        if (isset($array['_other'])) $this->_other = $this->unrefModel($array['_other']);
        if (isset($array['_rel'])) {
            $this->_rel = $this->unrefRelation($array['_rel']);
        }
        if (isset($array['_otherRel'])) $this->_otherRel = $this->unrefRelation($array['_otherRel']);
    }
    
    function applyToSqlSelectPrototype(array & $prototype) {
        $rd = $this->computeRelationData();
        if ($this->isManyToMany()) {
            if (isset($rd['midTableName']) && $rd['midTableName'] && isset($rd['fieldLinks2']) && count($rd['fieldLinks2'])) {
                if (isset($rd['srcNNIdsVarName']) && $rd['srcNNIdsVarName']) {
                    $critName = preg_replace('/^_/', '', $rd['srcNNIdsVarName']);
                    $midTableAlias = 'mid__'.preg_replace('/^_/', '', $this->getClassMemberName());
                    $srcCols = array_values($rd['fieldLinks']);
                    $destCols = array_keys($rd['fieldLinks2']);
                    $tableKeys = array_values($rd['fieldLinks2']);
                    if (count($srcCols) == 1 && count($tableKeys) == 1) {
                        $proto = array(
                            'class' => 'Ac_Sql_Filter_NNCriterion_Simple',
                            'midSrcKey' => $srcCols[0],
                            'midDestKey' => $destCols[0],
                            'tableKey' => $tableKeys[0],
                        );
                    } else {
                        $proto = array(
                            'class' => 'Ac_Sql_Filter_NNCriterion_Omni',
                            'midSrcKeys' => $srcCols,
                            'midDestKeys' => $destCols,
                            'tableKeys' => $tableKeys,
                        );
                    }
                    $proto['midTableAlias'] = $midTableAlias;
                    $prototype['parts'][$critName] = $proto;
                }
            }
            
        }
    }
    
    
}

