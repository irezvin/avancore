<?php

interface Ac_I_Search_Provider_WithCriteria extends Ac_I_SearchProvider {
    
    /**
     * @return array
     */
    function listOwnCriteria();
    
    /**
     * @return Ac_I_Search_Criterion
     */
    function getOwnCriterion($name);

    /**
     * @return bool
     */
    function hasCriterion($name);
    
    /**
     * @return Ac_I_Search_Criterion
     */
    function getCriterion($name);
    
}