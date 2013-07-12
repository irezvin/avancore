<?php

/**
 * Is created by Output to merge everything in the response tree before composing result HTML 
 * or providing data to external application
 */
class Ac_Response_Consolidated extends Ac_Response {
    
    protected $titleImplodeChar = ' - ';
    
    protected $titleReverse = true;
    
    protected $defaultDocType = 'html';
    
    protected $xmlns = false;

    function setXmlns($xmlns) {
        $this->xmlns = $xmlns;
    }

    function getXmlns() {
        return $this->xmlns;
    }
    
    function setTitleImplodeChar($titleImplodeChar) {
        $this->titleImplodeChar = $titleImplodeChar;
    }

    function getTitleImplodeChar() {
        return $this->titleImplodeChar;
    }

    function setTitleReverse($titleReverse) {
        $this->titleReverse = (bool) $titleReverse;
    }

    function getTitleReverse() {
        return $this->titleReverse;
    }    

    function setDefaultDocType($defaultDocType) {
        $this->defaultDocType = $defaultDocType;
    }

    function getDefaultDocType() {
        return $this->defaultDocType;
    }    
    
    function __construct(array $options = array()) {
        parent::__construct();
        $rta = new Ac_Registry_Consolidated;
        if (strlen($this->xmlns)) $rta->setRegistry($this->xmlns, 'xmlns');
        $this->setRegistry(array(
            'headers' => new Ac_Registry_Consolidated(array(
                'flatten' => Ac_Registry_Consolidated::flattenSecondLevel,
                'default' => array(),
            )),
            'metaKeywords' => new Ac_Registry_Consolidated(array(
                'flatten' => true, 
                'implode' => ', ',
                'toArray' => false,
            )),
            'metaDescription' => new Ac_Registry_Consolidated(array(
                'flatten' => true, 
                'implode' => '; ',
                'toArray' => false,
            )),
            'title' => new Ac_Registry_Consolidated(array(
                'implode' => $this->titleImplodeChar, 
                'reverse' => $this->titleReverse,
                'toArray' => false,
            )),
            'docType' => new Ac_Registry_Consolidated(array(
                'default' => $this->defaultDocType, 
                'singleValue' => Ac_Registry_Consolidated::svLast, 
                'toArray' => false
            )),
            'noHtml' => new Ac_Registry_Consolidated(array(
                'default' => false, 
                'singleValue' => Ac_Registry_Consolidated::svLast, 
                'toArray' => false
            )),
            'rootTagAttribs' => $rta,
            'bodyTagAttribs' => new Ac_Registry_Consolidated(),
            'headContent' => new Ac_Registry_Consolidated(),
            'assetLibs' => new Ac_Registry_Consolidated(array('flatten' => true, 'unique' => true)),
            'headScripts' => new Ac_Registry_Consolidated(),
            'initScripts' => new Ac_Registry_Consolidated(),
            'debug' => new Ac_Registry_Consolidated(),
            'content' => new Ac_Registry_Consolidated(array(
                'registry' => '',
                //'toArray' => false,
            )),
             
        ));
        $this->registry['headers']->debug = true;
    }
    
    function hasPublicVars() {
        return false;
    }
    
}