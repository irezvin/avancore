<?php

class Tr_Class_Entry extends Ac_Prototyped {

    const ENTRY_PARENT = '__parent__';
    const ENTRY_ROOT = '__root__';
    
    /**
     * @var string
     */
    protected $key = false;

    /**
     * @var Tr_Class_Table
     */
    protected $table = false;

    /**
     * @var Tr_Class_Entry
     */
    protected $parent = false;

    /**
     * @var array
     */
    protected $objectProbePrototype = array();

    /**
     * @var array
     */
    protected $resultProviderPrototype = array();

    /**
     * @var array
     */
    protected $resultProbePrototype = array();

    /**
     * @var array
     */
    protected $extendObjectProbe = array(Tr_Class_Entry::ENTRY_PARENT);

    /**
     * @var array
     */
    protected $extendResultProbe = array(Tr_Class_Entry::ENTRY_PARENT);

    /**
     * @var array
     */
    protected $extendResultProvider = array(Tr_Class_Entry::ENTRY_PARENT);
    
    /**
     * @param string $key
     */
    function setKey($key) {
        if ($key === self::ENTRY_PARENT) {
            throw new Ac_E_InvalidCall("'{$key}' is not allowed to use as a \$key of a ".__CLASS__);
        }
        if ($this->key !== $key && $this->table)
            throw  new Ac_E_InvalidCall("Cannot change \$key of an Entry that already has \$table");
        $this->key = $key;
    }

    /**
     * @return string
     */
    function getKey() {
        return $this->key;
    }

    function setTable(Tr_Class_Table $table = null) {
        $this->table = $table;
    }

    /**
     * @return Tr_Class_Table
     */
    function getTable() {
        return $this->table;
    }

    /**
     * @return Tr_Class_Entry
     */
    function getParent() {
        if ($this->parent === false && $this->table) {
            $parent = Tr_Class_Table::getParentClassEntryKey($this->key);
            if (strlen($parent)) $this->parent = $this->table->findEntry($parent);
        }
        return $this->parent;
    }

    function setObjectProbePrototype(array $objectProbePrototype) {
        $this->objectProbePrototype = $objectProbePrototype;
    }

    /**
     * @return array
     */
    function getObjectProbePrototype() {
        return $this->objectProbePrototype;
    }

    function setResultProviderPrototype(array $resultProviderPrototype) {
        $this->resultProviderPrototype = $resultProviderPrototype;
    }

    /**
     * @return array
     */
    function getResultProviderPrototype() {
        return $this->resultProviderPrototype;
    }

    function setResultProbePrototype(array $resultProbePrototype) {
        $this->resultProbePrototype = $resultProbePrototype;
    }

    /**
     * @return array
     */
    function getResultProbePrototype() {
        return $this->resultProbePrototype;
    }

    function setExtendObjectProbe(array $extendObjectProbe) {
        $this->extendObjectProbe = $extendObjectProbe;
    }

    /**
     * @return array
     */
    function getExtendObjectProbe() {
        return $this->extendObjectProbe;
    }

    function setExtendResultProbe(array $extendResultProbe) {
        $this->extendResultProbe = $extendResultProbe;
    }

    /**
     * @return array
     */
    function getExtendResultProbe() {
        return $this->extendResultProbe;
    }

    function setExtendResultProvider(array $extendResultProvider) {
        $this->extendResultProvider = $extendResultProvider;
    }

    /**
     * @return array
     */
    function getExtendResultProvider() {
        return $this->extendResultProvider;
    }
    
    /**
     * @return Tr_Node
     */
    function getNode() {
        $res = null;
        if ($this->table && ($p = $this->table->getPlan())) {
            $res = $p->getCurrentNode();
        }
        return $res;
    }    
    
    function getCalculatedObjectProbePrototype() {
        return $this->calculatePrototype($this->extendObjectProbe, 'objectProbePrototype');
    }
    
    /**
     * @return Tr_Probe_List
     */
    function createObjectProbe(array $overrides = array()) {
        $prototype = $this->getCalculatedObjectProbePrototype();
        Ac_Util::ms($prototype, $overrides);
        $res = Ac_Prototyped::factory($prototype, 'Tr_Probe_List', array('sourceProperty' => 'object'));
        return $res;
    }
    
    function getCalculatedResultProbePrototype() {
        return $this->calculatePrototype($this->extendResultProbe, 'resultProbePrototype');
    }
    
    /**
     * @return Tr_Probe_List
     */
    function createResultProbe(array $overrides = array()) {
        $prototype = $this->getCalculatedResultProbePrototype();
        Ac_Util::ms($prototype, $overrides);
        $res = Ac_Prototyped::factory($prototype, 'Tr_Probe_List', array('sourceProperty' => 'result'));
        return $res;
    }
    
    function getCalculatedResultProviderPrototype() {
        return $this->calculatePrototype($this->extendResultProvider, 'resultProviderPrototype');
    }
    
    /**
     * @return Tr_I_ResultProvider
     */
    function createResultProvider(array $overrides = array()) {
        $prototype = $this->getCalculatedResultProviderPrototype();
        Ac_Util::ms($prototype, $overrides);
        $res = Ac_Prototyped::factory($prototype, 'Tr_I_ResultProvider');
        return $res;
    }
    
    protected function calculatePrototype(array $list, $propName) {
        $myFn = 'get'.ucfirst($propName);
        $calFn = 'getCalculated'.ucfirst($propName);
        $res = array();
        if ($this->table) {
            foreach ($list as $object) {
                if ($object === self::ENTRY_PARENT) {
                    $ancestor = $this->getParent();
                } else {
                    $ancestor = $this->table->getEntry($object);
                }
                if ($ancestor) {
                    Ac_Util::ms($res, $ancestor->$calFn());
                }
            }
        }
        Ac_Util::ms($res, $this->$myFn());
        return $res;
    }
        
}