<?php

interface Tr_I_ScannerImpl {
    
    /**
     * @return Tr_Node
     */
    function createRootNode($object);
    
    function scanNode(Tr_Node $node);
    
}