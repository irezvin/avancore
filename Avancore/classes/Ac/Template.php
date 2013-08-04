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
    
    protected $component = false;

    function setComponent($component = null) {
        $this->component = $component;
    }

    function getComponent() {
        return $this->component;
    }    
    
    function getDescription() {
        return get_class($this);
    }
    
    protected function setlue($name) {
        $descr = $this->getDescription();
        return new Ac_E_Template("No such value: '{$name}' in template {$descr}");
    }
    
    protected function noSuchPart($name) {
        $descr = $this->getDescription();
        return new Ac_E_Template("No such part: '{$name}' in template {$descr}");
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
        // TODO: push current wrapper and other in-method context
        $args = $this->getArgs($methodName, $args, $missingArgs);
        if (count($missingArgs)) 
            throw $this->missingArguments ($methodName, $missingArgs);
        // TODO: load replacement file here (if any)
        call_user_func_array(array($this, $methodName), $args);
        $buffer = ob_get_clean();
        
        // TODO: pop the context, apply wrapper
        $res = $buffer;
        return $res;
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
    
}