<?php

class Ac_Response_HtmlPage extends Ac_Response_Html {
    
    function __construct() {
        parent::__construct();
        $this->mergeRegistry(
            array(
                'title' => new Ac_Registry(),
                'docType' => new Ac_Registry_Consolidated(array('singleValue' => Ac_Registry_Consolidated::svLast)),
                'rootTagAttribs' => new Ac_Registry(),
                'bodyTagAttribs' => new Ac_Registry(),
            )
        );
    }
    
    function setTitle($title, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('title'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getTitle($title, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('title'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }
    
    function setDocType($docType, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('docType'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getDocType($docType, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('docType'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }
    
    function setRootTagAttribs($rootTagAttribs, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('rootTagAttribs'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getRootTagAttribs($rootTagAttribs, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('rootTagAttribs'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }
    
    function setBodyTagAttribs($bodyTagAttribs, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('bodyTagAttribs'));
        return call_user_func_array(array($this, 'setRegistry'), $args);
    }

    function getBodyTagAttribs($bodyTagAttribs, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('bodyTagAttribs'));
        return call_user_func_array(array($this, 'getRegistry'), $args);
    }
    
}