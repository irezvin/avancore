<?php

interface Ac_I_EvaluationContext_WithCache extends Ac_I_EvaluationContext {
    
    /**
     * Should return array of params or the ready-made hash to calculate 
     * the groupId for given evaluator' setup
     * 
     * Should include $evaluator->getGroupData() to be accurate.
     * 
     * Special value 'null' means caching is disabled for given evaluator.
     */
    function getEvaluationGroupData(Ac_Evaluator $evaluator);

    /**
     * Should return cached data from group  
     * @param string $groupId
     * @param array|null $keys 
     *      NULL means return all data; 
     *      array means return data for given keys only
     */
    function getEvaluationResults($groupId, $keys = null);
    
    /**
     * Should return cached results for the group $groupId
     * @param string $groupId
     * @param array $data
     * @param bool $replace Whether to replace whole group data with $data
     *      (and don't merge it)
     */
    function setEvaluationResults($groupId, array $data, $replace = false);
    
}