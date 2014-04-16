<?php

class Tr_Node implements ArrayAccess, RecursiveIterator {
    
    protected $object = false;

    protected $extra = false;

    protected $index = false;
    
    protected $pos = 0;
    
    /**
     * @var Tr_I_Dumper
     */
    protected $dumper = null;

    /**
     * @var Tr_Node
     */
    protected $parent = null;
    
    protected $children = array();
    
    /**
     * @var Tr_Probe_List
     */
    protected $objectProbeList = false;

    /**
     * @var Tr_Probe_List
     */
    protected $resultProbeList = false;
    
    /**
     * @var Tr_I_ResultProvider
     */
    protected $resultProvider = false;
    
    protected $result = false;
    
    protected $childClass = 'Tr_Node';
    
    function __construct($object, $extra = false, $index = false, Tr_Node $parent = null) {
        foreach (array('object', 'extra', 'index', 'parent') as $v) $this->$v = $$v;
        if ($index !== false && !$parent) throw new Exception("\$parent must be provided if index is non-false");
        if ($parent) $parent->addChild($this);
    }
    
    function getObject() {
        return $this->object;
    }

    function getExtra() {
        return $this->extra;
    }

    function getIndex() {
        return $this->index;
    }

    /**
     * @return Tr_Node
     */
    function getParent() {
        return $this->parent;
    }    
    
    function getNodes() {
        return $this->children;
    }
    
    function listNodes() {
        return array_keys($this->children);
    }
    
    /**
     * @return Tr_Node
     */
    function createNode($object, $extra = false, $index = false) {
        if ($index === false) $index = count($this->children);
        $res = new $this->childClass ($object, $extra, $index, $this);
        return $res;
    }
    
    /**
     * @param int $index
     * @return Tr_Node
     */
    function getChild($index) {
        if (isset($this->children[$index])) $res = $this->children[$index];
        else throw new Exception("No such child: \$index");
        return $res;
    }
    
    protected function addChild(Tr_Node $child) {
        if (isset($this->children[$idx = $child->getIndex()]) && $this->children[$idx] !== $child) {
            throw new Exception("\$child with index '$index' is already registered");
        }
        $this->children[$idx = $child->getIndex()] = $child;
    }
    
    protected function getAggregate($name) {
        if (isset($this->$name) && is_object($this->$name)) return $this->$name;
        elseif ($this->parent) return $this->parent->getAggregate($name);
        else return null;
    }

    function setDumper(Tr_I_Dumper $dumper = null, $recursive = false) {
        $this->dumper = $dumper;
        if ($recursive) foreach ($this->children as $child) $child->setDumper($dumper, true);
    }

    /**
     * @return Tr_I_Dumper
     */
    function getDumper() {
        return $this->getAggregate('dumper');
    }    
    
    function defaultDump() {
        if (is_object($this->object)) $res = get_class($this->object).'#'.spl_object_hash ($this->object);
        elseif (is_resource($this->object)) $res = get_resource_type ($this->object);
        else $res = gettype($this->object);
        return $res;
    }
    
    function dump() {
        $res = false;
        if ($d = $this->getDumper()) {
            $res = $d->dump($this);
        }
        if ($res === false) {
            $res = $this->defaultDump();
        }
        return $res;
    }
    
    /**
     * @return Tr_Node
     */
    function getRoot() {
        $res = $this;
        while ($p = $res->getParent()) $res = $p;
        return $res;
    }
    
    function isRoot() {
        return !$this->parent;
    }
    
    /**
     * Creates a node that has only one children - current node - solely for Iterator purposes
     * @return Tr_Node
     */
    public function createSuperNode() {
        $res = new Tr_Node(null);
        $res->children = array($this);
        return $res;
    }
    
    function setObjectProbeList(Tr_Probe_List $probeList) {
        $this->objectProbeList = $probeList;
        $this->objectProbeList->setNode($this);
    }
    
    /**
     * @return Tr_Probe_List
     */
    function getObjectProbeList() {
        return $this->objectProbeList;
    }
    
    function setResultProbeList(Tr_Probe_List $probeList) {
        $this->resultProbeList = $probeList;
        $this->resultProbeList->setNode($this);
    }

    /**
     * @return Tr_Probe_List
     */
    function getResultProbeList() {
        return $this->resultProbeList;
    }
    
    function setResultProvider(Tr_I_ResultProvider $resultProvider) {
        $this->resultProvider = $resultProvider;
        $this->resultProvider->setNode($this);
    }

    /**
     * @return Tr_I_ResultProvider
     */
    function getResultProvider() {
        return $this->resultProvider;
    }    

    function setResult($result) {
        $this->result = $result;
    }

    function getResult() {
        if ($this->result === false) {
            if ($rp = $this->getResultProvider()) {
                $this->result = $rp->createResult();
            }
        }
        return $this->result;
    }    

    function test() {
        if (!$this->result) {
            if (!$this->getResultProvider() && !$this->getResult()) {
                trigger_error("No resultProvider at node {$this} - probably node wasn\'t configured properly", E_USER_WARNING);
            }
        }
        $this->getResultProbeList()->setNode($this);
        $this->getObjectProbeList()->setNode($this);
        var_dump($this->getResult()->getDomNode());
        //var_dump(''.$this, $this->getObjectProbeList()->getValues(), $this->getResultProbeList()->getValues());
    }
    
    // --- ArrayAccess ----
    
    function offsetExists($offset) {
        return isset($this->children[$offset]);
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
    
    /**
     * @return Tr_Node
     */
    function findNodeByObject($object) {
        $res = null;
        if ($this->object === $object) $res = $this;
        else {
            foreach ($this->children as $node) {
                if (($res = $node->findByObject($object))) break;
            }
        }
        return $res;
    }
    
    /**
     * @param array $pattern
     * @param bool $strict
     * @param string $className
     * @return array
     */
    function findNodesByObjectProps(array $pattern, $strict = false, $className = false) {
        $res = array();
        if (Ac_Accessor::itemMatchesPattern($this->object, $pattern, $strict, $className)) 
            $res[] = $this;
        foreach ($this->children as $node) {
            if ($sub = ($node->findNodesByObjectProps($pattern, $strict, $className))) {
                $res = array_merge($res, $sub);
            }
        }
        return $res;
    }
    
    // --- Iterator ---
    
    public function current() {
        if ($this->pos !== null && $this->pos < count($this->children)) return $this->getChild($this->key());
        else return false;
    }
    
    public function next() {
        if ($this->pos < count($this->children)) {
            $this->pos++;
        }
    }
    
    public function rewind() {
        $this->pos = 0;
    }
    
    public function key() {
        if ($this->pos < count($this->children)) {
            $tmp = array_keys($this->children);
            $res = $tmp[$this->pos];
        } else {
            $res = NULL;
        }
        return $res;
    }
    
    public function valid() {
        return $this->pos < count($this->children);
    }
    
    public function hasChildren() {
        if ($this->valid()) $res = count($this->current()->getNodes()) > 0;
            else $res = false;
        return $res;
    }
    
    public function getChildren() {
        if ($this->valid()) {
            $res = $this->current();
        } else {
            $res = null;
        }
        return $res;
    }
    
}