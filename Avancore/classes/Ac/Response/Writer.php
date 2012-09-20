<?php

class Ac_Response_Writer implements Ac_I_Response_Writer {

    protected $consolidatedResponseOptions = array();
    
    /**
     * @var Ac_Response
     */
    protected $response = null;
    
    protected $assetPlaceholders = array();
    
    protected $showDebugInfo = false;

    function setShowDebugInfo($showDebugInfo) {
        $this->showDebugInfo = (bool) $showDebugInfo;
    }

    function getShowDebugInfo() {
        return $this->showDebugInfo;
    }
    
    function setAssetPlaceholders(array $assetPlaceholders) {
        $this->assetPlaceholders = $assetPlaceholders;
    }

    function getAssetPlaceholders() {
        return $this->assetPlaceholders;
    }
    
    function showAssetLibs($assetLibs, $indent) {
        $css = array();
        $js = array();
        foreach ($assetLibs as $lib) {
            if (strpos($lib, '.css') !== false) $css[] = $this->getCssLibTag($lib);
            else $js[] = $this->getJsScriptTag ($lib);
        }
        echo implode($indent, array_merge($css, $js));
    }

    function getJsScriptTag($jsLib) {
        $url = self::unfoldAssetString($jsLib, $this->assetPlaceholders);
        $res = '<script type="text/javascript" src="'.$url.'"> </script>';
        return $res;
    }
    
    function getCssLibTag($cssLib) {
        $url = self::unfoldAssetString($cssLib, $this->assetPlaceholders);
        $res = '<link rel="stylesheet" type="text/css" href="'.$url.'" />';
        return $res;
    }
    
    static function unfoldAssetString($jsOrCssLib, array $assetPlaceholders) {
        $i = 0;
        for ($i = 0; $i < 10; $i++) {
            $new = strtr($jsOrCssLib, $assetPlaceholders);
            if ($new == $jsOrCssLib) break;
            $jsOrCssLib = $new;
        }
        return $jsOrCssLib;
    }
    
    /**
     * @param Ac_Response $response 
     * @return Ac_Response_Consolidated
     */
    function getConsolidatedResponse(Ac_Response $response = null) {
        if (is_null($response)) $response = $this->response;
        if (!$response) throw new Exception("No default \$response specified - call setResponse() first");
        if ($response instanceof Ac_Response_Consolidated) {
            $res = $response;
        } else {
            $res = Ac_Prototyped::factory($this->consolidatedResponseOptions, 'Ac_Response_Consolidated');
            $o = $this->doOnConsolidatedResponseCreate($res);
            if (is_object($o)) $res =  $o;
            $res->mergeRegistry($response);
        }
        return $res;
    }
    
    protected function doOnConsolidatedResponseCreate(Ac_Response_Consolidated $response) {
    }
    
    function setConsolidatedResponseOptions(array $consolidatedResponseOptions) {
        $this->consolidatedResponseOptions = $consolidatedResponseOptions;
    }

    function getConsolidatedResponseOptions() {
        return $this->consolidatedResponseOptions;
    }    
    
    function setResponse(Ac_Response $response) {
         $this->response = $response;
    }
    
    function writeResponse(Ac_Response $response = null) {
        if (!$response) $response = $this->response;
        if (!$response) throw new Exception("Call \$setResponse() first");
        $consolidated = $this->getConsolidatedResponse($response);
        $arrData = $consolidated->getConsolidated();
        $this->processConsolidatedArray($arrData);
    }
    
    protected function processConsolidatedArray(array $arrData) {
        
    }

}