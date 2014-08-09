<?php

abstract class Ac_Model_Tree_AbstractImpl extends Ac_Prototyped implements Ac_I_Tree_Node {

    const STATE_BEFORE_DELETE = 'beforeDelete';
    const STATE_AFTER_DELETE = 'afterDelete';
    const STATE_BEFORE_SAVE = 'beforeSave';
    const STATE_AFTER_SAVE = 'afterSave';
    const STATE_SAVE_FAILED = 'saveFailed';
    
    const ORDER_FIRST = 0;
    const ORDER_LAST = -1;
    
    /**
     * @var Ac_Model_Object
     */
    protected $container = false;
	
    /**
     * During do(Before|After)Container(Save|Delete) specifies corresponding container state. 
     * One of self::STATE* constants.
     * 
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
            if ($provider && $this->getNodeId()) 
                $this->treeProvider->registerNodes(array($this));
        }
    }
    
    /**
     * @return Ac_Model_Tree_Provider
     */
    function getTreeProvider() {
        return $this->treeProvider;
    }
    
    function setTreeNodeTitleGetter($treeNodeTitleGetter) {
        $this->treeNodeTitleGetter = $treeNodeTitleGetter;
    }
    
    /**
     * Returns ID of container object that contained in the Impl instance
     * Is called only in case when Impl is detached from container
     */
    abstract protected function doGetInternalNodeId();
    
    /**
     * Returns ID of container object
     * If container is loaded and referenced, returns container' PK
     * If container is not loaded, takes in-memory value from Impl ($this->doGetInternalNodeId())
     * @return mixed
     */
    function getNodeId() {
        if ($this->container) 
            $res = $this->container->{$this->modelIdField};
        else $res = $this->doGetInternalNodeId();
        return $res;
    }
    
    /**
     * Returns ID of parent node that is contained in the Impl
     */
    abstract protected function doGetInternalParentId();
    
    /**
     * Returns ID of parent node
     * In-memory value from related parent' Impl object has preference over internal value
     * @return int|null
     */
    function getParentNodeId() {
        if ($this->parentId === false) {
            if ($this->tmpParent) {
                $res = $this->tmpParent->getNodeId();
            }
            else {
            	$res = $this->doGetInternalParentId();
            }
        } else {
            $res = $this->parentId; 
        }
        return $res;
    }
    
    /**
     * Sets ID of parent node.
     * 
     * If there is in-memory association with the "parent" Impl AND new ID is different 
     * from ID of currently referenced parent' Impl object, current 'node' 
     * is de-associated from old parent Impl.
     * 
     * @param mixed $parentId
     */
    function setParentNodeId($parentId) {
        $this->parentId = $parentId;
        if ($this->tmpParent && $this->tmpParent->getNodeId() !== $parentId) {
            $this->tmpParent->notifyChildNodeRemoved($this);
            $this->tmpParent = false;
        }
    }
    
    /**
     * Sets instance of parent node.
     * When persistent Impl (with assigned ID) is provided, DOES NOT create in-memory association
     * and remembers it's ID instead
     * 
     * @param Ac_I_Tree_Node|null $parentNode
     */
    function setParentNode(Ac_I_Tree_Node $parentNode = null) {
    	if (is_null($parentNode)) $this->setParentNodeId(null);
        elseif (strlen($id = $parentNode->getNodeId())) $this->setParentNodeId($id);
        else {
            if ($this->tmpParent && ($this->tmpParent !== $parentNode)) 
                $this->tmpParent->notifyChildNodeRemoved($this);
            $this->tmpParent = $parentNode;
            $this->tmpParent->notifyChildNodeAdded($this);
        }
    }
    
    /**
     * @return Ac_Model_Tree_NestedSetsImpl
     */
    function createChildNode(Ac_Model_Object $container = null) {
        $prototype = array(
            'mapper' => $this->mapper,
            'treeProvider' => $this->treeProvider,
            'treeNodeTitleGetter' => $this->treeNodeTitleGetter,
            'containerImplSetter' => $this->containerImplSetter,
            'modelIdField' => $this->modelIdField,
        );
        
        if ($container) $prototype['container'] = $container;
        $this->doOnCreateChildNodePrototype($prototype, $container);
        $c = get_class($this);
        $res = new $c($prototype);
        $res->setParentNode($this);
        return $res;
    }
    
    protected function doOnCreateChildNodePrototype(array $prototype, $container) {
    }
    
    function notifyMapperUpdated() {
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
            if (!$hasNode) {
                $this->tmpChildren['_tmp_'.($this->tmpc++)] = $childNode;
            }
        } else {
        }
    }
    
    function notifyChildNodeRemoved(Ac_Model_Tree_AbstractImpl $childNode) {
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
    
    protected function getParentIdIfChanged(& $currParentId = false) {
        $currParentId = false;
        $res = false;
        $log = array();
        
        // 1. Determine current parent id ($currParentId)
        
        // we have $this->parentId? Use it
        if ($this->parentId !== false) {
            $log['this->parentId'] = $this->parentId; 
            $currParentId = $this->parentId;
        }
        
        // in other case let's try to obtain our associated $tmpParent's nodeId()
        if (!strlen($currParentId) 
            && !is_null($currParentId) 
            && $this->tmpParent 
            && (strlen($nsId = $this->tmpParent->getNodeId()))
        ) {
            $currParentId = $nsId;
            $log['nsId'] = $nsId;
        }
        
        // still no trace of the parent? get value from the container
        if (!strlen($currParentId) && !is_null($currParentId)) {
            $log['doGetInternalParentId()'] = $currParentId = $this->doGetInternalParentId();
        }
        
        if (!strlen($currParentId) && !is_null($currParentId)) {
            $log['getParentIdFromDb()'] = $currParentId = $this->getParentIdFromDb();
        }
        
        $log["currParentId"] = $currParentId;
        
        $pidFromDb = $this->getParentIdFromDb();
        if (($pidFromDb !== false) && ((string) $currParentId !== (string) $pidFromDb)) {
            $log['Way 1']['currParentId'] = $currParentId;
            $log['Way 1']['getParentIdFromDb()'] = $this->getParentIdFromDb();
            $res = $currParentId;
        } elseif (!$this->isPersistent()) {
            // we are not persistent - return $currParentId
            $log['Way 2'] = true;
            $res = $currParentId;
            
            // default to top-level
            if ($res === false) $res = null; 
        } else {
            $res = false;
        }

        $log["internal parent id"] = $this->doGetInternalParentId();
        $log["my parent id"] = $this->parentId;
        $log["res"] = $res;
        
//        Ac_Debug_FirePHP::getInstance()->log($log, 'getParentIdIfChanged()');
        
        return $res;
    }
    
    protected function getParentIdFromDb() {
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
        $this->containerState = self::STATE_BEFORE_SAVE;
        $this->doBeforeContainerSave();
        $this->containerState = false;
    }
    
    final function afterContainerSave() {
        $this->containerState = self::STATE_AFTER_SAVE;
        $this->doAfterContainerSave();
        $this->containerState = false;
    }
    
    final function beforeContainerDelete() {
        $this->containerState = self::STATE_BEFORE_DELETE;
        $this->doBeforeContainerDelete();
        $this->containerState = false;
    }
    
    final function afterContainerDelete() {
        $this->containerState = self::STATE_AFTER_DELETE;
        $this->doAfterContainerDelete();
        $this->containerState = false;
    }
    
    function afterContainerLoad() {
    }
    
    abstract function isPersistent();
    
    function isRootObject() {
        return false;
    }
    
    final function onContainerSaveFailed() {
        $this->containerState = self::STATE_SAVE_FAILED;
        $this->doOnContainerSaveFailed();
        $this->containerState = false;
    }
    
    protected function doOnContainerSaveFailed() {
    }
    
}