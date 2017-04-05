<?php

class Ac_Etl_Map extends Ac_Prototyped implements IteratorAggregate {
    
    /**
     * @var Ac_Sql_Db
     */
    protected $db = false;

    /**
     * @var ps
     */
    protected $map = false;

    protected $leftAlias = false;

    protected $rightAlias = false;
    
    protected $items = array();
    
    /**
     * @param type $rightAlias 
     * @param type $leftAlias
     */
    static function create($map, Ac_Sql_Db $db, $leftAlias, $rightAlias) {
        if ($map instanceof Ac_Etl_Map) $res = $map->cloneObject();
        else $res = new Ac_Etl_Map(array('map' => $map));
        $res->leftAlias = $leftAlias;
        $res->rightAlias = $rightAlias;
        $res->db = $db;
        return $res;
    }
    
    /**
     * @return array
     */
    function getItems() {
        return $this->items;
    }
    
    function getIterator () {
        return new ArrayIterator($this->items);
    }
    
    protected function setDb(Ac_Sql_Db $db) {
        $this->db = $db;
    }

    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->db;
    }

    /**
     * @param array|Ac_Etl_I_MapProvider $map
     * @return Ac_Etl_Map
     */
    function setMap($map) {
        if (!(is_array($map) || $map instanceof Ac_Etl_I_Matcher)) {
            throw new Exception("\$map must be either an array or Ac_Etl_I_Matcher");
        }
        if (!is_array($map)) $map = $map->getColMatches();
        $keys = array();
        $i = 0;
        foreach ($map as $k => $v) {
            if (is_numeric($k) && is_array($v) && count($v) == 2) {
                $k = $v[0];
                $v = $v[1];
                
            // this gives us ability to provide matching columns as numeric keys -- 
            // let's hope that columns like 0, 1, 2 won't be encountered very often                
            } elseif (is_numeric($k) && !is_array($v)) { 
                
                $k = $v;
            }
            $i++;
            $key = md5(is_string($k)? $k : serialize($k));
            if (in_array($key, $keys)) {
                $strKey = is_string($k)? "'$k'" : " of item #{$i} ";
                trigger_error("Duplicate key $strKey in the map", E_USER_WARNING);
            } else {
                $keys[] = $key;
            }
            $this->items[$key] = new Ac_Etl_MapItem($this, $key, $k, $v);
        }
        return $this;
    }

    function setLeftAlias($leftAlias) {
        $this->leftAlias = $leftAlias;
        return $this;
    }

    function getLeftAlias() {
        return $this->leftAlias;
    }

    function setRightAlias($rightAlias) {
        $this->rightAlias = $rightAlias;
        return $this;
    }

    function getRightAlias() {
        return $this->rightAlias;
    }    
    
    function wrapLeft($wrap, $key = false) {
        foreach ($this->items as $item) $item->wrapLeft($wrap, $key);
        return $this;
    }
    
    function wrapRight($wrap, $key = false) {
        foreach ($this->items as $item) $item->wrapRight($wrap, $key);
        return $this;
    }

    /**
     * @return Ac_Etl_Map 
     */
    function cloneObject() {
        $res = clone $this;
        return $res;
    }
    
    function __clone() {
        foreach ($this->items as $k => $item) $this->items[$k] = $item->cloneObject($this);
    }
    
    /**
     * @return Ac_Etl_Map 
     */
    function flip() {
        $newMap = array();
        foreach ($this->items as $item) {
            $newItem = $item->cloneObject($this, true);
            $k = $newItem->leftCol();
            $newMap[md5(is_string($k)? $k : serialize($k))] = $newItem;
        }
        $a = array($this->leftAlias, $this->rightAlias);
        list ($this->rightAlias, $this->leftAlias) = $a;
        $this->items = $newMap;
        return $this;
    }
    
    function listItems() {
        return array_keys(array_keys($this->items));
    }
    
    /**
     * @return Ac_Etl_MapItem
     */
    function getItem($idx) {
        $k = array_keys($this->items);
        if (!isset($k[$idx])) throw new Exception("No item with index: {$idx}");
        $res = $this->items[$k[$idx]];
        return $res;
    }
    
    /**
     * @return Ac_Etl_MapItem $item 
     */
    function findByLeft($item) {
        if (is_object($item) && $item instanceof Ac_Etl_MapItem) {
            $k = $item->getKey();
        } else {
            $k = md5(is_string($item)? $item : serialize($item));
        }
        if (isset($this->items[$k])) $res = $this->items[$k];
            else $res = null;
        
        return $res;   
    }
    
    
    function merge($map, $wrapRight = false) {
        $map = $this->mkMap($map);
        foreach ($map as $key => $item) {
            if (isset($this->items[$key])) {
                $old = $this->items[$key];
                if (!strlen($wrapRight)) {
                    $this->items[$key] = $item->cloneObject($this);
                    $this->items[$key]->setWrapLeft($old->getWrapLeft());
                } else {
                    $w = $wrapRight;
                    $w = str_replace(array('{o}', '{n}'), array($this->db->q($this->items[$key]->right()), $this->db->q($item->right())), $w);
                    $this->items[$key] = new Ac_Etl_MapItem($this, $key, $old->leftCol(), new Ac_Sql_Expression($w));
                    $this->items[$key]->setWrapLeft($old->getWrapLeft());
                }
            } else {
                $this->items[$key] = $item->cloneObject($this);
            }
        }
        return $this;
    }
    
    /**
     * @return Ac_Etl_Map
     */
    function mkMap($map, $clone = false) {
        if (!(is_object($map) && $map instanceof Ac_Etl_Map)) $map = self::create($map, $this->db, $this->leftAlias, $this->rightAlias);
        elseif ($clone) $map = $map->cloneObject();
        return $map;
    }
    
    protected function getHashes() {
        return array_keys($this->items);
    }
    
    /**
     * @return Ac_Etl_Map 
     */
    function intersect($map) {
        $map = $this->mkMap($map);
        $this->items = array_intersect_key($this->items, $map->items);
        return $this;
    }
    
    /**
     * @return Ac_Etl_Map 
     */
    function union($map) {
        $map = $this->mkMap($map);
        $newItems = array_diff_key($map->items, $this->items);
        foreach ($newItems as $k => $i) {
            $this->items[$k] = $i->cloneObject($this);
        }
        return $this;
    }
     
    /**
     * @return Ac_Etl_Map 
     */
    function diff($map) {
        $map = $this->mkMap($map);
        $this->items = array_diff_key($this->items, $map->items);
        return $this;
    }
    
    function eq($c = ' = ', $implode = ', ', $wrap = null) {
        $res = array();
        foreach ($this->items as $item) $res[] = $item->eq($c, $wrap);
        if ($implode !== false) $res = implode($implode, $res);
        return $res;
    }
    
    function left($implode = ', ', $asString = false) {
        $res = array();
        foreach ($this->items as $item) $res[] = $item->left($asString || $implode !== false);
        if ($implode !== false) $res = implode($implode, $res);
        return $res;
    }
    
    function leftCols($implode = ', ') {
        $res = array();
        foreach ($this->items as $item) {
            $lc = $item->leftCol();
            if (is_object($lc) && $lc instanceof Ac_Sql_Expression)
                $lc = $this->db->n($lc);
            $res[] = $this->db->n($lc);
        }
        if ($implode !== false) $res = implode($implode, $res);
        return $res;
    }
    
    function right($implode = ', ', $asString = false) {
        $res = array();
        foreach ($this->items as $item) $res[] = $item->right($asString || $implode !== false);
        if ($implode !== false) $res = implode($implode, $res);
        return $res;
    }
    
    /**
     * @return Ac_Etl_Map
     */
    function rightAreValues() {
        foreach ($this->items as $key => $item) {
            $right = $item->rightCol();
            if (!(is_object($right) && $right instanceof Ac_Sql_Expression)) {
                $right = new Ac_Sql_Expression($this->db->quote($right));
                $this->items[$key] = new Ac_Etl_MapItem($this, $key, $item->leftCol(), $right);
            }
        }
        return $this;
    }
    
    /**
     * @return array 
     */
    function assoc() {
        $res = array();
        foreach ($this->items as $item) {
            $res[$item->left(true)] = $item->right();
        }
        return $res;
    }
    
    /**
     * @return Ac_Etl_Map
     */
    function applyDefaults($defaults) {
        $defaults = $this->mkMap($defaults, true)->rightAreValues();
        $this->merge($defaults, 'IFNULL({o}, {n})');
        return $this;
    }
    
    /**
     * @param bool|array $ignoreNulls Encode right-side expressions in ifnull() so only non-null values will be assigned (if array, list of dest columns to ignore nulls)
     * @return array|string
     */
    function mkUpdateMap($ignoreNulls = false, $asArray = false) {
        $map = $this;
        if ($ignoreNulls) {
            $map = $map->cloneObject();
            if ($ignoreNulls = true) $map->wrapRight('IFNULL({o},{l})');
            else {
                $in = array();
                foreach ($ignoreNulls as $col) $in[] = array($col, 'dummy');
                foreach ($map->mkMap($in) as $item)
                if ($i = $m->findByLeft($item)) $i->wrapRight('IFNULL({o},{l})');
            }
        }
        $res = $asArray? $map->assoc() : $map->eq();
        return $res;
    }
    
    /**
     * @param Ac_Sql_Select $select 
     * @return string
     */
    
    function updateStmt(Ac_Sql_Select $select, $ignoreNulls = false) {
        $setExpression = $this->mkUpdateMap($ignoreNulls);
        $sql = "UPDATE\n".$select->getFromClause()."\nSET\n{$setExpression}\n".$select->getWhereClause(true);
        return $sql;
    }
    
    /**
     * @param Ac_Sql_Select $select
     * @return string 
     */
    function copyStmt(Ac_Sql_Select $select, $ignore = false, $useReplace = false) {
        $insertColumnsList = $this->leftCols();
        $select->columns = $this->right();
        $ignore = $ignore? "IGNORE " : "";
        if ($useReplace) $stmt = "REPLACE ";
            else $stmt = "INSERT ";
        $stmt .= "{$ignore}INTO ".$this->db->nameQuote($this->leftAlias)." ({$insertColumnsList}) \n".$select;
        return $stmt;
    }
    
    function nullCriterion($notNull = false, $forRight = false, $implode = ' AND ') {
        $items = $forRight? $this->right(false, true) : $this->left(false, true);
        $res = array();
        foreach ($items as $item) {
            $res[] = $item.($notNull? " IS NOT NULL " : " IS NULL ");
        }
        if ($implode) $res = implode($implode, $res);
        return $res;
    }
    
    
    
    function sameRecordsCriterion(array $nullableLeftColumns, $ignoreNulls = false, $implode = "\n AND ", & $debugColumns = array()) {

        $sameRecordsCriterion = array();
        
        $nlc = array();
        foreach ($nullableLeftColumns as $lc) $nlc[] = array($lc, 'not used');
        $nlc = $this->mkMap($nlc);
        
        $debugColumns = array();

        $lrCols = array();
        $eqCols = array();
        
        foreach ($this->items as $key => $item) {
            
            $nullable = $nlc->findByLeft($item);
            
            $qDest = $item->left(true);
            $qSrc = $item->right(true);
            
            $dName = $item->leftCol();
            $sName = $dName.'_s';
            $eqName = $dName.'_eq';
            
            $lrCols[] = $item->right(true)." AS ".$this->db->n($sName);
            $lrCols[] = $item->left(true)." AS ".$this->db->n($dName);
            
            if (!$nullable) {
                $c = "($qSrc IS NULL OR $qSrc = $qDest)";
            } elseif ($ignoreNulls) { // we don't copy nulls and destination column is NOT nullable
                $c = "(($qDest IS NULL) OR ($qSrc IS NOT NULL AND $qDest IS NOT NULL AND $qSrc = $qDest))";
            } else {
                $c = "(($qSrc IS NULL AND $qDest IS NULL) OR ($qSrc IS NOT NULL AND $qDest IS NOT NULL AND $qSrc = $qDest))";
            }
            
            $eqCols[] = $c." AS ".$this->db->n($eqName);
            
            $sameRecordsCriterion[] = $c;
        }
        
        $debugColumns = array_merge($lrCols, $eqCols);
        
        if ($implode) $sameRecordsCriterion = implode($implode, $sameRecordsCriterion);
        
        return $sameRecordsCriterion;
        
    }
    
}
