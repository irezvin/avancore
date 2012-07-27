<?php

class Ac_Cr_Result {

    protected $methodReturnValue = false;

    protected $methodOutput = false;
    
    function setMethodReturnValue($methodReturnValue) {
        $this->methodReturnValue = $methodReturnValue;
    }

    function getMethodReturnValue() {
        return $this->methodReturnValue;
    }

    function setMethodOutput($methodOutput) {
        $this->methodOutput = $methodOutput;
    }

    function getMethodOutput() {
        return $this->methodOutput;
    }    
    
    /**
     * Pushes data contained in the result into the response.
     * Creates new empty response if no $response is provided.
     * 
     * Returns either provided $response or newly created one.
     * 
     * @return Ac_Response 
     */
    function populateResponse(Ac_Response $response = null) {
       if (is_null($response)) $response = new Ac_Response; // TODO: use Factory?
       // ...
       return $response;
    }
    
    /**
     * Creates empty response and populates it
     * @return Ac_Response 
     */
    function getResponse() {
        return $this->populateResponse(null);
    }
    
}