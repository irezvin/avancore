<?php

class Ac_Etl_Matcher extends Ac_Prototyped implements Ac_Etl_I_Matcher {
    
    /**
     * @var bool
     */
    protected $noCache = false;

    protected $colMatches = false;
    
    /**
     * @var array
     */
    protected $ignore = array();

    /**
     * @var array
     */
    protected $overrides = array();
    
    /**
     * @var Ac_Etl_I_TablePair 
     */
    protected $tablePair = false;
    
    /**
     * @var Ac_Sql_Db
     */
    protected $sqlDb = false;

    function setTablePair(Ac_Etl_I_TablePair $tablePair) {
        $this->tablePair = $tablePair;
        $this->colMatches = false;
    }

    /**
     * @return Ac_Etl_I_TablePair 
     */
    function getTablePair() {
        return $this->tablePair;
    }

    function setSqlDb(Ac_Sql_Db $sqlDb) {
        $this->sqlDb = $sqlDb;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getSqlDb() {
        return $this->sqlDb;
    }    
    
    function getLeftDbName() {
        
    }
    
    function getColMatches() {
        if ($this->colMatches === false) {
            if (!$this->sqlDb) throw new Exception("setSqlDb() first");
            if (!$this->tablePair) throw new Exception("setTablePair() first");
            $this->colMatches = array();
            $goOn = true;
            $m = Ac_Accessor::getObjectProperty($this->tablePair, array('leftDbName', 'rightDbName', 'leftTableName', 'rightTableName'));
            foreach ($m as $k => $v) {
                if (!strlen($v)) {
                    trigger_error ("Notice: \$k is not returned by associated Ac_Etl_I_TablePair so getColMatches() won\'t detect any matches", E_USER_NOTICE);
                    $goOn = false;
                }
            }
            if ($goOn) {
                $dbi = new Ac_Sql_Dbi_Inspector_MySql5($this->sqlDb);
                $leftDb = new Ac_Sql_Dbi_Database($dbi, $m['leftDbName'], $this->tablePair->getLeftDbPrefix());
                $leftTable = $leftDb->getTable($m['leftTableName']);
                $rightDb = new Ac_Sql_Dbi_Database($dbi, $m['rightDbName'], $this->tablePair->getRightDbPrefix());
                $rightTable = $rightDb->getTable($m['rightTableName']);
                $matches = array_diff(array_intersect($leftTable->listColumns(), $rightTable->listColumns()), $this->ignore);
                foreach ($matches as $m) {
                    $this->colMatches[$m] = $m;
                }
                $this->colMatches = array_merge($this->colMatches, $this->overrides);
                foreach ($this->colMatches as $k => $v) if (is_scalar($v) && !strlen($v)) unset($this->colMatches[$k]);
            }
            
        }
        $res = $this->colMatches;
        if ($this->noCache) unset($this->colMatches);
        return $res;
    }
    
    

    /**
     * Sets which columns will be ignored when creating the matches
     */
    function setIgnore(array $ignore) {
        $this->colMatches = false;
        $this->ignore = $ignore;
    }

    /**
     * @return array
     */
    function getIgnore() {
        return $this->ignore;
    }

    /**
     * Sets overrides for resulting matches ($col => NULL will mean same as adding $col to ignore)
     */
    function setOverrides(array $overrides) {
        $this->overrides = $overrides;
        $this->colMatches = false;
    }

    /**
     * @return array
     */
    function getOverrides() {
        return $this->overrides;
    }    

    /**
     * @param bool $noCache
     */
    function setNoCache($noCache) {
        $this->noCache = (bool) $noCache;
    }

    /**
     * @return bool
     */
    function getNoCache() {
        return $this->noCache;
    }
    
    function clear() {
        $this->colMatches = false;
    }
    
}