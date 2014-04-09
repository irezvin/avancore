<?php

class Ac_Model_Tree_Object extends Ac_Mixable {
    
    /**
     * @var Ac_Model_Object
     */
    protected $mixin = false;
    
    protected $mixableId = 'treeObject';
    
    protected $mixinClass = 'Ac_Model_Object';
    
    protected $treeImpl = false;
    
    protected $ensureOwnClass = true;
    
    function listChildItems() {
        return $this->getTreeImpl()->listChildNodes();
    }

    /**
     * @return Ac_Model_Mapper
     */
    protected function getMapper() {
        return $this->mixin->getMapper();
    }
    
    /**
     * @return Ac_Model_Object
     */
    function getChildItem($index) {
        if (in_array($index, $this->listChildItems())) $res = $this->getTreeImpl()->getChildNode($index)->getContainer();
            else trigger_error("No such child item: '{$index}'", E_USER_ERROR);
        return $res;
    }
    
    /**
     * @return Ac_Model_Object
     */
    function createChildItem(array $values = array()) {
        $child = $this->getMapper()->createRecord();
        if ($values) $child->bind($values);
        $this->getTreeImpl()->createChildNode($child);
        return $child;
    }
    
    /**
     * @return Ac_Model_Object
     */
    function addChildItem(Ac_Model_Object $childItem, $replace = false) {
        if ($this->ensureOwnClass && !is_a($childItem, get_class($this->mixin))) 
            trigger_error("\$childItem must be an instance of ".get_class($this->mixin), E_USER_ERROR);
        $childItem->getTreeImpl()->setParentNode($this->getTreeImpl());
        return $childItem;
    }
    
    /**
     * @return Ac_Model_Object 
     * Returns $parentItem that was passed as the parameter.
     */
    function setParentItem(Ac_Model_Object $parentItem = null) {
        if ($parentItem !== null && $this->ensureOwnClass && !is_a($parentItem, get_class($this->mixin)))
            trigger_error("\$parentItem must be an instance of ".get_class($this->mixin), E_USER_ERROR);
        $this->getTreeImpl()->setParentNode($parentItem? $parentItem->getTreeImpl() : null);
        return $parentItem;
    }
    
    function setParentItemId($parentItemId) {
        $old = $this->getParentItemId();
        $this->getTreeImpl()->setParentNodeId($parentItemId);
        if ($parentItemId !== $old) {
            $ord = $this->getOrderingValues();
            $k = array_reverse(array_keys($ord));
            $this->setOrdering(trim($k[0]));            
        }
    }
    
    /**
     * @return Ac_Model_Object
     */
    function getParentItem() {
        $parentNode = $this->getTreeImpl()->getParentNode();
        if ($parentNode) $res = $parentNode->getContainer();
            else $res = null;
        return $res;
    }
    
    function getParentItemId() {
        $res = $this->getTreeImpl()->getParentNodeId();
        return $res;
    }
    
    function areChildrenLoaded() {
        $this->getTreeImpl()->areAllChildrenLoaded();
    } 
    
    function getDirectChildrenCount($recalcFromDb = false) {
        $impl = $this->getTreeImpl();
        if ($recalcFromDb) $impl->setChildNodesCount(false);
        $res = $impl->getChildNodesCount();
        return $res;
    }
    
    function getAllChildrenCount($recalcFromDb = false) {
        $impl = $this->getTreeImpl();
        if ($recalcFromDb) $impl->setAllChildNodesCount(false);
        $res = $impl->getAllChildNodesCount();
        return $res;
    }
    
    function setTreeImpl (Ac_I_Tree_Node $impl = null) {
        if ($this->treeImpl !== $impl) {
            $this->treeImpl = $impl;
            if ($this->treeImpl) $this->treeImpl->setContainer($this->mixin);
        }
    }
    
    /**
     * @return Pmt_I_Tree_Node
     */
    function getTreeImpl() {
        if ($this->treeImpl === false) {
            $this->treeImpl = $this->getMapper()->createTreeImpl($this->mixin); 
        }
        return $this->treeImpl;
    }
    
    function getTreeNodeTitle() {
        $tf = $this->getMapper()->getTitleFieldName();
        if (strlen($tf)) $res = $this->mixin->$tf;
            else $res = $this->mixin->getPrimaryKey();
        return $res;
    }
    
    function getOrdering() {
        return $this->getTreeImpl()->getOrdering();
    }
    
    function setOrdering($value) {
        return $this->getTreeImpl()->setOrdering($value);
    }    
    
    function getOrderingValues() {
        return $this->getMapper()->getOrderingValues($this->mixin);
    }
    
    function canOrderUp() {
        return $this->getOrdering() > 1;
    }
    
    function canOrderDown() {
        if ($p = $this->getParentItem()) {
            $lst = $p->listChildItems();
        } else {
            $m = $this->getMapper();
            $lst = $m->listTopNodes();
        }
        $ord = 1;
        foreach ($lst as $i) {
            if ($p) $ci = $p->getChildItem($i);
            else $ci = $m->loadRecord($i);
            if ($ci) $ord = max($ord, $ci->getOrdering()); 
        }
        $res = $this->getOrdering() < $ord;
        return $res;
    }
    
    function onListOwnProperties(array & $properties) {
        $properties = array_unique(array_merge($properties, array(
            'parentItemId', 'ordering',
        )));
    }
    
    function onGetPropertiesInfo(& $propertiesInfo) {
        Ac_Util::ms($propertiesInfo, array(
            'parentItemId' => array(
                'caption' => new Ac_Lang_String('model_tree_parent', array('ucfirst' => true)),
            ),
            'ordering' => array(
                'caption' => new Ac_Lang_String('model_tree_ordering', array('ucfirst' => true)),
                'valuesGetter' => 'getOrderingValues',
            ),
        ));
    }
    
    function onCheck(& $errors) {
        Ac_Util::ms($errors, $this->getTreeImpl()->getErrors(true, false, true, 'childItems'));
    }
    
    function onBeforeDelete(& $res) {
        if ($res !== false) {
            $res = $this->getTreeImpl()->beforeContainerDelete() !== false;
        }
        return $res;
    }
    
    function onAfterDelete() {
        if ($this->getTreeImpl()) {
            $res = $this->getTreeImpl()->afterContainerDelete() !== false;
        }
        return $res;
    }
    
    function onBeforeSave(& $res) {
        if ($res !== false) {
            $res = $this->getTreeImpl()->beforeContainerSave() !== false;
        }
    }
    
    function onAfterSave(& $res) {
        if ($res !== false) {
            if ($this->mixin->isPersistent()) {
                $res = $this->getTreeImpl()->afterContainerSave() !== false;
            }
        }
    }
    
} 