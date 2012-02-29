<?php

Ae_Dispatcher::loadClass('Ae_Legacy_Controller_Response_Http');

class Ae_Legacy_Controller_Response_Html extends Ae_Legacy_Controller_Response_Http {
    
    var $pageTitle = array();
    
    var $jsLibs = array();
    
    var $cssLibs = array();
    
    var $metas = array();
    
    var $pathway = array();
    
    var $noWrap = false;
    
    var $noHtml = false;
    
    var $bodyAttribs = array();
    
    var $headTags = array();
    
    function addPageTitle($title) {
        $this->pageTitle[] = $title;
    }
    
    function addJsLib($jsLib, $isLocal, $atBeginning = false) {
        if ($atBeginning) $this->jsLibs = array_merge(array(array($jsLib, $isLocal)), $this->jsLibs);
        else $this->jsLibs[] = array($jsLib, $isLocal);
        $this->jsLibs = Ae_Util::array_unique($this->jsLibs);
    }
    
    function addCssLib($cssLib, $isLocal) {
        $this->cssLibs[] = array($cssLib, $isLocal);
        $this->cssLibs = Ae_Util::array_unique($this->cssLibs);
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
    
    function getJsScriptTag($jsLib, $isLocal) {
        $disp = & Ae_Dispatcher::getInstance();
        $res = '<script type="text/javascript" src="'.($disp->adapter->getJsUrlStr($jsLib, $isLocal)).'"> </script>';
        return $res;
    }
    
    function getCssLibTag($cssLib, $isLocal) {
        $disp = & Ae_Dispatcher::getInstance();
        $res = '<link rel="stylesheet" type="text/css" href="'.($disp->adapter->getCssUrlStr($cssLib, $isLocal)).'" />';
        return $res;
    }
    
    function getMetaTag($metaName, $metaValue, $isHttpEquiv = false) {
        $a = array('content' => $metaValue);
        if ($isHttpEquiv) $a['http-equiv'] = $metaName;
            else $a['name'] = $metaName;
        $res = Ae_Util::mkElement('meta', false, $a);
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
     * @param Ae_Legacy_Controller_Response_Html $subResponse
     */
    function mergeWithResponse (& $subResponse, $withTitleAndPathway = true, $putContent = false) {
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
        $this->jsLibs = Ae_Util::array_unique(array_merge($this->jsLibs, $subResponse->jsLibs));
        $this->cssLibs = Ae_Util::array_unique(array_merge($this->cssLibs, $subResponse->cssLibs));
        Ae_Util::ms($this->data, $subResponse->data);
        $this->extraHeaders = Ae_Util::array_unique(array_merge($this->extraHeaders, $subResponse->extraHeaders));
        $this->bodyAttribs = Ae_Util::m($this->bodyAttribs, $subResponse->bodyAttribs);
        $this->headTags = Ae_Util::array_unique(array_merge($this->headTags, $subResponse->headTags));
        $this->parts = array_merge($this->parts, $subResponse->parts);
        $subResponse->parts = array();
        
        if (!$this->noWrap && !$this->noHtml) {
            if (is_string($putContent)) $this->content = str_replace($putContent, $subResponse->content, $this->content);
            elseif ($putContent === true) $this->content .= $subResponse->content;
        } 
    }
    
    function __sleep() {
        if (class_exists('Ae_Legacy_Controller_Response_Global', false)) {
            // We have to save global response data when response is serialized 
            if ($this !== Ae_Legacy_Controller_Response_Global::r()) {
                Ae_Legacy_Controller_Response_Global::i()->pourToResponse($this);
            }
        }
        $res = parent::__sleep();
        return $res;
    }
    
}

?>