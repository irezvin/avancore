<?php

class Ac_Template extends Ac_Prototyped {

    const ARG_AUTO = 'ARG_AUTO:bfbf4b77dc241c2a94d849a2b9863b3a';
    
    const ARG_DEFAULT = 'ARG_DEFAULT:36d82df14db770153cb56a4ccd5518e5';
    
    protected $values = array();
    
    protected $methodSignatures = array();
    
    /**
     * @var Ac_Result
     */
    protected $result = null;
    
    protected $defaultResultClass = 'Ac_Result_Html';
    
    protected $compatibleResultClasses = array('Ac_Result_Html');
    
    protected $component = false;
    
    protected $stack = array();
    
    protected $stackItems = array('wrap' => false);
        
    protected $wrap = false;
    
    protected $wrapTopLevel = true;
    
    protected $defaultWrapper = false;
    
    function setComponent($component = null) {
        $this->component = $component;
    }

    function getComponent() {
        return $this->component;
    }    
    
    function getDescription() {
        return get_class($this);
    }
    
    protected function incompatibleResultClass($class) {
        $descr = $this->getDescription();
        return new Ac_E_Template("Incompatible result class {$class} passed to template {$descr}. Check with isResultCompatible() first.");
    }
    
    protected function noSuchValue($name) {
        $descr = $this->getDescription();
        return new Ac_E_Template("No such value: '{$name}' in template {$descr}");
    }
    
    protected function noSuchPart($name) {
        $descr = $this->getDescription();
        return new Ac_E_Template("No such part: '{$name}' in template {$descr}");
    }
    
    protected function callFromTemplatePartOnly($method) {
        return new Ac_E_Template("Method {$method} can be called from a template part only");
    }
    
    protected static function describeArgs($signature) {
        $sigD = array();
        foreach ($signature as $paramName => $param) {
            $s = "\${$paramName}";
            if (strlen($param['class']))
                $s = $param['class'].' '.$s;
            elseif ($param['isArray']) $s = 'array '.$s;
            $sigD[] = $s;
        }
        $res = implode(", ", $sigD);
        return $res;
    }
    
    protected function noSuchArgument($methodName, $argument, array $signature) {
        $descr = $this->getDescription();
        $signs = self::describeArgs($signature);
        $cl = get_class($this);
        return new Ac_E_Template("No such argument: '{$argument}' in method {$cl}::{$methodName}({$signs}) of template '{$descr}'");
    }
    
    protected function missingArguments($methodName, array $missingArgs) {
        $descr = $this->getDescription();
        $signature = $this->getSignature($methodName);
        $missing = $this->getSignature(array_intersect_key($signature, array_flip($missingArgs)));
        $cl = get_class($this);
        return new Ac_E_Template("Missing argument(s) '{$missing}' in method {$cl}::{$methodName}({$signs}) of template '{$descr}'");
    }
    
    function getValue($name, $own = false) {
        if (array_key_exists($name, $this->values)) $res = $this->values[$name];
        elseif (!$own && $this->component && Ac_Accessor::objectPropertyExists($this->component, $name)) {
            $res = Ac_Accessor::getObjectProperty($this->component, $name);
        }
        else throw $this->noSuchValue($name);
        return $res;
    }
    
    function setValue($name, $value) {
        $this->values[$name] = $value;
    }
    
    function setValues(array $values = array(), $override = false) {
        if ($override) $this->values = array_merge($this->values, $values);
            else $this->values = $values;
    }
    
    function getValues() {
        return $this->values;
    }
    
    function deleteValue($name) {
        unset($this->values[$name]);
    }
    
    function hasValue($name, $own = false) {
        $res = false;
        if (array_key_exists($name, $this->values)) $res = true;
        elseif (!$own && $this->component && Ac_Accessor::objectPropertyExists($this->component, $name)) $res = true;
        return $res;
    }
    
    function __set($name, $value) {
        return $this->setValue($name, $value);
    }
    
    function __get($name) {
        return $this->getValue($name);
    }
    
    function __isset($name) {
        return $this->hasValue($name);
    }
    
    function __unset($name) {
        $this->deleteValue($name);
    }
    
    function __call($name, $args) {
        $px = substr($name, 0, 4);
        $show = $px == 'show';
        $fetch = $px == 'fetch';
        if ($show || $fetch) {
            $partName = substr($name, 4);
            if ($show) $this->showWithArgs($partName, $args);
                else $this->fetchWithArgs($partName, $args);
        }
    }
    
    protected function getSignature($methodName) {
        if (!isset($this->methodSignatures[$methodName])) {
            $this->methodSignatures[$methodName] = array();
            $m = new ReflectionMethod(get_class($this), $methodName);
            foreach ($m->getParameters() as $param) {
                $s = $param.'';
                $class = false;
                if (!$param->isArray()) {
                    $ss = explode(">", $s, 2);
                    $s1 = explode(" ", ltrim($ss[1], ' '), 2);
                    if ($s1[0]{0} !== '$') $class = $s1{0};
                }
                /* @var $param ReflectionParameter */
                $this->methodSignatures[$methodName][$param->getName()] = array(
                    'class' => $class,
                    'isArray' => $param->isArray(),
                    'optional' => $param->isOptional(),
                    'defaultValue' => $param->isOptional()? $param->getDefaultValue() : null,
                    'string' => $s,
                );
            }
        }
        return $this->methodSignatures[$methodName];
    }
    
    protected function invokeMethod($methodName, array $args) {
        ob_start();
        $this->push();
        $popped = false;
        try {
            if (count($this->stack) == 1 && $this->wrapTopLevel !== false) {
                $this->wrap($this->wrapTopLevel);
            }
            $args = $this->getArgs($methodName, $args, $missingArgs);
            if (count($missingArgs)) 
                throw $this->missingArguments ($methodName, $missingArgs);
            // TODO: load replacement file here (if any)
            call_user_func_array(array($this, $methodName), $args);
            $buffer = ob_get_clean();

            if ($this->wrap !== false) $buffer = $this->applyWrapper($buffer);
            
            $res = $buffer;
            $popped = true;
            $this->pop();
        } catch (Exception $e) {
            if (!$popped) $this->pop();
            throw $e;
        }
        return $res;
    }
    
    protected function applyWrapper($buffer) {
        if ($this->wrap === true) $wrap = $this->getDefaultWrapper();
        else $wrap = $this->wrap;
        if ($wrap !== false) {
            $buffer = $this->fetchWithArgs($wrap, array('buffer' => $buffer));
        }
        return $buffer;
    }
    
    function setWrapTopLevel($wrapTopLevel) {
        if (!is_bool($wrapTopLevel) && !$this->hasPart($wrapTopLevel)) {
            throw $this->noSuchPart($wrapTopLevel);
        }
        $this->wrapTopLevel = $wrapTopLevel;
    }

    function getWrapTopLevel() {
        return $this->wrapTopLevel;
    }
    
    function setDefaultWrapper($defaultWrapper) {
        if ($defaultWrapper !== false && !$this->hasPart($defaultWrapper)) {
            throw $this->noSuchPart($defaultWrapper);
        }
        $this->defaultWrapper = $defaultWrapper;
    }

    function getDefaultWrapper() {
        return $this->defaultWrapper;
    }
    
    protected function wrap($wrapper = true) {
        if (!count($this->stack)) throw $this->callFromTemplatePartOnly(__METHOD__);
        if (!is_bool($wrapper) && !$this->hasPart($wrapper)) 
            throw $this->noSuchPart ($wrapper);
        $this->wrap = $wrapper;
    }
    
    protected function dontWrap() {
        if (!count($this->stack)) throw $this->callFromTemplatePartOnly(__METHOD__);
        $this->wrap = false;
    }
    
    /**
     * @return array
     */
    protected function getArgs($methodName, array $args, & $missingArgs = array()) {
        $sig = $this->getSignature($methodName);
        $missingArgs = array();
        if (count($sig) || count($args)) {
            $res = array();
            $missingArgs = $names = array_keys($sig); // 0 => argName0, 1 => argName1...
            $indexes = array_flip($names); // argName0 => 0, argName1 => 1...
            $def = array();
            foreach ($args as $k => $v) {
                if (is_numeric($k)) { // it's a positional argument
                    $idx = $k;
                } else {
                    if (isset($indexes[$k])) {
                        $idx = $indexes[$k];
                    } else {
                        throw $this->noSuchArgument($methodName, $k, $sig);
                    }
                }
                if ($v === self::ARG_AUTO) continue;
                if ($v === self::ARG_DEFAULT) {
                    $def[$names[$idx]] = true;
                }
                $res[$idx] = $v;
                unset($missingArgs[$idx]); 
            }
            foreach ($missingArgs as $idx => $argName) {
                $hasVal = $sig[$argName]['optional'];
                $val = $sig[$argName]['defaultValue'];
                if (!isset($def[$argName])) {
                    if ($this->hasValue($argName)) {
                        $val = $this->getValue($argName);
                        if ($sig[$argName]['isArray'] && is_array($val)) $hasVal = true;
                        elseif ($sig[$argName]['class'] !== false 
                            && (
                                is_object($val) 
                                && $val instanceof $sig[$argName]['class']
                            )
                        ) $hasVal = true;
                    }
                }
                if ($hasVal) {
                    $res[$idx] = $val;
                    unset($missingArgs[$idx]);
                }
            }
            ksort($res);
        } else {
            $res = array();
        }
        return $res;
    }
    
    function hasPart($partName) {
        $res = method_exists($this, 'part'.$partName);
        return $res;
    }
    
    function listParts() {
        $res = preg_grep('/part/', get_class_methods(get_class($this)));
        return $res;
    }
    
    protected function fetchWithArgs($partName, array $args) {
        $methodName = 'part'.$partName;
        if (method_exists($this, $methodName)) {
            $res = $this->invokeMethod($methodName, $args);
        } else {
            throw $this->noSuchPart($partName);
        }
        return $res;
    }
    
    protected function showWithArgs($partName, array $args) {
        echo $this->fetchWithArgs($partName, $args);
    }
    
    function fetch($partName, $_ = null) {
        $args = func_get_args();
        array_shift($args); 
        return $this->fetchWithArgs($partName, $args);
    }
    
    function show($partName, $_ = null) {
        $args = func_get_args();
        array_shift($args); 
        return $this->showWithArgs($partName, $args);
    }
    
    function renderResult($partName, $_ = null) {
        $this->result = new $this->defaultResultClass;
        $args = func_get_args();
        array_shift($args); 
        $this->result->put($this->fetchWithArgs($partName, $args));
        return $this->result;
    }
    
    function renderResultWithArgs($partName, array $args = array()) {
        $this->result = new $this->defaultResultClass;
        $this->result->put($this->fetchWithArgs($partName, $args));
        return $this->result;
    }
    
    protected function push() {
        $s = array();
        foreach ($this->stackItems as $varName => $default) {
            $s[$varName] = $this->$varName;
            $this->$varName = $default;
        }
        $this->stack[] = $s;
    }
    
    protected function pop() {
        if (!count($this->stack)) throw new Exception("Cannot pop(): is at top");
        foreach (array_pop($this->stack) as $i => $v) {
            $this->$i = $v;
        }
    }
    
    function getDefaultResultClass() {
        return $this->defaultResultClass;
    }
    
    function getCompatibleResultClasses() {
        return $this->compatibleResultClasses;
    }
    
    function isResultCompatible(Ac_Result $result) {
        $res = true;
        if (count($this->compatibleResultClasses)) {
            $res = false;
            foreach ($this->compatibleResultClasses as $rc) {
                if ($result instanceof $rc) {
                    $res = true;
                    break;
                }
            }
        }
        return $res;
    }
 
    /**
     * @return Ac_Result
     */
    function renderTo(Ac_Result $result, $partName, array $args = array()) {
        if ($this->isResultCompatible($result)) {
            $this->result = $result;
            $this->result->put($a = $this->fetchWithArgs($partName, $args));
            return $this->result;
        } else {
            throw $this->incompatibleResultClass(get_class($result));
        }
    }
    
}