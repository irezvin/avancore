<?php

class Ae_Tree_Provider implements Ae_I_Tree_Provider {
	
    /**
     * @var Ae_I_Tree_Mapper
     */
    protected $mapper = false;
    
    protected $nodeClass = false;
    
    protected $topNodeIds = false;
    
    protected $allNodes = array();
    
    protected $childNodeIdsCache = array();
    
    protected $mapperClass = false;

    function __construct(Ae_I_Tree_Mapper $mapper) {
        $this->mapper = $mapper;
        $this->nodeClass = $mapper->getNodeClass();
    }
    
    function listTopNodes() {
        if ($this->topNodeIds === false) {
            $this->topNodeIds = $this->mapper->listTopNodes();
            $this->registerNodeStubs($this->topNodeIds);
        }
        return $this->topNodeIds;
    }
    
    function nodeExists($id) {
        if (isset($this->allNodes[$id])) {
            $res = true;
        } else {
            $res = (bool) $this->loadNodes(array($id));
        }
        return $res;
    }

    /**
     * @return Ae_I_Tree_Node
     */
    function getTopNode($id) {
        if (in_array($id, $this->listTopNodes())) {
            
        } else throw new Exception("There is no top node with id '{$id}'");
    }
    
    function listLoadedNodes() {
        $res = array();
        foreach (array_keys($this->allNodes) as $k)
            if ($this->allNodes[$k]) $res[] = $k;
        return $res;
    }
    
    function loadNodes($ids) {
        if ($ids === false) $ids = array_keys($this->allNodes);
        elseif (!is_array($ids)) $ids = array($ids);
        $ids = array_diff($ids, $this->listLoadedNodes());
        if ($ids) $this->registerNodes($nodes = $this->mapper->loadNodes($ids));
            else $nodes = array();
        return count($nodes);
    }
    
    /**
     * @return Ae_I_Tree_Node
     */
    function getNode($id, $loadIfNeeded = true) {
        if ((!isset($this->allNodes[$id]) || !($this->allNodes[$id])) && $loadIfNeeded) $this->loadNodes(array($id));
        if (isset($this->allNodes[$id]) && $this->allNodes[$id]) $res = $this->allNodes[$id];
            else $res = null;
        return $res;
    }
    
    function reloadLists($forAllNodes = false) {
        $this->topNodeIds = false;
        if ($forAllNodes) {
            foreach ($this->allNodes as $id => $node) {
                if ($node instanceof Ae_I_Tree_Node) {
                    $node->reloadLists();
                }
            }
        }
    }

    function registerNodeStubs($ids) {
        if (!is_array($ids)) $ids = array($ids);
        foreach ($ids as $id)
            if (!isset($this->allNodes[$id]))
                $this->allNodes[$id] = false;
    }
    
    function registerNodes($nodes) {
        if (!is_array($nodes)) $nodes = array($nodes);
        foreach ($nodes as $node) {
            if (!$node instanceof $this->nodeClass) 
                throw new Exception("Wrong node type/class: ".gettype($node)."/".get_class($node)."; only objects of class '{$this->nodeClass}' are allowed'");
                
            if (!strlen($id = $node->getNodeId())) 
                throw new Exception("Cannot register node without id");
                
            if (isset($this->allNodes[$id])) {
                if (is_object($this->allNodes[$id])) $this->allNodes[$id]->refreshFromNode($node);
                else {
                    $this->allNodes[$id] = $node;
                    $this->allNodes[$id]->setTreeProvider($this);
                }
            } else {
                $this->allNodes[$id] = $node;
                $this->allNodes[$id]->setTreeProvider($this);
            }
        }
    }
    
    function unregisterNodes($nodes) {
        if (!is_array($nodes)) $nodes = array($nodes);
        foreach (array_keys($nodes) as $k) {
            if ($nodes[$k]) {
                $nodeId = $nodes[$k]->getNodeId();
                if (strlen($nodeId) && isset($this->allNodes[$nodeId])) {
                    $tmp = & $this->allNodes[$nodeId];
                    unset($this->allNodes[$nodeId]);
                    $tmp->setTreeProvider(null);
                }
            }
        }
    }
    
    protected function collectNodesToLoad($ids, $checkMethod) {
        if ($ids === false) {
            $ids = array();
            foreach ($this->allNodes as $id => $node) {
                if ($node instanceof Ae_I_Tree_Node && !$node->$checkMethod()) {
                    $ids[] = $id;
                }
            }
        }
        $nodes = array();
        foreach ($ids as $id) if (isset($this->allNodes[$id]) && $this->allNodes[$id]) $nodes[] = & $this->allNodes[$id];
        return $nodes;
    }
    
    function loadChildNodeCounts($ids = false) {
        $nodes = $this->collectNodesToLoad($ids, 'hasChildNodesCount');
        $this->mapper->loadNodeChildrenCounts($nodes);
    }
    
    function loadAllChildNodeCounts($ids = false) {
        $nodes = $this->collectNodesToLoad($ids, 'hasAllChildNodesCount');
        $this->mapper->loadNodeAllChildrenCounts($nodes);
        $rel = $this->mapper->getAllChildrenCountRelation();
        $rel->loadDest($nodes, false, true);        
    }
    
    function loadChildNodeIds($ids = false) {
        $nodes = $this->collectNodesToLoad($ids, 'hasChildNodeIds');
        $ids = $this->mapper->loadNodeChildIds($nodes);
    	$this->registerNodeStubs($ids);
    }
    
    function loadContainers($ids = false) {
        $nodes = $this->collectNodesToLoad($ids, 'hasContainer');
        $this->mapper->loadNodeContainers($nodes);
    }
    
    function destroyAllNodes() {
        foreach (array_keys($this->allNodes) as $k) {
            $tmp = & $this->allNodes[$k];
            unset($this->allNodes[$k]);
            $tmp->setTreeProvider(null);
            $tmp->destroy();
        }
        $this->allNodes = array();
    }
 	
	
}