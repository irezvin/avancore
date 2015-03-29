<?php

class Tr_Node_Iterator implements RecursiveIterator {
    
    protected $node = false;
    
    protected $pos = 0;
    
    function __construct(Tr_Node $node) {
        $this->node = $node;
    }
   
    public function current() {
        if ($this->pos !== null && $this->pos < count($this->node->getNodes())) return $this->getChild($this->key());
        else return false;
    }
    
    public function next() {
        if ($this->pos < count($this->node->getNodes())) {
            $this->pos++;
        }
    }
    
    public function rewind() {
        $this->pos = 0;
    }
    
    public function key() {
        if ($this->pos < count($this->node->getNodes())) {
            $tmp = array_keys($this->node->getNodes());
            $res = $tmp[$this->pos];
        } else {
            $res = NULL;
        }
        return $res;
    }
    
    public function valid() {
        return $this->pos < count($this->node->getNodes());
    }
    
    public function hasChildren() {
        return count($this->node->getNodes()) > 0;
    }
    
    public function getChildren() {
        static $i = 0;
        if ($i++ > 10) die();
        var_dump($this->dump());
        return $this;
    }
     
}