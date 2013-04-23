<?php

class Ac_Facet_Sql_ItemImpl extends Ac_Facet_ItemImpl {

    protected $titleAlias = false;

    protected $titleCol = false;

    protected $valueAlias = false;

    protected $valueCol = false;

    protected $valueSetter = false;
    
    protected $selectExtra = array();
    
    protected $withCounts = null;
    
    /**
     * @return Ac_Facet_Sql_SetImpl
     */
    function getSetImpl() {
        $res = $this->getItem()->getFacetSet()->getImpl();
        if (!$res instanceof Ac_Facet_Sql_SetImpl) throw new Exception("Incompatible \$setImpl, Ac_Facet_Sql_SetImpl expected, got: ".get_class($res));
        return $res;
    }
    
    function getPossibleValues() {
        $select = $this->getSetImpl()->createSelectForItem($this);
        //$select->distinct = true;
        $tc = $this->getTitleCol();
        $vc = $this->getValueCol();
        $ta = $this->getTitleAlias();
        $va = $this->getValueAlias();
        if ($ta !== false) {
            $select->useAlias($ta);
            if (!is_array($ta)) $tc = $select->getDb()->n(array($ta, $tc));
        }
        if ($va !== false) {
            $select->useAlias($va);
            if (!is_array($va)) $vc = $select->getDb()->n(array($va, $vc));
        }
        $select->columns = array('title' => $tc, 'value' => $vc);
        $select->orderBy = 'title ASC';
        if ($this->getWithCounts()) {
            $select->columns['count'] = $this->getSetImpl()->getCountColName();
            $select->groupBy = array($vc);
            if (!is_object($select->columns['count'])) $select->columns['count'] = 'COUNT(DISTINCT '.$select->columns['count'].')';
        }
        $t0 = microtime(true);
        $res = $select->getDb()->fetchArray($select, 'value');
        if ($this->debug) {
            $t1 = microtime(true) - $t0;
            Ac_Debug::fb($select."\n -- ".round($t1, 4));
        }
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

    function getValueCol() {
        if ($this->valueCol === false) return $this->getItem()->getName();
        return $this->valueCol;
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
    
    function applyToSelectPrototype(array & $prototype) {
        if ($this->selectExtra) Ac_Util::ms($prototype, $this->selectExtra);
    }
    
    function applyToSelect(Ac_Sql_Select $select) {
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
    
    
}