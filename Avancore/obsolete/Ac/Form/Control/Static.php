<?php

/**
 * Text field, textarea, rte, hidden
 */
class Ac_Form_Control_Static extends Ac_Form_Control {
    
    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'static';
    
    var $tagName = 'div';
    
    var $allowHtml = '?';
    
    var $debug = false;
    
    function isHtmlAllowed() {
        if ($this->allowHtml === '?') {
            if ($this->getType() == 'rte') $res = true;
            else {    
                $p = $this->getModelProperty();
                if ($p && isset($p->allowHtml)) $res = $p->allowHtml;
                    else $res = false;
            } 
        } else {
            $res = $this->allowHtml;
        }
        return $res;
    }
    
    function isReadOnly() {
        return true;
    }
    
}

?>