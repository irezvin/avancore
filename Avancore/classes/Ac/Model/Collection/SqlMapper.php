<?php

class Ac_Model_Collection_SqlMapper extends Ac_Model_Collection_Mapper {
        
    /**
     * @var Ac_Sql_Select
     */
    protected $sqlSelect = null;

    protected $sqlSelectPrototype = array();

    function setSqlSelect(Ac_Sql_Select $sqlSelect = null) {
        if ($sqlSelect !== ($oldSqlSelect = $this->sqlSelect)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->sqlSelect = $sqlSelect;
            if ($this->sqlSelect) $this->sqlSelectPrototype = array();
        }
    }

    /**
     * @return Ac_Sql_Select
     */
    function getSqlSelect() {
        return $this->sqlSelect;
    }

    function setSqlSelectPrototype(array $sqlSelectPrototype) {
        if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
        $this->sqlSelectPrototype = $sqlSelectPrototype;
        if ($sqlSelectPrototype) $this->sqlSelect = false;
    }

    function getSqlSelectPrototype() {
        return $this->sqlSelectPrototype;
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function createSqlSelect() {
        if (!$this->isOpen) {
            if ($this->autoOpen) $this->setIsOpen(true);
                else throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while not isOpen(). open() first");
        }
        $storage = $this->getMapper()->getStorage();
        $res = $storage->createSqlSelect(array(), $this->appliedQuery, $this->sort, $this->limit, $this->offset);
        return $res;
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function getSqlSelectInstance() {
        if ($this->sqlSelectPrototype) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot instantiate Sql Select in ".__FUNCTION__."() "
                . "while isOpen(). close() first");
            $this->sqlSelect = $this->getMapper()->createSqlSelect($this->sqlSelectPrototype);
            $this->sqlSelectPrototype = null;
        }
        if ($this->sqlSelect) $res = $this->sqlSelect;
            else $res = null;
        return $res;
    }
    
    /**
     * @return array
     */
    protected function listExtraCriteria() {
        $res = array();
        if ($sel = $this->getSqlSelectInstance()) {
            $res = $sel->listParts();
        } else {
            $res = parent::listExtraCriteria();
        }
        return $res;
    }
    
    
    protected function resetState() {
        parent::resetState();
        if ($this->sqlSelect) {
            $this->appliedQuery[Ac_Model_Storage_MonoTable::QUERY_SQL_SELECT] = $this->sqlSelect;
        } elseif ($this->sqlSelectPrototype) {
            $this->appliedQuery[Ac_Model_Storage_MonoTable::QUERY_SQL_SELECT] = $this->sqlSelectPrototype;
        }
    }    
    
}