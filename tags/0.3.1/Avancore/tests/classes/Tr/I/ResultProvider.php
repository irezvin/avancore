<?php

interface Tr_I_ResultProvider {
    
    function setNode(Tr_Node $node);

    function createResult();
    
}