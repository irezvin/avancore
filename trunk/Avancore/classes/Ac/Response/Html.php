<?php

class Ac_Response_Html extends Ac_Response_Http {

    function __construct() {
        parent::__construct();
        $this->mergeRegistry(
            array(
                'headContent' => Ac_Registry(),
                'metaKeywords' => Ac_Registry(),
                'metaDescription' => Ac_Registry(),
                'assetLibs' => Ac_Registry_Consolidated(array('unique' => true)),
                'headScripts' => Ac_Registry(),
                'initScripts' => Ac_Registry(),
            )
        );
    }
    
    function setHeaderContent($headerContent, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('headerContent'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getHeaderContent($headerContent, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('headerContent'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }
    
    function setMetaKeywords($metaKeywords, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('metaKeywords'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getMetaKeywords($metaKeywords, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('metaKeywords'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }
    
    function setMetaDescription($metaDescription, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('metaDescription'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getMetaDescription($metaDescription, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('metaDescription'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }
    
    function setAssetLibs($assetLibs, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('assetLibs'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getAssetLibs($assetLibs, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('assetLibs'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }
    
    function setInitScripts($initScripts, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('initScripts'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getInitScripts($initScripts, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('initScripts'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }
    
    function setHeadScripts($headScripts, $index = null) {
        $args = func_get_args();
        array_splice($args, 1, 0, array('headScripts'));
        return call_user_func(array($this, 'setRegistry'), $args);
    }

    function getHeadScripts($headScripts, $index = null) {
        $args = func_get_args();
        array_splice($args, 0, 0, array('headScripts'));
        return call_user_func(array($this, 'getRegistry'), $args);
    }
    
}