<?php

class Ac_Facet_Sql_ItemImpl extends Ac_Facet_ItemImpl implements Ac_Facet_Sql_I_ItemImpl {

    protected $titleAlias = false;

    protected $titleCol = false;

    protected $valueAlias = false;

    protected $valueCol = false;

    protected $valueSetter = false;
    
    protected $selectExtra = array();
    
    protected $alwaysApply = false;
    
    protected $withCounts = null;
    
    /**
     * @var array
     */
    protected $selectExtraForValues = array();
    
    /**
     * @var array
     */
    protected $selectPrototypesForValues = array();
    
    /**
     * @var Ac_Sql_Select
     */
    protected $selectForValues = false;
    
    /**
     * @var array
     */
    protected $selectPrototypeForOtherValues = false;

    protected $valuesWhere = array();

    protected $valuesHaving = array();
    
    
    function setSelectForValues(Ac_Sql_Select $selectForValues) {
        $this->selectForValues = $selectForValues;
    }

    /**
     * @return Ac_Sql_Select
     */
    function getSelectForValues() {
        return $this->selectForValues;
    }

    function setValuesWhere(array $valuesWhere = array()) {
        $this->valuesWhere = $valuesWhere;
    }

    function getValuesWhere() {
        return $this->valuesWhere;
    }

    function setValuesHaving(array $valuesHaving = array()) {
        $this->valuesHaving = $valuesHaving;
    }

    function getValuesHaving() {
        return $this->valuesHaving;
    }    
    
    /**
     * @return Ac_Facet_Sql_SetImpl
     */
    function getSetImpl() {
        $res = $this->getItem()->getFacetSet()->getImpl();
        if (!$res instanceof Ac_Facet_Sql_SetImpl) throw new Exception("Incompatible \$setImpl, Ac_Facet_Sql_SetImpl expected, got: ".get_class($res));
        return $res;
    }

    function setAlwaysApply($alwaysApply) {
        $this->alwaysApply = (bool) $alwaysApply;
    }

    function getAlwaysApply() {
        return $this->alwaysApply;
    }
    
    function getPossibleValues() {
        if ($this->selectForValues) $select = $this->selectForValues;
            else $select = $this->getSetImpl()->createSelectForItem($this);
        //$select->distinct = true;
        $tc = $this->getTitleCol();
        $vc = $this->getValueCol();
        $ta = $this->getTitleAlias();
        $va = $this->getValueAlias();
        if ($ta !== false) {
            $select->useAlias($ta);
            if (!is_array($ta) && !(is_object($tc) && $tc instanceof Ac_I_Sql_Expression)) $tc = $select->getDb()->n(array($ta, $tc));
        }
        if ($va !== false) {
            $select->useAlias($va);
            if (!is_array($vc) && !(is_object($vc) && $vc instanceof Ac_I_Sql_Expression)) {
                $vc = $select->getDb()->n(array($va, $vc));
            }
        }
        // WTF
        $columns = array('title' => $tc, 'value' => $vc);
        /*if (is_array($select->columns) && $select->columns) $columns = array_merge($columns, $select->columns);*/
        $select->columns = $columns;
        if (!$select->orderBy) $select->orderBy = 'title ASC';
        if ($this->getWithCounts()) {
            $select->columns['count'] = $this->getSetImpl()->getCountColName();
            $select->groupBy = array($vc);
            if (!is_object($select->columns['count'])) $select->columns['count'] = 'COUNT(DISTINCT '.$select->columns['count'].')';
        }
        if (is_array($this->selectExtra)) {
            if (isset($this->selectExtra['columns']) && is_array($this->selectExtra['columns'])) {
                Ac_Util::ms($select->columns, $this->selectExtra['columns']);
            }
        }
        if ($this->valuesWhere) Ac_Util::ms($select->where, $this->valuesWhere);
        if ($this->valuesHaving) {
            Ac_Util::ms($select->having, $this->valuesHaving);
        }
        $t0 = microtime(true);
        $res = $this->fetchValues($select);
        if ($this->item->getDebug()) {
            $t1 = microtime(true) - $t0;
            $this->item->setDebugData('impl', array('time' => round($t1, 4), 'query' => $select->getDb()->replacePrefix(''.$select)));
        }
        return $res;
    }
    
    protected function fetchValues($select) {
        $res = $select->getDb()->fetchArray($select, 'value');
        return $res;
    }

    function setTitleAlias($titleAlias) {
        $this->titleAlias = $titleAlias;
    }

    function getTitleAlias() {
        if ($this->titleAlias === false && $this->valueCol !== false) return $this->valueAlias;
        return $this->titleAlias;
    }

    function setTitleCol($titleCol) {
        $this->titleCol = $titleCol;
    }

    function getTitleCol() {
        return $this->titleCol !== false? $this->titleCol : $this->getValueCol();
    }

    function setValueAlias($valueAlias) {
        $this->valueAlias = $valueAlias;
    }

    function getValueAlias() {
        return $this->valueAlias;
    }

    function setValueCol($valueCol) {
        $this->valueCol = $valueCol;
    }

    function getValueCol(Ac_Sql_Db $quoteBy = null) {
        if ($this->valueCol === false) $res = $this->getItem()->getName();
        else $res = $this->valueCol;
        if ($quoteBy) {
            $va = $this->getValueAlias();
            if ($va !== false) {
                if (!is_array($res) && !(is_object($res) && $res instanceof Ac_I_Sql_Expression)) {
                    $res = $quoteBy->n(array($va, $res));
                }
            }
        }
        return $res;
    }

    function setValueSetter($valueSetter) {
        $this->valueSetter = $valueSetter;
    }

    /**
     * @return Ac_Facet_Sql_ValueSetter
     */
    function getValueSetter() {
        if (!$this->valueSetter) $this->valueSetter = array(
            'class' => 'Ac_Facet_Sql_ValueSetter_Where',
            //'partName' => 'facet_'.$this->getItem()->getName()
        );
        if (is_array($this->valueSetter)) {
            if (!isset($this->valueSetter['class'])) 
                $this->valueSetter['class'] = isset($this->valueSetter['partName'])? 'Ac_Facet_Sql_ValueSetter_Part' : 'Ac_Facet_Sql_ValueSetter_Where';
            $this->valueSetter = Ac_Prototyped::factory ($this->valueSetter, 'Ac_Facet_Sql_ValueSetter');
        }
        return $this->valueSetter;
    }
    
    function applyToSelectPrototype(array & $prototype, Ac_Facet_Sql_I_ItemImpl $currValuesImpl = null) {
        /*if ($currValuesImpl === $this && $this->selectExtraForValues) {
            if ($this->selectExtraForValues) {
                Ac_Util::ms($prototype, $this->selectExtraForValues);
            }
        }*/
        if ($currValuesImpl === $this && !$this->alwaysApply) return;
        if ($this->selectExtra) {
            Ac_Util::ms($prototype, $this->selectExtra);
        }
        $found = false;
        if ($this->selectPrototypesForValues) {
            $v = $this->getValue();
            if ($v !== false || is_array($v) && count($v)) {
                $v = Ac_Util::toArray($v);
                foreach ($v as $val) {
                    if (isset($this->selectPrototypesForValues[$val])) {
                        $found = true;
                        Ac_Util::ms($prototype, $this->selectPrototypesForValues[$val]);
                    }
                }
            }
        }
        if (!$found && $this->selectPrototypeForOtherValues) {
            Ac_Util::ms($prototype, $this->selectPrototypeForOtherValues);
        }
    }
    
    function applyToSelect(Ac_Sql_Select $select, Ac_Facet_Sql_I_ItemImpl $currValuesImpl = null) {
        if ($currValuesImpl === $this && !$this->alwaysApply) return;
        $v = $this->getValue();
        if ($v === false || is_array($v) && !count($v)) return; // value not provided
        $this->getValueSetter(true)->setValue($this, $select);
    }

    function setSelectExtra(array $selectExtra) {
        $this->selectExtra = $selectExtra;
    }

    function getSelectExtra() {
        return $this->selectExtra;
    }    

    function setWithCounts($withCounts) {
        $this->withCounts = $withCounts;
    }

    function getWithCounts() {
        if (is_null($this->withCounts)) return $this->getSetImpl()->getWithCounts();
        return $this->withCounts;
    }    

    function setSelectPrototypesForValues(array $selectPrototypesForValues) {
        $this->selectPrototypesForValues = $selectPrototypesForValues;
    }

    /**
     * @return array
     */
    function getSelectPrototypesForValues() {
        return $this->selectPrototypesForValues;
    }    

    function setSelectPrototypeForOtherValues(array $selectPrototypeForOtherValues) {
        $this->selectPrototypeForOtherValues = $selectPrototypeForOtherValues;
    }

    /**
     * @return array
     */
    function getSelectPrototypeForOtherValues() {
        return $this->selectPrototypeForOtherValues;
    }    

    function setSelectExtraForValues(array $selectExtraForValues) {
        $this->selectExtraForValues = $selectExtraForValues;
    }

    /**
     * @return array
     */
    function getSelectExtraForValues() {
        return $this->selectExtraForValues;
    }    
    
}