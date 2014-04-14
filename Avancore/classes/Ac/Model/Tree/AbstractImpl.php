<?php

abstract class Ac_Model_Tree_AbstractImpl extends Ac_Prototyped implements Ac_I_Tree_Node {

    const csBeforeDelete = 'beforeDelete';
    const csAfterDelete = 'afterDelete';
    const csBeforeSave = 'beforeSave';
    const csAfterSave = 'afterSave';
    
    /**
     * @var Ac_Model_Object
     */
    protected $container = false;
	
    /**
     * During do(Before|After)Container(Save|Delete) specifies corresponding container state. One of self::cs* constants.
     * @var string
     */
    protected $containerState = false;
    
    protected $modelIdField = false;
    
    /**
     * @var Ac_I_Tree_Provider
     */
    protected $treeProvider = false;
    
    /**
     * @var Ac_I_Tree_Mapper
     */
    protected $mapper = false;
    
    protected $mapperClass = false;
    
    protected $childNodeIds = false; 
    
    protected $childNodesCount = false;
    
    protected $allChildNodesCount = false;
    
    protected $treeNodeTitleGetter = 'getTreeNodeTitle';
    
    protected $containerImplSetter = 'setTreeImpl';
    
    protected $tmpChildren = array();
    
    protected $tmpc = 0;
    
    protected $lockErrors = 0;
    
    protected $lockStore = 0;
    
    protected $lockDelete = 0;
    
    protected $lockDestroy = 0;
    
    protected $ordering = null;
    
    /**
     * @var Ac_I_Tree_Node
     */
    protected $tmpParent = false;
    
    protected $parentId = false;
    
    function setContainer(Ac_Model_Object $container = null) {
        if ($this->container !== $container) {
            
            if ($this->container) {
                return;
                //throw new Exception("Can set container only once");
            }
            $this->container = $container;
            if ($container) {
                if (!$this->mapper) $this->setMapper($container->getMapper());
                $s = $this->containerImplSetter;
                $this->container->$s ($this);
            }
        }
    }
    
    function setMapper(Ac_I_Tree_Mapper $mapper) {
        $this->mapper = $mapper;
        if ($this->treeProvider === false) $this->setTreeProvider($this->mapper->getDefaultTreeProvider());
    }
    
    /**
     * @return Ac_Model_Object
     */
    function getContainer() {
        if (!$this->container && $this->mapper) {
            if (strlen($id = $this->getNodeId())) { 
                $this->container = $this->mapper->loadRecord($id);
                if (!$this->container) trigger_error("Can't load container with id '$id'", E_USER_NOTICE);
            } else {
                $this->container = $this->mapper->createRecord();
            }
            if ($this->container) {
                $containerImplSetter = $this->containerImplSetter;
                $this->container->$containerImplSetter($this);
            }
        }
        return $this->container;
    }
    
    function hasContainer() {
        return (bool) $this->container;
    }
    
    protected function setContainerImplSetter($containerImplSetter) {
        $this->containerImplSetter = $containerImplSetter;
    }
    
    protected function setModelIdField($id) {
    	$this->modelIdField = $id; 
    }
    
    function getModelIdField() {
    	return $this->modelIdField; 
    }
    
    function setTreeProvider(Ac_I_Tree_Provider $provider = null) {
        if ($this->treeProvider && ($this->treeProvider !== $provider))
            $this->treeProvider->unregisterNodes(array($this));
            
        if ($this->treeProvider !== $provider) {
            $this->treeProvider = $provider;
            if ($provider && ($nsId = $this->getNodeId())) $this->treeProvider->registerNodes(array($this));
        }
    }
    
    /**
     * @return Ac_Model_Tree_Provider
     */
    function getTreeProvider() {
        return $this->treeProvider;
    }
    
    abstract protected function doGetInternalNodeId();
    
    function getNodeId() {
        if ($this->container) 
            $res = $this->container->{$this->modelIdField};
        else $res = $this->doGetInternalNodeId();
        return $res;
    }
    
    abstract protected function doGetInternalParentId();
    
    function getParentNodeId() {
        if ($this->parentId === false) {
            if ($this->tmpParent) $res = $this->tmpParent->getNodeId();
            else {
            	$res = $this->doGetInternalParentId();
            }
        } else {
            $res = $this->parentId; 
        }
        return $res;
    }
    
    function setParentNodeId($parentId) {
        //Pm_Conversation::log($this->getTitle()." - set \$parentId to ", $parentId, " old value was ", $this->getParentNodeId());
        $this->parentId = $parentId;
        if ($this->tmpParent && $this->tmpParent->getNodeId() !== $parentId) {
            $this->tmpParent->notifyChildNodeRemoved($this);
            $this->tmpParent = false;
        }
    }
    
    function setParentNode(Ac_I_Tree_Node $parentNode = null) {
    	if (is_null($parentNode)) $this->setParentNodeId(null);
        elseif (strlen($id = $parentNode->getNodeId())) $this->setParentNodeId($id);
        else {
            $this->tmpParent = $parentNode;
            if (($this->tmpParent !== $parentNode)) $this->tmpParent->notifyChildNodeRemoved($this);
            $this->tmpParent->notifyChildNodeAdded($this);
        }
    }
    

    protected function doOnCreateChildNodePrototype(array $prototype, $container) {
    }
    
    function notifyChildNodeAdded(Ac_I_Tree_Node $childNode) {
        if (!strlen($childNode->getNodeId())) {
            $hasNode = false;
            foreach (array_keys($this->tmpChildren) as $k) {
                if ($this->tmpChildren[$k] === $childNode) {
                    $hasNode = true;
                    break;
                }
            }
            if (!$hasNode) $this->tmpChildren['_tmp_'.($this->tmpc++)] = $childNode;
        }
    }
    
    function notifyChildNodeRemoved(Ac_Model_Tree_NestedSetsImpl $childNode) {
        if ($id = $childNode->getNodeId()) {
            if ($this->childNodeIds !== false) $this->childNodeIds = array_diff($this->childNodeIds, array($id));
        }
        foreach (array_keys($this->tmpChildren) as $k)
            if ($this->tmpChildren[$k] === $childNode)
                unset($this->tmpChildren[$k]);
    }
    
    function notifyChildNodeSaved(Ac_Model_Tree_NestedSetsImpl $childNode) {
        if (strlen($nsId = $childNode->getNodeId())) {
            $this->childNodeIds = false;
            foreach (array_keys($this->tmpChildren) as $k) 
                if ($this->tmpChildren[$k] === $childNode) 
                    unset($this->tmpChildren[$k]);
        }
    }
        
    abstract function notifyParentNodeSaved();
    
    protected function getParentIdIfChanged() {
        $currParentId = false;
        $res = false;
        if ($this->parentId !== false) {
            $log['this->parentId'] = $this->parentId; 
            $currParentId = $this->parentId;
        }
        if (!strlen($currParentId) 
            && !is_null($currParentId) 
            && $this->tmpParent 
            && (strlen($nsId = $this->tmpParent->getNodeId()))
        ) {
            $currParentId = $nsId;
            $log['nsId'] = $nsId;
        }
        if (!strlen($currParentId) && !is_null($currParentId)) {
            $log['doGetInternalParentId()'] = $this->doGetInternalParentId();
            $currParentId = $this->doGetInternalParentId();
        }
        if (!strlen($currParentId) && !is_null($currParentId)) {
            $log['getCurrentParentId()'] = $this->getCurrentParentId(); 
            $currParentId = $this->getCurrentParentId();
        }
        
        $log = array();
        $log["currParentId"] = array($currParentId, gettype($currParentId));
        
        if (($this->getCurrentParentId() !== false) && ((string) $currParentId !== (string) $this->getCurrentParentId())) {
             $log['Way 1']['currParentId'] = array($currParentId, gettype($currParentId));
             $log['Way 1']['getCurrentParentId()'] = array($this->getCurrentParentId(), gettype($this->getCurrentParentId()));
             $res = $currParentId;
        } elseif (!$this->hasOriginalData()) {
            $log['Way 2'] = true;
            $res = $currParentId;
        } else {
            $res = false;
        }
                

        $log["internal parent id"] = array($this->doGetInternalParentId(), gettype($this->doGetInternalParentId()));
        $log["my parent id"] = array($this->parentId, gettype($this->doGetInternalParentId()));
        $log["res"] = array($res, gettype($res));
        
//        Ac_Debug_FirePHP::getInstance()->log($log, 'getParentIdIfChanged()');
        
        return $res;
    }
    
    protected function getCurrentParentId() {
        return $this->doGetInternalParentId();
    }
    
    function hasParentChanged() {
        return ($this->getParentIdIfChanged() !== false);
    }    
    
    abstract protected function doGetInternalOrdering();
    
    function setOrdering($ordering) {
        $this->ordering = $ordering;
    }

    function getOrdering() {
        if (is_null($this->ordering)) {
            $res = $this->doGetInternalOrdering();
        } else $res = $this->ordering;
        return $res;
    }
    
    protected function getOrderingIfChanged($parentIsChanged = false) {
        $res = null;
        if (!is_null($this->ordering)) {
            if ($parentIsChanged) $res = $this->ordering;
            else {
                if ($this->doGetInternalOrdering() != $this->ordering) $res = $this->ordering;
            }
        }
        Ac_Debug_Log::l(array(
        	"Item #" => $this->getNodeId(),
        	"ordering" =>  $this->ordering, 
        	"doGetInternalOrdering()" => $this->doGetInternalOrdering(),
            "parentIsChanged" => $parentIsChanged,
            "res" => $res), "getOrderingIfChanged()");
        return $res;
    }
    
    function hasToStoreByParent() {
        $res = !$this->getNodeId() || $this->hasToStoreContainer() || $this->listChildrenToStore();
        return $res;
    }
    
    function hasToStoreContainer() {
        $res = ($this->containerState === false) && $this->container && !$this->container->_isBeingStored && !$this->container->isPersistent() || $this->container->getChanges();
        return $res;
    }
    
    function listChildrenToStore() {
        $res = array();
        if ($this->tmpChildren) $res = array_keys($this->tmpChildren);
        elseif ($this->childNodeIds !== false) {
            $loadedChildrenIds = array_intersect($this->childNodeIds, $this->treeProvider->listLoadedNodes());
            foreach ($loadedChildrenIds as $lcid) {
                $child = $this->treeProvider->getNode($lcid);
                if ($child->hasToStoreByParent()) {
                    $res[] = $lcid;
                }
            }
        }
        return $res;
    }

    abstract protected function isParentOf($nodeId, $possibleChildId);
    
    function hasToStoreParent() { 
        return false; 
    } 
    
    function getErrors($recursive = true, $withContainer = true, $withChildContainers = true, $childrenKey = false, $withParent = true) {
        $errors = array();
        if (!$this->lockErrors) { 
            $this->lockErrors++;
            if (($newId = $this->getParentIdIfChanged()) !== false) {
                $nsId = $this->getNodeId();
                if (strlen($nsId)) {
                    if ($this->isParentOf($nsId, $newId)) $errors['parentItemId'] = "Cannot move the node into it's own child";
                    elseif ($nsId == $newId) {
                        $errors['parentItemId'] = "Cannot move the node into itself";
                    }
                }
                if ($withContainer && $this->hasToStoreContainer()) {
                    Ac_Util::ms($errors, $this->container->getErrors());
                }
                
                if ($recursive) foreach ($this->listChildrenToStore() as $cId) {
                    if ($ce = $this->getChildNode($cId)->getErrors($recursive, $withContainer, $withChildContainers, $childrenKey)) {
                        if ($childrenKey !== false) $errors[$childrenKey][$cId] = $ce;
                            else $errors[$cId] = $ce;
                    }
                }
                
                if ($withParent && $this->hasToStoreParent()) {
                    $pe = $this->getParentNode()->getErrors($recursive, $withContainer, $withChildContainers);
                    if ($pe) $errors['parent'] = $pe;
                }
            }
            $this->lockErrors--;
        }
        return $errors;
    }
    
    abstract protected function doGetPathIds($nodeId);
    
    /**
     * @return array (immediateParentId, nextParentId, ..., topParentId)
     */
    function getAllParentNodeIds() {
        $res = array();
        $curr = $this;
        while ($curr && ($id = $curr->getParentNodeId())) {
            $res[] = $id;
            if ($id) $curr = $this->treeProvider->getNode($id);
        }
        if ($id) {
            $ids = $this->doGetPathIds($id);
            $this->treeProvider->registerNodeStubs(array_merge(array($id), $ids));
            $res = array_merge($res, array_reverse($ids));
        }
        return $res;
    }
    
    /**
     * @return Ac_I_Tree_Node
     */
    function getParentNode() {
        $res = null;
        if ($this->tmpParent) $res = $this->tmpParent;
        elseif ($id = $this->getParentNodeId()) {
            if (is_null($this->treeProvider)) $this->treeProvider = $this->mapper->getDefaultTreeProvider();
            $res = $this->treeProvider->getNode($id, true);
        }
        return $res;
    }
    
    function getAllParentNodes() {
        $parentIds = $this->getAllParentNodeIds();
        $this->treeProvider->loadNodes($parentIds);
        $res = array();
        foreach ($parentIds as $id) {
            $res[$id] = $this->treeProvider->getNode($id);
        }
        return $res;
    }
    
    function getChildNodesCount() {
        if ($this->childNodesCount === false)
            $this->treeProvider->loadChildNodeCounts(array($this->getNodeId()));
        return $this->childNodesCount;
    }
    
    function getAllChildNodesCount() {
        if ($this->allChildNodesCount === false)
            $this->treeProvider->loadAllChildNodeCounts(array($this->getNodeId()));
        return $this->allChildNodesCount;
    }
    
    function listChildNodes() {
        if ($this->childNodeIds === false) {
            $this->treeProvider->loadChildNodeIds(array($this->getNodeId()));
            if (!$this->childNodeIds) $this->childNodeIds = array();
        }
        $res = array_merge($this->childNodeIds, array_keys($this->tmpChildren));
        return $res;
    }
    
    /**
     * @return Ac_I_Tree_Node
     */
    function getChildNode($id) {
        if (isset($this->tmpChildren[$id])) $res = $this->tmpChildren[$id];
        else{
            if (!in_array($id, $this->listChildNodes())) $res = null;
            else $res = $this->treeProvider->getNode($id, true);
        }
        return $res;
    }
    
    function getDefaultTitle() {
        return false;
    }
    
    function getTitle() {
        if ($con = $this->getContainer()) {
            $c = $this->treeNodeTitleGetter;
            $res = $this->getContainer()->$c();
        } else {
            $res = $this->getDefaultTitle();
        }
        return $res;
    }

    function reloadLists() {
        $this->childNodesCount = false;
        $this->allChildNodesCount = false;
        $this->childNodeIds = false;
        $this->parentId = false;
    }

    function refreshFromNode(Ac_I_Tree_Node $node) {
    }
    
    function hasChildNodesCount() {
        return $this->childNodesCount !== false;
    }
    
    function hasAllChildNodesCount() {
        return $this->allChildNodesCount !== false;
    }
    
    function setChildNodesCount($count) {
        if (is_null($count)) $count = 0;
        if (is_array($count) && isset($count['count'])) $this->childNodesCount = $count['count'];
        elseif ($count === false || is_numeric($count)) $this->childNodesCount = $count;
        else throw new Exception("Invalid \$count type: ".gettype($count));
    }

    function setAllChildNodesCount($count) {
        if (is_null($count)) $count = 0;
        if (is_array($count) && isset($count['count'])) $this->allChildNodesCount = $count['count'];
        elseif ($count === false || is_numeric($count)) $this->allChildNodesCount = $count;
        else throw new Exception("Invalid \$count type: ",gettype($count));
    }
    
    protected function doOnDestroy() {
    }
    
    function destroy() {
        if ($this->lockDestroy > 0) return;
        $this->lockDestroy++;
        if ($this->container) {
            $f = $this->containerImplSetter;
            $this->container->$f(null);
            $this->container = null;
        }
        if ($this->treeProvider) {
            $this->treeProvider->unregisterNodes($this);
            $this->treeProvider = null;
        }
        $this->mapper = null;
        if ($this->tmpParent) {
            $this->tmpParent->destroy();
            $this->tmpParent = null;
        }
        foreach (array_keys($this->tmpChildren) as $k) $this->tmpChildren[$k]->destroy();
        $this->doOnDestroy();
        $this->lockDestroy--;
    }
    
    function hasNodeData() {
        return $this->treeNode !== false;
    }
    
    function setNodeData($nodeData) {
        if (!(is_array($nodeData) || $nodeData === false))
            throw new Exception("\$nodeData should be either array or FALSE");
        $this->treeNode = $nodeData;
    }
    
    function hasChildNodeIds() {
        return $this->childNodeIds !== false;
    }

    abstract protected function doExtractChildNodeId($idData);
    
    function setChildNodeIds($childNodeIds) {
        if ($childNodeIds === false) $this->setChildNodeIds($childNodeIds);
        elseif (is_array($childNodeIds)) {
            $myIds = array();
            $ids = array_values($childNodeIds);
            if (count($ids)) {
                foreach ($ids as $id)
                    if (!is_scalar($id)) { 
                        $myIds[] = $this->doExtractChildNodeId($id);
                    } elseif (strlen($id)) {
                        $myIds[] = $id;
                    }
            }
            $this->childNodeIds = $myIds;
        }
    }
    
    function areAllChildrenLoaded() {
        if (is_array($this->childNodeIds)) {
            $diff = array_diff($this->childNodeIds, $this->treeProvider->listLoadedNodes());
            $res = !count($diff);
        } else {
            $res = false;
        }
        return $res;
    }
    
    abstract function store();
    
    abstract function delete();
    
    abstract protected function doBeforeContainerSave();
    
    abstract protected function doAfterContainerSave();
    
    abstract protected function doBeforeContainerDelete();
    
    abstract protected function doAfterContainerDelete();
    
    final function beforeContainerSave() {
        $this->containerState = self::csBeforeSave;
        $this->doBeforeContainerSave();
        $this->containerState = false;
    }
    
    final function afterContainerSave() {
        $this->containerState = self::csAfterSave;
        $this->doAfterContainerSave();
        $this->containerState = false;
    }
    
    final function beforeContainerDelete() {
        $this->containerState = self::csBeforeDelete;
        $this->doBeforeContainerDelete();
        $this->containerState = false;
    }
    
    final function afterContainerDelete() {
        $this->containerState = self::csAfterDelete;
        $this->doAfterContainerDelete();
        $this->containerState = false;
    }
    
    abstract function hasOriginalData();
    
    abstract function isRootObject();
    
} 