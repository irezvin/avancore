<?php

Ae_Dispatcher::loadClass('Ae_Form_Control');

/**
 * Anything that can be toggled on and off (checkbox, yes/no list, on/off buttons etc) to enter boolean value   
 */
class Ae_Form_Control_Button extends Ae_Form_Control {
    
    var $buttonCaption = false;
    
    var $buttonValue = false;
    
    var $allowAnyButtonValue = false;
    
    var $templateClass = 'Ae_Form_Control_Template_Basic';
    
    var $templatePart = 'button';
    
    var $buttonType = 'submit';
    
    var $showsOwnCaption = true;
        
    function getButtonCaption() {
        if ($this->buttonCaption === false) $res = $this->getCaption();
            else $res = $this->buttonCaption;
        return $res;  
    }
    
    function getButtonValue() {
        if ($this->buttonValue === false) $res = $this->getButtonCaption();
            else $res = $this->buttonValue;
        return $res;  
    }
    
    function _doGetValue() {
        if (!($this->readOnly === true) && isset($this->_rqData['value']) && !is_array($this->_rqData['value'])) {
            $val = $this->_rqData['value'];
            if (strlen($val)) $res = $this->allowAnyButtonValue? $val : $this->getButtonValue();
        } else {
            $res = false;
        }
        return $res;
    }
    
}

?>