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
    
    var $initScripts = array();
    
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
        $k = $jsLib.'-'.$isLocal;
        if (is_array($jsLib)) {
            foreach ($atBeginning? array_reverse($jsLib) : $jsLib as $lib) {
                $this->addJsLib($lib, $isLocal, $atBeginning);
            }
        } else {
            if ($atBeginning) $this->jsLibs = array_merge(array($k => array($jsLib, $isLocal)), $this->jsLibs);
            else $this->jsLibs[$k] = array($jsLib, $isLocal);
        }
    }
    
    function addCssLib($cssLib, $isLocal = false, $atBeginning = false) {
        $k = $cssLib.'-'.$isLocal;
        if (is_array($cssLib)) {
            foreach ($atBeginning? array_reverse($cssLib) : $cssLib as $lib) 
                $this->addCssLib($lib, $isLocal, $atBeginning);
        } else {
            if ($atBeginning) $this->cssLibs = array_merge(array($k => array($cssLib, $isLocal)), $this->cssLibs);
            else $this->cssLibs[$k] = array($cssLib, $isLocal);
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
                $this->initScripts = array();
        }
        
        if (!$this->contentType) $this->contentType = $subResponse->contentType;
        
        if ($subResponse->noHtml) $this->noHtml = true;
        
        if ($withTitleAndPathway) {
            $this->pageTitle = array_merge($this->pageTitle, $subResponse->pageTitle);
            $this->pathway = array_merge($this->pathway, $subResponse->pathway);
        }
        $this->metas = array_merge($this->metas, $subResponse->metas);
        foreach ($subResponse->jsLibs as $k => $l) {
            $this->jsLibs[$k] = $l;
        }
        foreach ($subResponse->cssLibs as $k => $l) {
            $this->cssLibs[$k] = $l;
        }
        Ac_Util::ms($this->data, $subResponse->data);
        $this->extraHeaders = Ac_Util::array_unique(array_merge($this->extraHeaders, $subResponse->extraHeaders));
        $this->bodyAttribs = Ac_Util::m($this->bodyAttribs, $subResponse->bodyAttribs);
        $this->headTags = Ac_Util::array_unique(array_merge($this->headTags, $subResponse->headTags));
        $this->initScripts = Ac_Util::array_unique(array_merge(Ac_Util::toArray($this->initScripts), Ac_Util::toArray($subResponse->initScripts)));
        $this->parts = array_merge($this->parts, $subResponse->parts);
        $subResponse->parts = array();
        
        if (!$this->noWrap && !$this->noHtml) {
            if (is_string($putContent)) $this->content = str_replace($putContent, $subResponse->content, $this->content);
            elseif ($putContent === true) $this->content .= $subResponse->content;
        } 
    }
    
    function __sleep() {
        // TODO: find workaround for this!
        
//        if (class_exists('Ac_Legacy_Controller_Response_Global', false)) {
//            // We have to save global response data when response is serialized 
//            if ($this !== Ac_Legacy_Controller_Response_Global::r()) {
//                Ac_Legacy_Controller_Response_Global::i()->pourToResponse($this);
//            }
//        }
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
    
    function replaceResultsInContent() {
        if (strpos($this->content, '##[') !== false) {
            $buf = Ac_StringObject::sliceStringWithObjects($this->content);
            $w = new Ac_Legacy_ResultWriter();
            $w->setResponse($this);
            foreach ($buf as $k => $v) {
                if (is_object($v) && $v instanceof Ac_Result_Http_Abstract) {
                    $v->setWriter($w);
                    $cnt = $v->writeAndReturn();
                    if ($v instanceof Ac_Result_Http || $v->getOverrideMode() !== Ac_Result::OVERRIDE_NONE) {
                        $buf = array($cnt);
                        break;
                    }
                    $buf[$k] = $cnt;
                }
            }
            $this->content = implode('', $buf);
        }
    }
    
    /**
     * @return Ac_Result_Http_Abstract
     */
    function createResult() {
        if (strlen($u = $this->redirectUrl)) {
            $res = new Ac_Result_Redirect(array('url' => $u));
            if ($this->redirectType) $res->setStatusCode ($this->redirectType);
        } else {
            if ($this->noHtml) {
                $res = new Ac_Result_Http();
            } else {
                $res = new Ac_Result_Html();
                if ($this->noWrap) $res->setOverrideMode(Ac_Result::OVERRIDE_ALL);
                if (isset($this->metas['description'])) $res->meta['description'] = $this->metas['description'];
                if (isset($this->metas['keywords'])) $res->meta['keywords'] = $this->metas['keywords'];
                foreach ($this->metas as $k => $v) {
                    if (is_array($v)) {
                        if ($v[1]) $this->metas['http'][$k] = $v[0];
                            else $this->metas[$k] = $v[0];
                    } else {
                        $this->metas[$k] = $v;
                    }
                }
                if ($this->pageTitle) {
                    foreach (Ac_Util::toArray($this->pageTitle) as $t) $res->title[] = $t;
                }
                if ($this->pathway) {
                    $pw = $res->getPlaceholder('pathway', true);
                    if (!$pw) $pw = $res->addPlaceholder (new Ac_Result_Placeholder, 'pathway');
                    foreach ($this->pathway as $p) $pw[] = $p;
                }
                
                foreach ($this->jsLibs as $l) $res->assets[] = $l[0];
                foreach ($this->cssLibs as $l) $res->assets[] = $l[0];
                foreach ($this->headTags as $t) $res->headTags[] = $t;
                if ($this->initScripts) {
                    $res->initScripts = $this->initScripts;
                }
            }
            
            if ($this->contentType) {
                $res->setContentType($this->contentType);
                if (preg_match('/([^;]+)\s*;\s*charset\s*=\s*(.+)$/', $this->contentType, $matches)) {
                    $res->setCharset($matches[2]);
                }
            }
            
            foreach ($this->extraHeaders as $h) {
                $res->headers[] = ''.$h;
            }
            
            // TODO: placeholders compatibility???
            $res->setContent($this->replacePlaceholders(false, true));
        }
        return $res;
    }
    
    function __toString() {
        return ''.$this->createResult();
    }
    
}

