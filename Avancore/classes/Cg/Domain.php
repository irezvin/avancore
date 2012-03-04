<?php

/**
 * Domain metamodel for Code Generator. Models between different domains are not linked
 **/

class Cg_Domain {

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
    
    var $appBaseClass = 'Ae_Application';
    
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
     * @var Cg_Generator
     */
    var $_gen = false;
    
    /**
     * @var Ae_Sql_Dbi_Database
     */
    var $_database = false;
    
    /**
     * @var array Cg_Model instances
     */
    var $_models = false;
    
    var $strategySettings = array();
    
    /**
     * Default name for model special properties auto-detection
     * @see Cg_Model::detectSpecialProperties  
     */
    var $defaultTitlePropName = false;
    
    /**
     * Default name for model special properties auto-detection
     * @see Cg_Model::detectSpecialProperties  
     */
    var $defaultPublishedPropName = false;
    
    /**
     * Default name for model special properties auto-detection
     * @see Cg_Model::detectSpecialProperties  
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
     * @var Cg_Dictionary
     */
    var $dictionary = false;
    
    /**
     * Use 'new Pmt_Lang_String(...)' instead of captions
     * @var bool
     */
    var $captionsToPmtLangStrings = false;
    
    /**
     * @param Cg_Generator $generator 
     */
    function Cg_Domain(& $generator, $name, $config = array()) {
         $this->_gen = $generator;
         $this->$name = $name;
         Ae_Util::simpleBind($config, $this);
         if (!$this->dbName) $this->dbName = $name;
         if (!$this->appName) $this->appName = $name;
         if (!$this->caption) $this->caption = $this->appName;
         if (!$this->josComId) $this->josComId = $this->appName;
         
         Ae_Dispatcher::loadClass('Cg_Dictionary');
         if (isset($config['dictionary']) && is_array($config['dictionary'])) {
             $dicConf = $config['dictionary'];
         } else {
             $dicConf = array();
         }
         if (!isset($dicConf['constantPrefix'])) $dicConf['constantPrefix'] = $this->appName; 
         $this->dictionary = new Cg_Dictionary($dicConf);
    }
    
    function listModels() {
        $l = '_models';
        if ($this->$l === false) {
            $this->$l = array();
            Ae_Dispatcher::loadClass('Cg_Model');
            foreach ($this->_calculateModelsConfig() as $name => $config) {
                 $this->{$l}[$name] = $config; 
            }
        }
        return array_keys($this->$l);
    }
    
    /**
     * @return Ae_Sql_Dbi_Inspector
     */
    function getInspector() {
        $res = $this->_gen->getInspector();
        return $res;
    }
    
    /**
     * @return Ae_Sql_Dbi_Database
     */
    function getDatabase() {
        if ($this->_database === false) {
            $insp = $this->getInspector();
            Ae_Dispatcher::loadClass('Ae_Sql_Dbi_Database');
            $this->_database = new Ae_Sql_Dbi_Database($insp, $this->dbName, $this->tablePrefix, $this->replaceTablePrefixWith, $this->schemaExtras);
        }
        return $this->_database;
    }
    
    /**
     * @param string $name Name of model 
     * @return Cg_Model
     */
    function getModel($name) {
        if (!in_array($name, $this->listModels())) trigger_error ('No such model: \''.$name.'\'', E_USER_ERROR);
        if (is_array($this->_models[$name])) {
            $conf = $this->_models[$name];
            if (is_array($this->modelDefaults)) $conf = Ae_Util::m($this->modelDefaults, $conf);
            
            if (isset($conf['metaModelClass']) && $conf['metaModelClass']) $cls = $conf['metaModelClass'];
            else $cls = 'Cg_Model';  
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
                if (!$tbl->isBiJunctionTable()) {
                    list ($name, $conf) = $this->getModelAutoConfig($tName);
                    $autoConf[$name] = $conf;
                }
            }
            $res = Ae_Util::m($autoConf, $this->models);
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
        $name = Cg_Util::makeIdentifier($coolName);
        
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
	            $l = strlen($this->extraStripFromIds);
	            if (!strncmp($coolName, $this->extraStripFromIds, $l)) $coolName = substr($coolName, $l);
	        }
	        $coolName = Cg_Util::addSpacesBeforeCamelCase($coolName);
	        $coolName = str_replace('_', ' ', $coolName);
	        $coolName = strtolower($coolName);
	        $coolName = preg_replace('/ +/', ' ', $coolName);
    	}
        return $coolName;
    }
    
    /**
     * @return Cg_Model
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
     * - 'singleEntity' => array|false - Cg_Inflector::explode'd parts of the entity identifier if $this->tableNamesArePlural is FALSE or $autoChangeForm is true, FALSE otherwise    
     * - 'pluralEntity' => array|false - Cg_Inflector::explode'd parts of the entity identifier if $this->tableNamesArePlural is TRUE or $autoChangeForm is true, FALSE otherwise
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
        Ae_Dispatcher::loadClass('Cg_Inflector');
        $nameParts = Cg_Inflector::explode($aName);
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
            if ($autoChangeForm) $res['singleEntity'] = Cg_Inflector::explode($this->dictionary->getSingular(implode('_', $nameParts)));
        } else {
            $res['singleEntity'] = $nameParts;
            if ($autoChangeForm) $res['pluralEntity'] = Cg_Inflector::explode($this->dictionary->getPlural(implode('_', $nameParts)));
        }
        
        
        return $res;
    }
    
    /**
     * This function is called by Cg_Generator when Cg_Strategy object is initialized for this Cg_Domain
     * Function result is merged with prototype for strategy settings.
     * @return array
     */
    function getStrategySettings() {
        if (is_array($this->strategySettings)) $res = $this->strategySettings;
            else $res = array();
            
        if ($this->captionsToPmtLangStrings) $res['domainTemplates'] = array('Cg_Template_Domain', 'Cg_Template_Languages');
            
        return $res;
    }
    
}

?>