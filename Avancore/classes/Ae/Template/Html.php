<?php

class Ae_Template_Html extends Ae_Template {

    /**
     * @var Ae_Legacy_Controller_Context_Http
     */
    var $context = false;
    
    /**
     * @var Ae_Legacy_Controller_Response_Html
     */
    var $htmlResponse = false;
    
    var $quoteStyle = ENT_QUOTES;
    
    var $charset = false;
    
    var $doubleEncode = true;
    
    function a(array $attribs) {
        echo Ae_Util::mkAttribs($attribs);
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
        if ($noEscape) echo $var; else echo Ae_Util::htmlspecialchars($var, $this->quoteStyle, $this->charset, $this->doubleEncode);
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
            if (method_exists($obj, 'toString')) echo Ae_Util::htmlspecialchars($obj->toString(), $this->quoteStyle, $this->charset, $this->doubleEncode);
            elseif (method_exists($obj, 'show')) { 
                ob_start(); 
                $obj->show(); 
                echo Ae_Util::htmlspecialchars(ob_get_clean(), $this->quoteStyle, $this->charset, $this->doubleEncode); 
            }
        }
    }
    
    function hsc($string) {
        //return htmlspecialchars($string, $this->quoteStyle, $this->charset);
        return Ae_Util::htmlspecialchars($string, $this->quoteStyle, $this->charset, $this->doubleEncode);
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
        $disp = & Ae_Dispatcher::getInstance();
        $this->d($disp->config->liveSite, $noEscape);
    }
    
    function attribs($attribs = array(), $return = false) {
        $res = Ae_Util::mkAttribs($attribs);
        if ($return) return $res; else echo $res;
    }
    

    function addPageTitle($title) {
        if (is_a($this->htmlResponse, 'Ae_Legacy_Controller_Response_Html')) $this->htmlResponse->addPageTitle($title);
    }
    
    function addJsLib($jsLib, $isLocal = true, $atBeginning = false) {
        if (is_array($jsLib)) {
            foreach ($jsLib as $l) $this->addJsLib($l, $isLocal);
        } else {
            if (is_a($this->htmlResponse, 'Ae_Legacy_Controller_Response_Html')) {
                $this->htmlResponse->addJsLib($jsLib, $isLocal, $atBeginning);
            }
            else {
                echo Ae_Legacy_Controller_Response_Html::getJsScriptTag($jsLib, $isLocal);
            }
        }
    }
    
    function addCssLib($cssLib, $isLocal = true) {
        if (is_array($cssLib)) {
            foreach ($cssLib as $l) $this->addCssLib($l, $isLocal);
        } else {
            if (is_a($this->htmlResponse, 'Ae_Legacy_Controller_Response_Html')) $this->htmlResponse->addCssLib($cssLib, $isLocal);
            else {
                echo Ae_Legacy_Controller_Response_Html::getCssLibTag($cssLib, $isLocal);
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
        if (is_a($this->htmlResponse, 'Ae_Legacy_Controller_Response_Html')) $this->htmlResponse->addMeta($metaName, $metaValue);
    }
    
    function appendPathway($url, $text) {
        if (is_a($this->htmlResponse, 'Ae_Legacy_Controller_Response_Html')) $this->htmlResponse->appendPathway($url, $text);
    }
    
    /**
     * @return Ae_Template_Helper_Html
     */
    function getHtmlHelper () {
        $res = & $this->getHelper ('Ae_Template_Helper_Html');
        return $res; 
    }
    
    function mapParam($paramName, $noEscape = false, $return = false) {
        $p = $paramName;
        if ($this->context) {
            $p = $this->context->mapParam(Ae_Util::pathToArray($paramName), true);
        }
        $p = Ae_Util::arrayToPath($p);
        if (!$noEscape) $p = htmlspecialchars($p, $this->quoteStyle, $this->charset);
        if ($return) return $p;
            else echo $p; 
    }
    
    /**
     * @param array|string|Ae_Url $extraParams Url can be provided; if $extraParams is an array, template controller's URL will be returned
     * @param bool $noEscape Escape return / output or not
     * @param bool $return Return value instead of displaying it
     * @param bool $asString Return string instead of Ae_Url (has meaning only with $return == true) 
     * @return Ae_Url
     */
    function & url($extraParams = array(), $noEscape = false, $return = false, $asString = false) {
        if (is_a($extraParams, 'Ae_Url')) $u = & $extraParams;
        else { 
            $u = false;
            if ($this->controller) $u = & $this->controller->getUrl($extraParams);
        }
        
        if ($u) {
            if ($return) {
                if ($asString) {
                    $res = $u->toString();
                    if (!$noEscape) $res = Ae_Util::htmlspecialchars($res, $this->quoteStyle, $this->charset);
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
        echo '<form '.Ae_Util::mkAttribs($formAttribs).'>'; 
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
        echo Ae_Util::mkElement($name, $content, $attribs);
    }
    
}