<?php

class Ac_Facet_Sql_ValueSetter_Where extends Ac_Facet_Sql_ValueSetter {
    
    protected $alias = false;

    protected $colExpr = false;

    function setAlias($alias) {
        $this->alias = $alias;
    }

    function getAlias() {
        return $this->alias;
    }

    function setColExpr($colExpr) {
        $this->colExpr = $colExpr;
    }

    function getColExpr() {
        return $this->colExpr;
    }
    
    function setValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select) {
        $colExpr = $this->colExpr;
        if ($colExpr === false) $colExpr = $impl->getValueCol();
        $alias = $this->alias;
        if ($alias === false) $alias = $impl->getValueAlias();
        if ($alias !== false) $select->useAlias($alias);
        $value = $impl->getValue();
        if ($this->isEmptyValue($impl, $value)) {
            $select->where['facets_'.$impl->getItem()->getName()] = new Ac_Sql_Expression(
                "IFNULL ($colExpr) IN (0, '')"
            );
        } else {
            $val = new Ac_Sql_Value($value);
            $db = new Ac_Sql_Db_Ae();
            $select->where['facets_'.$impl->getItem()->getName()] =
	      new Ac_Sql_Expression($colExpr.' = '.$db->q($val));
        }
    }
    
//    function unsetValue (Ac_Facet_Sql_ItemImpl $impl, Ac_Sql_Select $select) {
//        
//    }
    
}