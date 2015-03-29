<?php

interface Ac_I_Tree_Node {

    function setTreeProvider(Ac_I_Tree_Provider $provider = null);
    
    function getTreeProvider();
    
    function getNodeId();
    
    function getParentNodeId();
    
    /**
     * @return array (immediateParentId, nextParentId, ..., topParentId)
     */
    function getAllParentNodeIds();
    
    /**
     * @return Ac_I_Tree_Node
     */
    function getParentNode();

    function setParentNode(Ac_I_Tree_Node $parentNode = null);
    
    function setParentNodeId($parentId);
    
    function getAllParentNodes();
    
    function getChildNodesCount();
    
    function getAllChildNodesCount();
    
    function listChildNodes();
    
    /**
     * @return Ac_I_Tree_Node
     */
    function getChildNode($id);
    
    function getTitle();

    function reloadLists();

    function refreshFromNode(Ac_I_Tree_Node $node);

    
    function hasChildNodesCount();
    
    function hasAllChildNodesCount();
    
    function setChildNodesCount($count);

    function setAllChildNodesCount($count);
    
    function hasNodeData();
    
    function setNodeData($nodeData);
    
    function hasChildNodeIds();
    
    function setChildNodeIds($childNodeIds);

    function destroy();
    
    function hasParentChanged();
    
    function getOrdering();
    
}

