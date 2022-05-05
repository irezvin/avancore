<?php

class Ac_UrlMapper_UrlMapper extends Ac_Prototyped {

    const LEADING_SLASH_ANY = 'any';
    
    const LEADING_SLASH_ADD = 'add';
    
    const LEADING_SLASH_STRIP = 'strip';
    
    protected $patterns = array();
    
    protected $lastMatch = null;
    
    protected $leadingSlashHandling = self::LEADING_SLASH_ADD;
    
    /**
     * @var Ac_Url
     */
    protected $baseUrl = false;
    
    /**
     * @var Ac_UrlMapper_UrlMapper
     */
    protected $parentMapper = false;
    
    protected $parentPathInfoParam = '__pathInfo__';
    
    /**
     * $patterns is array of patterns or pattern definitions.
     * When numeric key is provided, key will be pattern definition.
     */
    function setPatterns(array $patterns, $replace = true) {
        if ($replace) $this->patterns = array();
        foreach ($patterns as $definition => $item) {
            if (is_string($item)) $pattern = new Ac_UrlMapper_Pattern(array('definition' => $item));
            elseif (is_array($item) || is_object($item)) $pattern = Ac_Prototyped::factory($item, 'Ac_UrlMapper_Pattern');
            else throw Ac_E_InvalidCall("Invalid definition of \$patterns['{$definition}']; expected array, object or string");
            if (is_numeric($definition)) $definition = $pattern->getDefinition();
            $this->patterns[$definition] = $pattern;
        }
        $this->sortPatterns();
    }
    
    protected function rebuildPatterns() {
        $this->patterns = array();
    }

    // sort by: number of params DESC, definition length DESC
    protected function sortPatterns() {
        uasort($this->patterns, function($a, $b) {
            $res = - (count($a->getParams()) - count($b->getParams()));
            if (!$res) $res = - (strlen($a->getDefinition()) - strlen($b->getDefinition()));
            return $res;
        });
    }
    
    function listPatterns() {
        if ($this->patterns === false) $this->rebuildPatterns();
        return array_keys($this->patterns);
    }
    
    /**
     * @return Ac_UrlMapper_Pattern
     */
    function getPattern($definition, $dontThrow) {
        if ($this->patterns === false) $this->rebuildPatterns();
        if (isset($this->patterns[$definition])) {
            return $this->patterns[$definition];
        }
        if ($dontThrow) return null;
        throw Ac_E_InvalidCall::noSuchItem('pattern', $definition);
    }
    
    /**
     * @return Ac_UrlMapper_Pattern[]
     */
    
    function getPatterns() {
        if ($this->patterns === false) $this->rebuildPatterns();
        return $this->patterns;
    }
    
    function paramsToString(array $params) {
        return $this->moveParamsToString($params);
    }
    
    function moveParamsToString(array & $params) {
        if ($this->patterns === false) $this->rebuildPatterns();
        $this->lastMatch = null;
        foreach ($this->patterns as $i => $pat) {
            $res = $pat->moveParamsToString($params);
            if (!is_null($res)) {
                $this->lastMatch = $i;
                $res = $this->handleLeadlingSlash($res);
                if ($this->parentMapper) break; 
                return $res;
            }
        }
        if (is_null($res)) return;
        if ($this->parentMapper) {
            if ($res !== null && strlen($this->parentPathInfoParam)) {
                $params[$this->parentPathInfoParam] = $res;
            }
            $parentRes = $this->parentMapper->moveParamsToString($params);
            if ($parentRes === null) {
                if ($res !== null && strlen($this->parentPathInfoParam))
                    unset($params[$this->parentPathInfoParam]);
            }
            return $parentRes;
        }
    }
    
    function stringToParams($string) {
        if ($this->patterns === false) $this->rebuildPatterns ();
        $string = $this->handleLeadlingSlash($string);
        $this->lastMatch = null;
        foreach ($this->patterns as $i => $pat) {
            $res = $pat->stringToParams($string);
            if (!is_null($res)) {
                $this->lastMatch = $i;
                return $res;
            }
        }
    }
    
    /**
     * Returns key (usually definition) of last matched pattern, or NULL
     * if last pattern search gave no results.
     * 
     * All calls to paramsToString(), moveParamsToString(), stringToParams(),
     * pathToQuery(), queryToPath(), strPathToQuery(), strQueryToPath()
     * change value of this variable.
     * 
     * @return string|null
     */
    function getLastMatch() {
        return $this->lastMatch;
    }

    /**
     * Matches $url against patterns and extracts parameters from its' pathInfo.
     * 
     * If $this->baseUrl is set, checks if \$url is applicable to parsing by being
     * prefixed with $this->baseUrl.
     * 
     * Also if $this->baseUrl is set, it will be used to extract $pathInfo from
     * $url if one doesn't have $pathInfo set. 
     * 
     * Returns new $url (it will have same values as original $url if there was
     * no match), or $url instance if $dontClone was TRUE.
     * 
     * Will set pathInfo if pathInfo wasn't set before, and baseUrl allows to determine the path info.
     * Will do that even if searching for pattern will fail.
     * 
     * @param array $extractedParams If resolved Ok, extracted query parameters will be returned here.
     * @param bool $dontClone Modify \$url directly, don't clone it
     * 
     * @return Ac_Url
     */
    function pathToQuery(Ac_Url $url, & $extractedParams = array(), $dontClone = false) {
        $extractedParams = array();
        $this->lastMatch = null;
        $pathInfo = null;
        if ($this->baseUrl) {
            if (!$url->hasBase($this->baseUrl, $pathInfo)) return $url;
        }
        if (!$dontClone) $url = clone $url;
        if ($pathInfo !== null && !strlen($url->pathInfo)) $url->setPathInfo($pathInfo);
        
        $params = $this->stringToParams($url->pathInfo);
        
        if ($params === null) return $url;
                
        $extractedParams = $params;
        Ac_Util::ms($url->query, $extractedParams);
        
        return $url;
    }
    
    /**
     * @return Ac_Url
     * @param Ac_Url $url
     */
    function queryToPath(Ac_Url $url, $dontClone = false) {
        
        $this->lastMatch = null;
        $pathInfo = null;
        $newPath = null;
        
        if ($this->baseUrl) {
            if (!$url->hasBase($this->baseUrl, $pathInfo)) return $url;
        }
        if (!$dontClone) $url = clone $url;
        $params = $url->query;
        if (strlen($url->pathInfo)) {
            $params['__pathInfo__'] = $url->pathInfo;
        } elseif (strlen($pathInfo)) {
            $params['__pathInfo__'] = $pathInfo;
            $newPath = substr($url->path, 0, -strlen($pathInfo));
        }
        $newPathInfo = $this->moveParamsToString($params);
        if (is_null($newPathInfo)) return $url;
        if (isset($params['__pathInfo__'])) return $url; // path info wasn't used
        $url->query = $params;
        if ($newPath !== null) {
            $url->path = $newPath;
        }
        // ugly hack to get rid of double backslashes
        $url->setPathInfo($newPathInfo);
        return $url;
    }

    /**
     * Extracts 
     * 
     * @param string $url
     * @param array $extractedParams
     * @param bool $modifyUrl
     * 
     * @return string
     */
    function strPathToQuery($url, & $extractedParams = array()) {
        if (is_object($url) && $url instanceof Ac_Url) $urlObject = $url;
        else $urlObject = new Ac_Url($url);
        
        return ''.($this->pathToQuery($urlObject, $extractedParams));
    }
    
    /**
     * @param string $url
     * @return string
     */
    function strQueryToPath($url) {
        if (is_object($url) && $url instanceof Ac_Url) $urlObject = $url;
        else $urlObject = new Ac_Url($url);
        
        return ''.($this->queryToPath($urlObject));
    }

    function setBaseUrl($baseUrl) {
        if (is_string($baseUrl) || (is_object($baseUrl) && $baseUrl instanceof Ac_Url)) {
            $baseUrl = new Ac_Url($baseUrl);
        } elseif ($baseUrl === true) $baseUrl = Ac_Url::guessBase();
        elseif (!$baseUrl) $this->baseUrl = null;
        else throw Ac_E_InvalidCall::wrongType ('baseUrl', $baseUrl, array('string', 'Ac_Url', 'boolean', 'null'));
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return Ac_Url
     */
    function getBaseUrl() {
        return $this->baseUrl;
    }

    function setLeadingSlashHandling($leadingSlashHandling) {
        $this->leadingSlashHandling = $leadingSlashHandling;
    }

    function getLeadingSlashHandling() {
        return $this->leadingSlashHandling;
    }    
    
    protected function handleLeadlingSlash($string) {
        if ($this->leadingSlashHandling !== self::LEADING_SLASH_ANY) {
            $hasSlash = substr($string, 0, 1) == '/';
            if ($this->leadingSlashHandling === self::LEADING_SLASH_STRIP && $hasSlash) {
                $string = substr($string, 1);
            } elseif ($this->leadingSlashHandling === self::LEADING_SLASH_ADD && !$hasSlash) {
                $string = '/'.$string;
            }
        }
        return $string;
    }

    function setParentMapper(Ac_UrlMapper_UrlMapper $parentMapper = null) {
        $this->parentMapper = $parentMapper;
    }

    /**
     * @return Ac_UrlMapper_UrlMapper
     */
    function getParentMapper() {
        return $this->parentMapper;
    }    
    
}