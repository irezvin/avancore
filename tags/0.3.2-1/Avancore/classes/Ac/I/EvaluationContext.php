<?php

interface Ac_I_EvaluationContext {
    
    /**
     * Returns all EvaluatedObject instances in this Context
     * @return array of Ac_I_EvaluatedObject instances
     */
    function getEvaluatedObjects();
    
}