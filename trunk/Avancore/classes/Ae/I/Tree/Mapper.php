<?php

interface Ae_I_Tree_Mapper {
	
    function listTopNodes();
	            
    function getNodeClass();
    
    function loadNodes(array $ids);

    /**
     * @return Ae_I_Tree_Provider
     */
    function getDefaultTreeProvider();

    /**
     * @return Ae_I_Tree_Provider
     */
    function createTreeProvider();

    function loadNodeChildrenCounts(array $nodes);
    
    function loadNodeAllChildrenCounts(array $nodes);
    
    function loadNodeChildIds(array $nodes);
    
    function loadNodeContainers(array $nodes);
	
    /** 
     * @return array 
     */
    function getOrderingValues(Ae_Model_Object $modelObject);
    
    /**
     * @return Ae_I_Tree_Impl
     */
    function createTreeImpl(Ae_Model_Object $modelObject);
    
}