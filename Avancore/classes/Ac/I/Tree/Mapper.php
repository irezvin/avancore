<?php

interface Ac_I_Tree_Mapper {
	
    function listTopNodes();
	            
    function getNodeClass();
    
    function loadNodes(array $ids);

    /**
     * @return Ac_I_Tree_Provider
     */
    function getDefaultTreeProvider();

    /**
     * @return Ac_I_Tree_Provider
     */
    function createTreeProvider();

    function loadNodeChildrenCounts(array $nodes);
    
    function loadNodeAllChildrenCounts(array $nodes);
    
    function loadNodeChildIds(array $nodes);
    
    function loadNodeContainers(array $nodes);
	
    /** 
     * @return array 
     */
    function getOrderingValues(Ac_Model_Object $modelObject);
    
    /**
     * @return Ac_I_Tree_Impl
     */
    function createTreeImpl(Ac_Model_Object $modelObject);
    
}