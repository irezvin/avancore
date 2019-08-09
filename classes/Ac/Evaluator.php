<?php

class Ac_Evaluator {
    
    const recurseNone = 'recurseNone';
    const recurseBefore = 'recurseBefore';
    const recurseAfter = 'recurseAfter';
    
    protected $supportedClasses = array();
    
    protected $recurseMode = Ac_Evaluator::recurseNone;
    
    protected $contexts = array();
    
    protected $childEvaluators = array();
    
    protected $parentEvaluator = null;
    
    protected $cacheProperties = array();
    
    protected $ignoreCache = false;

    /**
     * Will ignore the cached values even if works with Ac_I_EvaluationContext_WithCache
     * @param bool $ignoreCache 
     */
    function setIgnoreCache($ignoreCache) {
        $this->ignoreCache = (bool) $ignoreCache;
    }

    function getIgnoreCache() {
        return $this->ignoreCache;
    }
    
    static function getArrayHash(array $arr) {
        return md5(serialize(self::safeCloneArray($arr)));
    }
    
    /**
     * Creates semi-clone array $arr suitable for checksum calculation,
     * tries to be recursion-safe, replaces objects with their UIDs
     * 
     * @param array $arr
     * @return array
     */
    protected static function safeCloneArray($arr) {
        $res = array();
        if (isset($arr['___safeLock'])) return null;
        $arr['___safeLock'] = true;
        foreach ($arr as $k => $v) {
            if (is_object($v)) $v = spl_object_hash($v);
            elseif (is_resource($v)) $v = '*resource*';
            elseif (is_array($v)) $v = self::safeCloneArray($v);
            $res[$k] = $v;
        }
        unset($arr['__safeLock']);
        return $res;
    }
    
    function setParentEvaluator(Ac_Evaluator $parentEvaluator) {
        if ($this->parentEvaluator) throw Ac_E_InvalidCall::canRunMethodOnce ($this, __FUNCTION__);
        $this->parentEvaluator = $parentEvaluator;
    }

    /**
     * @return Ac_Evaluator
     */
    function getParentEvaluator() {
        return $this->parentEvaluator;
    }    
    
    /**
     * @param array $prototype
     * @return Ac_I_Evaluator
     */
    protected function getChildEvaluator(array $prototype, & $hash) {
        $hash = self::getArrayHash($prototype);
        if (!isset($this->childEvaluators[$hash])) 
            $this->childEvaluators[$hash] = Ac_Prototyped::factory (array_merge($prototype, array('parentEvaluator' => $this)), 'Ac_Evaluator');
        return $this->childEvaluators[$hash];
    }
    
    /**
     * @return Ac_I_EvaluationContext 
     */
    function getCurrentContext() {
        $res = null;
        if ($c = count($this->contexts)) $res = $this->contexts[$c - 1];
        return $res;
    }

    function setCacheProperties(array $cacheProperties) {
        $this->cacheProperties = $cacheProperties;
    }

    function getCacheProperties() {
        return $this->cacheProperties;
    }
    
    function setRecurseMode($recurseMode) {
        if (!in_array($recurseMode, $a = array(self::recurseNone, self::recurseBefore, self::recurseAfter))) {
            throw Ac_E_InvalidCall::outOfSet('recurseMode', $recurseMode, $a);
        }
        $this->recurseMode = $recurseMode;
    }

    function getRecurseMode() {
        return $this->recurseMode;
    }
    
    function setSupportedClasses($supportedClasses) {
        if (!(
            is_string($supportedClasses) 
            || is_array($supportedClasses) 
            || $supportedClasses === false
            || is_null($supportedClasses)
        )) throw Ac_E_InvalidCall::wrongType ('supportedClasses', $supportedClasses, array('string', 'array', 'null', 'false'));
        $this->supportedClasses = Ac_Util::toArray($supportedClasses);
    }

    function getSupportedClasses() {
        return $this->supportedClasses;
    }
    
    function supports(Ac_I_EvaluatedObject $object) {
        if ($this->supportedClasses) {
            $res = false;
            foreach($this->supportedClasses as $sc) {
                if ($object instanceof $sc) {
                    $res = true;
                    break;
                }
            }
        } else {
            $res = true;
        }
        return $res;
    }
    
    function evaluateContext (Ac_I_EvaluationContext $context) {
        $objects = $context->getEvaluatedObjects();
        return $this->evaluate($objects, $context);
    }
    
    protected function makeResult($object, $key, $result) {
        return array('object' => $object, 'key' => $key, 'result' => $result);
    }
    
    protected function makeSubResult($key, array $subResult) {
        $res = array();
        if (!is_array($key)) $key = array($key);
        foreach ($subResult as $v) {
            if (!is_array($v['key'])) $v['key'] = array($v['key']);
            $v['key'] = array_merge($key, $v['key']);
            $res[] = $v;
        }
        return $res;
    }
    
    protected function evaluateManyContexts(array $contexts) {
        $res = array();
        foreach ($contexts as $key => $context) {
            $res = array_merge($res, $this->makeSubResult($key, $this->evaluateContext($context)));
        }
        return $res;
    }
    
    /**
     * Will return evaluation results for given objects.
     * If the context provides caching (Ac_I_EvaluationContext_WithCache) will
     * return cached values (if there are any) in case when $this->ignoreCache==false
     * 
     * @param array $objects
     * @param Ac_I_EvaluationContext $context
     * @return array(array('object' => $object, 'key' => $key, 'result' => $result))
     */
    function evaluate(array $objects, Ac_I_EvaluationContext $context = null) {
        if ($context) {
            $pc = $this->getCurrentContext() !== $context;
            if ($pc) array_push($this->contexts, $context);
            if ($context instanceof Ac_I_EvaluationContext_Mutable) $context->notifyEvaluationBegin($this);
        }
        $toDive = array();
        $toEval = array();
        foreach ($objects as $key => $obj) {
            if ($this->supports($obj)) $toEval[$key] = $obj;
            elseif ($this->recurseMode !== self::recurseNone && $obj instanceof Ac_I_EvaluationContext) {
                $toDive[$key] = $obj;
            }
        }
        $res = array();
        
        if ($toDive && $this->recurseMode == self::recurseBefore)
            $res = array_merge($res, $this->evaluateManyContexts($toDive));
        
        if ($toEval) {
            $results = array();
            $map = $this->dispatch($toEval, $context);
            foreach ($map as $hash => $objects) {
                if (strlen($hash)) $ev = $this->childEvaluators[$hash];
                else $ev = $this;
                $evRes = $ev->evaluateWithOptionalCache($objects, $context);
                foreach ($evRes as $key => $result)
                    $res[] = $this->makeResult($toEval[$key], $key, $result);
            }
        }
        
        if ($toDive && $this->recurseMode == self::recurseAfter)
            $res = array_merge($res, $this->evaluateManyContexts($toDive));
        
        if ($context) {
            if ($context instanceof Ac_I_EvaluationContext_Mutable) {
                $context->notifyEvaluationEnd($this, $newObjects);
                if ($newObjects) {
                    // Perform additional evaluation since the context was changed
                    $res = array_merge($res, $this->evaluate($newObjects, $context));
                }
            }
            if ($pc) array_pop($this->contexts);
        }
        return $res;
    }
    
    /**
     * @param array 'hash'|'' => array(objects)
     * @param type $context 
     */
    protected function dispatch(array $objects, $context) {
        $map = array();
        foreach ($objects as $k => $obj) {
            if ($obj instanceof Ac_I_EvaluatedObject_WithSuggestion) {
                if (!is_null($proto = $obj->suggestEvaluatorPrototype($this))) {
                    if (!is_array($proto)) throw new Exception("Ac_I_EvaluatedObject_WithSuggestion::suggestEvaluatorPrototype should return either NULL or an array");
                    $this->getChildEvaluator($proto, $hash);
                    $map[$hash][$k] = $obj;
                } else {
                    $map[''][$k] = $obj;
                }
            } else {
                $map[''][$k] = $obj;
            }
        }
        return $map;
    }
    
    /**
     * If no caching support detected, will pass execution directly to doGetEvaluationResults
     * Otherwise
     * -    detects objects that are not in the cache
     * -    if there are any non-cached results:
     *      -   calls doGetEvaluationResults for them 
     *      -   puts them to the cache
     * -    returns the merged (cached and just evaluated) results
     * 
     * @param array $objects
     * @param Ac_I_EvaluationContext $context 
     */
    protected function evaluateWithOptionalCache(array $objects, Ac_I_EvaluationContext $context = null) {
        
        if ($context && $context instanceof Ac_I_EvaluationContext_WithCache && !$this->ignoreCache) {
            
            $groupId = $context->getEvaluationGroupData($this);
            
            if ($groupId !== null) {

                if (is_array($groupId)) $groupId = self::getArrayHash ($groupId);

                $keys = array_keys($objects);

                $res = array();

                // This is done to ensure same key order independent of cache usage
                foreach ($keys as $k) $res[$k] = null;


                $readyResults = $context->getEvaluationResults($groupId, $keys);
                foreach ($readyResults as $k => $r) {
                    $res[$k] = $r;
                }
                $toCalc = array_diff_key($objects, $readyResults);
                if ($toCalc) {
                    $newResults = $this->doGetEvaluationResults($toCalc, $context);
                    foreach ($newResults as $k => $r) $res[$k] = $r;
                    $context->setEvaluationResults($groupId, $newResults);
                }
            }
            
        } 
        if (!isset($res)) $res = $this->doGetEvaluationResults ($objects, $context);
        return $res;
    }
    
    /**
     * Should return same keys as in $objects
     * 
     * @return array $key => $rawResult
     * 
     * @param array $objects
     * @param Ac_I_EvaluationContext $context
     */
    protected function doGetEvaluationResults(array $objects, Ac_I_EvaluationContext $context = null) {
    }
    
    protected function getPrototype() {
        return array(
            'class' => get_class($this),
            'recurseMode' => $this->getRecurseMode(),
            'supportedClasses' => $this->getSupportedClasses(),
        );
    }
    
    function assignTo(Ac_Evaluator $evaluator) {
        $this->recurseMode = $evaluator->recurseMode;
        $this->supportedClasses = $evaluator->supportedClasses;
        $this->contexts = $evaluator->contexts;
        $this->ignoreCache = $evaluator->ignoreCache;
        $this->cacheProperties = $evaluator->cacheProperties;
    }
    
    static function getPrototypeDefaults() {
        return array_intersect_key(
            get_class_vars(__CLASS__), 
            array_flip(array('recurseMode', 'supportedClasses', 'ignoreCache', 'cacheProperties')
        ));
    }
    
    function matchesPrototype(array $prototype) {
        if (isset($prototype['class']) && strlen($prototype['class'])) {
            if (is_subclass_of($prototype['class'], 'Ac_Evaluator')) {
                $pd = call_user_func(array($prototype['class'], 'getPrototypeDefaults'));
                $prototype = Ac_Util::m($pd, $prototype);
            }
        }
        return !array_diff_assoc($prototype, $this->getPrototype());
    }

    /**
     * Should return data used to calculate cacheGroupId 
     * Must be called by Ac_I_EvaluationContext_WithCache
     * @return array
     */
    function getCacheGroupData() {
        $res = array(
            'class' => get_class($this),
            'recurseMode' => $this->recurseMode,
        );
        if ($this->cacheProperties) 
            $res = array_merge($res, Ac_Accessor::getObjectProperty ($this, $this->cacheProperties));
        return $res;
    }
    
}