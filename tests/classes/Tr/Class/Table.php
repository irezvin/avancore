<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Tr_Class_Table {
    
    /**
     * @var array
     */
    protected $entries = array();
    
    /**
     * @var Tr_Plan
     */
    protected $plan = null;

    function setEntries(array $entries, $dontReplace = false) {
        if (!$dontReplace) $this->entries = array();
        Ac_Util::ms($this->entries, Ac_Prototyped::factoryCollection(
            $entries, 'Tr_Table_Entry', array('table' => $this), 'key', true
        ));
        $this->entries = $entries;
    }
    
    function addEntry(Tr_Class_Entry $entry) {
        if ($this->hasEntry($key = $entry->getKey())) {
            throw Ac_E_InvalidCall::alreadySuchItem('entry', $key);
        }
        $this->entries[$entry->getKey()] = $entry;
        $entry->setTable($this);
    }

    function setPlan(Tr_Plan $plan = null) {
        $this->plan = $plan;
    }

    /**
     * @return Tr_Plan
     */
    function getPlan() {
        return $this->plan;
    }    

    /**
     * @return array
     */
    function getEntries() {
        return $this->entries;
    }
    
    function hasEntry($key) {
        return isset($this->entries[$key]);
    }
    
    /**
     * @param string $key
     * @param type $dontThrow Don't throw exception if an Entry isn't found
     * @return Tr_Class_Entry
     * @throws Ac_E_InvalidCall
     */
    function getEntry($key, $dontThrow = false) {
        $res = isset($this->entries[$key])? $this->entries[$key] : null;
        if (!$res && !$dontThrow)
            throw Ac_E_InvalidCall::noSuchItem ('entry', $key, 'hasEntry');
        return $res;
    }
    
    /**
     * Finds an entry in the table which $key matches class of $objectOrClass
     * or it's closest ancestors, if such entry not found.
     * 
     * findNearestEntry := 
     *      hasEntry(get_class($objectOrClass))?
     *          getEntry(get_class($objectOrClass))
     *        : getEntry(get_parent_class($objectOrClass))
     * 
     * @param string|object $objectOrClass
     * @return Tr_Class_Entry
     */
    function findEntry($objectOrClass) {
        if (!is_object($objectOrClass) && !is_string($objectOrClass))
            throw Ac_E_InvalidCall::wrongType ('objectOrClass', $objectOrClass, 'object or string');
        if (($hasClass = is_object($objectOrClass))) {
            $class = get_class($objectOrClass);
        } else {
            $class = $objectOrClass;
        }
        $res = false;
        if ($this->hasEntry($class)) $res = $this->getEntry($class);
        elseif ($hasClass || class_exists($class)) {
            do {
                $class = self::getParentClassEntryKey($class);
                if (strlen($class) && $this->hasEntry($class)) {
                    $res = $this->getEntry($class);
                } else {
                    if (!strlen($class)) $class = false;
                }
            } while (strlen($class) && !$res);
        }
        return $res;
    }
    
    static function getParentClassEntryKey($class) {
        if ($class === Tr_Class_Entry::ENTRY_ROOT) $res = false;
        else {
            if (is_string($class) && !class_exists($class)) $res = false;
            else {
                $res = get_parent_class($class);
                if ($res === false) $res = Tr_Class_Entry::ENTRY_ROOT;
            }
        }
        return $res;
    }
    
    
    
}