<?php

class Ac_Result_Position {

    /**
     * @var Ac_Result
     */
    protected $result = false;
    
    protected $propertyName = false;
    
    protected $offset = false;
    
    protected $isString = null;

    protected $currentObject = false;
    
    protected $classes = array();
    
    protected $isDone = false;
    
    protected $insOffset = 0;
    
    function __construct(Ac_Result $result = null, $classes = false) {
        $this->init($result, $classes);
    }
    
    function gotoPosition($propertyName, $offset) {
        if (array($this->propertyName, $this->offset) !== ($args = func_get_args())) {
            $this->currentObject = false;
            $this->isDone = false;
            $this->insOffset = 0;
        }
        if ($this->propertyName !== $propertyName)
            $this->isString = null;
        
        list($this->propertyName, $this->offset) = $args;
    }
    
    function hasPosition() {
        return $this->propertyName !== false;
    }
    
    function init(Ac_Result $result, $classes = false) {
        $this->reset();
        $this->result = $result;
        $this->setClasses($classes);
    }
    
    function setResult($result) {
        $this->result = $result;
        $this->reset();
    }
    
    function setClasses($classes = false) {
        if ($this->hasPosition()) {
            throw new Ac_E_InvalidUsage("Cannot setClasses() when hasPosition(); reset() first");
        }
        $this->classes = $classes === false? array() : Ac_Util::toArray($classes);
    }
    
    function reset() {
        $this->gotoPosition(false, false);
    }
    
    function getPosition() {
        if (!$this->hasPosition()) $this->advance();
        return array($this->propertyName, $this->offset);
    }

    protected function advanceInProperty($bunch, & $context = null) {
        $val = $bunch[$this->propertyName];
        $this->isString = is_string($bunch[$this->propertyName]);
        if ($this->isString) $next = $this->advanceStringOffset($val, $this->offset, $context);
            else $next = $this->advanceArrayOffset($val, $this->offset);
        $this->insOffset = 0;
        if ($next) {
            $res = $next;
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * Advances position to the next object and returns it. Returns NULL if no object found
     * @return object
     */
    function advance() {
        if (!$this->isDone) {
            $bunch = $this->getBunch();
            $context = null;
            if ($this->propertyName !== false) {
                if ($this->currentObject)
                    $this->actualizePosition($bunch, $context);
            }
            else $this->propertyName = $this->advanceProperty($bunch, $this->propertyName);
            $this->currentObject = null;
            if (!$this->isDone) {
                $ip = $this->advanceInProperty($bunch, $context);
                if (!$ip && !is_null($this->propertyName = $this->advanceProperty($bunch, $this->propertyName))) {
                    $this->offset = false;
                    $ip = $this->advanceInProperty($bunch);
                }
                if ($ip) list($this->currentObject, $this->offset) = $ip;
                    else $this->isDone = true;
            }
        }
        return $this->currentObject;
    }
    
    protected function actualizePosition(array $bunch, & $context = null) {
        $res = true;
        $obj = $this->getObjectAtPosition(false, $context);
        if ($obj !== $this->currentObject) {
            $context = null;
            $val = $bunch[$this->propertyName];
            if (is_string($val)) {
                $newOffset = strpos($val, ''.$this->currentObject);
            } else {
                $newOffset = array_search($this->currentObject, array_values($val), true);
            }
            if (is_null($newOffset)) {
                $this->handleObjectHasDisappeared();
                $res = false;
            } else {
                $this->offset = $newOffset;
            }
        }
        return $res;
    }
    
    /**
     * @param array $bunch
     * @param string $property
     * @return string
     */
    protected function advanceProperty(array $bunch, $property) {
        $res = null;
        $props = array_keys($bunch);
        if ($property === false) $currPropIdx = -1;
        else {
            $currPropIdx = array_search($property, $props);
        }
        if ($currPropIdx === false) {
            $this->handlePropertyHasDisappeared();
        } else {
            if ($currPropIdx < count($props) - 1) {
                $currPropIdx++;
                $res = $props[$currPropIdx];
            } else {
                $this->isDone = true;
            }
        }
        return $res;
    }
    
    /**
     * @return array($object, $newOffset)
     */
    protected function advanceStringOffset($strValue, $offset, & $context = null) {
        if ($offset === false) $offset = -1;
        $res = false;
        do {
            if (!$context || $res) 
                $context = Ac_StringObject::getStringObjectContext ($strValue, $offset);
            
            $res = $context['next'];
            if ($res) {
                $res[0] = Ac_StringObject::getObjectByMark($res[0]);
                $offset = $res[1];
            }
        } while ($res && !$this->isSatisfiedBy($res[0]));
        return $res;
    }
    
    /**
     * @return array($object, $newOffset)
     * @param array $arrValue
     * @param int $offset
     */
    protected function advanceArrayOffset(array $arrValue, $offset) {
        if ($offset === false) $offset = -1;
        do {
            $next = array_slice(array_keys($arrValue), $offset + 1, 1);
            if (!count($next)) $res = false;
            else {
                $res = array($arrValue[$next[0]], $offset + 1);
                $offset = $offset+1;
            }
        } while ($res && !$this->isSatisfiedBy($res[0]));
        return $res;
    }
    
    /**
     * Returns true if $object is one of allowed $this->classes
     * @return boolean
     */
    protected function isSatisfiedBy($object) {
        if (!$this->classes) $res = true;
        else {
            $res = false;
            foreach ($this->classes as $class) {
                if ($object instanceof $class) {
                    $res = true;
                    break;
                }
            }
        }
        return $res;
    }
    
    function getIsDone() {
        return $this->isDone;
    }
    
    protected function handlePropertyHasDisappeared() {
        $this->isDone = true;
    }
    
    protected function handleObjectHasDisappeared() {
        
    }
    
    /**
     * @return array
     */
    protected function getBunch() {
        $res = $this->result->getTraversableBunch($this->classes? $this->classes : false);
        return $res;
    }
    
    protected function getObjectAtPosition($advanceIfNoPosition = false, & $context = null) {
        $res = null;    
        if ($this->propertyName === false && $advanceIfNoPosition) $this->advance();
        if ($this->propertyName !== false && !$this->isDone) {
            $bunch = $this->getBunch();
            if (isset($bunch[$this->propertyName])) {
                $val = $bunch[$this->propertyName];
                if ($this->isString) {
                    $context = Ac_StringObject::getStringObjectContext($val, $this->offset);
                    if ($context['current']) $res = Ac_StringObject::getObjectByMark ($context['current'][0]);
                } else {
                    $v = array_slice($val, $this->offset, 1);
                    if (count($v)) $res = array_shift ($v);
                }
            } else {
                $this->handlePropertyHasDisappeared();
            }
        }
        return $res;
    }
    
    function getObject() {
        if ($this->currentObject === false) {
            $this->currentObject = $this->getObjectAtPosition();
        }
        return $this->currentObject;
    }
   
    /**
     * @return bool
     */
    function getIsString() {
        if (!$this->hasPosition()) $this->advance();
        return $this->isString;
    }
    
    /**
     * @return Ac_Result
     */
    function getResult() {
        return $this->result;
    }
    
    // Modifier methods
    
    function insertBefore($objectOrString) {
        if ($this->propertyName === false && $advanceIfNoPosition) $this->advance();
        if ($this->isDone) throw new Ac_E_InvalidUsage("Cannot insertBefore() when not at object; check with !Ac_Result_Position::isDone() first");
        if (!is_object($objectOrString) && !$this->isString) 
            throw new Ac_E_InvalidUsage("Cannot insertBefore(".gettype($withObjectOrString).") into non-string property '".$this->propertyName."'; check with Ac_Result_Position::isString() first");
        // TODO: check if current offset isn't INSIDE a string object mark (probably better to do it at Ac_Result)
        
        if ($this->isString) {
            $this->result->insertAtPosition($this->offset, $objectOrString);
            $this->offset += strlen(''.$objectOrString);
        } else {
            $this->result->addToList($this->propertyName, $objectOrString, $this->offset);
            $this->offset += 1;
        }
    }
    
    function insertAfter($objectOrString, $append = false) {
        if ($this->propertyName === false && $advanceIfNoPosition) $this->advance();
        if ($this->isDone) throw new Ac_E_InvalidUsage("Cannot insertAfter() when not at object; check with isDone() first");
        if (!is_object($objectOrString) && !$this->isString) 
            throw new Ac_E_InvalidUsage("Cannot insertAfter(".gettype($withObjectOrString).") into non-string property '".$this->propertyName."'; check with Ac_Result_Position::isString() first");
        // TODO: check if current offset isn't INSIDE a string object mark (probably better to do it at Ac_Result)
        
        if ($this->isString) {
            $len = $this->currentObject instanceof Ac_I_StringObject? strlen($this->currentObject->getStringObjectMark()) : strlen(''.$this->currentObject);
            $pos = $this->offset + $len;
            if ($append) $pos += $this->insOffset;
            $this->insOffset += strlen(($s = ''.$objectOrString));
            $this->result->insertAtPosition($pos, $s);
        } else {
            $pos = $this->offset + 1;
            if ($append) $pos += $this->insOffset;
            $this->insOffset += 1;
            $this->result->addToList($this->propertyName, $objectOrString, $pos);
        }
    }
    
    function removeCurrentObject() {
        if ($this->propertyName === false && $advanceIfNoPosition) $this->advance();
        if ($this->isDone) throw new Ac_E_InvalidUsage("Cannot removeCurrentObject() when not at object; check with !Ac_Result_Position::isDone() first");
        
        if ($this->isString) {
            $this->result->removeFromContent($this->currentObject);
        } else {
            $this->result->removeFromList($this->propertyName, $this->currentObject);
        }
    }
    
    function replaceCurrentObject($withObjectOrString) {
        if ($this->propertyName === false && $advanceIfNoPosition) $this->advance();
        if ($this->isDone) throw new Ac_E_InvalidUsage("Cannot replaceCurrentObject() when not at object; check with !Ac_Result_Position::isDone() first");
        if (!is_object($withObjectOrString) && !$this->isString) 
            throw new Ac_E_InvalidUsage("Cannot replaceCurrentObject(".gettype($withObjectOrString).") into non-string property '".$this->propertyName."'; check with Ac_Result_Position::isString() first");
        if ($this->propertyName === false && $advanceIfNoPosition) $this->advance();
        if ($this->isDone) throw new Ac_E_InvalidUsage("Cannot removeCurrentObject() when not at object; check with !Ac_Result_Position::isDone() first");
        
        if ($this->isString) {
            if (! $this->currentObject instanceof Ac_I_StringObject) {
                Ac_Debug::dd(''.new Exception);
            }
            $this->result->replaceObjectInContent($this->currentObject, $withObjectOrString);
        } else {
            $this->result->removeFromList($this->propertyName, $this->currentObject);
            $this->result->addToList($this->propertyName, $withObjectOrString, $this->offset);
        }
        if (is_object($withObjectOrString) && $withObjectOrString instanceof Ac_I_StringObject)
            $this->currentObject = $withObjectOrString;
        else 
            $this->currentObject = false;
    }
    
}