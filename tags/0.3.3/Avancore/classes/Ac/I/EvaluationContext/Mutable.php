<?php

interface Ac_I_EvaluationContext_Mutable extends Ac_I_EvaluationContext {
    
    function notifyEvaluationBegin(Ac_Evaluator $evaluator);
    
    /**
     * @param array $newObjects Objects that were added since notifyEvaluationBegin ($evaluator)
     * $newObjects should be returned with the matching keys as in getEvaluatedObjects()
     * 
     * Reference implementation can be seen in Ac_Test_Evaluated, class ExampleContextMutable
     */
    function notifyEvaluationEnd(Ac_Evaluator $evaluator, & $newObjects);
    
}