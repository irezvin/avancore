<?php

class Ac_Etl_Selector extends Ac_Prototyped {

    protected $defaultLogicProvider = false;
    
    protected $children = array();    
    
    protected $id = false;

    protected $parentId = false;
    
    /**
     * @var Ac_Etl_Selector
     */
    protected $parent = false;

    /**
     * @var Ac_Etl_Operation
     */
    protected $operation = false;
    
    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;
    
    protected $selectPrototype = array();

    protected $ignoreParentPrototype = false;
    
    protected $putSqlToStats = false;
    
    protected $sqlComments = false;

    /**
     * @return Ac_Etl_Selector
     */
    function findChildByPath(array $path, $throw = true) {
        //if (!is_array($path)) $path = Ac_Util::pathToArray ($path);
        $curr = $this;
        while ($seg = array_shift($path)) { $curr = $curr->getChild($seg, $throw); }
        return $curr;
    }
    
    /**
     * @return Ac_Etl_Selector
     */
    function getParent() {
        $res = null;
        if (!strlen($this->parentId)) $res = null;
            else $res = $this->operation->getSelector($this->parentId);
        return $res;
    }
    
    function setDefaultLogicProvider($defaultLogicProvider) {
        $this->defaultLogicProvider = $defaultLogicProvider;
    }

    function getDefaultLogicProvider() {
        return $this->defaultLogicProvider;
    }
    
    function listChildren() {
        if (strlen($this->id)) {
            $res = Ac_Accessor::getObjectProperty(Ac_Prototyped::findItems($this->operation->getSelectors(), array('parentId' => $this->id)), 'id');
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @return Ac_Etl_Selector
     * @param string $id
     * @throws Exception
     */
    function getChild($id, $throw = true) {
        return $this->operation->getSelector($id, !$throw);
    }

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }    

    function setOperation(Ac_Etl_Operation $operation) {
        $this->operation = $operation;
    }

    /**
     * @return Ac_Etl_Operation
     */
    function getOperation() {
        if (!$this->operation && $this->parent) return $this->parent->getOperation();
        return $this->operation;
    }

    function setDb(Ac_Sql_Db $db = null) {
        $this->db = $db;
    }
    
    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        if (!$this->db) {
            if ($p = $this->getOperation()) $this->db = $this->getOperation()->getDb();
        }
        return $this->db;
    }

    function setIgnoreParentPrototype($ignoreParentPrototype) {
        $this->ignoreParentPrototype = (bool) $ignoreParentPrototype;
    }

    function getIgnoreParentPrototype() {
        return $this->ignoreParentPrototype;
    }    
    
    protected function getPrototypeBase() {
        $res = array();
        if ($this->ignoreParentPrototype || !strlen($this->parentId)) {
            $res = $this->getOperation()->getSelectPrototype();
        } else {
            $res = $this->getParent()->getSelectPrototype(true);
        }
        return $res;
    }

    function setSelectPrototype(array $selectPrototype) {
        $this->selectPrototype = $selectPrototype;
    }

    function getSelectPrototype($full = false) {
        $res = $this->selectPrototype;
        if ($full) {
            $proto = $this->getPrototypeBase();
            $res = Ac_Util::m($proto, $res);
        }
        return $res;
    }

    /**
     * @return Ac_Sql_Select
     */
    function createSelect(array $overrides = array()) {
        $proto = $this->getSelectPrototype(true);
        if ($overrides) Ac_Util::ms($proto, $overrides);
        $res = new Ac_Sql_Select($this->getDb(), $proto);
        return $res;
    }
    
    function setPutSqlToStats($putSqlToStats) {
        $this->putSqlToStats = (bool) $putSqlToStats;
    }

    function getPutSqlToStats() {
        return $this->putSqlToStats;
    }    

    function setParentId($parentId) {
        if (strlen($this->parentId) && $this->parnetId !== $parentId) throw new Exception("can setParentId() only once");
        $this->parentId = $parentId;
    }

    function getParentId() {
        return $this->parentId;
    }    
    
    function getStatistics($withChildren = false) {
        $s = $this->createSelect(array(
            'columns' => 'COUNT(DISTINCT t.id)'
        ));
        $procId = $this->operation->getIdPath();
        
        $sql = "-- tags: stats/{$this->id} operations/{$procId}/stats/{$this->id}\n".$s->getStatement();
        
        if (strlen($this->sqlComments)) {
            $sql = "/* ".$this->sqlComments." */\n".$sql;
        }
        
        $ownCount = $s->getDb()->fetchValue($sql);
        $res = array(
            '_own' => $ownCount
        );
        if ($this->putSqlToStats) {
            $res['_sql'] = $sql;
        }
        if ($withChildren) {
            foreach ($this->listChildren() as $i) {
                $res[$i] = $this->getChild($i)->getStatistics();
            }
        }
        return $res;
    }

    function setSqlComments($sqlComments) {
        $this->sqlComments = $sqlComments;
    }

    function getSqlComments() {
        return $this->sqlComments;
    }    

}