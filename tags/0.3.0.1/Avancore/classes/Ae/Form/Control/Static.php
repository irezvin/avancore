<?php

Ae_Dispatcher::loadClass('Ae_Form_Control');

/**
 * Text field, textarea, rte, hidden
 */
class Ae_Form_Control_Static extends Ae_Form_Control {
    
    var $templateClass = 'Ae_Form_Control_Template_Basic';
    
    var $templatePart = 'static';
    
    var $tagName = 'div';
    
    var $allowHtml = '?';
    
    function isHtmlAllowed() {
        if ($this->allowHtml === '?') {
            if ($this->getType() == 'rte') $res = true;
            else {    
                $p = & $this->getModelProperty();
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