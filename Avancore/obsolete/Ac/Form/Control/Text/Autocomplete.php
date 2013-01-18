<?php

class Ac_Form_Control_Text_Autocomplete extends Ac_Form_Control_Text {

    var $loadScripts = false;
    
    var $templateClass = 'Ac_Form_Control_Template_Autocomplete';
    
    var $templatePart = 'textInput';
    
    var $listElementTagName = 'div';
    
    var $listElementAttribs = array(
        'style' => 'display: none',
        'class' => 'autocompleteList',
    );
    
    var $paramName = false;
    
    var $fullSearch = true;
    
    var $partialSearch = true;
    
    var $autocompleteAttribs = array(
    );
        
    var $valueList = false;
    
    var $_valuesProvider = false;
    
    var $_valuesProviderPrototype = false;
    
    /**
     * If url is empty, local autocompleter will be created
     * @var string
     */
    var $url = false;
    
    function doInitProperties($options = array()) {
        parent::doInitProperties($options);
        if (isset($options['valuesProvider'])) $this->setValuesProvider($options['valuesProvider']);
        elseif (isset($options['valuesProviderPrototype'])) $this->setValuesProviderPrototype($options['valuesProviderPrototype']);
    }
    
    function setValuesProviderPrototype($prototype = array()) {
        $this->_valuesProvider = false;
        $this->_valuesProviderPrototype = false;
        if ($prototype) $this->_valuesProviderPrototype = $prototype;
    }
    
    /**
     * @param Ac_Model_Values $provider
     */
    function setValuesProvider(& $provider) {
        if ($provider && !is_a($provider, 'Ac_Model_Values')) trigger_error ("\$provider should be instance of Ac_Model_Values", E_USER_ERROR);
        $this->_valuesProviderPrototype = false;
        $this->_valuesProvider = false;
        if ($provider) {
            $this->_valuesProvider = $provider;
            $this->valueList = false;
        }
    }
    
    /**
     * @return Ac_Model_Values
     */
    function & _getValuesProvider() {
        if ($this->_valuesProvider === false) {
            if ($this->_valuesProviderPrototype) {
                $this->_valuesProvider = Ac_Model_Values::factoryIndependent($this->_valuesProviderPrototype);
            }
            elseif ($p = $this->getModelProperty()) {
                if (isset($p->values) && $p->values) {
                    $this->_valuesProvider = Ac_Model_Values::factoryWithProperty($p);
                }
            }
            else $this->_valuesProvider = null;
        }
        return $this->_valuesProvider;
    }
    
    function getValueList() {
         if (!is_array($this->valueList)) {
             $res = array();
             if ($vp = $this->_getValuesProvider()) {
                 $res = $vp->getValueList();
             } elseif ($p = $this->getModelProperty()) {
                 if (isset($p->valueList) && is_array($p->valueList))
                    $res = $p->valueList;
             }
         } else {
             $res = $this->valueList;
         }
         return $res;
    }
    
    function getAutocompleteListId() {
        return $this->_context->mapIdentifier('_acList');
    }
    
    function getInputId() {
        return $this->_context->mapIdentifier('_value');
    }
    
    function getAutocompleteListJson() {
        $res = $this->autocompleteAttribs;
        $res['fullSearch'] = $this->fullSearch;
        $res['partialSearch'] = $this->partialSearch;
        if (strlen($sep = $this->getListSeparator())) {
            if ($sep{0} != ' ') $sep = trim($sep, ' ');
            $res['tokens'] = array($sep);
        }
        if (strlen($this->paramName)) $res['paramName'] = $this->paramName;
        return $res;
    }
    
    function getUrl() {
        return $this->url;
    }
    
}

?>