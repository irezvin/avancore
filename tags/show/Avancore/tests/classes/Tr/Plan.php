<?php

class Tr_Plan {
    
    /**
     * @var Tr_Class_Table
     */
    protected $classTable = false;
    
    /**
     * @var Tr_Node
     */
    protected $rootNode = null;

    /**
     * @var Tr_Node
     */
    protected $currentNode = null;
    
    protected $currentStage = false;

    function __construct(Tr_Node $root, Tr_Class_Table $table) {
        $this->rootNode = $root;
        $this->classTable = $table;
        $this->classTable->setPlan($this);
    }
    
    /**
     * @return Tr_Class_Table
     */
    function getClassTable() {
        return $this->classTable;
    }

    /**
     * @return Tr_Node
     */
    function getRootNode() {
        return $this->rootNode;
    }

    /**
     * @return Tr_Node
     */
    function getCurrentNode() {
        return $this->currentNode;
    }
    
    function configureNode(Tr_Node $node) {
        if ($node->getObjectProbeList()) return $this->reconfigureNode($node);
        else {
            $match = $this->classTable->findEntry($node->getObject());
            if ($match) {
                $node->setObjectProbeList($match->createObjectProbe());
            }
        }
    }
    
    function reconfigureNode(Tr_Node $node) {
    }
    
    function testNode(Tr_Node $node) {
        $match = $this->classTable->findEntry($node->getObject());
        if ($match) {
            if (!$node->getResultProbeList()) {
                $node->setResultProbeList($match->createResultProbe());
            }
            if (!$node->getResultProvider()) {
                $node->setResultProvider($match->createResultProvider());
            }
        }
        $node->test();
    }
    
    function listStages() {
        return array(0);
    }
    
    function beginStage($id) {
        $this->currentStage = $id;
        $this->traverseNodes('configureNode');
    }
    
    function runStage() {
        $this->traverseNodes('testNode');
    }
    
    function endStage() {
    }
    
    function execute() {
        foreach ($this->listStages() as $id) {
            $this->beginStage($id);
            $this->runStage();
            $this->endStage();
        }
    }
    
    protected function traverseNodes($method, Tr_Node $node = null, array $extraArgs = array()) {
        if (!$node) $node = $this->rootNode;
        $iter = new RecursiveIteratorIterator($node->createSuperNode(), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iter as $node) {
            $this->currentNode = $node;
            $args = array_merge(array($node), $extraArgs);
            if (!is_array($method)) $method = array($this, $method);
            call_user_func_array($method, $args);
        }
    }
    
}