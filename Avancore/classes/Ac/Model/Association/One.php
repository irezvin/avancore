<?php

class Ac_Model_Association_One extends Ac_Model_Association_Abstract {

    protected $isReferenced = true;
    
    /**
     * @var string
     */
    protected $getDestObjectMethod = false;

    /**
     * @var string
     */
    protected $setDestObjectMethod = false;

    /**
     * @var string
     */
    protected $clearDestObjectMethod = false;

    /**
     * @param string $getDestObjectMethod
     */
    function setGetDestObjectMethod($getDestObjectMethod) {
        if ($getDestObjectMethod !== ($oldGetDestObjectMethod = $this->getDestObjectMethod)) {
            $this->getDestObjectMethod = $getDestObjectMethod;
        }
    }

    /**
     * @return string
     */
    function getGetDestObjectMethod() {
        if ($this->getDestObjectMethod === false) {
            $this->guessMethods();
        }
        return $this->getDestObjectMethod;
    }

    /**
     * @param string $setDestObjectMethod
     */
    function setSetDestObjectMethod($setDestObjectMethod) {
        if ($setDestObjectMethod !== ($oldSetDestObjectMethod = $this->setDestObjectMethod)) {
            $this->setDestObjectMethod = $setDestObjectMethod;
        }
    }

    /**
     * @return string
     */
    function getSetDestObjectMethod() {
        if ($this->setDestObjectMethod === false) {
            $this->guessMethods();
        }
        return $this->setDestObjectMethod;
    }

    /**
     * @param string $clearDestObjectMethod
     */
    function setClearDestObjectMethod($clearDestObjectMethod) {
        if ($clearDestObjectMethod !== ($oldClearDestObjectMethod = $this->clearDestObjectMethod)) {
            $this->clearDestObjectMethod = $clearDestObjectMethod;
        }
    }

    /**
     * @return string
     */
    function getClearDestObjectMethod() {
        if ($this->clearDestObjectMethod === false) {
            $this->guessMethods();
        }
        return $this->clearDestObjectMethod;
    }   
    
    
    function getDestObject($object) {
        if ($this->useModelMethods && ($m = $this->getDestObjectMethod)) {
            $res = $object->$m();
        } else {
            $f = $this->getInMemoryField();
            if ($object->$f === false) {
                $this->getRelation()->loadDest($object);
            }
            $res = $object->$f;
        }
        return $res;
    }
    
    function setDestObject($object, $destObject) {
        if ($this->useMapperMethods && ($m = $this->setDestObjectMethod)) {
            $res = $object->$m($destObject);
        } else {
            $f = $this->getInMemoryField();
            $ok = $this->checkDestType($destObject, $ex);
            if (!$ok) throw $ex;
            $object->$f = $destObject;
        }
        return $res;
    }
    
    function clearDestObject($object) {
        if ($this->useMapperMethods && ($m = $this->clearDestObjectMethod)) {
            $res = $object->$m();
        } else {
            $f = $this->getInMemoryField();
            $object->$f = null;
        }
        return $res;
    }
    
    protected function doAssignDestObject($object, $destObject) {
        $this->setDestObject($object, $destObject);
    }
    
    protected function getGuessMap() {
        $res = array_merge(parent::getGuessMap(), array(
            'getDestObjectMethod' => 'get{Single}',
            'setDestObjectMethod' => 'set{Single}',
            'clearDestObjectMethod' => 'clear{Single}',
        ));
        return $res;
    }
    
    protected function getMethodImplMap() {
        return array_merge(parent::getMethodImplMap(), array(
            'getDestObjectMethod' => 'getDestObject',
            'setDestObjectMethod' => 'setDestObject',
            'clearDestObjectMethod' => 'clearDestObject',
        ));
    }
    
}