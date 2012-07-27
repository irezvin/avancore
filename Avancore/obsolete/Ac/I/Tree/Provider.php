<?php

interface Ac_I_Tree_Provider {
    
    function listTopNodes();

    /**
     * @return Ac_I_Tree_Node
     */
    function getTopNode($id);
    
    function listLoadedNodes();
    
    function loadNodes($ids);
    
    /**
     * @return Ac_I_Tree_Node
     */
    function getNode($id, $loadIfNeeded = true);
    
    function reloadLists($forAllNodes = false);
    
    function loadContainers($ids = false);
    
    function loadChildNodeCounts($ids = false);
    
    function loadAllChildNodeCounts($ids = false);
    
    function loadChildNodeIds($ids = false);
    
    function registerNodes($nodes);
    
    function unregisterNodes($nodes);

    function destroyAllNodes();
    
    function registerNodeStubs($ids);
    
}

?>