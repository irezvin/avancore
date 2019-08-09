<?php

class Ac_Util_AssetRenderer {

    protected $glue = "\n    ";
    
    protected $assetPlaceholders = array();
    
    function __construct(array $assetPlaceholders = array()) {
        $this->assetPlaceholders = $assetPlaceholders;
    }
    
    function setAssetPlaceholders(array $assetPlaceholders) {
        $this->assetPlaceholders = $assetPlaceholders;
    }

    function getAssetPlaceholders() {
        return $this->assetPlaceholders;
    }    
    
    function renderAssets(array $assetLibs, $asArray = false) {
        $css = array();
        $js = array();
        foreach ($assetLibs as $lib) {
            if (strpos($lib, '.css') !== false) $css[] = $this->getCssLibTag($lib);
            else $js[] = $this->getJsScriptTag ($lib);
        }
        $res = array_merge($css, $js);
        if (!$asArray) $res = implode($this->glue, $res);
        return $res;
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
    
    function setGlue($glue) {
        $this->glue = $glue;
    }

    function getGlue() {
        return $this->glue;
    }    
    
}