<?php

class Ac_Param extends Ac_Prototyped implements Ac_I_Param_WithSource {
    
    /**
     * @var Ac_I_Param_Source
     */
    protected $source = null;
    
    /**
     * @var Ac_I_Param_Destination
     */
    protected $destination = null;
    
    protected $conditions = array();
    
    protected $filters = array();
    
    protected $default = null;
    
    protected $noDefault = true;
    
    /**
     * @var Ac_Param_Parent
     */
    protected $parent = null;
    
    protected $id = false;
    
    protected $processed = false;
    
    protected $hasValue = false;
    
    protected $value = null;
    
    protected $errors = array();
    
    protected $path = false;
    
    protected $destPath = false;
    
    protected $actualSrc = null;
    
    protected $actualDest = null;
    
    protected $actualSrcPath = null;

    protected $actualDestPath = null;
    
    protected $evaluateAllConditions = true;
    
    protected $nullIsNoValue = false;
    
    // --------------- Utility static functions --------------
    
    static function applyConditionsAndFilters($value, array $conditions, array $filters, & $ok, Ac_I_Param $param = null, array & $errors = array(), 
        $evaluateAllConditions = false) {

            $finalFilters = array();
            foreach ($filters as $filter) 
                if (!$filter->getIsFilterFinal()) 
                    $value = $filter->filter($value, $param);
                else
                  $finalFilters[] = $filter;
            
            if (!is_array($errors)) $errors = array();
            
            $cond = array_values($conditions);
            $k = array_keys($conditions);
            $ok = true;

            $c = count($cond);
            
            for ($i = 0; ($i < $c) && ($ok || $evaluateAllConditions); $i++) {
                $e = array();
                $propName = $cond[$i]->getPropName();
                if (strlen($propName)) $propVal = $param? Ac_Accessor::getObjectProperty($param, $cond[$c]->getPropName()) : null;  
                    else $propVal = $value;
                $ok = $cond[$i]->match($propVal, $e, $param);
                if ($e) {
                    $tmp = array($k[$i] => $e);
                    Ac_Util::ms($errors, $tmp);
                }
            }
            
            if ($ok) foreach ($finalFilters as $filter) 
                $value = $filter->filter($param);
                
            return $value;
    }
    
    // ---------------- Setup-related functions --------------
    
    function setConditions(array $conditions) {
        $this->conditions = Ac_Prototyped::factoryCollection($conditions, 'Ac_I_Param_Condition');
    }
    
    function getConditions() {
        return $this->conditions;
    }

    function setFilters(array $filters) {
        $this->filters = Ac_Prototyped::factoryCollection($filters, 'Ac_I_Param_Filter');
    }
    
    function getFilters() {
        return $this->filters;
    }
    
    function setEvaluateAllConditions($evaluateAllConditions) {
        $this->evaluateAllConditions = $evaluateAllConditions;
    }
    
    function getEvaluateAllConditions() {
        return $this->evaluateAllConditions;
    }

    function getNoDefault() {
        return $this->noDefault;
    }
    
    function setNoDefault($noDefault) {
        $this->noDefault = (bool) $noDefault;
    }
    
    function getDefault() {
        return $this->default;
    }
    
    function setDefault($default) {
        $this->default = $default;
        $this->noDefault = false;
    }
    
    function setPath($path) {
        if (!is_array($path) && $path !== false) $path = Ac_Util::toArray($path);
        $this->path = $path;
    }
    
    function getPath() {
        return $this->path;
    }
    
    function setDestPath($path) {
        if (!is_array($path) && $path !== false) $path = Ac_Util::toArray($path);
        $this->destPath = $path;
    }
    
    function getDestPath() {
        return $this->destPath;
    }
    
    function getId() {
        return $this->id;
    }
    
    function setId($id) {
        $oldId = $this->id;
        if ($oldId !== $id) {
            $this->id = $id;
            if ($this->parent)
                $this->parent->renameParam($oldId, $id, $this);
        }
    }
    
    /**
     * @return Ac_I_Param_Source
     */
    function getSource() {
        return $this->source;
    }
    
    function setSource(Ac_I_Param_Source $source = null) {
        $this->source = $source;
    }
    
	/**
     * @return Ac_I_Param_Destination
     */
    function getDestination() {
        return $this->destination;
    }
    
    function setDestination(Ac_I_Param_Destination $destination = null) {
        $this->destination = $destination;
    }

    function setNullIsNoValue($nullIsNoValue) {
        $this->nullIsNoValue = $nullIsNoValue;
    }

    function getNullIsNoValue() {
        return $this->nullIsNoValue;
    }
    
    /**
	 * @return Ac_Param_Parent
     */
    function getParent() {
        return $this->parent;
    }
    
    function setParent(Ac_Param_Parent $parent = null) {
        if ($this->parent !== $parent) {
            if ($tmp = $this->parent) {
                $this->parent = null;
                $tmp->removeParam($this);
            }
            $this->parent = $parent;
            if ($parent && !$this->parent->hasParam($this)) $this->parent->addParam($this);
        }
    }
    
    function getIdPath() {
        $parents = Ac_Accessor::getAllParents($this, 'parent', true, true);
        $res = implode('.', Ac_Accessor::getObjectProperty($parents, 'id'));
        return $res;
    }
    
    // ---------------- Processing-related functions ----------------
    
    function hasValue() {
        $this->process();
        return $this->hasValue;
    }
    
    function getHasValue() {
        return $this->hasValue();
    }
    
    function getValue() {
        if (!$this->hasValue) $this->process();
        return $this->value;
    }
    
    function setValue($value) {
        $this->value = $value;
        $this->hasValue = true;
    }
    
    function deleteValue() {
        $this->hasValue = false;
    }
    
    function getActualSrcPath() {
        $this->process();
        return $this->actualSrcPath;
    }
    
    function getActualDestPath() {
        $this->process();
        return $this->actualDestPath;
    }
    
    function getErrors() {
        $this->process();
        return $this->errors;    
    }
    
    function setErrors(array $errors) {
        $this->errors = $errors;
    }
    
    function reset() {
        $this->processed = false;
        $this->hasValue = false;
    }
    
    protected function process() {
        if (!$this->processed) {
            $this->processed = true;
            
            $this->actualSrc = $this->source;
            $this->actualSrcPath = $this->path === false? array($this->id) : $this->path;
            if ($this->parent) $this->actualSrc = $this->parent->getSourceForChild($this, $this->actualSrc, $this->actualSrcPath);
            if (!$this->actualSrc) throw new Exception("Cannot retrieve source for param '".$this->getIdPath()."'");
            
            $this->value = $this->actualSrc->getParamValue($this->actualSrcPath, $this->default, $this->hasValue);
            
            $this->actualDest = $this->destination;
            $this->actualDestPath = $this->destPath === false? $this->actualSrcPath : $this->destPath;
            if ($this->parent) $this->actualDest = $this->parent->getDestinationForChild($this, $this->actualDest, $this->actualDestPath);

            if ($this->hasValue)
                $this->value = self::applyConditionsAndFilters($this->value, $this->conditions, $this->filters, $this->hasValue, $this, $this->errors,
                    $this->evaluateAllConditions);
            
            if ($this->nullIsNoValue && is_null($this->value)) $this->hasValue = false;    
            
            if (!$this->hasValue) $this->value = $this->default;
            
            
        }
    }
    
}