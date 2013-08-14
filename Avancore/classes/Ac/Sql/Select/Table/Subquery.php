<?php

class Ac_Sql_Select_Table_Subquery extends Ac_Sql_Select_Table {
    
    /**
     * @var Ac_Sql_Select
     */
    protected $subquery = false;

    /**
     * @var array
     */
    protected $subqueryPrototype = false;

    function setSubquery(Ac_Sql_Select $subquery) {
        $this->subquery = $subquery;
        if ($this->subqueryPrototype) {
            trigger_error("\$subqueryPrototype property will not be used because setSubquery() is applied directly");
        }
        $this->subqueryPrototype = false;
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function getSubquery() {
        if ($this->subquery === false) {
            if ($this->subqueryPrototype === false) throw new Ac_E_InvalidUsage("Please either setSelect() or setSubqueryPrototype() before getSelect()");
            $this->subquery = Ac_Prototyped::factory($this->subqueryPrototype, 'Ac_Sql_Select');
        }
        return $this->subquery;
    }

    function setSubqueryPrototype(array $subqueryPrototype) {
        $this->subqueryPrototype = $subqueryPrototype;
    }

    /**
     * @return array
     */
    function getSubqueryPrototype() {
        return $this->subqueryPrototype;
    }
    
    protected function getSqlSrc() {
        if (strlen($this->name)) trigger_error("\$name property of Ac_Sql_Select_Subquery is ignored", E_USER_NOTICE);
        $subquery = $this->getSubquery();
        if (!$subquery->hasDb()) {
            $parent = $this->getSqlSelect();
            if ($parent->hasDb()) {
                $subquery->setDb($parent->getDb());
            }
        }
        $res = '('.$subquery.')';
        return $res;
    }
    
}