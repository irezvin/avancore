<?php

/**
 * Parses signatures of controller methods to create patterns like /method/arg1/arg2/
 * (but with support of optional arguments that have default values)
 * 
 * executeFoo($a, $b = 10) will create two patterns:
 * /{?'action'foo}/{a}/{?c} with constant b => null (b has default and can be omitted)
 * /{?'action'foo}/{a}/{b}/{?c}
 * 
 * $controllerClass is class of the controller 
 * (will NOT work properly with mixins that add more methods, therefore *Static*Signatures)
 * 
 * $methodParamName is argument that provides method
 * 
 * Custom patterns may be provided with setPatterns(). They will be added to generated patterns,
 * but returned only by getCustomPatterns(), because getPatterns() returns both custom and generated patterns.
 * 
 * setPatterns() will modify or replace only custom patterns list.
 */
class Ac_UrlMapper_StaticSignatures extends Ac_UrlMapper_UrlMapper {
    
    protected $controllerClass = false;

    protected $suffix = '/{?c}';
    
    protected $methodParamName = 'action';
    
    /**
     * @var array
     */
    protected $ignoreMethods = array();

    function setControllerClass($controllerClass) {
        if ($this->controllerClass === $controllerClass) return;
        $this->controllerClass = $controllerClass;
        $this->patterns = false;
    }

    function getControllerClass() {
        return $this->controllerClass;
    }

    function setSuffix($suffix) {
        if ($this->suffix === $suffix) return;
        $this->suffix = $suffix;
        $this->patterns = false;
    }

    function getSuffix() {
        return $this->suffix;
    }

    function setMethodParamName($methodParamName) {
        if ($this->methodParamName === $methodParamName) return;
        $this->methodParamName = $methodParamName;
        $this->patterns = false;
    }

    function getMethodParamName() {
        return $this->methodParamName;
    }    
    
    protected $customPatterns = array();
    
    function setPatterns(array $patterns = array(), $overwrite = false) {
        $this->patterns = false;
        if ($overwrite) $this->customPatterns = $patterns;
        else Ac_Util::ms($this->customPatterns, $patterns);
    }
    
    /**
     * @return array
     */
    function getCustomPatterns() {
        return $this->customPatterns();
    }
    
    protected function rebuildPatterns() {
        $patterns = $this->customPatterns;
        if ($this->controllerClass) $patterns = array_merge($patterns, $this->calcAllPatterns());
        parent::setPatterns($patterns);
    }
    
    protected function calcAllPatterns() {
        $patterns = array();
        $l = strlen($s = 'execute');
        foreach (get_class_methods($this->controllerClass) as $m) {
            if (strncmp($m, $s, $l)) continue;
            $methodValue = substr($m, $l);
            if (strlen($methodValue)) $methodValue[0] = strtolower($methodValue[0]);
            $patterns = array_merge($patterns, $this->calcMethodPatterns($methodValue));
        }
        return $patterns;
    }
    
    protected function getArgInfo($methodName, & $signature = null) {
        $signature = Ac_Accessor::getMethodSignature($this->controllerClass, $methodName);
        $sig = array_values($signature);
        $c = count($sig);
        $res = array();
        while ($c && $sig[$c - 1]['optional']) {
            $res[$sig[$c - 1]['name']] = false;
            $c--;
        }
        while ($c) {
            $res[$sig[$c - 1]['name']] = true;
            $c--;
        }
        $res = array_reverse($res, true);
        return $res;
    }
    
    protected function makePatternDefinition($path, array $const = array()) {
        if (substr($path, -1)) $path = substr($path, 0, -1) . $this->suffix;
        if ($const) $res = array('definition' => $path, 'const' => $const);
        else $res = $path;
        return $res;
    }
    
    protected function calcMethodPatterns($methodValue) {
        $methodParamName = $this->methodParamName;
        $methodName = 'execute'.ucfirst($methodValue);
        $args = $this->getArgInfo($methodName);
        if (in_array($methodName, $this->ignoreMethods) || in_array($methodValue, $this->ignoreMethods)) {
            return array();
        }
        $path = array();
        $opt = array();
        $const = array();
        if (strlen($methodValue)) $path[] = "/{?'{$methodParamName}'{$methodValue}}/";
        else {
            $path[] = "/";
            $const[$methodParamName] = null;
        }
        foreach ($args as $name => $required) {
            if ($required) $path[] = "{{$name}}/";
            else $opt[$name] = "{{$name}}/";
        }
        $res = array();
        if (count($path)) {
            $p = implode("", $path);
            $res[$p] = $this->makePatternDefinition($p, $const);
        }
        $optNames = array_keys($opt);
        $n = count($opt);
        $nulls = array_fill_keys($optNames, null);
        for ($i = 0; $i < $n + 1; $i++) {
            $newPath = implode("", array_merge($path, array_slice($opt, 0, $i)));
            $newConst = array_merge($const, $s = array_slice($nulls, $i, $n, true));
            $res[$newPath] = $this->makePatternDefinition($newPath, $newConst);
        }
        return $res;
    }    

    /**
     * @param array $ignoreMethods Names of methods for which we don't want to create rules
     * (with prefixed 'execute' or without)
     */
    function setIgnoreMethods(array $ignoreMethods) {
        $this->ignoreMethods = $ignoreMethods;
        $this->patterns = false;
    }

    /**
     * @return array
     */
    function getIgnoreMethods() {
        return $this->ignoreMethods;
    }    

    
}