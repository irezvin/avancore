<?php

class Tr_Probe_List implements ArrayAccess, Iterator {
    
    protected $probes = array();
    
    protected $source = null;
    
    protected $pos = 0;
    
    protected $probeBaseClass = 'Tr_Probe';
    
    protected $acceptedSourceClasses = array();
    
    protected $sourceProperty = false;
    
    /**
     * @var Tr_Node
     */
    protected $node = null;
    
    function __construct($probeBaseClass = 'Tr_Probe', $acceptedSourceClasses = array(), 
        $source = null, array $probes = array()) {
        
        $this->probeBaseClass = $probeBaseClass;
        $this->acceptedSourceClasses = $acceptedSourceClasses;
        if ($source !== null) $this->source = $source;
        if ($probes) $this->setProbes($probes);
    }
    
    function setNode(Tr_Node $node = null) {
        if ($node !== ($oldNode = $this->node)) {
            $this->node = $node;
            if ($this->sourceProperty) $this->source = null;
        }
    }
    
    /**
     * @return Tr_Node
     */
    function getNode() {
        return $this->node;
    }

    function setSourceProperty($sourceProperty) {
        $this->sourceProperty = $sourceProperty;
    }

    function getSourceProperty() {
        return $this->sourceProperty;
    }    
    
    function canAcceptSource($source) {
        $res = true;
        if ($this->acceptedSourceClasses) {
            $res = false;
            if (is_object($source)) {
                foreach ($this->acceptedSourceClasses as $class) {
                    if ($source instanceof $class) {
                        $res = true;
                        break;
                    }
                }
            }
        }
        return $res;
    }
    
    function setSource($source) {
        if (!$this->canAcceptSource($source)) 
            throw new Ac_E_InvalidCall("Cannot setSource(".Ac_Util::typeClass($item)."); "
                . "check with canAcceptSource() first");
        $this->source = $source;
    }
    
    
    
    function getSource() {
        if ($this->source === null && $this->sourceProperty && $this->node) {
            $this->setSource(Ac_Accessor::getObjectProperty($this->node, $this->sourceProperty));
        }
        return $this->source;
    }
    
    function listProbes() {
        return array_keys($this->probes);
    }
    
    /**
     * @param string $key
     * @return Tr_Probe
     * @throws Ac_E_InvalidCall
     */
    function getProbe($key) {
        if (isset($this->probes[$key])) $res = $this->probes[$key];
        else throw Ac_E_InvalidCall::noSuchItem("probe", $key);
        return $res;
    }
    
    function getProbes() {
        return $this->probes;
    }
    
    function setProbes(array $probes, $add = false) {
        if (!$add) $this->probes = array();
        $def = array('probeList' => $this);
        $probes = Ac_Prototyped::factoryCollection($probes, $this->probeBaseClass, $def, 'key', true, true);
        foreach ($probes as $key => $probe)
            $this->probes[$key] = $probe;
    }
    
    /**
     * @return array ($key => $result)
     */
    function getValues() {
        $res = array();
        foreach ($this->listProbes() as $key) {
            $res[$key] = $this->getProbe($key)->getResult();
        }
        return $res;
    }
     
    // --- ArrayAccess ----
    
    function offsetExists($offset) {
        return isset($this->probes[$offset]);
    }
    
    function offsetGet($offset) {
        return $this->getChild($offset);
    }
    
    function offsetSet($offset, $value) {
        throw new Exception("Not supported");
    }
    
    function offsetUnset($offset) {
        throw new Exception("Not supported");
    }
    
    function __toString() {
        return $this->dump();
    }
    
    // --- Iterator ---
    
    public function current() {
        if ($this->pos !== null && $this->pos < count($this->probes)) return $this->getChild($this->key());
        else return false;
    }
    
    public function next() {
        if ($this->pos < count($this->probes)) {
            $this->pos++;
        }
    }
    
    public function rewind() {
        $this->pos = 0;
    }
    
    public function key() {
        if ($this->pos < count($this->probes)) {
            $tmp = array_keys($this->probes);
            $res = $tmp[$this->pos];
        } else {
            $res = NULL;
        }
        return $res;
    }
    
    public function valid() {
        return $this->pos < count($this->probes);
    }
    
}