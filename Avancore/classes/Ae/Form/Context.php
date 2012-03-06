<?php

/**
 * Form context has specific behavior when it comes to name mapping:
 * parameters with name 'value' are mapped directly into the form namespace,
 * and other parameters of the control are mapped to the _details array.
 * 
 * For example, if we have control 'title' with parameters 'value' and 'lang',
 * parameter names in the request would be mapped as follows:
 * value => title
 * lang => _details[title][lang]
 */

class Ae_Form_Context extends Ae_Legacy_Controller_Context_Http {  
    
    var $_controlName = false;
    var $idPrefix = false;
    var $valueFirst = true;    
    
    /**
     * @param Ae_Legacy_Controller_Context_Http $context
     * @return Ae_Form_Context
     * @static
     */
    function & spawnFrom(& $context, $subPath) {
        $res = new Ae_Form_Context();
        $res->assign($context);
        $arrSubPath  = is_array($subPath)? $subPath : Ae_Util::pathToArray($subPath); 
        $res->setDataPath(array_merge($context->_arrDataPath, $arrSubPath));
        $dp = $res->_arrDataPath;
        /*if (count($dp)) $controlName = $dp[count($dp) - 1];
            else $controlName = false;*/
        $controlName = Ae_Util::pathToArray($subPath);
        $res->_controlName = $dp;
        $d = $context->getData();
        $subData = array();
        if ($controlName) {
            $vf = false;
            if (is_a($context, 'Ae_Form_Context')) $vf = $context->valueFirst;
            if ($vf) {
                if (is_array($det = Ae_Util::getArrayByPath($d, Ae_Util::concatPaths('_data', $controlName), null))) {
                    $details = $det;
                    if (is_array($det) && isset($det['value'])) unset($det['value']);
                    $subData = $details;
                }
                if (!is_null($val = Ae_Util::getArrayByPath($d, $controlName, null))) $subData = $val;
            } else {
                if (!is_null($val = Ae_Util::getArrayByPath($d, $controlName, null))) $subData = $val;
            }
        }
        $res->_data = $subData;
        return $res;
    }
    
    function getData($path = false, $defaultValue = false) {
        $d = $this->_data;
        if ($this->valueFirst) {
            if (!is_array($d)) $r = array('value' => $d);
            else {
                $r = array();
                if (isset($d['_details']) && is_array($d['_details'])) {
                    $r = $d['_details'];
                    if (isset($r['value'])) unset($r['value']); 
                }
                foreach (array_keys($d) as $k) if ($k !== '_details') $r['value'][$k] = $d[$k];
            }
        } else {
            if (is_array($d)) $r = $d;
                else $r = array();
        }
        if ($path === false) {
            $res = $r;
        } else {
            if (!is_array($path)) $path = Ae_Util::pathToArray($path);
            $res = Ae_Util::getArrayByPath($r, $path, $defaultValue);
        }
        return $res;
    }
    
    
    function mapParam($paramPath, $asArray = false) {
        if ($this->valueFirst) {
            $arrPath = is_array($paramPath)? $paramPath : Ae_Util::pathToArray($paramPath);
            if (count($arrPath)) {
                if ($arrPath[0] == 'value') {
                    $resPath = array_merge($this->_arrDataPath, array_slice($arrPath, 1));
                } else {
                    $resPath = array_merge($this->_arrDataPath, array('_details'), $arrPath);
                }
            } else {
                $resPath = $this->_arrDataPath;
            }
            if (!$asArray) $resPath = Ae_Util::arrayToPath($resPath);
            return $resPath;
        } else {
            return parent::mapParam($paramPath, $asArray);
        }
        
    }
    
    function mapIdentifier($identifier) {
        if ($this->idPrefix) $identifier = $this->idPrefix.$identifier;
        return parent::mapIdentifier($identifier);
    }
    
}

?>