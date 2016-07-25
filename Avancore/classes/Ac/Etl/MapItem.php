<?php

class Ac_Etl_MapItem {
    
    /**
     * @var Ac_Etl_Map
     */
    protected $map = false;
    
    protected $left = false;
    
    protected $right = false;
    
    protected $wrapLeft = array();
    
    protected $wrapRight = array();
    
    /**
     * @var string
     */
    protected $key = false;
    
    function __construct(Ac_Etl_Map $map, $key, $left, $right) {
        $this->map = $map;
        $this->key = $key;
        $this->left = $left;
        $this->right = $right;
    }
    
    /**
    * @return Ac_Etl_MapItem
    */
    function cloneObject(Ac_Etl_Map $newMap, $flip = false)  {
        $res = clone $this;
        $res->map = $newMap;
        if ($flip) {
            $res->left  = $this->right;
            $res->right = $this->left;
            $res->wrapLeft = $this->wrapRight;
            $res->wrapRight = $this->wrapLeft;
        }
        return $res;
    }
    
    protected function expr($foo, $alias, $wrap, $asString, $l = false) {
        if (is_object($foo) && $foo instanceof Ac_Sql_Expression) $res = $foo;
        else {
            if (is_array($alias)) $a = array_merge($alias, array($foo));
            elseif (strlen($alias)) $a = array($alias, $foo);
            else $a = $foo;
            $res = new Ac_Sql_Expression($this->map->getDb()->n($a));
        }
        if ($wrap) {
            $o = $this->map->getDb()->q($res);
            if ($l) $l = $this->left(true);
            foreach ($wrap as $foo) {
                if ($l !== false) $o = strtr($foo, array('{l}' => $l, '{o}' => $o));
                else $o = str_replace('{o}', $o, $foo);
            }
            $res = new Ac_Sql_Expression($o);
        }
        if ($asString) $res = $this->map->getDb()->q($res);
        return $res;
    }
    
    /**
     * @return Ac_Sql_Expression 
     */
    function left($asString = false) {
        return $this->expr($this->left, $this->map->getLeftAlias(), $this->wrapLeft, $asString);
    }
    
    /**
     * @return Ac_Sql_Expression 
     */
    function right($asString = false) {
        return $this->expr($this->right, $this->map->getRightAlias(), $this->wrapRight, $asString, true);
    }
    
    function leftCol() {
        return $this->left;
    }
    
    function rightCol() {
        return $this->right;
    }
    
    function wrapLeft($wrap, $key = false) {
        if (is_null($wrap) && strlen($key)) unset($this->wrapLeft[$key]);
        else {
            if ($key == false) $this->wrapLeft[] = $wrap;
            else $this->wrapLeft[$key] = $wrap;
        }
        return $this->left();
    }
    
    function wrapRight($wrap, $key = false) {
        if (is_null($wrap) && strlen($key)) unset($this->wrapRight[$key]);
        else {
            if ($key == false) $this->wrapRight[] = $wrap;
            else $this->wrapRight[$key] = $wrap;
        }
        return $this->right();
    }
    
    function eq($c = ' = ', $wrap = null) {
        $res = $this->map->getDb()->q($this->left()).$c.$this->map->getDb()->q($this->right());
        if (!is_null($wrap)) {
            foreach (Ac_Util::toArray($wrap) as $w) {
                $res = str_replace('{o}', $res, $w);
            }
        }
        return $res;
    }
    
    function getWrapLeft() {
        return $this->wrapLeft;
    }
    
    function getWrapRight() {
        return $this->wrapRight;
    }
    
    function setWrapLeft(array $wrapLeft) {
        $this->wrapLeft = $wrapLeft;
    }
    
    function setWrapRight(array $wrapRight) {
        $this->wrapRight = $wrapRight;
    }
    
    function getKey() {
        return $this->key;
    }
    
}