<?php

class Ac_Finder extends Ac_Prototyped {
    
	protected $anySortCriterionName = 'anySort';
    
	protected $anySubstringCriterionName = 'anySubstring';
	
    protected $criteria = array();
    
    protected $mapperClass = false;
    
    protected $primaryAlias = false;
    
    protected $sqlSelectPrototype = array();
    
    private $initialized = false;
    
    /**
     * @var Ac_Sql_Db
     */
    protected $sqlDb = false;
    
    function __construct(array $options = array()) {
        parent::__construct($options);
        if (!$this->initialized) {
            $this->doOnInitialize();
            $this->initialized = true;
        }
    }
    
    protected function doOnInitialize() {
        if (strlen($this->anySortCriterionName)) {
			$this->registerCriterion(new Ac_Finder_Criterion_SortByProperty(array(
				'name' => $this->anySortCriterionName,
			)));
        }
        
        if (strlen($this->anySubstringCriterionName)) {
			$this->registerCriterion(new Ac_Finder_Criterion_SearchByProperties(array(
				'name' => $this->anySubstringCriterionName,
			)));
        }
    }
    
    protected function registerCriterion($criterion) {
        if (is_array($criterion)) $criterion = Ac_Prototyped::factory($criterion, 'Ac_Finder_Criterion');
        elseif (!$criterion instanceof Ac_Finder_Criterion) throw new Exception ("\$criterion must be an instance of Ac_Finder_Criterion");
        if (!strlen($n = $criterion->getName())) throw new Exception("Cannot add criterion without a name");
        $this->criteria[$n] = $criterion;
        $vn = 'c'.ucfirst($n);
        if (isset($this->$vn)) $this->$vn = $criterion;
        $criterion->setFinder($this);
    }
    
    function listCriteria() {
        return array_keys($this->criteria);
    }
    
    function hasCriterion($key) {
        if (!$this->initialized) {
            $this->doOnInitialize();
            $this->initialized = true;
        }
        $res = isset ($this->criteria[$key]);
        return $res;
    }
    
    /**
     * @param string $key
     * @return Ac_Finder_Criterion
     */
    function getCriterion($key) {
        if (!$this->initialized) {
            $this->doOnInitialize();
            $this->initialized = true;
        }
        if (!isset($this->criteria[$key])) {
            throw new Exception ("No such criterion: '{$key}'");
        }
        return $this->criteria[$key];
    }
    
    function cleanToDelete() {
        foreach (array_keys($this->criteria) as $k) {
            $this->criteria[$k]->cleanToDelete();
            unset($this->criteria[$k]);
        }
    }
    
    function reset() {
        foreach ($this->criteria as $c) {
            $c->reset();
        }
        $this->doOnReset();
    }
    
    function setSqlDb(Ac_Sql_Db $sqlDb) {
        $this->sqlDb = $sqlDb;
    }
    
    /**
     * @return Ac_Sql_Db
     */
    function getSqlDb() {
        if ($this->sqlDb === false) {
            $this->sqlDb = new Ac_Sql_Db_Ae(Ac_Dispatcher::getInstance()->database);
        }
        return $this->sqlDb;
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function createSqlSelect(Ac_Sql_Db $sqlDb = null) {
        if (is_null($sqlDb)) $sqlDb = $this->getSqlDb();
        $prototype = $this->sqlSelectPrototype;
        $this->doOnGetSqlSelectPrototype($prototype);
        foreach ($this->listCriteria() as $i) {
            $c = $this->getCriterion($i);
            if ($c->getEnabled()) $c->applyToSelectPrototype($prototype);
        }
        $sqlSelect = new Ac_Sql_Select($sqlDb, $prototype);
        foreach ($this->listCriteria() as $i) {
            $c = $this->getCriterion($i);
            if ($c->getEnabled()) $this->getCriterion($i)->applyToSelect($sqlSelect);
        }
        return $sqlSelect;
    }
    
    /**
     * @return Ac_Model_Collection
     */
    function createCollection(Ac_Sql_Db $sqlDb = null) {
        $sqlSelect = $this->createSqlSelect($sqlDb);
        $res = $sqlSelect->createCollection($this->getMapperClassForCollection());
        return $res;
    }
    
    function getMapperClassForCollection() {
        return $this->mapperClass;
    }
    
    function getPrimaryAlias() {
    	return $this->primaryAlias;
    }
    
    function selectKeys(Ac_Legacy_Database $aeDb = null, $keys = false) {
        if ($keys !== false) {
            if (!is_array($keys)) $keys = array($keys);
        } else {
            $m = Ac_Model_Mapper::getMapper($this->mapperClass);
            $keys = $m->listPkFields(); 
        }
        if (is_null($aeDb)) $aeDb = Ac_Dispatcher::getInstance()->database;
        $sqlDb = new Ac_Sql_Db_Ae($aeDb);
        $sql = $this->createSqlSelect($sqlDb);
        $c = array();
        foreach ($keys as $kName) $c[] = $sqlDb->nameQuote(array($sql->getEffectivePrimaryAlias(), $kName));
        $sql->columns = $c;
        $aeDb->setQuery($sql->getStatement());
        if (count($keys) == 1) $res = $aeDb->loadResultArray(); else $res = $aeDb->loadAssocList();
        return $res;
    }
    
    function setValues(array $values = array(), $leaveExistingValues = false, $ignoreMissingCriteria = false) {
        if (!$leaveExistingValues) $this->reset();
        $l = $ignoreMissingCriteria? array_intersect($this->listCriteria(), array_keys($values)) : array_keys($values);
        foreach ($l as $k) {
            $this->getCriterion($k)->setValue($values[$k]);
        }
    }
    
    function getValues() {
        $res = array();
        foreach ($this->criteria as $k => $c) if ($c->getEnabled()) $res[$k] = $c->getValue();
        return $res;
    }
    
    protected function doOnReset() {
        
    }

    protected function doOnGetSqlSelectPrototype(array & $prototype) {
    	if (($this->mapperClass !== false) && ($this->primaryAlias !== false)) {
    		$m = Ac_Model_Mapper::getMapper($this->mapperClass);
    		Ac_Util::ms($prototype, array(
    			'tables' => array(
    				$this->primaryAlias => array(
    					'name' => $m->tableName
    				)
    			)
    		));
    	}
    }
    
    /**
     * @return Ac_Model_Object
     */
    function findUniqueRecord(& $numMatches = false) {
        $c = $this->createCollection();
        $numMatches = $c->countRecords();
        if ($numMatches == 1) $res = $c->getNext();
            else $res = null;
        return $res;
    }
    
    function notifyCriterionChanged(Ac_Finder_Criterion $criterion, $oldValue, $oldEnabled) {
    }
    
}

?>