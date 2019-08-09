<?php

class Ac_Cg_Model_Relation extends Ac_Cg_Base implements Ac_I_Prototyped {
    
    var $relationName = false;
    var $hasModel = false;
    var $isIncoming = false;
    var $otherRelationName = false;
    var $isOtherRelationIncoming = false;
    
    var $createRelationObject = true;
    var $createAssociationObject = true;
 
    function __construct(array $prototype = array()) {
        Ac_Util::simpleBindAll($prototype, $this);
    }
    
    function hasPublicVars() {
        return true;
    }
    
}
