<?php

class Ac_Cr_Result extends Ac_Prototyped {
    
    /**
     * @var Ac_Cr_Controller
     */
    protected $controller = false;
    
    protected $nextResult = false;
    
    protected $response = false;

    function setController(Ac_Cr_Controller $controller) {
        if ($this->controller !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->controller = $controller;
    }

    /**
     * @return Ac_Cr_Controller
     */
    function getController() {
        return $this->controller;
    }    
    
    /**
     * @return Ac_Cr_Result 
     */
    function getNextResult() {
        if ($this->nextResult === false) {
            if ($this->response !== false) {
                $this->nextResult = null;
            } else {
                $this->nextResult = $this->doGetNextResult();
            }
        }
        return $this->nextResult;
    }
    
    function setResponse(Ac_Response $response) {
        if ($this->nextResult !== false)
            throw new Ac_E_InvalidCall("Cannot setReponse() when \$nextResult is set");
        if ($this->response !== false) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->response = $response;
    }
    
    /**
     * @return Ac_Response 
     */
    function getResponse() {
        if ($this->response === false) {
            $nextResult = $this->getNextResult();
            if ($nextResult) $this->response = $nextResult->getResponse();
            else $this->response = $this->doGetResponse();
        }
        return $this->response;
    }
    
    protected function doGetNextResult() {
        return null;
    }
    
    protected function doGetResponse() {
        return null;
    }
    
}