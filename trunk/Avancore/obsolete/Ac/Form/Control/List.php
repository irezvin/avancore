<?php

/**
 * Single- and multiple- selection list, checkboxes list, radios list
 */
class Ac_Form_Control_List extends Ac_Form_Control_Listable {
    
    var $disableIfNoValues = false;
    
    var $valueList = false;
    
    var $dummyCaption = false;
    
    var $dummyValue = false;
    
    var $multiSelect = '?';
    
    /**
     * @var string selectList | buttonsOrChecks
     */
    var $type = 'selectList'; 
    
    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'selectList';
    
    var $_valuesProvider = false;
    
    var $_valuesProviderPrototype = false;
    
    var $valuesProviderOverride = array();
    
    var $_valuesGetter = false;
    
    var $singleArrayValueToScalarValue = true;

    function doInitProperties($options = array()) {
        parent::doInitProperties($options);
        if (isset($options['valuesProvider'])) $this->setValuesProvider($options['valuesProvider']);
        elseif (isset($options['valuesProviderPrototype'])) $this->setValuesProviderPrototype($options['valuesProviderPrototype']);
        elseif (isset($options['valuesGetter'])) $this->setValuesGetter($options['valuesGetter']);
    }
    
    function setValuesGetter($getter = false) {
        $this->_valuesProvider = false;
        $this->_valuesProviderPrototype = false;
        $this->_valuesGetter = false;
        if ($getter) $this->_valuesGetter = $getter;
    }
    
    function setValuesProviderPrototype($prototype = array()) {
        $this->_valuesProvider = false;
        $this->_valuesProviderPrototype = false;
        $this->_valuesGetter = false;
        if ($prototype) $this->_valuesProviderPrototype = $prototype;
    }
    
    /**
     * @param Ac_Model_Values $provider
     */
    function setValuesProvider(& $provider) {
        if ($provider && !is_a($provider, 'Ac_Model_Values')) trigger_error ("\$provider should be instance of Ac_Model_Values", E_USER_ERROR);
        $this->_valuesProviderPrototype = false;
        $this->_valuesProvider = false;
        $this->_valuesGetter = false;
        if ($provider) {
            $this->_valuesProvider = & $provider;
            $this->valueList = false;
        }
    }
    
    /**
     * @return Ac_Model_Values
     */
    function _getValuesProvider() {
        if ($this->_valuesProvider === false) {
            if ($this->_valuesProviderPrototype) {
                $this->_valuesProvider = Ac_Model_Values::factoryIndependent($this->_valuesProviderPrototype, $this->valuesProviderOverride, $this->valuesProviderOverride);
            }
            elseif ($p = & $this->getModelProperty()) {
                if (isset($p->values) && $p->values) {
                    $this->_valuesProvider = Ac_Model_Values::factoryWithProperty($p);
                }
            }
            else $this->_valuesProvider = null;
        }
        return $this->_valuesProvider;
    }
    
    function getDummyCaption() {
        $res = false;
        if ($this->dummyCaption === false) {
            if ($p = & $this->getModelProperty() && isset($p->dummyCaption) && ($p->dummyCaption !== false)) {
                $res = $p->dummyCaption;
            }
        } else $res = $this->dummyCaption;
        return $res;
    }
    
    function getDummyValue() {
        $res = false;
        if ($this->dummyValue === false) {
            if ($p = & $this->getModelProperty() && isset($p->dummyValue) && ($p->dummyValue !== false)) {
                $res = $p->dummyValue;
            }
        } else $res = $this->dummyValue;
        return $res;
    }
    
    function getMultiSelect() {
        if ($this->multiSelect === '?') {
            $res = $this->isList();
        } else {
            $res = $this->multiSelect;
        }
        return $res;
    }
    
    function getValueList() {
         if (!is_array($this->valueList)) {
             $res = array();
             if ($this->_valuesGetter) {
                 if ($this->_model && is_callable($c = array (& $this->_model, $this->_valuesGetter))) {
                     return call_user_func($c);
                 } else {
                     return call_user_func($this->_valuesGetter);
                 }
             }
             elseif ($vp = & $this->_getValuesProvider()) {
                 $res = $vp->getValueList();
             } elseif ($p = & $this->getModelProperty()) {
                 if (isset($p->valueList) && is_array($p->valueList))
                    $res = $p->valueList;
             }
         } else {
             $res = $this->valueList;
         }
         return $res;
    }
    
    function isItemSelected($item, $value = false) {
        if (func_num_args() == 1) $value = $this->getValue();
        if ($this->getMultiSelect()) {
            if (!is_array($value)) {
                if (strlen($ls = $this->getListSeparator())) {    
                    $value = explode($this->getListSeparator(), $value);
                } else {
                    $value = array($value);
                }
            }
            $res = in_array($item, $value); 
        } else {
            if (is_array($value)) {
                if ($this->singleArrayValueToScalarValue && (count($value) == 1) && in_array($item, $value)) $res = true;
                    else $res = false;
            } else {
                $res = (string) $item == (string) $value;
            }
        }
        return $res;
    }
    
    function _doGetValue() {
        if (!($this->readOnly === true) && $this->getMultiSelect() && $this->isSubmitted() && !isset($this->_rqData['value'])) {
            $res = array(); 
        } else {
            $res = parent::_doGetValue();
        }
        return $res;
    }
    
    function getId() {
        if ($this->id === false) {
            $this->id = '';
            $htmlAttribs = $this->getHtmlAttribs(true);
            if (isset($htmlAttribs['id']) && strlen($this->htmlAttribs['id'])) $this->id = $htmlAttribs['id'];
            if (!strlen($this->id) && $this->autoId) $this->id = $this->_context->mapIdentifier('');
        }
        return $this->id;
    }
    
    function getHtmlAttribs($dontFetchValues = false) {
        $res = parent::getHtmlAttribs();
        if (!$dontFetchValues && $this->disableIfNoValues && !count($this->getValueList())) $res['disabled'] = 'disabled';
        return $res;
    }
    
}

?>
