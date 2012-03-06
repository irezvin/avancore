<?php

/**
 * Text field, textarea, rte, hidden
 */
class Ae_Form_Control_Text extends Ae_Form_Control_Listable {
    
    var $maxLength = false;
    
    var $templateClass = 'Ae_Form_Control_Template_Basic';
    
    var $templatePart = 'textInput';
    
    var $defaultSize = 50;
    
    var $textAreaRows = false;

    var $allowHtml = '?';
    
    var $type = '?';
    
    var $inputCanBeArray = false;
    
    var $doubleEncodeInInput = false;

    /**
     * @access protected
     */
    function _getControlTypesForList() {
        return array('textArea');
    }
    
    function getOutputText() {
        return $this->formatValue($this->getValue());
    }
    
    function getType() {
        if ($this->type === '?') {
            $res = false;
            $p = & $this->getModelProperty();
            if ($p) {
                if (isset($p->controlType) && ($p->controlType == 'textArea' || $p->controlType == 'rte')) $res = $p->controlType;
                elseif (($p->plural || $p->arrayValue) && isset($p->listSeparator) && ($p->listSeparator == "\n")) {
                    $res = 'textArea';
                }
            }
            if ($res === false && ($this->isList() && ($this->getListSeparator() == "\n"))) $res = 'textArea';
            if ($res === false) $res = 'text';
        } else {
            $res = $this->type;
        }
        return $res;
    }
    
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
    
    function getMaxLength() {
        return $this->maxLength;
    }
    
    /**
     * @access protected
     */
    function _doProcessInputValue(& $val) {
        $ml = $this->getMaxLength();
        if (is_numeric($ml) && intval($ml)) $val = substr($val, 0, intval($ml));
        if (get_magic_quotes_gpc()) $val = stripslashes($val);
        if (!$this->isHtmlAllowed()) $val = strip_tags($val);
    }
    
    function getHtmlAttribs() {
        $res = parent::getHtmlAttribs();
        if (!isset($res['size']) && $this->defaultSize !== false) {
            $ml = $this->getMaxLength()? $this->getMaxLength() : $this->defaultSize;
            $res['size'] = max($ml, $this->defaultSize);
        }
        return $res;
    }
    
}

?>