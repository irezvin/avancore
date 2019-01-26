<?php

class Ac_Etl_Import extends Ac_Prototyped {
    
    /**
     * Name of the database where import tables are located
     * @var string
     */
    protected $importerDbName = false;

    /**
     * Name of the database where target (updated) tables are located
     * @var string
     */
    protected $targetDbName = false;
    
    protected $importerDbPrefix = false;

    protected $targetDbPrefix = false;
    
    protected $importId = false;
    
    /**
     * @var Ac_Etl_Table[]
     */
    protected $tables = array();
    
    protected $db = false;
    
    protected $exception = false;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;

    /**
     * @var Ac_Sql_Dbi
     */
    protected $importerDbi = false;
    
    /**
     * @var Ac_Sql_Dbi
     */
    protected $targetDbi = false;
    
    /**
     * @var Ac_Etl_I_Logger
     */
    protected $logger = false;
    
    protected $sql = array();

    protected $collectSql = true;
    
    /**
     * @var array
     */
    protected $operations = array();
    
    protected $loaders = array();
    
    protected $catchExceptions = false;
    
    protected $sections = false;

    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }    
    
    function setLogger($logger) {
        $this->logger = Ac_Prototyped::factory($logger, 'Ac_Etl_I_Logger');
        if ($this->db) $this->db->setLogger($this->logger);
    }

    /**
     * @return Ac_Etl_I_Logger
     */
    function getLogger() {
        if ($this->logger === false) {
            $this->logger = new Ac_Etl_Logger_Echo();
        }
        return $this->logger;
    }    
    
    function setDb(Ac_Sql_Db $db) {
        $this->db = new Ac_Etl_Db(array('db' => $db));
        if ($this->logger) $this->db->setLogger($this->logger);
    }
    
    /**
     * @return Ac_Etl_Db
     */
    function getDb() {
        if ($this->db === false) {
            $this->db = new Ac_Etl_Db();
            $this->db->setDb($this->application? $this->application->getDb() : new Ac_Sql_Db_Ae);
            if ($this->logger) $this->db->setLogger($this->logger);
        }
        return $this->db;
    }
    
    /**
     * @return Ac_Sql_Dbi_Database
     */
    function getImporterDbi() {
        if ($this->importerDbi === false) {
            // We bypass logging of Dbi queries by providing underlying db object ($this->getDb()->getDb())
            $this->importerDbi = new Ac_Sql_Dbi_Database(new Ac_Sql_Dbi_Inspector_MySql5($this->getDb()->getDb()), $this->getImporterDbName(), $this->getImporterDbPrefix());
        }
        return $this->importerDbi;
    }
    
    /**
     * @return Ac_Sql_Dbi_Database
     */
    function getTargetDbi() {
        if ($this->targetDbi === false) {
            // We bypass logging of Dbi queries by providing underlying db object ($this->getDb()->getDb())
            $this->targetDbi = new Ac_Sql_Dbi_Database(new Ac_Sql_Dbi_Inspector_MySql5($this->getDb()->getDb()), $this->getTargetDbName(), $this->getTargetDbPrefix());
        }
        return $this->targetDbi;
    }
    
    function setImportId($importId) {
        $this->importId = $importId;
    }
    
    function getImportId($require = false) {
        if (!$this->importId && $require) throw new Exception("\$importId not set()");
        return $this->importId;
    }
    
    function setTables (array $tables) {
        $this->tables = Ac_Prototyped::factoryCollection($tables, 'Ac_Etl_Table', array('import' => $this), 'id', true, true);
    }
    
    function addTables (array $tables) {
        $this->tables = array_merge($this->tables, Ac_Prototyped::factoryCollection($tables, 'Ac_Etl_Table', array('import' => $this), 'id', true, true));
    }
    
    /**
     * @return Ac_Etl_Table
     */
    function getTable($id, $throw = false) {
        $res = null;
        if (isset($this->tables[$id])) $res = $this->tables[$id];
        elseif ($throw) throw new Exception("No such table: {$id}");
        return $res;
    }
    
    function getTables() {
        return $this->tables;
    }
    
    function cleanTmpData($all = false, $tableGroupId = null) {
        foreach ($this->tables as $table) {
            if (!(!is_null($tableGroupId) && $table->tableGroupId === $tableGroupId)) continue;
            $table->cleanTmpData($all, $all);
        }
    }
    
    function logItem(Ac_Etl_Log_Item $item) {
        if ($this->logger) $this->logger->acceptItem($item);
    }
    
    function logMessage($message, $type = Ac_Etl_I_Logger::logTypeError, $lineNo = false, $colName = false, $key = false) {
        /*if (is_array($message)) {
            foreach ($message as $k => $v) $this->logMessage($v, $type, $lineNo, $colName, $key === false? $key . '.'.$k : $k);
        } else {
            $this->getLogger()->logMessage($message, $type, $lineNo, $colName, $key, $this);
        }*/
    }
     
    function setCollectSql($collectSql) {
        $this->collectSql = (bool) $collectSql;
    }

    function getCollectSql() {
        return $this->collectSql;
    }    
    
    function getSql() {
        return $this->sql;
    }
    
    function resetSql() {
        $this->sql = array();
    }
    
    function appendSql($sql, $ignoreCollectSql = false) {
        if ($ignoreCollectSql || $this->collectSql) $this->sql[] = $sql;
    }
    
    function flushSql() {
        foreach ($this->sql as $stmt) $this->getDb()->query ($stmt);
        $this->resetSql();
    }

    function addOperations(array $operations) {
        return $this->setOperations($operations, true);
    }
    
    function setOperations(array $operations, $add = false) {
        $newOperations = Ac_Prototyped::factoryCollection($operations, 'Ac_Etl_Operation', array('import' => $this), 'id', true, true);
        if ($add) {
            if ($k = array_intersect(array_keys($this->operations), array_keys($newOperations))) {
                throw new Exception("Operation(s) ".implode(", ", $k)." are already in Importer");
            }
            foreach ($newOperations as $k => $v) $this->operations[$k] = $v;
        } else {
            $this->operations = $newOperations;
        }
    }

    /**
     * @return array
     */
    function getOperations($group = false) {
        if ($group !== false) {
            $res = array();
            foreach ($this->operations as $id => $p) {
                if ($p->hasGroup($group)) {
                    $res[$id] = $p;
                }
            }
        } else {
            $res = $this->operations;
        }
        return $res;
    }    
    
    
    function listOperations() {
        return array_keys($this->operations);
    }
    
    /**
     * @return Ac_Etl_Operation
     */
    function getOperation($id, $dontThrow = false) {
        $res = null;
        if (isset($this->operations[$id])) $res = $this->operations[$id];
            elseif (!$dontThrow) throw new Exception("No such Ac_Etl_Operation: '{$id}'");
        return $res;
    }

    function setSections(array $sections) {
        $this->sections = Ac_Prototyped::factoryCollection($sections, 'Ac_Etl_Section', array('import' => $this), 'id', true, true);
    }

    /**
     * @return array
     */
    function getSections() {
        return $this->sections;
    }    
    
    function listSections() {
        return array_keys($this->sections);
    }
    
    /**
     * @return Ac_Etl_Section
     */
    function getSection($id, $dontThrow = false) {
        $res = null;
        if (isset($this->sections[$id])) $res = $this->sections[$id];
            elseif (!$dontThrow) throw new Exception("No such Ac_Etl_Section: '{$id}'");
        return $res;
    }
    
    /**
     * @param bool|string|array $operationsOrGroupId FALSE (all operations), Id of group (string) or array of Operation instances
     * @param array $okOperations List of operation IDs (or keys in $operationsOrGroupId array) that were processed successfully
     * @return bool TRUE if every operation completed successfully, FALSE otherwise
     */
    function process($operationsOrGroupId = false, & $okOperationIds = array()) {
        if (!is_array($operationsOrGroupId)) 
            $this->logItem(new Ac_Etl_Log_Item("Processing group {$operationsOrGroupId}", 'debug', array('chrono')));
        else
            $this->logItem(new Ac_Etl_Log_Item("Processing group of ".count($operationsOrGroupId)." operation(s)", 'debug', array('chrono'), array('ids' => array_keys($operationsOrGroupId))));
        $res = true;
        $okOperations = array();
        if (is_array($operationsOrGroupId)) {
            $operations = $operationsOrGroupId;
        } else {
            $operations = $this->getOperations($operationsOrGroupId);
        }
        foreach ($operations as $id => $proc) {
            $res = $proc->process();
            if (!$res) {
                $this->logItem(new Ac_Etl_Log_Item('Stopping group processing because of failed operation '.$id, 'error', array('chrono')));
            } else $okOperationIds[] = $id;
        }
        return $res;
    }

    function setImporterDbName($importerDbName) {
        $this->importerDbName = $importerDbName;
    }

    function getImporterDbName() {
        $res = $this->importerDbName;
        if (!strlen($res)) {
            $res = $this->getDefaultDbName();
        }
        return $res;
    }

    function setTargetDbName($targetDbName) {
        $this->targetDbName = $targetDbName;
    }

    function getTargetDbName() {
        $res = $this->targetDbName;
        if (!strlen($res)) {
            $res = $this->getDefaultDbName();
        }
        return $res;
    }    
    
    function getDefaultDbName() {
        if ($db = $this->getDb()) {
            $res = $db->getDbName();
        } else {
            $res = false;
        }
        return $res;
    }

    function setImporterDbPrefix($importerDbPrefix) {
        $this->importerDbPrefix = $importerDbPrefix;
    }

    function getImporterDbPrefix() {
        $res = $this->importerDbPrefix;
        if (!strlen($res)) {
            $res = $this->getDefaultDbPrefix();
        }
        return $res;
    }

    function setTargetDbPrefix($targetDbPrefix) {
        $this->targetDbPrefix = $targetDbPrefix;
    }

    function getTargetDbPrefix() {
        $res = $this->targetDbPrefix;
        if (!strlen($res)) {
            $res = $this->getDefaultDbPrefix();
        }
        return $res;
    }    
    
    function getDefaultDbPrefix() {
        if ($db = $this->getDb()) {
            $res = $db->getDbPrefix();
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * Returns either array(db, table) or string `db`.`table` or Ac_Sql_Expression(`db`.`table`)
     * 
     * @param string $table
     * @param string $db
     * @param string $kind array|string|object
     * 
     * @return array|string|Ac_Sql_Expression
     */
    function tableOfDb($table, $db, $kind = 'array') {
        $r = array($db, $table);
        if ($kind === 'array') $res = $r;
        elseif ($kind === 'string') $res = $this->db->n($r);
        elseif ($kind === 'object') $res = new Ac_Sql_Expression($this->db->n($r));
        else throw new Exception("Unsupported '\$kind' value '$kind', expexcted 'array'|'string'|'object'");
        return $res;
    }
    
    function tableOfImporterDb($table, $kind = 'array') {
        return $this->tableOfDb($table, $this->getImporterDbName(), $kind);
    }
    
    function tableOfTargetDb($table, $kind = 'array') {
        return $this->tableOfDb($table, $this->getTargetDbName(), $kind);
    }
    
    function addLoader($loader, $id = false) {
        $loader = Ac_Prototyped::factory($loader, 'Ac_Etl_Loader');
        if ($id === false && !strlen($id = $loader->getId())) {
            throw new Exception("cannot addLoader(): \$loader doesn't have an Id");
        } elseif ($id !== false && $loader->getId() === false) {
            $loader->setId($id);
        }
        
        $loader->setImport($this);
        
        $this->loaders[$id] = $loader;
    }
    
    function addLoaders(array $loaders) {
        $loaders = Ac_Prototyped::factoryCollection($loaders, 'Ac_Etl_Loader', array('import' => $this), 'id', true, true);
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }
    
    function setLoaders(array $loaders) {
        $this->loaders = array();
        $this->addLoaders($loaders);
    }
    
    function getLoaders() {
        return $this->loaders;
    }
    
    /**
     * @return Ac_Etl_Loader
     */
    function getLoader($id, $dontThrow = false) {
        $res = null;
        if (isset($this->loaders[$id])) {
            $res = $this->loaders[$id];
        } elseif (!$dontThrow) {
            throw new Exception("No such loader: {$id}");
        }
        return $res;
    }

    function setException(Exception $exception = null) {
        $this->exception = $exception;
        if (!$this->catchExceptions) throw $exception;
    }

    /**
     * @return Exception
     */
    function getException() {
        return $this->exception;
    }    

    function setCatchExceptions($catchExceptions) {
        $this->catchExceptions = (bool) $catchExceptions;
    }

    function getCatchExceptions() {
        return $this->catchExceptions;
    }    
    
}