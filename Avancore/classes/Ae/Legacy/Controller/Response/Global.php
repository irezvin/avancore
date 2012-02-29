<?php

class Ae_Legacy_Controller_Response_Global {

    protected $response = null;
    
    private static $instance = null;    
    
    /**
     * @return Ae_Legacy_Controller_Response_Global
     */
    static function getInstance() {
        if (!Ae_Legacy_Controller_Response_Global::$instance) {
            Ae_Legacy_Controller_Response_Global::$instance = new Ae_Legacy_Controller_Response_Global();
        }
        return Ae_Legacy_Controller_Response_Global::$instance;
    }
    
    /**
     * @return Ae_Legacy_Controller_Response_Global
     */
    static function i() {
        return Ae_Legacy_Controller_Response_Global::getInstance();
    }
    
    /**
     * @return Ae_Legacy_Controller_Response_Html
     */
    static function r() {
        return Ae_Legacy_Controller_Response_Global::getInstance()->getResponse();
    }
    
    /**
     * @return Ae_Legacy_Controller_Response_Html
     */
    function getResponse() {
        if (!$this->response) {
            $this->response = new Ae_Legacy_Controller_Response_Html();
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
    
    function pourToResponse(Ae_Legacy_Controller_Response $response) {
        if ($this->response) {
            $response->mergeWithResponse($this->response);
            $this->response = false;
        }
    }
    
}