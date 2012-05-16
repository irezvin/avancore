<?php

interface Ac_I_Tree_Mapper_NestedSets extends Ac_I_Tree_Mapper {
    
    /**
     * @return Ac_Sql_Nested_Sets
     */
    function getNestedSets();  
    
    function getRootNodeId();
    
}

?>