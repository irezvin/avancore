<?php

class Ac_Form_Control_ErrorList extends Ac_Form_Control {

    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'errorList';
    
    var $getErrorsFromModel = true;
  
    var $getErrorsFromParent = true;
    
    var $hideIfNoErrors = true;
    
    var $visible = '?';
    
    function isVisible() {
        if ($this->visible === '?' && $this->hideIfNoErrors) {
            $tmp = $this->visible;
            $this->visible = (bool) count($this->getErrors());
            $res = parent::isVisible();
            $this->visible = $tmp;
        } else {
            $res = parent::isVisible();
        }
        return $res;
    }
    
    function getErrors() {
        $res = array();
        $own = parent::getErrors();
        if ($own) {
            Ac_Util::setArrayByPath($res, $this->getPath(), $own);
        }
        if ($this->getErrorsFromParent) {
            if (!($root = $this->_getRootControl('Ac_Form'))) $root = $this->_getRootControl('Ac_Form_Control_Composite');
            if ($root) {
                $e = $root->getErrors();
                if (is_array($e) && count($e)) {
                    Ac_Util::ms($res, $e);
                }
            }
        }
        if ($this->getErrorsFromModel && ($m = $this->getModel()) && $m->isChecked()) {
            $e = $m->getErrors();
            if (is_array($e) && count($e)) {
                Ac_Util::ms($res, $e);
            }
        }
        return $res;
    }
    
}