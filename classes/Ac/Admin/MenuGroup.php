<?php

class Ac_Admin_MenuGroup extends Ac_Prototyped {
 
    var $id = null;
    
    var $title = null;
    
    var $regex = null;
    
    var $entryIds = [];
    
    protected $entries = [];    
    
    function construct(array $options = []) {
        $entryIds = [];
        foreach ($options as $k => $v) if (is_numeric($k)) {
            $entryIds[] = $v;
            unset($options[$k]);
        }
        if (isset($options['entryIds']) && is_array($options['entryIds'])) {
            $entryIds = array_unique(array_merge($options['entryIds'], $entryIds));
        }
        $options['entryIds'] = $entryIds;
        parent::__construct($options);
    }
    
    function hasMenuEntry(Ac_Admin_MenuEntry $entry) {
        return 
            $entry->groupId === $this->id 
            || in_array($entry->id, $this->entryIds) 
            || $this->regex && preg_match($this->regex, $entry->id);            
    }
    
    function getTitle() {
        if (strlen($this->title)) return $this->title;
        return new Ac_Lang_String('menu_group_'.$this->id, ['default' => ucfirst($this->id)]);
    }
    
    function hasPublicVars() {
        return true;
    }
    
}
