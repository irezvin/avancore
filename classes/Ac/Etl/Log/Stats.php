<?php

class Ac_Etl_Log_Stats {
    
    protected $items = array();
    
    protected $tags = false;
    
    function setItems(array $items) {
        $this->items = $items;
        $this->tags = false;
    }
    
    function getTags() {
        if ($this->tags === false) {
            $this->tags = array();
            $this->calcTags();
        }
        return $this->tags;
    }
    
    /**
     * @return Ac_Etl_Log_Stats_Tag
     */
    function getTag($name, $dontCreate = false) {
        $res = null;
        if (isset($this->tags[$name])) $res = $this->tags[$name];
        elseif (!$dontCreate) {
            $res = $this->tags[$name] = new Ac_Etl_Log_Stats_Tag($name, $this);
        }
        return $res;
    }
    
    protected function calcTags() {
        foreach ($this->items as $item) {
            foreach ($item->getDirectTags() as $name) {
                $this->getTag($name)->accept($item);
            }
        }
        $allTags = self::getAllTags(array_keys($this->tags));
        foreach ($allTags as $name) {
            if (!isset($this->tags[$name])) $this->getTag($name);
        }
        ksort($this->tags);
    }
    
    static function getAllTags(array $tags) {
        $res = $tags;
        foreach ($res as $item) {
            $foo = explode("/", $item);
            for ($i = 1; $i < count($foo); $i++) {
                $res[] = implode("/", array_slice($foo, 0, $i));
            }
        }
        $res = array_unique($res);
        return $res;        
    }
    
    function getExtendedStats($withIndirect = false) {
        $res = array();
        foreach ($this->getTags() as $name => $tag) {
            $res[$name] = $tag->getExtendedStats($withIndirect);
        }
        return $res;
    }
    
}