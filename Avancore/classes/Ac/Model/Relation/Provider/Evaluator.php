<?php

class Ac_Model_Relation_Provider_Evaluator {
    
    function evaluateProvider(array $relationProps) {
        // todo: replace with something better when decision 
        // about provider class is made
        $res = Ac_Model_Relation_Provider_Sql_Omni::evaluatePrototype($relationProps);
        return $res;
    }
    
}