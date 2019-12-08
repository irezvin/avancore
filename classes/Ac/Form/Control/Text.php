<?php

/**
 * Text field, textarea, rte, hidden
 */
class Ac_Form_Control_Text extends Ac_Form_Control_Listable {
    
    var $maxLength = false;
    
    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'textInput';
    
    var $defaultSize = 50;
    
    var $textAreaRows = false;

    var $allowHtml = '?';
    
    var $type = '?';
    
    var $inputCanBeArray = false;
    
    var $doubleEncodeInInput = false;
    
    var $rteAdapter = false;

    /**
     * @access protected
     */
    function _getControlTypesForList() {
        return array('textArea');
    }
    
    function getOutputText() {
        $res = $this->formatValue($v = $this->getValue());
        return $res;
    }
    
    function getType() {
        if ($this->type === '?') {
            $res = false;
            $p = $this->getModelProperty();
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
        if ($this->allowHtml === '?' && $this->getType() == 'rte') $res = true;
        else $res = parent::isHtmlAllowed();
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
    
    /**
     * @return Ac_Form_RteAdapter 
     */
    function getRteAdapter() {
        if (!$this->rteAdapter) {
            $this->rteAdapter = Ac_Form_RteAdapter::getDefaultInstance();
        } else {
            if (!is_object($this->rteAdapter)) $this->rteAdapter = Ac_Prototyped::factory($this->rteAdapter, 'Ac_From_RteAdapter');
        }
        return $this->rteAdapter;
    }
    
}

