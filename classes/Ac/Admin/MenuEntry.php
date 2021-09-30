<?php

class Ac_Admin_MenuEntry extends Ac_Prototyped {

    var $id = null;
    
    var $parentId = null;
    
    var $groupId = null;
    
    var $parentDefault = false;
    
    var $caption = null;
    
    var $query = null;
    
    var $translateQuery = true;
    
    var $orderBy = 0;
    
    var $requirePermissions = [];
    
    var $menuEntries = [];
    
    var $active = false;
    
    protected $defaultEntry = false;
    
    /**
     * @return Ac_Admin_MenuEntry
     */
    protected function getDefaultEntry() {
        if ($this->defaultEntry !== false) return $this->defaultEntry;
        $def = null;
        foreach ($this->menuEntries as $e) {
            if (!is_null($e->getQuery())) $def = $e;
            if ($e->parentDefault) break;
        }
        $this->defaultEntry = $def;
        return $this->defaultEntry;
    }
    
    function isActive() {
        return $this->active;
    }
    
    function isExpandable() {
        return (bool) count($this->menuEntries);
    }
    
    function hasActive() {
        foreach ($this->menuEntries as $e) {
            if ($e->isActive() || $e->hasActive()) return true;
        }
    }
    
    function getQuery() {
        if (!is_null($this->query)) return $this->query;
        $def = $this->getDefaultEntry();
        if ($def) return $def->query;
        return [];
    }
    
    function setMenuEntries(array $menuEntries, $add = false) {
        $this->defaultEntry = false;
        if (!$add) $this->menuEntries = [];
        $menuEntries = Ac_Prototyped::factoryCollection($menuEntries, 'Ac_Admin_MenuEntry');
        foreach ($menuEntries as $entry) {
            if (!$entry->id) $entry->id = 'm'.(count($this->menuEntries) + 1);
            $this->menuEntries[$entry->id] = $entry;
        }
        $this->sortMyEntries($this->menuEntries);
    }
    
    protected function sortMyEntries() {
        $def = $this->getDefaultEntry();
        uasort($this->menuEntries, function($a, $b) use ($def) {
            $res = - (($a === $def) - ($b === $def));
            if (!$res) $res = $a->orderBy - $b->orderBy;
            if (!$res) $res = strcmp($a->caption, $b->caption);
            return $res;
        });
    }
    
    static function sortMenuEntries(array & $menuEntries) {
        uasort($menuEntries, function($a, $b) {
            $res = $a->orderBy - $b->orderBy;
            if (!$res) $res = strcmp($a->caption, $b->caption);
            return $res;
        });
    }
    
    function hasPublicVars() {
        return true;
    }
    
}
