<?php

/**
 * 
 */

/**
 * Loads data from some external table into internal Import table
 */
class Ac_Etl_Operation_Loader extends Ac_Etl_Operation {
    
    protected $srcTableName = false;
    
    protected $srcDbName = false;
    
    protected $srcDbPrefix = false;
    
    /**
     * @var bool
     */
    protected $clear = true;
    
    
    
    /**
     * @var bool
     */
    protected $useReplace = false;
    
    protected $sqlSelectPrototype = array();
    
    protected $overrideValues = array();
    
    /**
     * @var array|Ac_Etl_I_Matcher
     * map ($importColName => $srcColName)
     */
    protected $colMatches = false;

    function setColMatches($colMatches) {
        if (!(is_array($colMatches) || ($colMatches instanceof Ac_Etl_I_Matcher))) 
            throw new Exception("\$colMatches must be an instance of Ac_Etl_I_Matcher or an array");
        if ($colMatches instanceof Ac_Etl_I_Matcher) {
            $colMatches->setTablePair($this);
        }
        $this->colMatches = $colMatches;
    }
    
    function getColMatches($asArray = false) {
        if ($asArray && $this->colMatches instanceof Ac_Etl_I_Matcher) {
            $this->colMatches->setTablePair($this);
            $this->colMatches->setSqlDb($this->getDb());
            return $this->colMatches->getColMatches();
        }
        return $this->colMatches;
    }
    
    protected function doProcess() {
        $t = $this->getTable();
        if ($this->clear) $t->cleanTmpData();
        $map = $this->mkMap($this->getColMatches(true), $t->tableName('array'), 'source'); // now it is of source => target
        $db = $this->getDb();
        if ($this->overrideValues) {
            $map->union($this->mkMap($target->restriction, $t->tableName('array'), false)->rightAreValues() );
        }
        $rest = $t->restriction;
        if (in_array('importId', $t->getImporterDbiTable()->listColumns()) && strlen($id = $this->import->getImportId())) {
            $rest['importId'] = $id;
        }
        if ($rest) { // apply restriction
           $map->union($this->mkMap($t->restriction, $t->tableName('array'), false)->rightAreValues() );
        }
        
        $proto = Ac_Util::m(array(
            'tables' => array(
                'source' => array(
                    'name' => new Ac_Sql_Expression($db->n(array($this->getSrcDbName(), str_replace('#__', $this->getSrcDbPrefix(), $this->srcTableName))))
                )
            ),
            'primaryAlias' => 'source',
        ), $this->sqlSelectPrototype);
        $select = new Ac_Sql_Select($db, $proto);
        $sql = $map->copyStmt($select, $this->insertIgnore, $this->useReplace);
        
        $idPath = $this->getIdPath();
        $res = $db->query("-- tags: operations/{$idPath}/modify\n".$sql);
        return $res;
    }
    
    function setDb(Ac_Sql_Db $db) {
        parent::setDb($db);
        if ($this->colMatches instanceof Ac_Etl_I_Matcher) $this->colMatches->setSqlDb($db);
    }
    
    function setSrcTableName($srcTableName) {
        $this->srcTableName = $srcTableName;
    }
    
    function getSrcTableName() {
        return $this->srcTableName;
    }    

    function setSrcDbName($srcDbName) {
        $this->srcDbName = $srcDbName;
    }

    function getSrcDbName() {
        if (!strlen($this->srcDbName)) return $this->import->getTargetDbName();
        return $this->srcDbName;
    }

    function setSrcDbPrefix($srcDbPrefix) {
        $this->srcDbPrefix = $srcDbPrefix;
    }

    function getSrcDbPrefix() {
        if (!strlen($this->srcDbPrefix)) return $this->import->getTargetDbPrefix();
        return $this->srcDbPrefix;
    }    

    function setClear($clear) {
        $this->clear = (bool) $clear;
    }

    /**
     * @return bool
     */
    function getClear() {
        return $this->clear;
    }    
    
    
    function getRightTableName() {
        return $this->getSrcTableName();
    }
    
    function getRightDbName() {
        return $this->getSrcDbName();
    }
    
    function getRightDbPrefix() {
        return $this->getSrcDbPrefix();
    }

    /**
     * @param bool $useReplace
     */
    function setUseReplace($useReplace) {
        $this->useReplace = $useReplace;
    }

    /**
     * @return bool
     */
    function getUseReplace() {
        return $this->useReplace;
    }    

    function setOverrideValues(array $overrideValues) {
        $this->overrideValues = $overrideValues;
    }

    /**
     * @return array
     */
    function getOverrideValues() {
        return $this->overrideValues;
    }    
    
    function setSqlSelectPrototype(array $sqlSelectPrototype) {
        $this->sqlSelectPrototype = $sqlSelectPrototype;
    }
    
    function getSqlSelectPrototype() {
        return $this->sqlSelectPrototype;
    }
    
}