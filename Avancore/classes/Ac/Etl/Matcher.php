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
    /**
     * RegExps search => replace to apply to left columns before matching
     * @var array
     */
    protected $leftFixes = array();

    /**
     * RegExps search => replace to apply to right columns before matching
     * @var array
     */
    protected $rightFixes = array();

    /**
     * function($left, $right, & $res, $leftWithFixes, $rightWithFixes) that returns additional matches
     * @var callback
     */
    protected $callback = false;    
    
    /**
     * comapre lowercased varsions of field names
     * @var bool
     */
    protected $caseFold = true;
    
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
                $matches = $this->matchColumns($leftTable->listColumns(), $rightTable->listColumns());
                foreach ($matches as $left => $right) {
                    $this->colMatches[$left] = $right;
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
    
    protected function applyFixes($strings, array $regExps, array $ignore = array()) {
        $res = array();
        foreach ($strings as $string) {
            $orig = $string;
            foreach ($regExps as $search => $replace) $string = preg_replace($search, $replace, $string);
            if ($this->caseFold) $string = strtolower($string);
            if (strlen($string) && !in_array($string, $ignore))
                $res[$string] = $orig;
        }
        return $res;
    }
    
    protected function matchColumns(array $left, array $right) {
        $leftMap = $this->applyFixes($left, $this->leftFixes, $this->ignore);
        $rightMap = $this->applyFixes($right, $this->rightFixes, $this->ignore);
        $same = array_intersect(array_keys($leftMap), array_keys($rightMap));
        $res = array();
        foreach ($same as $mapped) {
            $res[$leftMap[$mapped]] = $rightMap[$mapped];
        }
        if ($this->callback) {
            $items = call_user_func_array($this->$callback, array($left, $right, & $res, $leftMap, $rightMap));
            if (is_array($items))
                foreach ($items as $k => $v) {
                    $res[$k] = $v;
                }
        }
        return $res;
    }
    
    /**
     * Sets RegExps search => replace to apply to left columns before matching
     */
    function setLeftFixes(array $leftFixes) {
        $this->leftFixes = $leftFixes;
    }

    /**
     * Returns RegExps search => replace to apply to left columns before matching
     * @return array
     */
    function getLeftFixes() {
        return $this->leftFixes;
    }

    /**
     * Sets RegExps search => replace to apply to right columns before matching
     */
    function setRightFixes(array $rightFixes) {
        $this->rightFixes = $rightFixes;
    }

    /**
     * Returns RegExps search => replace to apply to right columns before matching
     * @return array
     */
    function getRightFixes() {
        return $this->rightFixes;
    }

    /**
     * Sets function($left, $right, & $res, $leftWithFixes, $rightWithFixes) that returns additional matches
     */
    function setCallback(callback $callback) {
        $this->callback = $callback;
    }

    /**
     * Returns function($left, $right, & $res, $leftWithFixes, $rightWithFixes) that returns additional matches
     * @return callback
     */
    function getCallback() {
        return $this->callback;
    }    
    
    /**
     * Sets comapre lowercased varsions of field names
     * @param bool $caseFold
     */
    function setCaseFold($caseFold) {
        $this->caseFold = $caseFold;
    }

    /**
     * Returns comapre lowercased varsions of field names
     * @return bool
     */
    function getCaseFold() {
        return $this->caseFold;
    }    
    
}