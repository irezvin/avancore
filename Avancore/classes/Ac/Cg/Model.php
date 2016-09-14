<?php

/**
 * Model metamodel for Code Generator
 */
        
class Ac_Cg_Model extends Ac_Cg_Base {

    /**
     * Model name
     * @var string
     */
    var $name = false;
    
    /**
     * Table name (defaults to model name)
     * @var string
     */
    var $table = false;
    
    /**
     * List of subsystem prefixes that prepend model name
     *
     * @var array
     */
    var $subsystemPrefixes = false;

    /**
     * Single name (defaults to model name)
     * @var string 
     */
    var $single = false;
    
    /**
     * Plural name (defaults to model name)
     * @var string
     */
    var $plural = false;
    
    /**
     * Class name (defaults to $this->single in PEAR-style, prefixed with appName, i.e. 'foo bar' of 'baz' app => 'Baz_Foo_Bar' 
     *
     * @var string
     */
    var $className = false;
    
    var $singleCaption = false;
    
    var $pluralCaption = false;
    
    /**
     * Name of class that overwritable object (<Model>_Base_Object) is derived from. Defaults to 'Ac_Model_Object' for models with simple primary key, 
     * 'Ac_Model_CpkObject' for models with composite primary key.
     * 
     * @var string
     */    
    var $parentClassName = false;
    
    /**
     * Name of class that overwritable mapper object (<Model>_Base_Mapper) is derived from. Defaults to 'Ac_Model_Mapper' for models with simple primary key, 
     * 'Ac_Model_->tableObject' for models with composite primary key.
     * 
     * @var string
     */    
    var $parentMapperClassName = false;
    
    var $parentStorageClassName = false;
    
    var $parentClassIsAbstract = false;
    
    var $parentMapperIsAbstract = false;
    
    var $parentStorageIsAbstract = false;
    
    var $parentFinderClassName = false;
    
    var $parentFinderClassIsAbstract = false;
    
    /**
     * @var array User-specified property settings
     */
    var $properties = array();
    
    var $fixMapperMethodNames = false;
    
    var $extraOwnPropertiesInfo = array();
    
    /**
     * ?|true|false
     * '?' => inheirted from Domain
     * @var string
     */
    var $useLangStrings = '?';
    
    var $langStringPrefix = false;
    
    var $tableLangStringPrefix = false;
    
    var $createAccessors = false;
    
    var $nullableColumns = false;
    
    var $mapperVars = array();
    
    protected $relationPrototypes = false;
    
    protected $assocProperties = false;
    
    protected $sqlSelectPrototype = false;

    // ---------------------------------------------------------------------
    
    /**
     * @var Ac_Cg_Domain
     */
    var $_domain = false;
    
    /**
     * @var Ac_Sql_Dbi_Table
     */
    var $tableObject = false;
    
    /**
     * Model properties
     */
    var $_properties = false;
    
    /**
     * List of model relations that will be used in properties and in the mapper including information whether they have corresponding models or not.
     * Obviously, if $this->_domain->relationsToNonModels = false, this array will have only elements with TRUE values.
     * $rel2 is null or reference to relation with third table for relations that use junction tables.
     * @var array (array($relName, $hasModel, $isIncoming, $rel2name, $isRel2Incoming), array($relName, $hasModel, $isIncoming, $rel2Name, $isRel2Incoming)) -- incoming relations are also included here
     */
    var $_relations = array();
    
    var $_init = false;
    
    /**
     * Name of property that holds record title (or FALSE)
     * @var string|bool
     */
    var $titleProp = '?';
    
    /**
     * Name of property that holds record 'published' status (or FALSE)
     * @var string|bool
     */
    var $publishedProp = '?';
    
    /**
     * Name of property that holds record 'ordering' value (or FALSE)
     * @var string|bool
     */
    var $orderingProp = '?';
    
    /**
     * Name of property that is used to group records orders (or FALSE)
     * @var string|bool
     */
    var $orderGroupProp = '?';
    
    /**
     * Whether to use unique index names instead of field names in mapper loadBy... methods 
     * @var bool
     */
    var $useIndexNamesInMapper = false;
    
    /**
     * @var string Name of class to inherit records list from in UI
     */
    var $uiListBaseClass = 'Ac_Page_List';
    
    /**
     * @var string Name of class to inherit records form from in UI
     */
    var $uiFormBaseClass = 'Ac_Page_Form';
    
    var $generateMethodPlaceholders = false;
    
    /**
     * Whether generated models hasUniformPropertiesInfo() should report true (it reports FALSE by default) 
     * @var bool
     */
    var $hasUniformPropertiesInfo = false;
    
    var $altDomainPrefix = false;
    
    var $modelCoreMixables = array();
    
    var $mapperCoreMixables = array();
    
    /**
     * Null or empty string - force NO parent model
     * FALSE - default behaviour (no parent model if there is no parent domain; otherwise, parent model
     * if parent domain has the same or matching table
     * @see Ac_Cg_Domain::autoParentModels
     * @see Ac_Cg_Domain::parentTableMap
     * @var type 
     */
    var $parentModelName = false;

    /**
     * If array is provided, only columns specified in this list will be used for the properties
     * @var bool|array
     */
    var $onlyColumns = false;
    
    /**
     * Ignore table columns specified in the list
     */
    var $ignoreColumns = array();

    /**
     * If array is provided, only relations specified in this list will be used to create associations
     * Identifier format: _rel_{ForeignKeyId}
     * @var bool|array
     */
    var $onlyRelations = false;
    
    /**
     * Ignore relations specified in the list
     * Identifier format: _rel_{foreignKeyId}
     * @var array
     */
    var $ignoreRelations = array();
    
    var $errors = array();
    
    var $warnings = array();
    
    /**
     * @var Ac_Cg_Model
     */
    protected $parentModel = false;
    
    function __construct($domain, $name, $config = array()) {
        $this->_domain = $domain;
        $this->name = $name;
        Ac_Util::simpleBindAll($config, $this);
    }
    
    function listProperties() {
        if ($this->_properties === false) {
            $this->_properties = array();
            $this->_determineUsableRelations();
            foreach ($this->_relations as $modelRelation) {
                $this->_addRelationPropertyConfig($modelRelation);
            }
            foreach ($this->tableObject->listColumns() as $colName) $this->_addSimplePropertyConfig($colName);
            if (is_array($this->properties) && $this->properties) {
                Ac_Util::ms($this->_properties, $this->properties);
            }
        }
        return array_keys($this->_properties);
    }
    
    function addProperty(Ac_Cg_Property $property) {
        $this->listProperties();
        $this->_properties[$property->name] = $property;
    }
    
    function addRelationInfo(Ac_Cg_Model_Relation $relation) {
        $this->_relations[] = $relation;
    }
    
    function _initAllProperties() {
        $pNames = array_keys($this->_properties);
        // Instantiating properties
        foreach ($pNames as $name) {
            $conf = $this->_properties[$name];
            if (isset($conf['metaPropertyClass']) && $conf['metaPropertyClass']) $cls = $conf['metaPropertyClass'];
            else $cls = 'Ac_Cg_Property_Simple';
            $this->_properties[$name]['_init'] = true;
            $this->_properties[$name] = new $cls ($this, $name, $this->_properties[$name]);
        }
        
    }
    
    /**
     * @return Ac_Cg_Property
     */
    function getProperty($name) {
        if (!in_array($name, $this->listProperties())) trigger_error ('No such property: \''.$name.'\'', E_USER_ERROR);
        if (is_array($this->_properties[$name])) {
            ini_set('html_errors', 1);
            $this->_initAllProperties();
        }
        return $this->_properties[$name];
    }
    
    function detectSpecialProperties() {
        //Name of property => name of domain property that holds default propname for autodetection or FALSE if domain doesn't have such default
        $adsp = array(
            'titleProp' => 'defaultTitlePropName',
            'orderingProp' => 'defaultOrderingPropName',
            'orderGroupProp' => false,
            'publishedProp' => 'defaultPublishedPropName',
        );
        foreach ($adsp as $myProp => $domainDefault) {
            if ($this->$myProp === '?') {
                $this->$myProp = false;
                if ($domainDefault && isset($this->_domain->$domainDefault) && ($dd = $this->_domain->$domainDefault)) {
                    if (!is_array($dd)) $dd = Ac_Util::toArray($dd);
                    if ($a = array_intersect($dd, $this->listProperties())) {
                        $this->$myProp = array_shift($a);
                    }
                }
            }
        }
        if (!strlen($this->titleProp) && $this->_domain->autoDetectTitles) {
            $titleCol = false;
            
            // search for first char/varchar column with length > 1, 
            // but prefer unique index char/varchar if found later
            
            foreach ($this->tableObject->listColumns() as $i) { $col = $this->tableObject->getColumn($i);
                if (in_array(strtolower($col->type), array('char', 'varchar')) && $col->width > 1) {
                    if ($col->isUnique()) {
                        $titleCol = $col->name;
                        break;
                    } elseif ($titleCol === false) {
                        $titleCol = $col->name;
                    }
                }
            }
            
            if (strlen($titleCol)) $this->titleProp = $titleCol;
            
        }
    }
    
    function getModelBaseName() {
        $res = $this->single;
        if ($this->subsystemPrefixes) $res = implode(' ', $this->subsystemPrefixes).' '.$res;
        if ($this->altDomainPrefix) $res = $this->altDomainPrefix.' '.$res;
            elseif ($this->_domain->appName && !$this->_domain->dontPrefixClassesWithAppName) $res = $this->_domain->appName.' '.$res;
        $res = Ac_Cg_Inflector::pearize($res);
        return $res;
    }
    
    function getDefaultClassName() {
        return $this->getModelBaseName();
    }
    
    function getDefaultParentClassName() {
        if (($pm = $this->getParentModel())) {
            $res = $pm->className;
        } else {
            $res = 'Ac_Model_Object';
        }
        return $res;
    }
    
    /**
     * @return array ('ownProperties' => array(...), 'ownAssociations' => array(...), 'ownLists' => array(...), 'ownPropertiesInfo' => array(...)) 
     */
    function getAeDataPropLists() {
        $ownProperties = array();
        $ownAssociations = array();
        $ownLists = array();
        $ownPropertiesInfo = array();
        
        foreach ($this->listProperties() as $name) {
            $prop = $this->getProperty($name);
            if (!$prop->isEnabled()) continue;
            if (!$this->isPropertyInherited($name)) {
                if (is_a($prop, 'Ac_Cg_Property_Object')) {
                    $ownAssociations[$prop->varName] = $prop->className;
                }
                if ($prop->pluralForList) $ownLists[$prop->varName] = $prop->pluralForList;
            }
            if ($prop->hasSeveralProperties()) {
                $pi = $prop->getAeModelPropertyInfo();
                if ($pi) $ownPropertiesInfo = array_merge($ownPropertiesInfo, $pi);
                $ownProperties = array_merge($ownProperties, array_keys($pi));
            } else {
                if ($mpi = $prop->getAeModelPropertyInfo()) $ownPropertiesInfo[$prop->varName] = $mpi;
                $ownProperties[] = $prop->varName;
            }
        }
        
        return array(
            'ownProperties' => $ownProperties, 
            'ownAssociations' => $ownAssociations, 
            'ownLists' => $ownLists, 
            'ownPropertiesInfo' => Ac_Util::m($ownPropertiesInfo, $this->extraOwnPropertiesInfo),
        );
    }
    
    function listInheritedModelMembers() {
        return array(
            'single', 'plural', 'singleCaption', 'pluralCaption', 
            'fixMapperMethodNames', 
            'useLangStrings', 'langStringPrefix', 'tableLangStringPrefix',
            'createAccessors'
        );
    }
    
    /**
     * Inititalizes default values
     */
    function init() {
        if ($this->_init) return;
        $this->_init = true;
        
        if (!$this->table) $this->table = $this->name;
        
        $infoOfTable = $this->_domain->analyzeTableName($this->table);
        $this->table = $infoOfTable['tableNameWithPrefix'];
        
        $info = $this->_domain->analyzeTableName($this->name);
        
        if ($parent = $this->getParentModel()) {
            $defs = get_class_vars(get_class($this));
            foreach ($this->listInheritedModelMembers() as $v) {
                if ($this->$v === $defs[$v]) $this->$v = $parent->$v;
            }
        }
        
        if (!strlen($this->plural)) {
            $this->plural = Ac_Cg_Inflector::camelize($info['pluralEntity']);
        }
        if (!strlen($this->single)) {
            $this->single = Ac_Cg_Inflector::camelize($info['singleEntity']);
        }
        $db = $this->_domain->getDatabase();
        $this->tableObject = $db->getTable($this->table);
        
        if ($this->subsystemPrefixes === false) $this->subsystemPrefixes = $info['subsystemPrefixes'];
        
        if (!$this->singleCaption) $this->singleCaption = Ac_Cg_Inflector::humanize($info['singleEntity']);
        if (!$this->pluralCaption) $this->pluralCaption = Ac_Cg_Inflector::humanize($info['pluralEntity']);
        if (!$this->className) $this->className = $this->getDefaultClassName();
        if (!$this->parentClassName) $this->parentClassName = $this->getDefaultParentClassName();
        if (!$this->parentMapperClassName) $this->parentMapperClassName = $this->getDefaultParentMapperClassName();
        if (!$this->parentStorageClassName) $this->parentStorageClassName = $this->getDefaultParentStorageClassName();
        
        if ($this->nullableColumns === false) {
            $this->nullableColumns = array();
            foreach ($this->listUsedColumns() as $i) {
                $col = $this->tableObject->getColumn($i);
                if ($col->nullable) $this->nullableColumns[] = $i;
            }
        }
        
        $this->detectSpecialProperties();
    }
    
    function beforeGenerate() {
    }

    /**
     * Checks whether other table of given relation is bi-junctional and returns relation from the junction table to third table or other model 
     *
     * @param Ac_Sql_Dbi_Relation $rel
     * @param bool $isIncoming Whether given relation is incoming
     * @param bool $toModelsOnly Ignore junctional tables that are related to non-models
     * 
     * @return Ac_Sql_Dbi_Relation Other relation that is involved in the junction
     */
    function _determineJunctionRelation ($rel, $isIncoming, $toModelsOnly) {
        $res = false;
        
        if ($isIncoming) $ot = $rel->ownTable; 
            else $ot = $rel->getForeignTable();
            
        if ($rels = $ot->isBiJunctionTable($this->_domain->getBiJunctionIgnore($ot->name))) {
            $otherRel = false;
            foreach (array_keys($rels) as $r) {
                if (!Ac_Util::sameObject($rels[$r], $rel)) {
                    $otherRel = $rels[$r];
                    break;
                }
            }
            if ($otherRel) {
                $thirdTable = $otherRel->getForeignTable();
                if (!$toModelsOnly || $this->_domain->searchModelByTable($thirdTable->name)) $res = $otherRel;
            }
        }
        return $res;
    }
    
    /**
     * Poplulates $this->_relations (see var description)
     */
    function _determineUsableRelations() {
        foreach ($this->tableObject->listRelations() as $relName) {
            $rel = $this->tableObject->getRelation($relName);
            if ($otherRel = $this->_determineJunctionRelation($rel, false, $this->_domain->relationsToNonModels)) {
                $otherModel = $this->_domain->searchModelByTable($otherRel->table);
                $this->_relations[] = new Ac_Cg_Model_Relation(array(
                    'relationName' => $relName, 
                    'hasModel' => $otherModel? true : false, 
                    'isIncoming' => false, 
                    'otherRelationName' => $otherRel->name, 
                    'isOtherRelationIncoming' => false
                ));
            } elseif (($model = $this->_domain->searchModelByTable($rel->table)) || $this->_domain->relationsToNonModels) {
                $this->_relations[] = new Ac_Cg_Model_Relation(array(
                    'relationName' => $relName, 
                    'hasModel' => $model? true : false, 
                    'isIncoming' => false, 
                    'otherRelationName' => false, 
                    'isOtherRelationIncoming' => false
                ));
            }
        }
        foreach ($this->tableObject->listIncomingRelations() as $relName) {
            $incRel = $this->tableObject->getIncomingRelation($relName);
            if ($otherRel = $this->_determineJunctionRelation($incRel, true, $this->_domain->relationsToNonModels)) {
                $otherModel = $this->_domain->searchModelByTable($otherRel->table);
                $this->_relations[] = new Ac_Cg_Model_Relation(array(
                    'relationName' => $relName, 
                    'hasModel' => $otherModel? true : false, 
                    'isIncoming' => true, 
                    'otherRelationName' => $otherRel->name, 
                    'isOtherRelationIncoming' => false
                ));
            } elseif (($model = $this->_domain->searchModelByTable($incRel->ownTable->name)) || $this->_domain->relationsToNonModels) {
                $this->_relations[] = new Ac_Cg_Model_Relation(array(
                    'relationName' => $relName, 
                    'hasModel' => $model? true : false, 
                    'isIncoming' => true, 
                    'otherRelationName' => false, 
                    'isOtherRelationIncoming' => false
                ));
            }
        }
    }
    
    /**
     * Adds automatic property config base on column $colName to $this->_properties array (if needed)
     */
    function _addSimplePropertyConfig($colName) {
        $this->_properties[$colName] = array(
            'column' => $colName, 
            'metaPropertyClass' => 'Ac_Cg_Property_Simple',
            'inherited' => $this->isPropertyInherited($colName, $ignore),
            'ignoreInDescendants' => $ignore,
            'enabled' => !$ignore && $this->isPropertyEnabled($colName, false),
        );
    }
    
    /**
     * Adds automatic property config based on relation $relName to $this->_properties array (if needed)
     */
    function _addRelationPropertyConfig(Ac_Cg_Model_Relation $modelRelation) {
        if ($modelRelation->hasModel) {
            $n = 0;
            do {
                /* following line of code caused getting class members like this:
                        var $_rel_#__foo_FK_foo_1 = false;
                   so it's commented out...
                */
                
                // $nmv = is_array($relName)? implode("_", $relName) : $relName;
                
                /*
                 * If relName is array, first value is table name and second value is database-unique relation name. We don't need table name to 
                 * guarantee uniqueness.
                 */
                $nmv = is_array($modelRelation->relationName)? $modelRelation->relationName[1] : $modelRelation->relationName;
                
                $nmv = str_replace($this->_domain->_database->replacePrefixWith, '', $nmv); 
                $nm = '_rel_'.$nmv.($n? $n : '');
                $n++;   
            } while (isset($this->_properties[$nm]));
            $xp = array(
                'relation' => $modelRelation->relationName, 
                'metaPropertyClass' => 'Ac_Cg_Property_Object', 
                'isIncoming' => $modelRelation->isIncoming, 
                'otherRelation' => $modelRelation->otherRelationName, 
                'isOtherIncoming' => $modelRelation->isOtherRelationIncoming,
                'modelRelation' => $modelRelation,
                'inherited' => $this->isPropertyInherited($nm, $ignore),
                'ignoreInDescendants' => $ignore,
                'enabled' => !$ignore && $this->isPropertyEnabled($nm, true),
            );
            //if (isset($this->properties[$nm]) && is_array($this->properties[$nm])) Ac_Util::ms($xp, $this->properties[$nm]);
            $this->_properties[$nm] = $xp;
        }
    }
    
    function isPropertyInherited($name, & $ignoreInDescendants = null) {
        $res = false;
        $ignoreInDescendants = false;
        if ($pm = $this->getParentModel()) {
            if (in_array($name, $pm->listProperties())) {
                $parentProp = $pm->getProperty($name);
                if ($parentProp->enabled || $parentProp->inherited && $parentProp->ignoreInDescendants) {
                    $res = true;
                }
                if ($parentProp->ignoreInDescendants) $ignoreInDescendants = true;
            }
        }
        return $res;
    }
    
    protected function isPropertyEnabled($name, $isRelation) {
        $res = true;
        if ($isRelation) {
            if (is_array($this->onlyRelations) && !in_array($name, $this->onlyRelations)) $res = false;
            elseif (in_array($name, $this->ignoreRelations)) $res = false;
        } else {
            if (is_array($this->onlyColumns) && !in_array($name, $this->onlyColumns)) $res = false;
            elseif (in_array($name, $this->ignoreColumns)) $res = false;
        }
        return $res;
    }
    
    /**
     * Returns array of model relations those which will be in <ModelName>_Mapper listRelations()/getRelations()) of current model
     * @return Ac_Cg_Model_Relation[]
     */
    function getParticipatingModelRelations() {
        $res = array();
        foreach ($this->listProperties() as $p) {
            $prop = $this->getProperty($p);
            if ($prop instanceof Ac_Cg_Property_Object && $prop->isEnabled()) {
                if ($prop->modelRelation) $res[] = $prop->modelRelation;
            }
        }
        return $res;
    }
    
    function getRelationPrototypes() {
        if ($this->relationPrototypes === false) {
            $this->relationPrototypes = array();
            $this->assocProperties = array();
            foreach ($this->getParticipatingModelRelations() as $r) {
                $prot = $this->getAeModelRelationPrototype($r);
                $key = isset($prot['srcVarName'])? $prot['srcVarName'] : count($this->relationPrototypes);
                if ($prop = $this->searchPropertyByRelation($r)) {
                    $this->assocProperties[$prop->getClassMemberName()] = $prop;
                } else {
                    var_dump("Prop by relation not found:", $r);
                }
                if ($r->createRelationObject) $this->relationPrototypes[$key] = $prot;
            }
        }
        return $this->relationPrototypes;
    }
    
    function getAssocProperties() {
        if ($this->assocProperties === false) $this->getRelationPrototypes ();
        return $this->assocProperties;
    }
    
    function getSqlSelectPrototype() {
        if ($this->sqlSelectPrototype === false) {
            $this->sqlSelectPrototype = array();
            foreach ($this->listProperties() as $i) {
                $this->getProperty($i)->applyToSqlSelectPrototype($this->sqlSelectPrototype);
            }
        }
        return $this->sqlSelectPrototype;
    }
    
    /**
     * @return array|false
     */
    function getAeModelRelationPrototype(Ac_Cg_Model_Relation $relation) {
        $res = false;
        if ($relation->hasModel) {
            if ($prop = $this->searchPropertyByRelation($relation)) {
                 $res = $prop->getAeModelRelationPrototype(); 
            } else {
                var_dump("Relation not found: ", $relation->relationName, $relation, ''.(new Exception), $this->className);
            }
        } else {
            $res = $this->getNonModelRelationPrototype($modelRelIndex, $relation->isIncoming, $relation->otherRelationName);
        }
        return $res;
    }
    
    /**
     * @return Ac_Cg_Property
     */
    function searchPropertyByRelation(Ac_Cg_Model_Relation $relation) {
        foreach ($this->listProperties() as $name) {
            $prop = $this->getProperty($name);
            if ($prop instanceof Ac_Cg_Property_Object && $prop->modelRelation === $relation) {
                $res = $prop;
                break; 
            }
        }
        return $res;
    }
    
    /**
     * @param Ac_Model_Property $prop
     */
    function searchRelationIdByProperty($prop) {
        $res = false;
        foreach ($this->_relations as $k => $v) {
            if ($v->relationName == $prop->relation) {
                $res = $k;
                break;
            }
        }
        return $res;
    }
    
    function getGenModelClass() {
        return $this->className.'_Base_Object';
    }
    
    function getGenMapperClass() {
        return $this->className.'_Base_Mapper';
    }
    
    function getGenStorageClass() {
        return $this->className.'_Base_Storage';
    }
    
    function getMapperClass() {
        return $this->className.'_Mapper';
    }
    
    function getStorageClass() {
        return $this->className.'_Storage';
    }
    
    function getMapperRecordClass() {
        return $this->className;
    }
    
    function getMapperInfoParams() {
        $res = array(
            'singleCaption' => $this->singleCaption,
            'pluralCaption' => $this->pluralCaption,
        );
        if ($this->getUseLangStrings()) {
            $s = $this->getTableLangStringPrefix($this->name).'_single';
            $p = $this->getTableLangStringPrefix($this->name).'_plural';
            $res['singleCaption'] = new Ac_Cg_Php_Expression("new Ac_Lang_String(".Ac_Util_Php::export($s, true).")");
            $res['pluralCaption'] = new Ac_Cg_Php_Expression("new Ac_Lang_String(".Ac_Util_Php::export($p, true).")");
        }
        return $res;
    }
    
    function getDefaultParentMapperClassName() {
        if ($pm = $this->getParentModel()) {
            $res = $pm->getMapperClass();
        } else {
            $res = 'Ac_Model_Mapper';
        }
        return $res;
    }
    
    function getDefaultParentStorageClassName() {
        if ($pm = $this->getParentModel()) {
            $res = $pm->getStorageClass();
        } else {
            $res = 'Ac_Model_Storage_MonoTable';
        }
        return $res;
    }
    
    /**
     * Returns prototype for creating Ac_Model_Relation
     * @return array
     */
    function getNonModelRelationPrototype($relName, $isIncoming, $otherRel) {
        $res = array();
        $res['srcRecordClass'] = $this->className;
        $res['srcMapperClass'] = $this->getMapperClass();
        if ($isIncoming) {
            $rel = $this->tableObject->getIncomingRelation($relName);
            $res['destTableName'] = $rel->ownTable->name;
            $res['fieldLinks'] = array_flip($rel->columns);
            $res['srcIsUnique'] = $rel->isOtherRecordUnique();
            $res['destIsUnique'] = $rel->isThisRecordUnique();
        } else {
            $rel = $this->tableObject->getRelation($relName);
            $res['destTableName'] = $rel->table;
            $res['fieldLinks'] = $rel->columns;
            $res['srcIsUnique'] = $rel->isThisRecordUnique();
            $res['destIsUnique'] = $rel->isOtherRecordUnique(); 
        }
        // TODO: $rel['srcVarName'] = ???? - where should I get this var???
        // TODO: create Ac_Cg_Property_Table   
        return $res;
    }
    
    /**
     * Returns array with subsystem prefixes that are common for both this and other models
     *
     * @param Ac_Cg_Model $otherModel
     * @return array
     */
    function getCommonSubsystemPrefixes($otherModel) {
        $myPrefixes = $this->subsystemPrefixes;
        $otherPrefixes = $otherModel->subsystemPrefixes;
        $res = array();
        $n = min (count($myPrefixes), count($otherPrefixes));
        for ($i = 0; $i < $n; $i++)
            if ($myPrefixes[$i] == $otherPrefixes[$i]) $res[] = $myPrefixes[$i];
                else break;
        $next = count($res);
        
        // Consider my name as a part of other subsystem' prefix (i.e. user => userItems)
        if (isset($otherPrefixes[$next]) && !isset($myPrefixes[$next]) && $this->single == $otherPrefixes[$next]) {
            $res[] = $this->single;
        }
        return $res; 
    }
    
    function listSystems() {
        return array_merge($this->subsystemPrefixes, array($this->name));
    }
    
    function getConflictsInfo($unresolved = false) {
        $res = array();
        $props = array();
        foreach ($this->listProperties() as $i) {
            $prop = $this->getProperty($i);
            if ($prop instanceof Ac_Cg_Property_Object) {
                if (!isset($props[$prop->className])) {
                    $props[$prop->className] = array('props' => array(), 'byVarName' => array(), 'unresolved' => 0);
                }
                $props[$prop->className]['props'][] = $i;
                if (!isset($props[$prop->className]['byVarName'][$v = $prop->getDefaultVarName(true)])) {
                    $props[$prop->className]['byVarName'][$v][] = 1;
                } else {
                    $props[$prop->className]['byVarName'][$v]++;
                    $props[$prop->className]['unresolved'] = 1;
                }
            }
        }
        foreach ($props as $className => $p) {
            if (count($p['props']) > 1 && $p['unresolved']) {
                $props[$className]['suffixes'] = $this->findRelationSuffixes($p['props']);
            }
        }
        foreach ($props as $className => $p) {
            if (count($p['props']) > 1) {
                if (!$unresolved || $p['unresolved']) {
                    $res[$className] = $p;
                }
            }
        }
        return $res;
    }

    function findRelationSuffixes($propsList) {
        $rels = array();
        foreach ($propsList as $prop) {
            if (!is_object($prop)) $prop = $this->getProperty($prop);
            if (!($prop instanceof Ac_Cg_Property_Object)) throw new Exception("items of \$propsList shuld be either Ac_Cg_Property_Object instances or their IDs");
            $rels[$prop->name] = $prop->getRelation()->name;
        }
        $cp = Ac_Cg_Util::findCommonPrefix($rels);
        $res = array();
        foreach ($rels as $propName => $relName) {
            $res[$propName] = Ac_Cg_Inflector::camelize(substr($relName, strlen($cp)));
        }
        return $res;
    }
    
    function getUseLangStrings() {
        if ($this->useLangStrings === '?') $res = $this->_domain->useLangStrings;
        else $res = $this->useLangStrings;
        return $res;
    }
    
    function getLangStringPrefix() {
        if ($this->langStringPrefix === false) $res = strtolower(Ac_Cg_Inflector::definize($this->_domain->getLangStringPrefix().'_'.$this->single));
        else $res = $this->langStringPrefix;
        return $res;
    }
    
    function getTableLangStringPrefix($after = false) {
        if ($this->tableLangStringPrefix === false) $res = $this->_domain->getTableLangStringPrefix();
            else $res = $this->tableLangStringPrefix;
        if ($after !== false) $res = strtolower(Ac_Cg_Inflector::definize($res.'_'.$after));
        return $res;
    }

    function getTableLangStrings() {
        $res = array();
        $res[$this->getTableLangStringPrefix($this->name).'_single'] = $this->single;
        $res[$this->getTableLangStringPrefix($this->name).'_plural'] = $this->plural;
        return $res;
    }
    
    function getAllLangStrings() {
        $res = $this->getTableLangStrings();
        foreach ($this->listProperties() as $p) {
            $prop = $this->getProperty($p);
            $res[$prop->getLangStringName()] = $prop->caption;
        }
        return $res;
    }
    
    function getReferenceFieldsData() {
        $res = array();
        foreach ($this->listProperties() as $i) {
            $prop = $this->getProperty($i);
            if ($prop instanceof Ac_Cg_Property_Object) {
                if (strlen($f = $prop->getForeignKeyFieldName())) {
                    $res[$f] = $prop->getClassMemberName();
                }
            }
        }
        return $res;
    }
    
    function onShow() {
        $res = array(
            'getReferenceFieldsData()' => $this->getReferenceFieldsData(),
            'class' => get_class($this),
            //'modelRelations' => $this->_relations,
        );
        if ($this->errors) $res['errors'] = $this->errors;
        if ($this->warnings) $res['warnings'] = $this->warnings;
        return $res;
    }
    
    function getInternalDefaults() {
        $res = array();
        foreach ($this->listProperties() as $i) {
            $prop = $this->getProperty($i);
            if ($prop->enabled && $prop instanceof Ac_Cg_Property_Object) {
                Ac_Util::ms($res, $prop->getAllClassMembers());
            }
        }
        return $res;
    }    
    
    function getAssociationPrototypes() {
        $res = array();
        foreach ($this->listProperties() as $p) {
            $prop = $this->getProperty($p);
            if ($prop instanceof Ac_Cg_Property_Object && $prop->isEnabled()) {
                if ($prop->modelRelation && $prop->modelRelation->createAssociationObject) {
                    $res[$prop->varName] = $prop->getAssociationPrototype();
                }
            }
        }
        return $res;
    }
    
    function getRelationProviderPrototypes() {
        $res = array();
        foreach ($this->listProperties() as $p) {
            $prop = $this->getProperty($p);
            if ($prop instanceof Ac_Cg_Property_Object && $prop->isEnabled()) {
                if ($prop->modelRelation && $prop->modelRelation->createRelationObject) {
                    if ($proto = $prop->getRelationProviderPrototype()) {
                        $res[$prop->varName] = $proto;
                    }
                }
            }
        }
        return $res;
    }
        
    function getTemplates() {
        return array(
            'modelAndMapper' => 'Ac_Cg_Template_ModelAndMapper'
        );
    }
    
    /**
     * @return Ac_Cg_Property_Object
     */
    function findPropertyByForeignKeyId($id) {
        $res = null;
        foreach ($this->listProperties() as $i) {
            $prop = $this->getProperty($i);
            if ($prop instanceof Ac_Cg_Property_Object) {
                if ($prop->getRelation()->name == $id) {
                    $res = $prop;
                }
            }
        }
        return $res;
    }
    
    /**
     * @return Ac_Cg_Model
     */
    function getParentModel() {
        if ($this->parentModel === false) {
            $this->parentModel = null;
            $parentDomain = $this->_domain->getParentDomain();
            if ($parentDomain) {
                if ($this->parentModelName === false && $this->_domain->autoParentModels) {
                    $searchTable = $this->table;
                    if (is_array($this->_domain->parentTableMap) && isset($this->_domain->parentTableMap[$searchTable])) {
                        $searchTable = $this->_domain->parentTableMap[$searchTable];
                    }
                    $this->parentModel = $parentDomain->searchModelByTable($searchTable);
                }
            }
        }
        return $this->parentModel;
    }
    
    function listUsedColumns() {
        $res = array();
        foreach ($this->listProperties() as $i) {
            $prop = $this->getProperty($i);
            if ($prop instanceof Ac_Cg_Property_Simple && $prop->enabled) $res[] = $i;
        }
        return $res;
    }
    
    /**
     * @return array (myProperty => array(arrayKey, defaultClass, crArgs))
     * crArgs => array(keyA, keyB, keyC) <- constructor args map
     * crArgs = false -- just copy $this->$myProperty to/from $array[$arrayKey]
     */
    function getSerializationMap() {
        $res = array(
            '_properties' => array('_properties', 'Ac_Cg_Property', array('__parent', 'name')),
            '_relations' => array('_relations', 'Ac_Cg_Model_Relation', array()),
        );
        return $res;
    }
    
    protected function beforeSerialize(& $vars) {
        unset($vars['tableObject']);
    }
    
    function initProperties() {
        foreach ($this->_properties as $p) $p->init();        
    }
    
    function unserializeFromArray($array) {
        parent::unserializeFromArray($array);
        $this->tableObject = $this->_domain->getDatabase()->getTable($this->table);
        $this->_init = true;
    }    
    
}
