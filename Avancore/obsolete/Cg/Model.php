<?php

/**
 * Model metamodel for Code Generator
 */
        
class Cg_Model {

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
    
    var $parentClassIsAbstract = false;
    
    var $parentMapperIsAbstract = false;
    
    var $parentFinderClassName = false;
    
    var $parentFinderClassIsAbstract = false;
    
    /**
     * @var bool Don't generate user interface for this model
     */
    var $noUi = false;
    
    /**
     * Records can be created with UI
     * @var bool
     */
    var $uiCanCreate = true;
    
    /**
     * Records can be deleted with UI
     * @var bool
     */
    var $uiCanDelete = true;
    
    /**
     * @var array User-specified property settings
     */
    var $properties = array();
    
    /**
     * Whether Ac_Model_Object should track it's changes (and it's trackChanges() function should return true)
     * @var bool
     */
    var $tracksChanges = false;
    
    // --------------------- tree-like structures support -----------------
    
    /**
     * Make model hierarchycally enabled either by using Pmt_Tree_NestedSetsImpl or by using Pmt_Tree_AdjacencyListImpl   
     */    
    var $hierarchy = false;
    
    /**
     * Name of table with nested sets. If $nestedSetsTable isn't set, adjacency list model will be assumed.
     * @var false|string
     */
    var $nestedSetsTable = false;
    
    /**
     * Name of column with node id for hierarchy based on adjacency list.
     * By default primary key will be assumed.
     * 
     * @var false|string
     */
    var $nodeIdCol = false;
    
    /**
     * Name of column with parent id for hierarchy based on adjacency list.
     * @var false|string
     */
    var $parentIdCol = false;
        
    /**
     * Name of column with ordering for hierarchy based on adjacency list.
     * @var false|string
     */
    var $orderingCol = false;
    
    var $fixMapperMethodNames = false;

    // ---------------------------------------------------------------------
    
    /**
     * @var Cg_Domain
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
    
    var $generatePmtFinder = '?';
    
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
    
    function Cg_Model($domain, $name, $config = array()) {
        $this->_domain = $domain;
        $this->name = $name;
        Ac_Util::simpleBindAll($config, $this);
    }
    
    function listProperties() {
        $this->init();        
        if ($this->_properties === false) {
            $this->_properties = array();
            $this->_determineUsableRelations();
            foreach ($this->_relations as $relInfo) {
                $this->_addRelationPropertyConfig($relInfo[0], $relInfo[1], $relInfo[2], $relInfo[3], $relInfo[4]);
            }
            foreach ($this->tableObject->listColumns() as $colName) $this->_addSimplePropertyConfig($colName);
            if (is_array($this->properties) && $this->properties) {
                Ac_Util::ms($this->_properties, $this->properties);
            }
        }
        return array_keys($this->_properties);
    }

    function _initAllProperties() {
        $pNames = array_keys($this->_properties);
        // Instantiating properties
        foreach ($pNames as $name) {
            $conf = $this->_properties[$name];
            if (isset($conf['metaPropertyClass']) && $conf['metaPropertyClass']) $cls = $conf['metaPropertyClass'];
            else $cls = 'Cg_Property_Simple';
            $this->_properties[$name] = new $cls ($this, $name, $this->_properties[$name]);
        }
        
    }
    
    function hasPmtFinder() {
        if ($this->generatePmtFinder === '?') $res = (bool) $this->_domain->_gen->generatePmtFinders;
            else $res = (bool) $this->generatePmtFinder;
        return $res;
    }
    
    /**
     * @return Cg_Property
     */
    function getProperty($name) {
        if (!in_array($name, $this->listProperties())) trigger_error ('No such property: \''.$name.'\'', E_USER_ERROR);
        if (is_array($this->_properties[$name])) {
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
    }
    
    function getModelBaseName() {
        $res = $this->single;
        if ($this->subsystemPrefixes) $res = implode(' ', $this->subsystemPrefixes).' '.$res;
        if ($this->_domain->appName && !$this->_domain->dontPrefixClassesWithAppName) $res = $this->_domain->appName.' '.$res;
        $res = Cg_Inflector::pearize($res);
        return $res;
    }
    
    function getDefaultClassName() {
        return $this->getModelBaseName();
    }
    
    function getDefaultParentClassName() {
        if (count($this->tableObject->listPkFields()) == 1) $res = 'Ac_Model_Object';
            else $res = 'Ac_Model_CpkObject'; 
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
            if (is_a($prop, 'Cg_Property_Object')) {
                $ownAssociations[$prop->varName] = $prop->className;
            }
            if ($prop->pluralForList) $ownLists[$prop->varName] = $prop->pluralForList;
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
            'ownPropertiesInfo' => $ownPropertiesInfo
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
        
        if (!strlen($this->plural)) {
            $this->plural = Cg_Inflector::camelize($info['pluralEntity']);
        }
        if (!strlen($this->single)) {
            $this->single = Cg_Inflector::camelize($info['singleEntity']);
        }
        $db = $this->_domain->getDatabase();
        $this->tableObject = $db->getTable($this->table);
        
        if ($this->subsystemPrefixes === false) $this->subsystemPrefixes = $info['subsystemPrefixes'];
        
        if (!$this->singleCaption) $this->singleCaption = Cg_Inflector::humanize($info['singleEntity']);
        if (!$this->pluralCaption) $this->pluralCaption = Cg_Inflector::humanize($info['pluralEntity']);
        if (!$this->className) $this->className = $this->getDefaultClassName();
        if (!$this->parentClassName) $this->parentClassName = $this->getDefaultParentClassName();
        if (!$this->parentMapperClassName) $this->parentMapperClassName = $this->getDefaultParentMapperClassName();
        
        $this->detectSpecialProperties();
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
                $this->_relations[] = array($relName, $otherModel? true : false, false, $otherRel->name, false);
            } elseif (($model = $this->_domain->searchModelByTable($rel->table)) || $this->_domain->relationsToNonModels) {
                $this->_relations[] = array($relName, $model? true : false, false, false, false);
            }
        }
        foreach ($this->tableObject->listIncomingRelations() as $relName) {
            $incRel = $this->tableObject->getIncomingRelation($relName);
            if ($otherRel = $this->_determineJunctionRelation($incRel, true, $this->_domain->relationsToNonModels)) {
                $otherModel = $this->_domain->searchModelByTable($otherRel->table);
                $this->_relations[] = array($relName, $otherModel? true : false, true, $otherRel->name, false);
            } elseif (($model = $this->_domain->searchModelByTable($incRel->ownTable->name)) || $this->_domain->relationsToNonModels) {
                $this->_relations[] = array($relName, $model? true : false, true, false, false);
            }
        }
    }
    
    /**
     * Adds automatic property config base on column $colName to $this->_properties array (if needed)
     */
    function _addSimplePropertyConfig($colName) {
        $this->_properties[$colName] = array('column' => $colName, 'metaPropertyClass' => 'Cg_Property_Simple');
    }
    
    /**
     * Adds automatic property config based on relation $relName to $this->_properties array (if needed)
     */
    function _addRelationPropertyConfig($relName, $hasModel, $isIncoming, $otherRelationName, $otherRelationIsIncoming) {
        if ($hasModel) {
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
                $nmv = is_array($relName)? $relName[1] : $relName;
                
                $nmv = str_replace($this->_domain->_database->replacePrefixWith, '', $nmv); 
                $nm = '_rel_'.$nmv.($n? $n : '');
                $n++;   
            } while (isset($this->_properties[$nm]));
            $xp = array('relation' => $relName, 'metaPropertyClass' => 'Cg_Property_Object', 'isIncoming' => $isIncoming, 
                'otherRelation' => $otherRelationName, 'isOtherIncoming' => $otherRelationIsIncoming);
            //if (isset($this->properties[$nm]) && is_array($this->properties[$nm])) Ac_Util::ms($xp, $this->properties[$nm]);
            $this->_properties[$nm] = $xp;
        }
    }
    
    /**
     * Returns names of used relations (those which will be in <ModelName>_Mapper listRelations()/getRelations()) of current model
     * @return array 
     */
    function listAeModelRelations() {
        $this->init();
        $res = array();
        //foreach (array_keys($this->_relations) as $k) {
        //  if ($this->_relations[$k]->isEnabled()) $res[] = $k;
        //}
        //var_dump($this->_relations);
        foreach ($this->listProperties() as $p) {
            $prop = $this->getProperty($p);
            if (is_a($prop, 'Cg_Property_Object') && $prop->isEnabled()) {
                $res[] = $this->searchRelationIdByProperty($prop);
            }
        }
        return $res;
    }
    
    /**
     * @return array|false
     */
    function getAeModelRelationPrototype($relName) {
        $this->init();
        $res = false;
        if (isset($this->_relations[$relName])) {
            if ($this->_relations[$relName][1]) {
                if ($prop = $this->_searchPropertyByRelation($this->_relations[$relName][0])) {
                     $res = $prop->getAeModelRelationPrototype(); 
                } else {
                    var_dump("Relation not found: ", $this->_relations[$relName][0]);
                }
            } else {
                $res = $this->getNonModelRelationPrototype($relName, $this->_relations[$relName][2], $this->_relations[$relName][3]);
            }
        }
        return $res;
    }
    
    /**
     * @return Cg_Property
     */
    function searchPropertyByRelation($relName) {
        $this->init();
        $res = null;
        if (isset($this->_relations[$relName])) {
            return $this->_searchPropertyByRelation($this->_relations[$relName][0]);
        }
        return $res;
    }
    
    /**
     * @return Cg_Property
     */
    function _searchPropertyByRelation($relName) {
        $res = null;
        foreach ($this->listProperties() as $name) {
            $prop = $this->getProperty($name);
            if (is_a($prop, 'Cg_Property_Object') && ($prop->relation == $relName)) {
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
            if ($v[0] == $prop->relation) {
                $res = $k;
                break;
            }
        }
        return $res;
    }
    
    function getGenMapperClass() {
        $this->init();
        return $this->className.'_Base_Mapper';
    }
    
    function getMapperClass() {
        $this->init();
        return $this->className.'_Mapper';
    }
    
    function getMapperInfoParams() {
        return array(
            'singleCaption' => $this->singleCaption,
            'pluralCaption' => $this->pluralCaption,
            'hasUi' => !$this->noUi,
        );
    }
    
    function getDefaultParentMapperClassName() {
        $this->init();
        if (count($this->tableObject->listPkFields()) == 1) $res = 'Ac_Model_Mapper';
            else $res = 'Ac_Model_CpkMapper'; 
        return $res;
    }
    
    /**
     * Returns prototype for creating Ac_Model_Relation
     * @return array
     */
    function getNonModelRelationPrototype($relName, $isIncoming, $otherRel) {
        $this->init();
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
        // TODO: create Cg_Property_Table   
        return $res;
    }
    
    /**
     * Returns array with subsystem prefixes that are common for both this and other models
     *
     * @param Cg_Model $otherModel
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
            if ($prop instanceof Cg_Property_Object) {
                if (!isset($props[$prop->className])) {
                    $props[$prop->className] = array('props' => array(), 'byVarName' => array(), 'unresolved' => 0);
                }
                $props[$prop->className]['props'][] = $i;
                if (!isset($props[$prop->className]['byVarName'][$v = $prop->getDefaultVarName()])) {
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
            if (!($prop instanceof Cg_Property_Object)) throw new Exception("items of \$propsList shuld be either Cg_Property_Object instances or their IDs");
            $rels[$prop->name] = $prop->getRelation()->name;
        }
        $cp = Cg_Util::findCommonPrefix($rels);
        $res = array();
        foreach ($rels as $propName => $relName) {
            $res[$propName] = substr($relName, strlen($cp));
        }
        return $res;
    }
    
}

?>