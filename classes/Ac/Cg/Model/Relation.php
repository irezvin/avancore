<?php

class Ac_Cg_Model_Relation extends Ac_Cg_Base {
    
    var $relationName = false;
    var $hasModel = false;
    var $isIncoming = false;
    var $otherRelationName = false;
    var $isOtherRelationIncoming = false;
    
    var $createRelationObject = true;
    var $createAssociationObject = true;
 
}
