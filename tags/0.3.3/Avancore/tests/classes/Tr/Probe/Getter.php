<?php

/**
 * 
 */

/**
 * Extracts $propertyName from $source.
 * Then applies $decorator if provided.
 * Returns resulting value.
 * 
 * If $equalsValue is set, returns boolean result 
 * of comparison instead ($strict responds for strictness). 
 * (If $decorator is provided, the DECORATED value is compared.)
 */
class Tr_Probe_Getter extends Tr_Probe {
    
    protected $propertyName = false;

    protected $equalsValue = false;

    /**
     * @var bool
     */
    protected $strict = false;
    
    protected $decorator = false;
    
    protected $equalsValueSet = false;
    
    public function doGetResult() {
        if (!strlen($this->propertyName)) {
            throw new Ac_E_InvalidUsage("Set \$propertyName first");
        }
        $res = Ac_Accessor::getObjectProperty($this->getSource(), $this->propertyName);
        if ($this->decorator) $res = Ac_Decorator::decorate ($this->decorator, $res, $this->decorator);
        if ($this->equalsValueSet) {
            if ($this->strict) $res = $res === $this->equalsValue;
                else $res = $res == $this->equalsValue;
        }
        return $res;
    }

    function setPropertyName($propertyName) {
        $this->propertyName = $propertyName;
    }

    function getPropertyName() {
        return $this->propertyName;
    }

    function setEqualsValue($equalsValue) {
        $this->equalsValue = $equalsValue;
        $this->equalsValueSet = true;
    }

    function getEqualsValue() {
        return $this->equalsValue;
    }

    function setDecorator($decorator) {
        $this->decorator = $decorator;
    }

    function getDecorator() {
        return $this->decorator;
    }

    /**
     * @param bool $strict
     */
    function setStrict($strict) {
        $this->strict = (bool) $strict;
    }

    /**
     * @return bool
     */
    function getStrict() {
        return $this->strict;
    }
    
}