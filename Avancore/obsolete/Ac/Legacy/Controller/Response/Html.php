<?php

class Ac_Legacy_Controller_Response_Html extends Ac_Legacy_Controller_Response_Http {
    
    var $pageTitle = array();
    
    var $jsLibs = array();
    
    var $cssLibs = array();
    
    var $metas = array();
    
    var $pathway = array();
    
    var $noWrap = false;
    
    var $noHtml = false;
    
    var $bodyAttribs = array();
    
    var $headTags = array();
    
    protected $assetPlaceholders = false;
    
    function addPageTitle($title) {
        $this->pageTitle[] = $title;
    }
    
    function addAssetLibs($assetLibs) {
        $assetLibs = Ac_Util::toArray($assetLibs);
        foreach ($assetLibs as $lib) {
            if (!strcasecmp(substr($lib, -4), ".css")) $this->addCssLib($lib);
                else $this->addJsLib($lib);
        }
    }
    
    function addJsLib($jsLib, $isLocal = false, $atBeginning = false) {
        if (is_array($jsLib)) {
            foreach ($atBeginning? array_reverse($jsLib) : $jsLib as $lib) {
                $this->addJsLib($lib, $isLocal, $atBeginning);
            }
        } else {
            if ($atBeginning) $this->jsLibs = array_merge(array(array($jsLib, $isLocal)), $this->jsLibs);
            else $this->jsLibs[] = array($jsLib, $isLocal);
            $this->jsLibs = Ac_Util::array_unique($this->jsLibs);
        }
    }
    
    function addCssLib($cssLib, $isLocal = false) {
        if (is_array($cssLib)) {
            foreach ($cssLib as $lib) $this->addCssLib($lib, $isLocal);
        } else {
            $this->cssLibs[] = array($cssLib, $isLocal);
            $this->cssLibs = Ac_Util::array_unique($this->cssLibs);
        }
    }
    
    function addMeta($metaName, $metaContent, $isHttpEquiv = false) {
        if ($isHttpEquiv) {
            $this->metas[$metaName] = array($metaContent, $isHttpEquiv);
        } else {
            $this->metas[$metaName] = $metaContent;
        }
    }
    
    function appendPathway($url, $text) {
        $this->pathway[] = array($url, $text);
    }
    
    function prependPathway($url, $text) {
        $this->pathway = array_merge(array(array($url, $text)), $this->pathway);
    }
    
    function setAssetPlaceholders(array $assetPlaceholders) {
        $this->assetPlaceholders = $assetPlaceholders;
    }
    
    function getAssetPlaceholders() {
        if ($this->assetPlaceholders === false) {
            $this->assetPlaceholders = $this->getApplication()->getAssetPlaceholders(true);
        }
        return $this->assetPlaceholders;
    }
    
    function getJsScriptTag($jsLib, $isLocal) {
        $url = self::unfoldAssetString($jsLib, $this->getAssetPlaceholders());
        $res = '<script type="text/javascript" src="'.$url.'"> </script>';
        return $res;
    }
    
    function getCssLibTag($cssLib, $isLocal) {
        $url = self::unfoldAssetString($cssLib, $this->getAssetPlaceholders());
        $res = '<link rel="stylesheet" type="text/css" href="'.$url.'" />';
        return $res;
    }
    
    function getMetaTag($metaName, $metaValue, $isHttpEquiv = false) {
        $a = array('content' => $metaValue);
        if ($isHttpEquiv) $a['http-equiv'] = $metaName;
            else $a['name'] = $metaName;
        $res = Ac_Util::mkElement('meta', false, $a);
        return $res;
    }
    
    function setBodyAttribute($attribute, $value = null) {
        if (!is_null($value)) $this->bodyAttribs[$attribute] = $value;
            else unset($this->bodyAttribs[$attribute]);
    }
    
    function addHeadTag($headTag, $key = false) {
        if ($key == false) $this->headTags[] = $headTag;
            else $this->headTags[$key] = $headTag;
    }
    
    /**
     * @param Ac_Legacy_Controller_Response_Html $subResponse
     */
    function mergeWithResponse ($subResponse, $withTitleAndPathway = true, $putContent = false) {
        if ($subResponse === $this) throw new Exception("Response cannot merge with itself");
        if ($subResponse->noWrap) {
                $this->noWrap = true;
                $this->content = $subResponse->content;
                $this->extraHeaders = array();
                $this->pageTitle = array();
                $this->jsLibs = array();
                $this->cssLibs = array();
                $this->metas = array();
                $this->pathway = array();
                $this->bodyAttribs = array();
                $this->headTags = array();
                $this->data = array();
        }
        
        if (!$this->contentType) $this->contentType = $subResponse->contentType;
        
        if ($subResponse->noHtml) $this->noHtml = true;
        
        if ($withTitleAndPathway) {
            $this->pageTitle = array_merge($this->pageTitle, $subResponse->pageTitle);
            $this->pathway = array_merge($this->pathway, $subResponse->pathway);
        }
        $this->metas = array_merge($this->metas, $subResponse->metas);
        $this->jsLibs = Ac_Util::array_unique(array_merge($this->jsLibs, $subResponse->jsLibs));
        $this->cssLibs = Ac_Util::array_unique(array_merge($this->cssLibs, $subResponse->cssLibs));
        Ac_Util::ms($this->data, $subResponse->data);
        $this->extraHeaders = Ac_Util::array_unique(array_merge($this->extraHeaders, $subResponse->extraHeaders));
        $this->bodyAttribs = Ac_Util::m($this->bodyAttribs, $subResponse->bodyAttribs);
        $this->headTags = Ac_Util::array_unique(array_merge($this->headTags, $subResponse->headTags));
        $this->parts = array_merge($this->parts, $subResponse->parts);
        $subResponse->parts = array();
        
        if (!$this->noWrap && !$this->noHtml) {
            if (is_string($putContent)) $this->content = str_replace($putContent, $subResponse->content, $this->content);
            elseif ($putContent === true) $this->content .= $subResponse->content;
        } 
    }
    
    function __sleep() {
        if (class_exists('Ac_Legacy_Controller_Response_Global', false)) {
            // We have to save global response data when response is serialized 
            if ($this !== Ac_Legacy_Controller_Response_Global::r()) {
                Ac_Legacy_Controller_Response_Global::i()->pourToResponse($this);
            }
        }
        $res = parent::__sleep();
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
    
    
}

?>