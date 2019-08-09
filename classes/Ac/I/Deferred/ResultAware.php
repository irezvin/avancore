<?php

interface Ac_I_Deferred_ResultAware extends Ac_I_Deferred {
    
    /**
     * @return bool
     */
    function shouldEvaluate(Ac_Result_Stage_Morph $stage);
    
}