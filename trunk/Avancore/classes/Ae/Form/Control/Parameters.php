<?php

/**
 * Implements editing parameter values with mosParameters object and .xml file 
 */
class Ae_Form_Control_Parameters extends Ae_Form_Control {

    var $xmlFilePath = false;
    
    var $returnsArray = '?';
    
    /**
     * @var mosParameters
     */
    var $_mosParameters = false;
    
    var $templateClass = 'Ae_Form_Control_Template_Basic';
    
    var $templatePart = 'parameters';
    
    function getXmlFilePath() {
        if ($this->xmlFilePath === false) {
            $p = & $this->getModelProperty();
            if ($p && isset($p->xmlFilePath) && strlen($p->xmlFilePath)) {
                $res = $p->xmlFilePath;
            } else {
                $res = false;
            }
             
        } else {
            $res = $this->xmlFilePath;
        }
        return $res;
    } 
    
    function getReturnsArray() {
        if ($this->returnsArray === '?') {
            if (($p = & $this->getModelProperty()) && ($p->plural || $p->arrayValue)) $res = true;
            else $res = false;
        } else {
            $res = $this->returnsArray;
        }
        return $res;
    }
    
    /**
     * @return mosParameters
     */
    function getMosParameters() {
        if ($this->_mosParameters === false) {
            $this->_loadMosParametersClass();
            $disp = & Ae_Dispatcher::getInstance();
            $xfp = $this->getXmlFilePath();
            if (strlen($xfp)) $xfp = $disp->getDir().'/'.$xfp;
            $this->_mosParameters = new mosParameters($this->getStringParams(), $xfp, 'module');
        }
        return $this->_mosParameters;
    }    
    
    function getStringParams() {
        $v = $this->getValue();
        if (is_array($v)) {
            $res = $this->_arrayToString($v);
        } else $res = $v;
        return $res;
    }

    function _loadMosParametersClass() {
        if (!class_exists('mosParameters')) {
            if (defined('_VALID_MOS')) {
                require_once($GLOBALS['mosConfig_absolute_path'].'/includes/joomla.xml.php');
            }
            else {
                $disp = & Ae_Dispatcher::getInstance();
                $GLOBALS['mosConfig_absolute_path'] = $disp->getDir();
                require ($disp->getDir().'/vendor/joomla.xml.php');    
            }
        }
    }
    
    function _arrayToString($v) {
        $lines = array();
        if (!is_array($v)) $res = (string) $v; else {
            foreach ($v as $paramName => $paramValue) {
                $lines[] = $paramName.'='.$paramValue;
            }
            $res = implode("\n", $lines);
        }
        return $res;        
    }
    
    function _stringToArray($string) {
        $this->_loadMosParametersClass();
        $res = mosParameters::parse($string);
    }
    
    /**
     * @access protected
     */
    function _doGetValue() {
        if (!($this->readOnly === true)) {
            if ($this->isSubmitted() && !isset($this->_rqData['value'])) $res = array();
            elseif (isset($this->_rqData['value'])) {
                if (is_string($this->_rqData['value'])) $res = $this->_stringToArray($this->_rqData['value']);
                elseif (is_array($this->_rqData['value'])) $res = Ae_Util::stripSlashes($this->_rqData['value']);
                else $res = array();
                if (!$this->getReturnsArray()) {
                    $res = $this->_arrayToString($res);
                }
            } else {
                $res = $this->getDefault();
            }
        } else {
            $res = $this->getDefault();
        }
        return $res;
    }
        
}

?>