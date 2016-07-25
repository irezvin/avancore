<?php

class Ac_Etl_Operation extends Ac_Prototyped implements Ac_Etl_I_TablePair {
    
    protected $id = false;

    /**
     * @var Ac_Etl_Import
     */
    protected $import = false;
    
    protected $tableId = false;
    
    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;
    
    /**
     * @var Ac_Etl_Table
     */
    protected $table = false;
    
    /**
     * @var array
     */
    protected $preOperations = array();

    /**
     * @var array
     */
    protected $postOperations = array();
    
    protected $insertIgnore = false;

    protected $selectors = false;
    
    protected $statusColName = false;

    protected $problemsColName = false;
    
    /**
     * @var array
     */
    protected $groups = array();
    
    /**
     * @var Ac_Etl_Stats
     */
    protected $stats = false;

    /**
     * @return Ac_Etl_Stats
     */
    function getStats() {
        if ($this->stats === false)
            $this->stats = Ac_Prototyped::factory(array('stats' => $this->getStatPrototypes()), 'Ac_Etl_Stats');
        return $this->stats;
    }
    
    function addStats($name, $value = null) {
        if (is_null($value) && is_array($name)) $this->getStats()->addMany ($name);
         else $this->getStats()->add ($name, $value);
    }
    
    function addAffected($name) {
        //$this->getStats()->add($name, $this->db->getAffectedRows());
    }
    
    protected function getStatPrototypes() {
        return array();
    }

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }    
    
    /**
     * @var array
     */
    protected $selectPrototype = false;
    
    protected $parentOperation = false;
    
    protected $parentOperationRelation = false;

    function setParentOperation($operation, $relation = false) {
        $this->parentOperation = $operation;
        $this->parentOperationRelation = $relation;
    }
    
    function setParentOperationRelation($relation) {
        $this->parentOperationRelation = $relation;
    }
    
    /**
     * @return Ac_Etl_Operation
     */
    function getParentOperation() {
        return $this->parentOperation;
    }
    
    function getParentOperationRelation() {
        return $this->parentOperationRelation;
    }
    
    function getIdPath() {
        $res = $this->id;
        if ($this->parentOperation) {
            if (strlen($this->parentOperationRelation)) $res = ':'.$this->parentOperationRelation.'.'.$res;
            else $res = '.'.$res;
            $res = $this->parentOperation->getIdPath().$res;
        }
        return $res;
    }
    
    function setSelectors(array $selectors) {
        $selectors = Ac_Util::m($this->getDefaultSelectorPrototypes(), $selectors);
        $this->selectors = Ac_Prototyped::factoryCollection($selectors, 'Ac_Etl_Selector', array('operation' => $this), 'id', true, true);
    }
    
    /**
     * @return array
     */
    function getSelectors() {
        if ($this->selectors === false) {
            $this->setSelectors($this->getDefaultSelectorPrototypes());
        }
        return $this->selectors;
    }
    
    function listSelectors() {
        return array_keys($this->getSelectors());
    }
    
    protected function getDefaultSelectorPrototypes() {
        return array();
    }
    
    /**
     * @return Ac_Selector
     */
    function getSelector($id, $dontThrow = false) {
        $res = null;
        if (!in_array($id, $this->listSelectors())) {
            if (!$dontThrow) throw new Exception("No such selector: '$id'");
        } else {
            $res = $this->selectors[$id];
        }
        return $res;
    }
    
    function setImport(Ac_Etl_Import $import) {
        $this->import = $import;
    }

    /**
     * @return Ac_Etl_Import
     */
    function getImport() {
        return $this->import;
    }    

    function setTableId($tableId) {
        if ($this->tableId !== $tableId) {
            $this->tableId = $tableId;
            $this->table = false;
        }
    }

    function getTableId() {
        return $this->tableId;
    }    

    function setTable(Ac_Etl_Table $table = null) {
        $this->table = $table;
    }

    /**
     * @return Ac_Etl_Table
     */
    function getTable() {
        if (!$this->table && strlen($this->tableId)) $this->table = $this->getImport()->getTable($this->tableId, true);
        return $this->table;
    }

    function setDb(Ac_Sql_Db $db) {
        $this->db = $db;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if ($this->db === false) {
            $this->db = $this->getImport()->getDb();
        }
        return $this->db;
    }

    function setPreOperations(array $preOperations) {
        foreach ($this->preOperations = Ac_Prototyped::factoryCollection($preOperations, 'Ac_Etl_Operation', array('import' => $this->getImport(), 'parentOperation' => $this, 'parentOperationRelation' => 'pre'), 'id', true, true) as $p) {
            if (!$p->getImport()) $p->setImport($this->getImport());
            if (!$p->getTable()) {
                if ($t = $this->getTable()) $p->setTable($this->getTable());
                elseif (strlen($this->getTableId())) $p->setTableId($this->getTableId());
            }
        }
    }

    /**
     * @return array
     */
    function getPreOperations() {
        return $this->preOperations;
    }

    function setPostOperations(array $postOperations) {
        foreach ($this->postOperations = Ac_Prototyped::factoryCollection($postOperations, 'Ac_Etl_Operation', array('import' => $this->getImport(), 'parentOperation' => $this, 'parentOperationRelation' => 'post'), 'id', true, true) as $p) {
            if (!$p->getImport()) $p->setImport($this->getImport());
            if (!$p->getTable()) {
                if ($t = $this->getTable()) $p->setTable($this->getTable());
                elseif (strlen($this->getTableId())) $p->setTableId($this->getTableId());
            }
        }
    }

    /**
     * @return array
     */
    function getPostOperations() {
        return $this->postOperations;
    }
    
    function process() {
        $res = true;
        
        $this->resetStats();
        
        $idp = $this->getIdPath();
        
        try {
        
            $this->getImport()->logItem(new Ac_Etl_Log_Item("Operation '{$idp}' started", 'debug', array('chrono')));
            $finishLog = new Ac_Etl_Log_Item("Operation '{$idp}' finished", 'debug', array('chrono'), array(), true);
        
            foreach ($this->preOperations as $i => $p) {
                if (!$p->process()) {
                    $res = false;
                    break;
                }
            }
            if ($res) {

                if ($this->doProcess() === false) {
                    $res = false;
                }

                foreach ($this->postOperations as $i => $p) {
                    if (!$p->process()) {
                        $res = false;
                        break;
                    }
                }
            }
        
            $this->import->logItem($finishLog);
            $this->logStats();
            
        } catch (Exception $e) {
            $this->import->logItem(new Ac_Etl_Log_Item("Operation '{$idp}' failed with ".get_class($e).": ".$e, 'error', array('chrono', 'errors')));
            $this->import->setException($e);
            $res = false;
        }
        
        return $res;
    }
 
    function setSelectPrototype(array $selectPrototype) {
        $this->selectPrototype = $selectPrototype;
    }
   
    function getSelectPrototype($full = true, $alias = 't') {
        if ($full) {
            $res = array(
                'tables' => array(
                    $alias => array(
                        'name' => $this->getTable()->tableName('object')
                    ),
                ),
                'where' => array(
                    'status' => 't.'.$this->statusColName." NOT IN ('bad', 'ignored')"
                ),
            );
            if (!strlen($this->statusColName)) unset($res['where']['status']);
            $id = $this->getImport()->getImportId(true);
            $res['where']['importId'] = $alias.'.importId '.$this->getDb()->eqCriterion($id);
            if (is_array($r = $this->getTable()->restriction) && $r) {
                $res['where']['restriction'] = $this->getDb()->valueCriterion($r, $alias);
            }
            if (is_array($this->selectPrototype) && $this->selectPrototype) Ac_Util::ms($res, $this->selectPrototype);
        } else {
            $res = $this->getSelectPrototype();
        }
        return $res;
    }
    
    
    protected function doProcess() {
    }
    
    protected function flagBad(Ac_Sql_Select $sel, $problems = false) {
        return $this->flag($sel, 'bad', $problems);
    }
    
    protected function flag(Ac_Sql_Select $sel, $status = false, $problems = false) {
        $u = array();
        if (strlen($this->statusColName)) $u[$this->statusColName] = $status;
        if (strlen($this->problemsColName && $problems !== false)) {
            $u[$this->problemsColName] = $problems;
        }
        if ($u) {
            $idPath = $this->getIdPath();
            $tags = "-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/flag\n";
            $res = $this->update(implode(", ", $this->getDb()->valueCriterion($u, $sel->getEffectivePrimaryAlias(), true)), $sel, $tags);
            $flagType = $status === 'bad' && strlen($problems)? 'bad.'.$problems : $status;
            $this->addAffected($flagType);
        } else {
            $res = true;
        }
        return $res;
    }
    
    protected function updateStmt($setExpression, Ac_Sql_Select $sel) {
        if (is_array($setExpression)) $setExpression = implode(",\n", $setExpression);
        $sql = "UPDATE\n".$sel->getFromClause()."\nSET\n{$setExpression}\n".$sel->getWhereClause(true);
        return $sql;
    }    
    
    protected function update($setExpression, Ac_Sql_Select $sel, $tags = "") {
        $res = $this->getDb()->query($stmt = $tags.$this->updateStmt($setExpression, $sel));
        return $res;
    }    
    
    function setInsertIgnore($insertIgnore) {
        $this->insertIgnore = (bool) $insertIgnore;
    }

    function getInsertIgnore() {
        return $this->insertIgnore;
    }    
    
    /**
     * @return Ac_Etl_Map
     */
    protected function mkMap($map, $leftAlias, $rightAlias) {
        return Ac_Etl_Map::create($map, $this->getDb(), $leftAlias, $rightAlias);
    }
    
    function __construct(array $options = array()) {
        if (isset($options['import'])) {
            $this->setImport($options['import']);
            unset($options['import']);
        }
        parent::__construct($options);
        
    }
    
    function hasPublicVars() {
        return true;
    }
    
    function getStatistics($ids = false, $own = false) {
        if ($ids === false) $ids = $this->listSelectors();
        if (($notArray = !is_array($ids))) {
            $ids = array($ids);
        }
        $res = array();
        foreach ($ids as $id) {
            $res[$id] = $this->getSelector($id)->getStatistics();
            if ($own) $res[$id] = $res[$id]['_own'];
        }
        if ($notArray) $res = $res[$ids[0]];
        return $res;
    }
    
    function setStatusColName($statusColName) {
        $this->statusColName = $statusColName;
    }

    function getStatusColName() {
        return $this->statusColName;
    }

    function setProblemsColName($problemsColName) {
        $this->problemsColName = $problemsColName;
    }

    function getProblemsColName() {
        return $this->problemsColName;
    }        

    function setGroups($groups) {
        $this->groups = Ac_Util::toArray($groups);
    }

    /**
     * @return array
     */
    function getGroups() {
        return $this->groups;
    }    
    
    function hasGroup($group) {
        if ($group == '') $res = !count($this->groups);
        else $res = in_array($group, $this->groups);
        return $res;
    }
    
    protected function logStats() {
        if ($this->stats) {
            $vals = $this->stats->getValues();
            $idPath = $this->getIdPath();
            $msg = "$idPath affected records:";
            foreach ($vals as $k => $v) $msg .= "\n".$k .': '.$v;
            $item = new Ac_Etl_Log_Item($msg, "stats", array("operations/{$idPath}/affected", "affected"), $vals, false);
            $this->import->logItem($item);
        }
    }
    
    protected function resetStats() {
        if ($this->stats) $this->stats->reset();
    }
    
    /**
     * @return FALSE or string
     */
    function getLeftDbName() {
        return $this->import->getImporterDbName();
    }
    
    function getLeftTableName() {
        return $this->import->getTable($this->tableId)->sqlTableName;
    }
    
    /**
     * @return FALSE or string
     */
    function getRightTableName() {
        return false;
    }
    
    function getRightDbName() {
        return false;
    }
    
    function getLeftDbPrefix() {
        return $this->import->getImporterDbPrefix();
    }
    
    function getRightDbPrefix() {
        return '';
    }    
    
}
