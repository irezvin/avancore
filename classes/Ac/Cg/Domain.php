<?php

/**
 * Domain metamodel for Code Generator. Models between different domains are not linked
 **/

class Ac_Cg_Domain extends Ac_Cg_Base {
    
    /**
     * @var string Name of the domain
     */
    
    var $name = false;
    
    /**
     * @var string Name of the application. In most cases, it will be used as class directory. Defaults to the name of the domain. 
     */
    var $appName = false;
    
    /**
     * @var string Class of the Application instance. Defaults to $appName
     */
    var $appClass = false;
    
    var $appBaseClass = 'Ac_Application';
    
    var $componentsAccessorBaseClass = 'Ac_Application_ComponentsAccessor';
    
    /**
     * Caption of whole application (defaults to appName)
     * @var string
     */
    var $caption = false;
    
    /**
     * @var string Name of the database (is taken from the config). Defaults to the name of the domain.
     */
    var $dbName = false;
    
    /**
     * @var array Associatve arrays of model prototypes (is taken from the config): 'modelName' => array(modelConfig...)
     */
    var $models = array();
    
    /**
     * @var bool Whether to generate relations in mappers to tables that are not models or not
     */
    var $relationsToNonModels = false;
    
    /**
     * @var string Table prefix to replace in table names, if any (i.e. "asm_")
     */
    var $tablePrefix = false;
    
    /**
     * @var string Placeholder to replace table prefix with (i.e. "#__")
     */
    var $replaceTablePrefixWith = '#__';
    
    var $stripTablePrefixFromIds = true;
    
    var $extraStripFromIds = false;
    
    /**
     * @var array|string|bool Names of tables or regular expression or TRUE to add all tables
     * 
     */
    var $autoTables = array();
    
    /**
     * @var bool All tables will be included to 'autoModelTables'
     */
    var $autoTablesAll = false;

    /**
     * @var array|string Names of tables or regular expression 
     * Which tables to ignore when creating models
     */
    var $autoTablesIgnore = array();
    
    /**
     * @var bool Whether table names are considered plural or singular
     */
    var $tableNamesArePlural = true;

    /**
     * List of subsystem prefixes that can appear in the table names and are not being part of caption
     *
     * @var array
     */
    var $subsystemPrefixes = array();
    
    /**
     * User-defined names of auto-generated models
     * @var array tableName => modelName
     */
    var $modelNames = array();
    
    /**
     * Pairs of subsystems that should not be automatically associated with to-subsystems (one-directional).
     * 
     * For example, we have Content_Article.versionId => Element_Version.id.
     * By default, codegen will create Content_Articles::getElementVersion() and Element_Version::getContentArticles().
     * But if we will add array('element', 'content') into this list, Element_Version::getContentArticles() won't be created.   
     *  
     * @var array(array(toSubsystem, fromSubsystem), array(toSubsystem2, fromSubsystem2), ...) 
     */
    var $dontLinkSubsystems = array();
    
    /**
     * Whether subsystem prefix can appera only once in the table name
     *
     * @var bool
     */
    var $subsystemPrefixAppearsOnlyOnce = true;
    
    /**
     * Replacements that is done on table names to convert them to model names
     * @var array regex => replacement
     */
    var $tableNameFixes = array();
    
    var $dontPrefixClassesWithAppName = false;
    
    /**
     * @var Ac_Cg_Generator
     */
    var $_gen = false;
    
    /**
     * @var Ac_Sql_Dbi_Database
     */
    var $_database = false;
    
    /**
     * @var array Ac_Cg_Model instances
     */
    var $_models = false;
    
    /**
     * Default name for model special properties auto-detection
     * @see Ac_Cg_Model::detectSpecialProperties  
     */
    var $defaultTitlePropName = false;
    
    var $autoDetectTitles = true;
    
    /**
     * Default name for model special properties auto-detection
     * @see Ac_Cg_Model::detectSpecialProperties  
     */
    var $defaultPublishedPropName = false;
    
    /**or
     * Default name for model special properties auto-detection
     * @see Ac_Cg_Model::detectSpecialProperties  
     */
    var $defaultOrderingPropName = false;
    
    /**
     * Default options for model
     * @var array 
     */
    var $modelDefaults = array();
    
    /**
     * User-defined elements to add to database schema
     * @var array
     */
    var $schemaExtras = array();
    
    /**
     * Dictionary to hold singular, plural forms and translations 
     *
     * @var Ac_Cg_Dictionary
     */
    var $dictionary = false;
    
    /**
     * Use 'new Pmt_Lang_String(...)' instead of captions
     * @var bool
     */
    var $useLangStrings = false;
    
    /**
     * @var type List of columns with service information to be ignored for proper recognition of n-to-n relations
     * Format: array(commonColName, commonColName, tableName => array(colName, colName), '' => array(colNamesForAllTablesNotListedHere))
     */
    var $ignoredColumnsInJunctionTables = array();
    
    /**
     * @var array('regExp' => array('extraConfig'))
     */
    var $extraConfigByTables = array();
    
    var $langStringPrefix = false;
    
    var $tableLangStringPrefix = false;
    
    var $addSubsystemsToMapperMethods = true;
    
    var $overrideTypesUsingDocBlocks = true;
    
    /**
     * Name of the domain that current domain inherits
     * Must be returned by Ac_Cg_Generator::getDomain()
     * @var string
     */
    var $parentDomainName = false;

    /**
     * If $autoParentModels === true and $parentDomainName !== false,
     * models will have parent models assigned using matching table names
     * ($parentModelName will still override this value)
     * @var bool
     */
    var $autoParentModels = true;
    
    /**
     * Map for automatic parent model assignment
     * array ($myTableName => $parentTableName) // both with prefixes replaced to #__
     * @var array 
     */
    var $parentTableMap = array();
    
    /**
     * Decorator to modify model names
     */
    var $modelNameDecorator = array();
    
    /**
     * If parent domain is set, list of the properties that shouldn't
     * be inherited from it
     */
    var $dontInheritProperties = array();
    
    var $domainTemplates = false;
    
    protected $parentDomain = false;
    
    protected $langStrings = array();
    
    /**
     * @var Ac_Sql_Dbi_Inspector
     */
    protected $inspector = false;
    
    /**
     * @param Ac_Cg_Generator $generator 
     */
    function __construct(array $options = array()) {
        $this->initOptionsFirst(['generator', 'name', 'parentDomainName', 'dontInheritProperties'], $options);
        if (!$this->name) throw new Ac_E_InvalidUsage("\$options['name'] must be provided for ".__METHOD__);
        $dicConf = [];
        if (isset($options['dictionary']) && is_array($options['dictionary'])) {
            $dicConf = $options['dictionary'];
            unset($options['dictionary']);
        }
        $this->inheritDefaults($options);
        parent::__construct($options);
        
        if (!$this->dbName) $this->dbName = $this->name;
        if (!$this->appName) $this->appName = $this->name;
        if (!$this->caption) $this->caption = $this->appName;
        
        if (!isset($dicConf['constantPrefix'])) $dicConf['constantPrefix'] = $this->appName; 
        $this->dictionary = new Ac_Cg_Dictionary($dicConf);
    }
    
    protected function setGenerator(Ac_Cg_Generator $generator) {
        $this->_gen = $generator;
    }
    
    protected function inheritDefaults(array & $options) {
        if ($this->parentDomainName) {
            $pd = $this->getParentDomain();
            foreach (array_diff(array(
                'dbName',
                'tablePrefix',
                'stripTablePrefixFromIds',
                'extraStripFromIds',
                'autoTables',
                'autoTablesAll',
                'autoTablesIgnore',
                'replaceTablePrefixWith',
                'dontLinkSubsystems',
                'defaultTitlePropName',
                'defaultPublishedPropName',
                'defaultOrderingPropName',
                'modelDefaults',
                'ignoredColumnsInJunctionTables',
                'extraConfigByTables',
                'langStringPrefix',
                'tableLangStringPrefix',
                'addSubsystemsToMapperMethods',
                'overrideTypesUsingDocBlocks',
                'subsystemPrefixes',
                'subsystemPrefixAppearsOnlyOnce',
                'schemaExtras',
                'dictionary'
            ), $this->dontInheritProperties) as $propName) {
                if ($propName === 'dictionary') {
                    $parentVal = $pd->dictionary->getConfig();
                    unset($parentVal['constantPrefix']);
                } else {
                    $parentVal = $pd->$propName;
                }
                $this->$propName = $parentVal;
                if (!in_array($propName, array('autoTablesIgnore'))) {
                    if (isset($options[$propName]) && is_array($options[$propName]) && is_array($this->$propName)) {
                        Ac_Util::ms($this->$propName, $options[$propName]);
                        if (in_array($propName, 'subsystemPrefixes')) {
                            $this->$propName = array_unique($this->$propName);
                        }
                        unset($options[$propName]);
                    }
                }
            }
        }
    }
    
    function listModels() {
        if ($this->_models === false) {
            $this->_models = array();
            foreach ($this->_calculateModelsConfig() as $name => $config) {
                $this->_models[$name] = $config; 
            }
            $this->resolveConflicts();
        }
        return array_keys($this->_models);
    }
    
    protected function resolveConflicts() {
        $ci = array();
        foreach ($this->listModels() as $i) {
            if ($c = $this->getModel($i)->getConflictsInfo(true)) {
                $ci[$i] = $c;
            }
        }

        foreach ($ci as $modelId => $cc) {
            $mod = $this->getModel($modelId);
            foreach ($cc as $c) {
                foreach ($c['props'] as $name) {
                    $p = $mod->getProperty($name);
                    $p->otherModelIdInMethodsPrefix = $c['suffixes'][$name];
                    $p->varName = $p->getDefaultVarName();
                    $p->pluralForList = $p->getDefaultPluralForList();
                }
            }
        }

    }
    
    /**
     * @return Ac_Sql_Dbi_Inspector
     */
    function getInspector() {
        if ($this->inspector === false) {
            $res = $this->_gen->getInspector();
        } else {
            $res = $this->inspector;
        }
        return $res;
    }
    
    /**
     * @return Ac_Sql_Dbi_Database
     */
    function getDatabase() {
        if ($this->_database === false) {
            $insp = $this->getInspector();
            $this->_database = new Ac_Sql_Dbi_Database([
                'inspector' => $insp, 
                'name' => $this->dbName, 
                'tablePrefix' => $this->tablePrefix, 
                'replacePrefixWith' => $this->replaceTablePrefixWith, 
                'extras' => $this->schemaExtras
            ]);
        }
        return $this->_database;
    }
    
    /**
     * @param string $name Name of model 
     * @return Ac_Cg_Model
     */
    function getModel($name) {
        if (!in_array($name, $this->listModels())) trigger_error ('No such model: \''.$name.'\'', E_USER_ERROR);
        if (is_array($this->_models[$name])) {
            $conf = $this->_models[$name];
            if (is_array($this->modelDefaults)) $conf = Ac_Util::m($this->modelDefaults, $conf);
            
            if (isset($conf['class']) && $conf['class']) $cls = $conf['class'];
            elseif (isset($conf['metaModelClass']) && $conf['metaModelClass']) $cls = $conf['metaModelClass'];
            else $cls = 'Ac_Cg_Model';  
            
            Ac_Util::ms($conf, [
                'domain' => $this,
                'name' => $name
            ]);
            $this->_models[$name] = new $cls($conf);
            $this->_models[$name]->init();
        }
        return $this->_models[$name];
    }
    
    function _calculateModelsConfig() {
        $res = array();
        if ($this->autoTablesAll || $this->autoTables) {
            $dbs = $this->getDatabase();
            if ($this->autoTablesAll) {
                $autoTables = $dbs->listTables();
                if (is_string($this->autoTablesAll)) {
                    $autoTables = preg_grep($this->autoTablesAll, $autoTables);
                }
            } elseif (is_string($this->autoTables)) {
                $autoTables = preg_grep($this->autoTables, $dbs->listTables());
            } elseif (is_array($this->autoTables)) {
                $autoTables = $this->autoTables;
            } else {
                $autoTables = array();
            }
            $ignoredTables = array();
            if (is_array($this->autoTablesIgnore)) $ignoredTables = $this->autoTablesIgnore;
            elseif (is_string($this->autoTablesIgnore) && strlen($this->autoTablesIgnore)) {
                $ignoredTables = preg_grep($this->autoTablesIgnore, $this->autoTablesAll);
            }
            if ($this->autoTablesIgnore) $autoTables = array_diff($autoTables, $this->autoTablesIgnore);
            $autoConf = array();
            foreach ($autoTables as $tName) {
                $tbl = $dbs->getTable($tName);
                if (!$tbl->isBiJunctionTable($this->getBiJunctionIgnore($tbl->name))) {
                    list ($name, $conf) = $this->getModelAutoConfig($tName);
                    if ($conf) $autoConf[$name] = $conf;
                }
            }
            $res = Ac_Util::m($autoConf, $this->models);
        } else {
            $res = $this->models;
        }
        return $res;
    }
    
    /**
     * @param string $tableName Name of database table that corresponds to model that we want to get autoconfig for
     * @return array($modelName, $modelAutoConf) Name and autoconfig of the model
     */
    function getModelAutoConfig($tableName) {
        $name = $tableName;
        $conf = array('table' => $tableName);
        
        $coolName = $this->extractNameFromTableName($tableName);
        
        $table = $this->getDatabase()->getTable($tableName);
        $pk = $table->listPkFields();
        if (!$pk) {
            $this->_gen->log("<strong>{$this->name}</strong>: table '{$tableName}' has no primary key and will be ignored.", true);
            return false;
        } elseif (count($pk) > 1) {
            $this->_gen->log("<strong>{$this->name}</strong>: table '{$tableName}' has composite primary key: '".implode("', '", $pk)."'. Composite PKs are not supported yet and the table will be ignored.", true);
            return false;
        }
        
        $conf['table'] = $tableName;
        
        $name = Ac_Cg_Util::makeIdentifier($coolName);
        
        if ($this->modelNameDecorator) {
            $name = Ac_Decorator::decorate($this->modelNameDecorator, $name);
        }
        
        $overConf = array();
        
        if ($this->extraConfigByTables) {
            foreach ($this->extraConfigByTables as $k => $extraConfig) {
                if (is_array($extraConfig) && ($k === $tableName || ($k[0] == '/' && preg_match($k, $tableName)))) {
                    Ac_Util::ms($overConf, $extraConfig);
                }
            }
        }
        
        // let's determine default class from parent domain
        if ($parentDomain = $this->getParentDomain()) {
            if (isset($this->models[$name]) && is_array($this->models[$name])) {
                $extraConf = $this->models[$name];
            } else {
                $extraConf = array();
            }
            $extraConf = Ac_Util::m($overConf, $conf);
            $parentModelName = false;
            if (isset($extraConf['parentModelName']))
                $parentModelName = $extraConf['parentModelName'];
            elseif ($this->autoParentModels) $parentModelName = $name;
            if (strlen($parentModelName) && in_array($parentModelName, $parentDomain->listModels())) {
                $class = get_class($parentDomain->getModel($parentModelName));
                $conf['class'] = $class;
            }
        }
        
        if ($overConf) Ac_Util::ms($conf, $overConf);
        
        return array($name, $conf);
    }
    
    function extractNameFromTableName($tableName) {
    	if (is_array($this->modelNames) && isset($this->modelNames[$tableName])) {
    		$coolName = $this->modelNames[$tableName];
    	} else {
	        $coolName = $tableName;
	        if ($this->tablePrefix && $this->stripTablePrefixFromIds) {
	            $l = strlen($this->replaceTablePrefixWith);
	            if (!strncmp($coolName, $this->replaceTablePrefixWith, $l)) $coolName = substr($coolName, $l);
	        }
	        if (strlen($this->extraStripFromIds)) {
                if ($this->extraStripFromIds[0] == '/') $coolName = preg_replace ($this->extraStripFromIds, '', $coolName);
                else {
                    $l = strlen($this->extraStripFromIds);
                    if (!strncmp($coolName, $this->extraStripFromIds, $l)) $coolName = substr($coolName, $l);
                }
	        }
	        $coolName = Ac_Cg_Util::addSpacesBeforeCamelCase($coolName);
	        $coolName = str_replace('_', ' ', $coolName);
	        $coolName = strtolower($coolName);
	        $coolName = preg_replace('/ +/', ' ', $coolName);
    	}
        return $coolName;
    }
    
    /**
     * @return Ac_Cg_Model
     */
    function searchModelByTable($tableName) {
        $res = null;
        foreach ($this->listModels() as $name) {
            $mdl = $this->getModel($name);
            if ($mdl->table === $tableName) {
                $res = $mdl;
                break;
            }
        }
        return $res;
    }
    
    /**
     * Analyzes table name and extracts table prefix, subsystem prefixes and entity identifiers from it
     * 
     * Following keys and values are present in returned array:
     * - 'tableNameWithPrefix' => string - name of table where prefix is replaced with $this->replaceTablePrefixWith, 
     * - 'subsystemPrefixes' => array - zero or more subsystem prefixes in order in which they appear in the $tableName, 
     * - 'singleEntity' => array|false - Ac_Cg_Inflector::explode'd parts of the entity identifier if $this->tableNamesArePlural is FALSE or $autoChangeForm is true, FALSE otherwise    
     * - 'pluralEntity' => array|false - Ac_Cg_Inflector::explode'd parts of the entity identifier if $this->tableNamesArePlural is TRUE or $autoChangeForm is true, FALSE otherwise
     * Subsystem prefixes are not included in neither single not plural form of the entity identifier. 
     * Note that if table name consists only of subsystem prefixes, last prefix is considered an entity identifier.   
     * 
     * @param string $tableName
     * @param bool $autoChangeForm Whether to change singular form to plural or vice versa (depending on $this->tableNamesArePlural settings)
     * @return array 
     */
    function analyzeTableName($tableName, $autoChangeForm = true) {
        $res = array();
        $aName = $tableName;
        if (($l = strlen($this->tablePrefix)) && !strncmp($aName, $this->tablePrefix, $l)) {
            // we have to replace prefix with $this->replaceTablePrefixWith string
            $res['tableNameWithPrefix'] = $this->replaceTablePrefixWith.($aName = substr($aName, $l));
        } 
        elseif (($l = strlen($this->replaceTablePrefixWith)) && !strncmp($aName, $this->replaceTablePrefixWith, $l)) {
            // $this->replaceTablePrefixWith string is already in the table name
            $res['tableNameWithPrefix'] = $aName;
            $aName = substr($aName, $l);
        } else {
            // no prefix or replacement - leave name as is
            $res['tableNameWithPrefix'] = $aName;
        }
        foreach ($this->tableNameFixes as $regex => $replacement) {
            $aName = preg_replace($regex, $replacement, $aName);
        }
        $nameParts = Ac_Cg_Inflector::explode($aName);
        $ssPrefixes = array();
        if (is_array($this->subsystemPrefixes)) {
            foreach ($this->subsystemPrefixes as $p) $ssPrefixes[strtolower($p)] = 1;
        }
        $res['subsystemPrefixes'] = array();
        
        $found = true;
        while ($found && count($ssPrefixes) && (count($nameParts) > 1)) {
            if (isset($ssPrefixes[$nameParts[0]])) {
                $found = true;
                $res['subsystemPrefixes'][] = $nameParts[0];
                $nameParts = array_slice($nameParts, 1);
            } else {
                $found = false;
            }
        }
        if ($this->tableNamesArePlural) {
            $res['pluralEntity'] = $nameParts;
            if ($autoChangeForm) $res['singleEntity'] = Ac_Cg_Inflector::explode($this->dictionary->getSingular(implode('_', $nameParts)));
        } else {
            $res['singleEntity'] = $nameParts;
            if ($autoChangeForm) $res['pluralEntity'] = Ac_Cg_Inflector::explode($this->dictionary->getPlural(implode('_', $nameParts)));
        }
        
        
        return $res;
    }
    
    protected function needsLangStrings() {
        $res = false;
        if ($this->useLangStrings) $res = true;
        else {
            foreach ($this->listModels() as $m) {
                if ($this->getModel($m)->getUseLangStrings()) {
                    $res = true;
                    break;
                }
            }
        }
        return $res;
    }
    
    function getBiJunctionIgnore($tableName) {
        $res = array();
        
        if (isset($this->ignoredColumnsInJunctionTables[$tableName]) && is_array($this->ignoredColumnsInJunctionTables[$tableName]))
            $res = $this->ignoredColumnsInJunctionTables[$tableName];
        else {
            if (isset($this->ignoredColumnsInJunctionTables['']) && is_array($this->ignoredColumnsInJunctionTables['']))
                $res = array_merge($res, $this->ignoredColumnsInJunctionTables['']);
        }
        
        foreach ($this->ignoredColumnsInJunctionTables as $k => $v) {
            if (is_numeric($k) && !is_array($v)) $res[] = $v;
        }
        return $res;
    }
    
    function getAppClass() {
        return strlen($this->appClass)? $this->appClass : $this->appName;
    }
    
    function getComponentsAccessorClass() {
        return $this->getAppClass().'_ComponentsAccessor';
    }
    
    function getComponentsAccessorGenClass() {
        return $this->getAppClass().'_GenComponentsAccessor';
    }
    
    function getComponentsAccessorBaseClass() {
        if (($pd = $this->getParentDomain())) {
            $res = $pd->getComponentsAccessorClass();
        } else {
            $res = $this->componentsAccessorBaseClass;
        }
        return $res;
    }
    
    function getLangStrings($key = false) {
        $res = $this->langStrings;
        if ($key !== false)  $res = isset($res[$key])? $res[$key] : null;
        return $res;
    }
    
    function registerLangString($key, $value) {
        $this->langStrings[$key] = $value;
    }
    
    function getLangStringPrefix() {
        if ($this->langStringPrefix === false) $res = strtolower(Ac_Cg_Inflector::definize($this->appName));
            else $res = $this->langStringPrefix;
        return $res;
    }
    
    function getTableLangStringPrefix() {
        if ($this->tableLangStringPrefix === false) $res = strtolower(Ac_Cg_Inflector::definize($this->appName));
            else $res = $this->tableLangStringPrefix;
        return $res;
    }
    
    function beforeGenerate() {
        foreach ($this->listModels() as $i) {
            $this->getModel($i)->beforeGenerate();
        }
    }
    
    /**
     * @return Ac_Cg_Domain
     */
    function getParentDomain() {
        if ($this->parentDomain === false) {
            if (strlen($this->parentDomainName)) {
                $res = $this->_gen->getDomain($this->parentDomainName);
                if ($res) $this->parentDomain = $res;
            }
        }
        if ($this->parentDomain) $res = $this->parentDomain;
            else $res = null;
        return $res;
    }
    
    function getParentAppClass() {
        if (($pd = $this->getParentDomain())) {
            $res = $pd->getAppClass();
        } else {
            $res = $this->appBaseClass;
        }
        return $res;
    }
    
    function getMapperAliases() {
        $res = array();
        if ($parent = $this->getParentDomain()) {
            foreach ($this->listModels() as $m) {
                $mod = $this->getModel($m);
                if ($pm = $mod->getParentModel()) {
                    $res[$pm->getMapperClass()] = $mod->getMapperClass();
                }
            }
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
            'dictionary' => array('dictionary', 'Ac_Cg_Dictionary', array()),
            '_models' => array('_models', 'Ac_Cg_Model', array()),
        );
        return $res;
    }
    
    protected function beforeSerialize(& $vars) {
        $can = new Ac_Sql_Dbi_Inspector_Canned();
        $can->defaultDatabaseName = $this->dbName;
        $can->import($this->getInspector());
        $vars['inspector'] = $can;
        parent::beforeSerialize($vars);
    }
    
    public function unserializeFromArray($array) {
        if (isset($array['inspector']) && is_array($array['inspector'])) {
            $this->inspector = new Ac_Sql_Dbi_Inspector_Canned;
            $this->inspector->unserializeFromArray($array['inspector']);
        }
        parent::unserializeFromArray($array);
        if (!$this->_models) $this->_models = array();
        foreach ($this->_models as $mod) $mod->initProperties();
    }
    
    public function serializeToArray() {
        $res = parent::serializeToArray();
        if (isset($res['models'])) {
            $models = $res['models'];
            unset($res['models']);
            $res['models'] = $models;
        }
        return $res;
    }
    
    function getDomainTemplates() {
        if ($this->domainTemplates === false) {
            $res = array(
                'domain' => 'Ac_Cg_Template_Domain',
            );
            if ($this->needsLangStrings()) $res['languages'] = 'Ac_Cg_Template_Languages';
        } else {
            $res = $this->domainTemplates;
        }
        return $res;
    }
    
    /**
     * @return Ac_Cg_Template[]
     */
    function getCommonTemplateInstances() {
        $res = array();
        foreach ($this->getDomainTemplates() as $k => $v) {
            $res[$k] = $this->createTemplate($v);
        }
        return $res;
    }
    
    /**
     * @return Ac_Cg_Template[]
     */
    function getModelTemplateInstances($modelNames = false) {
        if ($modelNames === false) $modelNames = $this->listModels ();
        else $modelNames = Ac_Util::toArray($modelNames);
        foreach ($modelNames as $m) {
            $model = $this->getModel($m);
            foreach ($model->getTemplates() as $k => $v) {
                $res[$m.".".$k] = $this->createTemplate($v, $model);
            }
        }
        return $res;
    }
    
    /**
     * @return Ac_Cg_Template[]
     */
    function getAllTemplateInstances() {
        $res = $this->getCommonTemplateInstances();
        $res = array_merge($res, ($this->getModelTemplateInstances()));
        return $res;
    }
    
    /**
     * @return Ac_Cg_Template
     */
    function createTemplate($classOrPrototype, Ac_Cg_Model $model = null) {
        $res = Ac_Prototyped::factory($classOrPrototype, 'Ac_Cg_Template', array(
            'generator' => $this->_gen,
            'domain' => $this,
            'model' => $model,
        ), true);
        return $res;
    }
        
}
