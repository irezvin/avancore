<?php

class Ac_Result extends Ac_Prototyped implements Ac_I_StringObject_Container, Ac_I_StringObject_ClonedWithBuffer, Ac_I_WithOutput, Ac_I_Result_ForSlot {

    const OVERRIDE_NONE = 0;
    const OVERRIDE_PARENT = 1;
    const OVERRIDE_ALL = 2;
    
    const SERIALIZATION_NONE = 0;
    const SERIALIZATION_INNER = 1;
    const SERIALIZATION_OUTER = 2;

    protected $content = '';
    
    protected $placeholders = false;
 
    protected $handlers = array();

    /**
     * @var string
     */
    protected $slotId = false;
    
    protected $slotContent = array();

    protected $slotParams = false;

    /**
     * @var Ac_Result
     */
    protected $replaceWith = null;

    /**
     * @var Ac_Result_Writer
     */
    protected $writer = false;

    protected $overrideMode = self::OVERRIDE_NONE;

    /**
     * @var bool
     */
    protected $writeOnStore = false;

    /**
     * @var bool
     */
    protected $cacheable = true;
    
    /**
     * @var array idHash => stringObject
     */
    protected $stringObjects = array();
    
    /**
     * @var boolclone
     */
    protected $merged = false;

    protected $stringObjectsUpdated = false;
    
    protected $foundSubResults = false;
    
    protected $stringObjectMark = false;
    
    protected $debugData = false;
    
    protected $serializationState = self::SERIALIZATION_NONE;

    protected $serializeTime = false;

    protected $unserializeTime = false;
    
    protected $maxLifetime = false;    
    
    /**
     * @var boolean
     */
    protected $isObsolete = null;
    
    function listPlaceholders($onlyUsed = false, $onlyDefault = false) {
        if (!is_array($this->placeholders)) $this->setPlaceholders(array());
        $res = array_keys($this->placeholders);
        if ($onlyUsed) $res = array_diff($res, array_keys($this->placeholders, false, true));
        if ($onlyDefault) $res = array_intersect($res, array_keys($this->doGetDefaultPlaceholders()));
        if ($onlyUsed || $onlyDefault) $res = array_values($res);
        return $res;
    }
    
    /**
     * @return Ac_Result_Placeholder
     */
    function getPlaceholder($id, $dontThrow = false) {
        if (!is_array($this->placeholders)) $this->setPlaceholders(array());
        if (isset($this->placeholders[$id])) {
            if ($this->placeholders[$id] === false) {
                $dp = $this->doGetDefaultPlaceholders();
                if (is_array($dp[$id]) && !isset($dp[$id]['id'])) $dp[$id]['id'] = $id;
                $this->placeholders[$id] = Ac_Prototyped::factory($dp[$id], 'Ac_Result_Placeholder');
            }
            return $this->placeholders[$id];
        } else {
            if (!$dontThrow) throw Ac_E_InvalidCall::noSuchItem('Placeholder', $id, 'listPlaceholders');
            return null;
        }
    }
    
    /**
     * @param Ac_Result_Placeholder $placeholder
     * @param type $id
     * @return \Ac_Result_Placeholder
     */
    function addPlaceholder(Ac_Result_Placeholder $placeholder, $id) {
        if (isset($this->placeholders[$id])) throw Ac_E_InvalidCall::alreadySuchItem ('Placeholder', $id, 'removePlaceholder');
        $this->placeholders[$id] = $placeholder;
        return $placeholder;
    }
    
    function isDefaultPlaceholder($id) {
        return in_array($id, array_keys($this->doGetDefaultPlaceholders()));
    }
    
    function removePlaceholder($id, $dontThrow = false) {
        if (isset($this->placeholders[$id])) {
            if ($this->placeholders[$id] === false || in_array($id, array_keys($this->doGetDefaultPlaceholders()))) 
                throw new Ac_E_InvalidCall("Cannot removePlaceholder('{$id}') because isDefaultPlaceholder('{$id}')");
            unset($this->placeholders[$id]);
        } else {
            if (!$dontThrow) throw Ac_E_InvalidCall::noSuchItem('Placeholder', $id, 'listPlaceholders');
        }
    }
    
    function setPlaceholders(array $placeholders) {
        if (!is_array($this->placeholders)) {
            $this->placeholders = array();
            foreach (array_keys($this->doGetDefaultPlaceholders()) as $dp) $this->placeholders[$dp] = false;
        } else {
            foreach (array_diff(array_keys($this->placeholders), array_keys($this->doGetDefaultPlaceholders())) as $k) unset($this->placeholders[$k]);
        }
        Ac_Util::ms($this->placeholders, Ac_Prototyped::factoryCollection($placeholders, 'Ac_Result_Placeholder', array(), 'id'));
    }
    
    /**
     * @return array
     */
    function getPlaceholders($onlyUsed = false) {
        if (!is_array($this->placeholders)) $this->setPlaceholders(array());
        $res = $this->placeholders;
        if ($notInstantiated = array_keys($res, false, true)) {
            if ($onlyUsed) {
                $res = array_diff_key($res, array_flip($notInstantiated));
            } else {
                foreach ($notInstantiated as $id) $res[$id] = $this->getPlaceholder($id);
            }
        }
        return $res;
    }
    
    function listHandlers() {
        return array_keys($this->handlers);
    }
    
    /**
     * @return Ac_I_Result_Handler
     */
    function getHandler($id, $dontThrow = false) {
        if (isset($this->handlers[$id])) {
            return $this->handlers[$id];
        } else {
            if (!$dontThrow) throw Ac_E_InvalidCall::noSuchItem('Handler', $id, 'listHandlers');
            return null;
        }
    }
    
    function addHandler(Ac_I_Result_Handler $handler, $id) {
        if (isset($this->handlers[$id])) throw Ac_E_InvalidCall::alreadySuchItem ('Handler', $id, 'removeHandler');
        $this->handlers[$id] = $handler;
    }
    
    function removeHandler($id, $dontThrow = false) {
        if (isset($this->handlers[$id])) {
            unset($this->handlers[$id]);
        } else {
            if (!$dontThrow) throw Ac_E_InvalidCall::noSuchItem('Handler', $id, 'listHandlers');
        }
    }
    
    function setHandlers(array $handlers) {
        foreach ($handlers as $k => $handler) if (!(is_object($handler) && $handler instanceof Ac_I_Result_Handler)) {
            throw Ac_E_InvalidCall::wrongClass("handlers['{$k}']", $handler, 'Ac_I_Result_Handler');
        }
        $this->handlers = $handlers;
    }
    
    /**
     * @return array
     */
    function getHandlers() {
        return $this->handlers;
    }

    /**
     * @param string $slotId
     */
    function setSlotId($slotId) {
        $this->slotId = $slotId;
    }

    /**
     * @return string
     */
    function getSlotId() {
        return $this->slotId;
    }
    
    function setSlotParams($slotParams) {
        $this->slotParams = $slotParams;
    }

    function getSlotParams() {
        return $this->slotParams;
    }
    
    /**
     * @param string|bool $slotId FALSE to get content of all slots; otherwise slot id
     * @return array
     */
    function getSlotContent($slotId = false) {
        if ($slotId === false) $res = $this->slotContent;
        else $res = isset($this->slotContent[$slotId])? $this->slotContent[$slotId] : array();
        return $res;
    }
    
    /**
     * Sets content of the slot
     * 
     * @param array $slotContent Contnent of the slot
     * @param bool $slotId FALSE to set content of all slots at once (requires array of arrays)
     */
    function setSlotContent(array $slotContent, $slotId = false) {
        if ($slotId === false) {
            $this->slotContent = array();
            foreach ($slotContent as $k => $v) {
                $this->setSlotContent($v, $k);
            }
        } else {
            foreach ($slotContent as $item) {
                if (!$item instanceof Ac_I_Result_ForSlot) 
                    throw new Ac_E_InvalidCall("Only Ac_I_Result_ForSlot is allowed to put into the slot");
                $this->slotContent[$slotId] = $item;
            }
        }
    }
    
    function hasSlotContent(Ac_I_Result_ForSlot $content, $slotId = false) {
        if ($slotId === false) $slotId = $content->getSlotId();
        $s = $this->getSlotContent($slotId);
        $res = false;
        foreach ($s as $v) 
            if ($v === $content) {
                $res = true;
                break;
            }
        return $res;
    }
    
    function addSlotContent(Ac_I_Result_ForSlot $content, $slotId = false, $noDuplicate = false) {
        if ($slotId === false) $slotId = $content->getSlotId();
        if (!isset($this->slotContent[$slotId])) {
            $this->slotContent[$slotId] = array($content);
        } else {
            if (!$noDuplicate || !$this->hasSlotContent($content, $slotId)) 
                $this->slotContent[$slotId][] = $content;
        }
    }

    function setReplaceWith(Ac_Result $replaceWith = null) {
        $this->replaceWith = $replaceWith;
    }

    /**
     * @return Ac_Result
     */
    function getReplaceWith() {
        return $this->replaceWith;
    }

    /**
     * @param Ac_Result_Writer $writer or its' prototype
     */
    function setWriter($writer) {
        if ($writer === $this->writer) return;
        if (is_object($writer) && $writer instanceof Ac_Result_Writer)
            $writer->setSource ($this);
        $this->writer = $writer;
    }

    /**
     * @return Ac_Result_Writer
     */
    function getWriter() {
        if ($this->writer === false) {
            $this->writer = $this->createDefaultWriter();
        } else {
            if (!is_object($this->writer) || !$this->writer instanceof Ac_Result_Writer)
                $this->writer = Ac_Prototyped::factory(
                    $this->writer, 
                    'Ac_Result_Writer',
                    array('source' => $this),
                    true
                );
        }
        return $this->writer;
    }
    
    function createDefaultWriter() {
        $res = new Ac_Result_Writer_Auto(array('source' => $this));
        return $res;
    }

    /**
     * @param bool $writeOnStore
     */
    function setWriteOnStore($writeOnStore) {
        $this->writeOnStore = (bool) $writeOnStore;
    }

    /**
     * @return bool
     */
    function getWriteOnStore() {
        return $this->writeOnStore;
    }

    /**
     * @param bool $cacheable
     */
    function setCacheable($cacheable) {
        $this->cacheable = (bool) $cacheable;
    }

    /**
     * @return bool
     */
    function getCacheable() {
        return $this->cacheable;
    }

    function setOverrideMode($overrideMode) {
        $overrideMode = (int) $overrideMode;
        if (!in_array($overrideMode, array(self::OVERRIDE_ALL, self::OVERRIDE_NONE, self::OVERRIDE_PARENT))) {
            throw Ac_E_InvalidCall::outOfConst('overrideMode', $overrideMode, array('OVERRIDE_ALL', 'OVERRIDE_NONE', 'OVERRIDE_PARENT'), __CLASS__);
        }
        $this->overrideMode = $overrideMode;
    }

    function getOverrideMode() {
        return $this->overrideMode;
    }

    /**
     * @param bool $merged
     */
    function setMerged($merged) {
        $merged = (bool) $merged;
        if ($merged !== ($oldMerged = $this->merged)) {
            if (!$merged) throw new Ac_E_InvalidUsage("Cannot remove merged flag once it have been set");
            $this->merged = $merged;
        }
    }

    /**
     * @return bool
     */
    function getMerged() {
        return $this->merged;
    }
    
    function __sleep() {
     
        $this->serializeTime = microtime(true);
        
        if ($this->serializationState === self::SERIALIZATION_INNER) { // we already have been visited - just release the lock
            
            $this->serializationState = self::SERIALIZATION_NONE;
            
        } else { // this call comes from outermost result -- let's call Store stage
            
            $storeStage = new Ac_Result_Stage_Serialize(array('root' => $this));
            $storeStage->invoke();
            $this->serializationState = self::SERIALIZATION_OUTER; // will be picked up on __wakeup
            
        }
        
        // this will register string objects if needed
        $this->getStringObjects(); 
        
        
        $props = get_object_vars($this);
        unset($props['bunches']); // don't serialize bunches
        $res = array_keys($props);
        return $res;
    }
    
    function __wakeup() {
        
        $this->unserializeTime = microtime(true);
        
        Ac_StringObject::onWakeup($this);
        if ($this->serializationState === self::SERIALIZATION_OUTER) {
            $this->serializationState = self::SERIALIZATION_NONE;
            $loadStage = new Ac_Result_Stage_Unserialize(array('root' => $this));
            $loadStage->invoke();
        }
    }
    
    /**
     * @return array
     */
    function getStringObjects() {
        if (!$this->stringObjectsUpdated) {
            Ac_StringObject::registerContainerStrings($this);
            $this->stringObjectsUpdated = true;
        }
        return $this->stringObjects;
    }
    
    function getContent() {
        return $this->content;
    }   
    
    function echoContent() {
        if (func_num_args() > 0) // simple check to help developer
            trigger_error (__METHOD__.": this method isn\'t supposed to receive any args and echo's result content into output stream. "
                . "Weren't you intended to ".__CLASS__."::put(\$contnent)?", E_USER_NOTICE);
        $this->output();
    }
    
    function output($callback = null) {
        foreach (Ac_StringObject::sliceStringWithObjects($this->getContent()) as $cnt) {
            $string = false;
            if (is_object($cnt) && $cnt instanceof Ac_I_WithOutput) $cnt->output($callback);
            elseif (is_object($cnt) && $cnt instanceof Ac_I_StringObject_WithRender) {
                $string = $cnt->getRenderedString();
            } else {
                $string = $cnt;
            }
            if ($string !== false) {
                if ($callback) call_user_func($callback, $string);
                else echo $string;
            }
        }
    }
    
    function setContent($content) {
        $this->stringObjects = array();
        $this->content = ''.$content;
        $this->touchStringObjects();
    }
    
    function beginCapture() {
        ob_start();
    }
    
    function endCapture() {
        $this->put(ob_get_clean());
    }
    
    function put($content, $_ = null) {
        foreach (func_get_args() as $cnt) {
            if (is_object($cnt) && ! $cnt instanceof Ac_I_StringObject && $cnt instanceof Ac_I_WithOutput)
                $cnt = new Ac_StringObject_Wrapper($cnt);
            $this->content .= $cnt;
        }
        $this->touchStringObjects();
    }
    
    function insertAtPosition($position, $content) {
        $this->content = substr($this->content, 0, $position).$content.substr($this->content, $position);
        $this->touchStringObjects();
    }
    
    function removeFromPosition($position, $length) {
        $this->content = substr($this->content, 0, $position).substr($this->content, $position + $length);
        $this->touchStringObjects();
    }
    
    function removeFromContent(Ac_I_StringObject $stringObject) {
        if (strpos($this->content, ''.$stringObject) === false) {
            throw new Exception("String object {$stringObject} is not in the content");
        } else {
            $this->content = str_replace(''.$stringObject, '', $this->content);
            unset($this->stringObjects[''.$stringObject]);
            $this->touchStringObjects();
        }
    }
    
    function replaceObjectInContent(Ac_I_StringObject $stringObject, $newContent) {
        if (($pos = strpos($this->content, ''.$stringObject)) === false) {
            throw new Exception("String object {$stringObject} is not in the content");
        } else {
            $length = strlen(''.$stringObject);
            $this->content = substr($this->content, 0, $pos).$newContent.substr($this->content, $pos + $length);
            $mark = $stringObject->getStringObjectMark();
            unset($this->stringObjects[$mark]); // unregister it since we replaced it
            $this->touchStringObjects();
        }
    }
    
    
    function addToList($property, $object, $position) {
        throw new Exception("Method isn't supported by ".get_class($this));
    }
    
    function removeFromList($property, $object) {
        throw new Exception("Method isn't supported by ".get_class($this));
    }
    
    protected function touchStringObjects() {
        $this->stringObjectsUpdated = false;
        $this->foundSubResults = false;
        $this->bunches = array();
        $this->stringObjects = array();
    }
    
    function getSubResults() {
        if ($this->foundSubResults === false) {
            $all = Ac_StringObject::getObjectsArr($this->getStringBuffers(), true);
            $this->foundSubResults = Ac_Util::getObjectsOfClass($all, 'Ac_Result', true);
        }
        return $this->foundSubResults;
    }
    
    function setDebugData($debugData) {
        $this->debugData = $debugData;
    }

    function getDebugData() {
        return $this->debugData;
    }
    
    // ---- Ac_I_StringObject_Container ----
    
    function getStringBuffers() {
        return array('content' => $this->content);
    }
    
    function registerStringObjects(array $stringObjects) {
        $this->stringObjects = array_merge($this->stringObjects, $stringObjects);
    }

    // ---- Ac_I_StringObject ----


    /**
     * @param string $stringObjectMark
     */
    function setStringObjectMark($stringObjectMark) {
        $this->stringObjectMark = $stringObjectMark;
    }

    /**
     * @return string
     */
    function getStringObjectMark() {
        return $this->stringObjectMark;
    }    
    
    function __toString() {
        if (!strlen($this->stringObjectMark)) Ac_StringObject::register($this);
        return $this->getStringObjectMark();
    }
    
    function __clone() {
        if (strlen($this->stringObjectMark)) Ac_StringObject::onClone($this);
        if (is_array($this->placeholders)) {
            foreach ($this->placeholders as $i => $p) {
                if (is_object($p)) $this->placeholders[$i] = clone $p;
            }
        }
        if (!(class_exists('Ac_Result_Stage_Clone') && Ac_Result_Stage_Clone::getIsRunning())) {
            $s = new Ac_Result_Stage_Clone(array('root' => $this));
            $s->invoke();
        }
    }

    protected $bunches = array();
    
    function getTraversableBunch($classes = false) {
        if (is_array($classes)) $hash = implode(',', $classes);
            else $hash = ''.$classes;
        if (!isset($this->bunches[$hash])) {
            $this->bunches[$hash] = array_merge($this->getStringBuffers(), $this->doGetTraversableBunch($classes));
        }
        return $this->bunches[$hash];
    }
    
    protected function doGetTraversableBunch($classes = false) {
        return array();
    }
    
    protected function doGetDefaultPlaceholders() {
        return array();
    }
    
    protected function initFromPrototype(array $prototype = array(), $strictParams = null) {
        $def = array_intersect_key($prototype, $dp =  $this->doGetDefaultPlaceholders());
        $prototype = array_diff_key($prototype, $dp);
        $res = parent::initFromPrototype($prototype, $strictParams);
        foreach ($def as $k => $v) if (!is_null($v) && $v !== false) {
            if (is_array($v)) $this->getPlaceholder($k)->addItems($v);
                else $this->getPlaceholder($k)->addItems(array($v));
        }
        return $res;
    }
    
    function __get($varName) {
        if ($varName === 'content') $res = $this->getContent();
        elseif (in_array($varName, $this->listPlaceholders())) {
            $res = $this->getPlaceholder($varName);
        } else {
            throw Ac_E_InvalidCall::noSuchProperty($this, $varName);
        }
        return $res;
    }
    
    function __set($varName, $value) {
        if ($varName === 'content') $this->setContent($value);
        elseif (in_array($varName, $this->listPlaceholders())) {
            $this->getPlaceholder($varName)->setItems($value);
        } else {
            throw Ac_E_InvalidCall::noSuchProperty($this, $varName);
        }
    }
    
    function __isset($varName) {
        if (in_array($varName, $this->listPlaceholders())) {
            $res = true;
        } else {
            $res = false;
        }
        return $res;        
    }
    
    /**
     * @param type $stage Ac_Result_Stage_Write or its prototype
     * @return Ac_Result_Stage_Write
     */
    function write($stage = null) {
        if (is_null($stage)) $stage = array();
        $stage = Ac_Prototyped::factory($stage, 'Ac_Result_Stage_Write', array('root' => $this), true);
        $stage->invoke();
        return $stage;
    }
    
    function writeAndReturn(& $stage = null) {
        ob_start();
        $stage = $this->write($stage);
        return ob_get_clean();
    }
    
    function notifyIsStored() {
        $this->serializationState = self::SERIALIZATION_INNER;
    }
    
    function getSerializeTime() {
        return $this->serializeTime;
    }

    function getUnserializeTime() {
        return $this->unserializeTime;
    }    
    
    function setMaxLifetime($maxLifetime) {
        if (!($maxLifetime === false || is_numeric($maxLifetime) && $maxLifetime >= 0))
            throw new Ac_E_InvalidCall("\$maxLifetime must be either FALSE or 0 or natural number");
        $this->maxLifetime = $maxLifetime;
    }

    function getMaxLifetime() {
        return $this->maxLifetime;
    }    

    /**
     * @return boolean
     */
    function getIsObsolete() {
        if ($this->isObsolete === null) {
            $l = $this->getMaxLifetime();
            $s = $this->getSerializeTime();
            return $l && $s && $s < (microtime() - $l);
        }
        return $this->isObsolete;
    }    
    
    function markObsolete() {
        $this->isObsolete = true;
    }
    
}