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
    
    /**
     * Caption of whole application (defaults to appName)
     * @var string
     */
    var $caption = false;
    
    /**
     * ID of Joomla Component (without "com_" prefix; defaults to appName)
     * @var string
     */
    var $josComId = false;
    
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
     * @var array Names of tables (with prefixes) to create model configs automatically
     */
    var $autoTables = array();
    
    /**
     * @var bool All tables will be included to 'autoModelTables'
     */
    var $autoTablesAll = false;

    /**
     * @var array Names of tables to ignore when creating auto models (using this var only has sense when $eachTableIsModel === true)  
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
    
    var $strategySettings = array();
    
    /**
     * Default name for model special properties auto-detection
     * @see Ac_Cg_Model::detectSpecialProperties  
     */
    var $defaultTitlePropName = false;
    
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
     * If parent domain is set, list of the properties that shouldn't
     * be inherited from it
     */
    var $dontInheritProperties = array();
    
    protected $parentDomain = false;
    
    protected $langStrings = array();
    
    /**
     * @var Ac_Sql_Dbi_Inspector
     */
    protected $inspector = false;
    
    /**
     * @param Ac_Cg_Generator $generator 
     */
    function __construct($generator, $name, $config = array()) {
        $this->_gen = $generator;
        $this->name = $name;
        foreach (array('parentDomainName', 'dontInheritProperties') as $k) {
            if (isset($config[$k])) {
                $this->$k = $config[$k];
                unset($config->$k);
            }
        }
        $this->inheritDefaults($config);
        Ac_Util::simpleBind($config, $this);
        if (!$this->dbName) $this->dbName = $name;
        if (!$this->appName) $this->appName = $name;
        if (!$this->caption) $this->caption = $this->appName;
        if (!$this->josComId) $this->josComId = $this->appName;
        
        if (isset($config['dictionary']) && is_array($config['dictionary'])) {
            $dicConf = $config['dictionary'];
        } else {
            $dicConf = array();
        }
        if (!isset($dicConf['constantPrefix'])) $dicConf['constantPrefix'] = $this->appName; 
        $this->dictionary = new Ac_Cg_Dictionary($dicConf);
    }
    
    protected function inheritDefaults(& $config) {
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
                    if (isset($config[$propName]) && is_array($config[$propName]) && is_array($this->$propName)) {
                        Ac_Util::ms($this->$propName, $config[$propName]);
                        if (in_array($propName, 'subsystemPrefixes')) {
                            $this->$propName = array_unique($this->$propName);
                        }
                        unset($config[$propName]);
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
            $this->_database = new Ac_Sql_Dbi_Database($insp, $this->dbName, $this->tablePrefix, $this->replaceTablePrefixWith, $this->schemaExtras);
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
            
            $this->_models[$name] = new $cls($this, $name, $conf);
            $this->_models[$name]->init();
        }
        return $this->_models[$name];
    }
    
    function _calculateModelsConfig() {
        if ($this->autoTablesAll || $this->autoTables) {
            $dbs = $this->getDatabase();
            if ($this->autoTablesAll) $autoTables = $dbs->listTables();
                else $autoTables = $this->autoTables;
            if ($this->autoTablesIgnore) $autoTables = array_diff($autoTables, $this->autoTablesIgnore);
            $autoConf = array();
            foreach ($autoTables as $tName) {
                $tbl = $dbs->getTable($tName);
                if (!$tbl->isBiJunctionTable($this->getBiJunctionIgnore($tbl->name))) {
                    list ($name, $conf) = $this->getModelAutoConfig($tName);
                    $autoConf[$name] = $conf;
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
        
        $conf['table'] = $tableName;
        
        $name = Ac_Cg_Util::makeIdentifier($coolName);
        
        $overConf = array();
        
        if ($this->extraConfigByTables) {
            foreach ($this->extraConfigByTables as $k => $extraConfig) {
                if (is_array($extraConfig) && ($k === $tableName || ($k{0} == '/' && preg_match($k, $tableName)))) {
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
                if ($this->extraStripFromIds{0} == '/') $coolName = preg_replace ($this->extraStripFromIds, '', $coolName);
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
        foreach ($this->subsystemPrefixes as $p) $ssPrefixes[strtolower($p)] = 1;
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
    
    /**
     * This function is called by Ac_Cg_Generator when Ac_Cg_Strategy object is initialized for this Ac_Cg_Domain
     * Function result is merged with prototype for strategy settings.
     * @return array
     */
    function getStrategySettings() {
        if (is_array($this->strategySettings)) $res = $this->strategySettings;
            else $res = array();
            
        if ($this->needsLangStrings()) $res['domainTemplates'] = array('Ac_Cg_Template_Domain', 'Ac_Cg_Template_Languages');
            
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
            '_models' => array('_models', 'Ac_Cg_Model', array('__parent', 'name')),
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

    
}

