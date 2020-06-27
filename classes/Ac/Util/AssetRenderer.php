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
    
    /**
     * Replaces placeholders in the asset paths using asset placeholders gathered 
     * from all registered Ac_Application instances. 
     *
     * If scalar is provided, returns single string; 
     * if $assets array, returns array, unless $glue is set to some string.
     *
     * @param string|array $assets
     * @param string $implode If array is provided, will implode result using $glue
     * @return string|array Passed value with replaced placeholders
     */
    static function replacePlaceholders($assets, $glue = false) {
        $isArray = is_array($assets);
        if (!$isArray) $assets = [$assets];
        $assetPlaceholders = Ac_Application::getDefaultInstance()->getAssetPlaceholders(true);
        $res = [];
        foreach ($assets as $string) {
            $res[] = self::unfoldAssetString($string, $assetPlaceholders); 
        }
        if ($glue) return implode($glue, $res);
        if ($isArray) return $res;
        $res = array_values($res);
        return array_pop($res);
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
