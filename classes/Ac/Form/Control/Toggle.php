<?php

/**
 * Anything that can be toggled on and off (checkbox, yes/no list, on/off buttons etc) to enter boolean value   
 */
class Ac_Form_Control_Toggle extends Ac_Form_Control {
    
    var $trueLabel = false;
    
    var $falseLabel = false;
    
    var $trueValue = 1;
    
    var $falseValue = 0;
    
    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'toggle';
    
    /**
     * @var string checkbox|selectList|radioList
     */
    
    var $type = 'checkbox';
    
    function getEmptyCaption() {
        $res = parent::getEmptyCaption();
        if ($res === false) $res = $this->getFalseLabel();
    }
    
    function getTrueLabel() {
        $res = $this->trueLabel;
        if ($res === false) {
            if ($p = $this->getModelProperty() && isset($p->valueList) && is_array($v = $p->valueList)) {
                if (isset($v[1])) $res = $v[1];
            }
            if ($res === false) {
                $res = 'Yes';
            }
        }
        return $res;
    }
    
    function getFalseLabel() {
        $res = $this->falseLabel;
        if ($res === false) {
            if ($p = $this->getModelProperty() && isset($p->valueList) && is_array($v = $p->valueList)) {
                if (isset($v[0])) $res = $v[0];
            }
            if ($res === false) {
                $res = 'No';
            }
        }
        return $res;
    }
    
    function updateFromModel() {
        parent::updateFromModel();
        $this->_submitted = false;
    }
    
    function deleteValue() {
        parent::deleteValue();
        $this->_submitted = false;
    }
    
    function updateFromRequest() {
        parent::updateFromRequest();
        $this->_submitted = null;
    }
    
    function _doGetValue() {
        if (!$this->isReadOnly()) {
            if (isset($this->_rqData['value'])) {
                $val = (bool) $this->_rqData['value'];
                $res = $val? $this->trueValue : $this->falseValue;
            } elseif ($this->type == 'checkbox' && $this->isSubmitted()) {
                
                $res = $this->falseValue; 
            } else {
                $res = $this->getDefault();
            }
        } else {
            $res = $this->getDefault();
        }
        return $res;
    }
    
}

