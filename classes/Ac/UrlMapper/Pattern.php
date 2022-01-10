<?php

class Ac_UrlMapper_Pattern extends Ac_Prototyped {
    
    const PARSE_RX = '/([^{}]+)|[{}]/u';
    
    const SEG_TYPE_PARAM = 'param';
    
    const SEG_TYPE_STRING = 'string';

    protected $definition = null;
    
    protected $regex = null;
    
    protected $params = [];
    
    protected $segments = [];
    
    protected $const = null;
    
    function __construct(array $prototype = array()) {
        parent::__construct($prototype);
        if ($this->definition === null) throw new Ac_E_InvalidCall("\$options['definition'] is required");
    }
    
    /**
     * @param array $const
     * 
     * $const property is used to add "invisible" parameters to the pattern.
     * 
     * These values will be returned by stringToParams($string) if $string matches pattern;
     * and they required to be present with same values in $params argument of paramsToString() 
     * or moveParamsToString() to make reverse mapping work.
     * 
     * Example:
     * 
     * $p = new Ac_UrlMapper_Pattern([
     *      'definition' => '/list', 
     *      'const' => ['controllerName' => 'listController', 'action' => 'list']
     * ]);
     * var_dump($p->stringToParams('/list')); // ['controllerName' => 'listController', 'action' => 'list']
     * var_dump($p->paramsToString(['controllerName' => 'listController', 'action' => 'list']); // '/list'
     * var_dump($p->paramsToString(['controllerName' => 'otherController', 'action' => 'list']); // null
     * var_dump($p->paramsToString(['action' => 'list']); // null
     */
    function setConst(array $const = array()) {
        if ($this->const !== null) throw new Exception("can setConst() only once");
        $this->const = $const;
        foreach ($this->const as $k => $v) $this->params[$k] = false;
    }
    
    /**
     * @return array|null
     */
    function getConst() {
        if ($this->const === null) return array();
        return $this->const;
    }
    
    function isConstSet() {
        return $this->const === null;
    }

    /**
     * Definition format:
     * 
     * pathSegment/otherPathSegment/{paramName}/etc/{?'paramName'regex}.html{?c}
     * 
     * {paramName} - requires value consiting of "word" (\w) characters only (non-empty)
     * {?'paramName'regex} - requires parameter that must match regular expression (delimiter is '~')
     * {?c} - means previous string segement is optional, but will present in 'canonical' version of URL,
     *       which is produced by paramsToString() or moveParamsToString()
     * {?nc} - means previous string segment is optional and will be omitted from 'canonical' version or URL
     * {} - may be used to split string segments, i.e. foo{}/{?nc} will mean that {?nc} is related to "/" only
     */    
    function setDefinition($definition) {
        if ($this->definition !== null)
            throw new Ac_E_InvalidUsage("Can setDefinition() only once");
        
        if (!is_string($definition))
            throw Ac_E_InvalidCall::wrongType ('definition', $definition, 'string');
        
        $this->definition = $definition;
        
        $this->parseDefinition();
    }
    
    function getDefinition() {
        return $this->definition;
    }
    
    protected function parseDefinition() {
        $definition = $this->definition;
        if (!strlen($definition)) $matches = array(array(''));
        else if (!preg_match_all(self::PARSE_RX, $definition, $matches)) {
            throw new Ac_E_InvalidCall("Invalid UrlMapper_Pattern definition: '{$definition}'");
        }
        $matches = $matches[0];
        if (implode("", $matches) !== $definition) {
            throw new Ac_E_InvalidCall("UrlMapper_Pattern definition '{$definition}' wasn't fully parsed");
        }
        
        $currParam = null;
        $pos = 0;
        $paramLevel = 0;
        $currParam = '';
        while (($curr = array_shift($matches)) !== null) {
            $len = strlen($curr);
            if ($curr === '{') {
                $paramLevel++;
                if ($paramLevel == 1) $currParam = '';
            }
            elseif ($curr === '}') {
                if (!$paramLevel) throw Ac_E_InvalidCall("Unmatched '}' at position {$pos} of definition '{$definition}'");
                $paramLevel--;
                if (!$paramLevel) $this->parseParam($currParam, $pos, $definition);
            } else {
                if ($paramLevel) $currParam .= $curr;
                else $this->segments[] = $curr;
            }
            $pos += $len;
        }
        $this->regex = $this->buildRegex();
        //var_dump($this->definition, $this->segments, $this->params, $this->regex);
    }
    
    protected function parseParam($currParam, $pos, $definition) {
        if (!strlen($currParam)) { // this is empty separator of segments
            return;
        }
        if ($currParam === '...') {
            $currParam = "?'__pathInfo__'.*"; // support "..." - optional pathInfo
        } else if ($currParam === '+++') {
            $currParam = "?'__pathInfo__'.+"; // support "+++" - mandatory pathInfo
        }
        if ($currParam == '?c' || $currParam == '?nc') { // previous segment was optional string that is required in canonical version
            $l = count($this->segments);
            if (!$l || is_array($this->segments[$l - 1])) {
                throw Ac_E_InvalidCall("{{$currParam}} must follow string segment (position {$pos} of definition '{$definition}')");
            }
            $lastSegment = array_pop($this->segments);
            $this->segments[] = ['type' => self::SEG_TYPE_STRING, 'string' => $lastSegment, 'canonical' => $currParam == '?c'];
            return;
        }
        if ($currParam[0] == '?' && $currParam[1] == "'") { // this is pattern-based param
            $paramPattern = explode("'", substr($currParam, 2));
            if (count($paramPattern) < 2) 
                throw new Ac_E_InvalidCall("Unmatched single quote in pattern-based param definition"
                    . " (position {$pos} of definition '{$definition}'); correct format: {?'param'pattern}");
            $paramName = $paramPattern[0];
            if (!strlen($paramName)) {
                throw new Ac_E_InvalidCall("Paramument name is required (position {$pos} of definition '{$definition}')");
            }
            $regex = $paramPattern[1];
            if (!strlen($regex)) {
                throw new Ac_E_InvalidCall("Pattern required (position {$pos} of definition '{$definition}')");
            }
            $this->segments[] = ['type' => self::SEG_TYPE_PARAM, 'name' => $paramName, 'regex' => $regex];
            $this->params[$paramName] = '~^'.$regex.'$~u';
            return;
        }
        $paramName = $currParam;
        $this->params[$paramName] = true;
        $this->segments[] = ['type' => self::SEG_TYPE_PARAM, 'name' => $paramName, 'regex' => null];
    }
    
    protected function buildRegex() {
        $res = '~^';
        foreach ($this->segments as $segment) {
            // a - const
            // b - optional param
            // c - param
            if (is_string($segment)) {
                $res .= preg_quote($segment, '~');
                continue;
            }
            if ($segment['type'] === self::SEG_TYPE_PARAM) $res .= $this->createParamRegex($segment);
            else if ($segment['type'] === self::SEG_TYPE_STRING) $res .= $this->createStringRegex($segment);
            else throw new Exception("assertion: unknown segment type");
        }
        $res .= '$~u';
        return $res;
    }
    
    protected function createParamRegex($segment) {
        if ($segment['regex']) $rx = $segment['regex'];
        else $rx = '[-\\w]+';
        return "(?'{$segment['name']}'{$rx})";
    }
    
    protected function createStringRegex($segment) {
        return "(?:".preg_quote($segment['string'], '~').')?';
    }
    
    /**
     * Tries to parse path. If path matches definition of the pattern, returns
     * associative array with paramument values. Otherwise returns NULL
     * 
     * @return mixed array|null
     */
    function stringToParams($path) {
        if (!preg_match($this->regex, $path, $matches)) return null;
        $res = [];
        if ($this->const) $res = $this->const;
        foreach ($this->params as $param => $condition) {
            if ($condition === false) continue; // this is 'const' param
            $res[$param] = $matches[$param];
        }
        return $res;
    }
    
    function paramsToString(array $params) {
        return $this->moveParamsToString($params);
    }
    
    function moveParamsToString(array & $params) {
        foreach ($this->params as $param => $condition) {
            // missing paramument
            if (is_string($condition)) { // check that param matches regex
                if (!array_key_exists($param, $params)) return;
                if (!preg_match($condition, $params[$param])) return;
            } elseif ($condition === true) {
                if (!array_key_exists($param, $params)) return;
                if (!strlen($params[$param])) return;
            } elseif ($condition === false) {
                if ($this->const[$param] === null) {
                     if (isset($params[$param]) && strlen($params[$param])) return;
                } else {
                    if (!array_key_exists($param, $params)) return;
                    if ((string) $params[$param] !== (string) $this->const[$param]) return;
                }
            }
        }
        $res = '';
        foreach ($this->segments as $seg) {
            if (!is_array($seg)) {
                $res .= $seg;
                continue;
            }
            if ($seg['type'] === self::SEG_TYPE_STRING) {
                if ($seg['canonical']) $res .= $seg['string'];
                continue;
            }
            if ($seg['type'] === self::SEG_TYPE_PARAM) {
                $res .= $params[$seg['name']];
                unset($params[$seg['name']]);
                continue;
            }
            throw new Exception("Assertion: unknown segement type");
        }
        if ($this->const) foreach ($this->const as $name => $value) unset($params[$name]);
        return $res;
    }
    
    function getRegex() {
        return $this->regex;
    }
    
    function getParams() {
        return array_keys($this->params);
    }
    
}