<?php

class Ac_Legacy_Controller_Response_Global {

    protected $response = null;
    
    private static $instance = null;    
    
    /**
     * @return Ac_Legacy_Controller_Response_Global
     */
    static function getInstance() {
        if (!Ac_Legacy_Controller_Response_Global::$instance) {
            Ac_Legacy_Controller_Response_Global::$instance = new Ac_Legacy_Controller_Response_Global();
        }
        return Ac_Legacy_Controller_Response_Global::$instance;
    }
    
    /**
     * @return Ac_Legacy_Controller_Response_Global
     */
    static function i() {
        return Ac_Legacy_Controller_Response_Global::getInstance();
    }
    
    /**
     * @return Ac_Legacy_Controller_Response_Html
     */
    static function r() {
        return Ac_Legacy_Controller_Response_Global::getInstance()->getResponse();
    }
    
    /**
     * @return Ac_Legacy_Controller_Response_Html
     */
    function getResponse() {
        if (!$this->response) {
            $this->response = new Ac_Legacy_Controller_Response_Html();
        }
        $res = $this->response;
        return $res;
    }
    
    function hasResponse() {
        $res = (bool) $this->response;
        return $res;
    }
    
    function __call($name, $args = array()) {
        return call_user_func_array(array($this->getResponse(), $name), $args); 
    }
    
    function pourToResponse(Ac_Legacy_Controller_Response $response) {
        if ($this->response) {
            $response->mergeWithResponse($this->response);
            $this->response = false;
        }
    }
    
}