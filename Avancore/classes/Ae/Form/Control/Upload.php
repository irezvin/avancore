<?php

class Ae_Form_Control_Upload extends Ae_Form_Control {
    
        
    var $templateClass = 'Ae_Form_Control_Template_Basic';
    var $templatePart = 'upload';
    
    function getFileParamName() {
        $res = md5($this->_context->mapParam('value'));
        return $res;
    }
    
    function _doGetValue() {
        $res = false;
        if (isset($_FILES) && isset($_FILES[$this->getFileParamName()])) {
            $res = $_FILES[$this->getFileParamName()];
        }
        return $res;
    }
    
    function fetchPresentation($refresh = false, $withWrapper = null) {
        $p = $this->_parent;
        while ($p && (!$p instanceof Ae_Form)) {
            $p = $p->_parent;
        }
        if ($p) $p->htmlAttribs['enctype'] = 'multipart/form-data';
        $res = parent::fetchPresentation($refresh, $withWrapper);
        return $res;
    }
    
}