<?php

class Ac_Controller_Response_Global {

    protected $response = null;
    
    private static $instance = null;
    
    /**
     * @return Ac_Controller_Response_Global
     */
    static function getInstance() {
        if (!Ac_Controller_Response_Global::$instance) {
            Ac_Controller_Response_Global::$instance = new Ac_Controller_Response_Global();
        }
        return Ac_Controller_Response_Global::$instance;
    }
    
    /**
     * @return Ac_Controller_Response_Global
     */
    static function i() {
        return Ac_Controller_Response_Global::getInstance();
    }
    
    /**
     * @return Ac_Controller_Response_Html
     */
    static function r($dontCreate = false) {
        $i = Ac_Controller_Response_Global::getInstance();
        if (!$dontCreate || $i->hasResponse()) $res = $i->getResponse();
            else $res = null;
        return $res;
    }
    
    /**
     * @return Ac_Controller_Response_Html
     */
    function getResponse() {
        if (!$this->response) {
            $this->response = new Ac_Controller_Response_Html();
        }
        $res = $this->response;
        return $res;
    }
    
    function setResponse(Ac_Controller_Response_Html $response) {
        $this->response = $response;
    }
    
    function hasResponse() {
        $res = (bool) $this->response;
        return $res;
    }
    
    function __call($name, $args = array()) {
        return call_user_func_array(array($this->getResponse(), $name), $args); 
    }
    
    function pourToResponse(Ac_Controller_Response $response) {
        if ($this->response) {
            $response->mergeWithResponse($this->response);
            $this->response = false;
        }
    }
    
    function clearResponse() {
        $this->response = false;
    }
    
}