<?php

class Tr_Scanner {
    
    protected $impl = false;
    
    protected $position = false;
    
    protected $stack = array();
    
    function __construct(Tr_I_ScannerImpl $impl) {
        $this->impl = $impl;
    }
    
    /**
     * @return Tr_I_Scanner_Impl
     */
    function getImpl() {
        return $this->impl;
    }
    
    /**
     * @return Tr_Node
     */
    function scan($rootObject) {
        $rootNode = $this->impl->createRootNode($rootObject);
        if ($this->impl->scanNode($rootNode) !== false) $this->scanNode($rootNode);
        return $rootNode;
    }    
    
    protected function scanNode(Tr_Node $node) {
        foreach ($node->listNodes() as $i) {
            $child = $node->getChild($i);
            if ($this->impl->scanNode($child) !== false) $this->scanNode($child);
        }
    }
    
}