<?php

interface Ae_I_Tree_Mapper_NestedSets extends Ae_I_Tree_Mapper {
    
    /**
     * @return Ae_Sql_Nested_Sets
     */
    function getNestedSets();  
    
    function getRootNodeId();
    
}

?>