<?php
/**
 * 
 */

/**
 * 1. Content is created based on provided content or content prototype
 * 2.   
 *      A   If content prototype isn't provided by controller, Ac_Cr_Result_Content tries to figure out anything by method return value
 *          -   arrays and objects with toJs() method are converted to Js()
 *          -   streamable objects are streamed
 *          -   content objects are used
 *          -   URLs are used to redirect
 *      B   If both content prototype and and return value are provided, $returnTarget is used
 * 3.   
 *      If any output is captured, it is merged using $outputTarget
 */
class Ac_Cr_Result_Content extends Ac_Cr_Result {

    protected $outputTarget = 'Ac_Cr_Result_Content_Output';

    protected $returnTarget = 'Ac_Cr_Result_Content_Intelligent';

    function setOutputTarget($outputTarget) {
        $this->outputTarget = $outputTarget;
    }

    function getOutputTarget() {
        return $this->outputTarget;
    }

    function setReturnTarget($returnTarget) {
        $this->returnTarget = $returnTarget;
    }

    function getReturnTarget() {
        return $this->returnTarget;
    }
    
}