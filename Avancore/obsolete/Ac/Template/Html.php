<?php

class Ac_Template_Html extends Ac_Template {

    /**
     * @var Ac_Legacy_Controller_Context_Http
     */
    var $context = false;
    
    /**
     * @var Ac_Legacy_Controller_Response_Html
     */
    var $htmlResponse = false;
    
    var $quoteStyle = ENT_QUOTES;
    
    var $charset = false;
    
    var $doubleEncode = true;
    
    function a(array $attribs) {
        echo Ac_Util::mkAttribs($attribs);
    }
    
    function displayAttribs(array $attribs) {
        return $this->a($attribs);
    }
    
    /**
     * Outputs variable
     * @param bool $noEscape Don't escape output with htmlspecialchars()
     * @param mixed $var 
     */
    function d($var, $noEscape = false) {
        //if ($noEscape) echo $var; else echo htmlspecialchars($var, $this->quoteStyle, $this->charset);
        if ($noEscape) echo $var; else echo Ac_Util::htmlspecialchars($var, $this->quoteStyle, $this->charset, $this->doubleEncode);
    }
    
    /**
     * Outputs object
     * @param bool $noEscape Don't escape output with htmlspecialchars()
     * @param object $obj Object that supports toString() or show() methods
     */
    function o(& $obj, $noEscape = false) {
        if ($noEscape) {
            if (method_exists($obj, 'toString')) echo $obj->toString();
            elseif (method_exists($obj, 'show')) $obj->show();
        } else {
            if (method_exists($obj, 'toString')) echo Ac_Util::htmlspecialchars($obj->toString(), $this->quoteStyle, $this->charset, $this->doubleEncode);
            elseif (method_exists($obj, 'show')) { 
                ob_start(); 
                $obj->show(); 
                echo Ac_Util::htmlspecialchars(ob_get_clean(), $this->quoteStyle, $this->charset, $this->doubleEncode); 
            }
        }
    }
    
    function hsc($string) {
        //return htmlspecialchars($string, $this->quoteStyle, $this->charset);
        return Ac_Util::htmlspecialchars($string, $this->quoteStyle, $this->charset, $this->doubleEncode);
    }
    
    /**
     * Outputs dump of any variable
     * @param mixed $anything 
     */
    function p(& $anything) {
        echo '<pre>'.htmlspecialchars(print_r($anything, 1)).'</pre>';
    }
    
    /**
     * Outputs variable
     * @param mixed $var 
     * @param bool $noEscape Don't escape output with htmlspecialchars()
     */
    function display($var, $noEscape = false) {
        return $this->d($var, $noEscape);
    }
    
    /**
     * Outputs object
     * @param object $obj Object that supports toString() or show() methods
     * @param bool $noEscape Don't escape output with htmlspecialchars()
     */
    function displayObject(& $obj, $noEscape = false) {
        return $this->d($obj, $noEscape);
    }
    
    /**
     * Shows site URL
     */
    function site($noEscape = false) {
        $disp = & Ac_Dispatcher::getInstance();
        $this->d($disp->config->liveSite, $noEscape);
    }
    
    function attribs($attribs = array(), $return = false) {
        $res = Ac_Util::mkAttribs($attribs);
        if ($return) return $res; else echo $res;
    }
    

    function addPageTitle($title) {
        if (is_a($this->htmlResponse, 'Ac_Legacy_Controller_Response_Html')) $this->htmlResponse->addPageTitle($title);
    }
    
    function addJsLib($jsLib, $isLocal = true, $atBeginning = false) {
        if (is_array($jsLib)) {
            foreach ($jsLib as $l) $this->addJsLib($l, $isLocal, $atBeginning);
        } else {
            if (is_a($this->htmlResponse, 'Ac_Legacy_Controller_Response_Html')) {
                $this->htmlResponse->addJsLib($jsLib, $isLocal, $atBeginning);
            }
            else {
                echo Ac_Legacy_Controller_Response_Html::getJsScriptTag($jsLib, $isLocal);
            }
        }
    }
    
    function addCssLib($cssLib, $isLocal = true) {
        if (is_array($cssLib)) {
            foreach ($cssLib as $l) $this->addCssLib($l, $isLocal);
        } else {
            if (is_a($this->htmlResponse, 'Ac_Legacy_Controller_Response_Html')) $this->htmlResponse->addCssLib($cssLib, $isLocal);
            else {
                echo Ac_Legacy_Controller_Response_Html::getCssLibTag($cssLib, $isLocal);
            }
        }
    }
    
    function addAssetLibs($assetLibs) {
        if (!is_array($assetLibs)) $assetLibs = array($assetLibs);
        foreach ($assetLibs as $lib) {
            if (!strcasecmp(substr($lib, -4), ".css")) $this->addCssLib($lib);
                else $this->addJsLib($lib);
        }
    }
    
    function addMeta($metaName, $metaValue) {
        if (is_a($this->htmlResponse, 'Ac_Legacy_Controller_Response_Html')) $this->htmlResponse->addMeta($metaName, $metaValue);
    }
    
    function appendPathway($url, $text) {
        if (is_a($this->htmlResponse, 'Ac_Legacy_Controller_Response_Html')) $this->htmlResponse->appendPathway($url, $text);
    }
    
    /**
     * @return Ac_Template_Helper_Html
     */
    function getHtmlHelper () {
        $res = & $this->getHelper ('Ac_Template_Helper_Html');
        return $res; 
    }
    
    function mapParam($paramName, $noEscape = false, $return = false) {
        $p = $paramName;
        if ($this->context) {
            $p = $this->context->mapParam(Ac_Util::pathToArray($paramName), true);
        }
        $p = Ac_Util::arrayToPath($p);
        if (!$noEscape) $p = htmlspecialchars($p, $this->quoteStyle, $this->charset);
        if ($return) return $p;
            else echo $p; 
    }
    
    /**
     * @param array|string|Ac_Url $extraParams Url can be provided; if $extraParams is an array, template controller's URL will be returned
     * @param bool $noEscape Escape return / output or not
     * @param bool $return Return value instead of displaying it
     * @param bool $asString Return string instead of Ac_Url (has meaning only with $return == true) 
     * @return Ac_Url
     */
    function & url($extraParams = array(), $noEscape = false, $return = false, $asString = false) {
        if (is_a($extraParams, 'Ac_Url')) $u = & $extraParams;
        else { 
            $u = false;
            if ($this->controller) $u = & $this->controller->getUrl($extraParams);
        }
        
        if ($u) {
            if ($return) {
                if ($asString) {
                    $res = $u->toString();
                    if (!$noEscape) $res = Ac_Util::htmlspecialchars($res, $this->quoteStyle, $this->charset);
                } else {
                    $res = & $u;
                }
                return $res;
            } else {
                $this->display($u->toString(), $noEscape);
            }
        }
        return $u;
    }
    
    function openFormElement($extraParams = array(), $formAttribs = array(), $urlsParamsToHiddenFields = true, $return = false) {
        if ($return) ob_start();
        $u = & $this->url($extraParams, false, true);
        if (!isset($formAttribs['action'])) $formAttribs['action'] = $u->toString(!$urlsParamsToHiddenFields);
        echo '<form '.Ac_Util::mkAttribs($formAttribs).'>'; 
        if ($urlsParamsToHiddenFields) echo "\n".$u->getHidden()."\n";
        if ($return) {
            $res = ob_get_clean();
            return $res;
        }
    }
    
    function echoJson($value, $indent = 0, $indentStep = 4, $newLines = true, $withNumericKeys = true) {
        $h = & $this->getHtmlHelper();
        echo $h->toJson($value, $indent, $indentStep, $newLines, $withNumericKeys);
    }

    function e($name, $content, array $attribs = array(), $noEscape = false) {
        if (strlen($content) && !$noEscape) $content = htmlspecialchars ($content);
        echo Ac_Util::mkElement($name, $content, $attribs);
    }
    
}