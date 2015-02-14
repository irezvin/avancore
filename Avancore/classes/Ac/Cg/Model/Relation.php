<?php

class Ac_Cg_Model_Relation extends Ac_Prototyped {
    
    var $relationName = false; // 0
    var $hasModel = false; // 1
    var $isIncoming = false; // 2
    var $otherRelationName = false; // 3
    var $isOtherRelationIncoming = false; // 4
    
    var $createRelationObject = true;
    var $createAssociationObject = true;
    
    function hasPublicVars() {
        return true;
    }
    
}