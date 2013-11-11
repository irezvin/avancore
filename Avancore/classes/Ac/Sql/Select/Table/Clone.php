<?php

class Ac_Sql_Select_Table_Clone extends Ac_Sql_Select_Table {
    
    protected $clonedAlias = false;
    
    /**
     * @var Ac_Sql_Select_Table
     */
    protected $original = false;
    
    protected $configured = false;

    function setClonedAlias($clonedAlias) {
        if ($clonedAlias !== ($oldClonedAlias = $this->clonedAlias)) {
            $this->clonedAlias = $clonedAlias;
            $this->configured = false;
        }
    }
    
    function setTableProvider(Ac_Sql_Select_TableProvider $tableProvider) {
        if ($tableProvider !== ($oldTableProvider = $this->_tableProvider)) {
            $this->_tableProvider = $tableProvider;
            $this->configured = false;
        }
    }

    function getClonedAlias() {
        return $this->clonedAlias;
    }    
    
    protected function configure() {
        $this->configured = true;
        if (!strlen($this->clonedAlias) || !$this->_tableProvider) return;
        $this->original = $this->_tableProvider->getTable($this->clonedAlias);
        if ($this->original) {
            if (!strlen($this->alias)) 
                throw new Ac_E_InvalidUsage("Cannot use Ac_Sql_Table_Clone without Alias");
        }
    }
    
    protected function replaceAlias($al) {
        $res = array();
        $id = $this->original->getIdentifier();
        foreach ($al as $k => $v) {
            if ($v == $id) $v = $this->alias;
            $res[$k] = $v;
        }
        return $res;
    }
    
    function getJoinClausePart() {
        $m = __FUNCTION__;
        if (!$this->configured) $this->configure();
        if ($this->original) {
            $myAlias = $this->alias;
            $res = $this->original->$m($myAlias);
        } else {
            $res = parent::$m();
        }
        return $res;
    }
    
    function getJoinsOn() {
        $m = __FUNCTION__;
        if (!$this->configured) $this->configure();
        if ($this->original) {
            $myAlias = $this->alias;
            $res = $this->original->$m($myAlias);
        } else {
            $res = parent::$m();
        }
        return $res;
    }
    
    function getDirectRequiredAliases() {
        $m = __FUNCTION__;
        if (!$this->configured) $this->configure();
        if ($this->original) {
            $res = $this->original->$m();
            $res = $this->replaceAlias($res);
        }
        else {
            $res = parent::$m();
        }
        return $res;
    }
    
    function getAllRequiredAliases() {
        $m = __FUNCTION__;
        if (!$this->configured) $this->configure();
        if ($this->original) {
            $res = $this->original->$m();
            $res = $this->replaceAlias($res);
        }
        else {
            $res = parent::$m();
        }
        return $res;
    }    
    
    function getEffectiveJoinType() {
        $m = __FUNCTION__;
        if (!$this->configured) $this->configure();
        if ($this->original) return $this->original->$m();
        else return parent::$m();
    }
    
    function hasUsingKeyword() {
        $m = __FUNCTION__;
        if (!$this->configured) $this->configure();
        if ($this->original) return $this->original->$m();
        else return parent::$m();
    }
    
}