<?php

/**
 * Holds controller response data
 */
class Ac_Legacy_Controller_Response {

    /**
     * Textual content of the response
     * 
     */
    var $content = false;
    
    /**
     * @var array of Ac_Legacy_Controller_Response_Part
     */
    var $parts = array();
    
    var $data = array();
    
    /**
     * Errors information (if there were errors)
     *
     * @var unknown_type
     */
    var $errors = array();

    /**
     * @var bool Whether this response can be cached or not
     * @deprecated Where I use it???
     */
    var $canBeCached = false;
    
    var $obsolete = false;
    
    /**
     * Timestamp when response was serialized
     * @var int
     */
    var $storeTs = false;
    
    function copyFrom(Ac_Legacy_Controller_Response $otherResponse) {
        $this->fromArray($otherResponse->toArray);
    }
    
    function clear() {
        foreach (get_class_vars(get_class($this)) as $k => $v) $this->$k = $v;
    }
    
    /**
     * Public fields of the response that will be ignored in toArray() / fromArray()
     * (template method)
     * return array
     */
    function listArrayIgnore() {
        return array('parts');
    }
    
    /**
     * Sets response fields
     * @see getArrayIgnore, toArray
     * @param array $data
     */
    function fromArray(array $data) {
        $this->clear();
        $vars = array_intersect(array_keys($data), array_keys(Ac_Util::getClassVars(get_class($this))));
        $vars = array_diff($vars, $this->listArrayIgnore());
        foreach ($vars as $varName) {
            if ($varName[0] != '_') $this->$varName = $def;
        }
        
    }
    
    /**
     * Returns response content as variables (to assign to other response with setData(), for example)
     * @return array
     */
    function toArray() {
        $res = array();
        $vars = Ac_Util::getClassVars(get_class($this));
        $vars1 = get_object_vars($this);
        foreach ($this->listArrayIgnore() as $varName) unset($vars[$varName]);
        foreach ($vars as $varName => $def) {
            if (array_key_exists($varName, $vars1) && $varName[0] != '_' && ($this->$varName !== $def)) $res[$varName] = $this->$varName;
        }
        return $res;
    }
    
    function setData($data, $key = false, $subKey = false) {
        if ($key === false) $this->data = $data;
        elseif ($subKey === false) $this->data[$key] = $data;
        else $this->data[$key][$subKey] = $data;
    }
    
    function addData($value, $key) {
        if (!isset($this->data[$key])) $this->data[$key] = array();
        $subKey = count($this->data[$key]);
        $this->data[$key][$subKey] = $value;
        return $subKey;
    }
    
    function deleteData($key, $subKey = false) {
        if (isset($this->data[$key])) {
            if ($subKey === false) {
                unset($this->data[$key]);
            } elseif(isset($this->data[$key][$subKey])) {
                unset($this->data[$key][$subKey]);
            }
        }
    }
    
    function getData($path, $default = null) {
        return Ac_Util::getArrayByPath($this->data, Ac_Util::pathToArray($path), $default);
    }
    
    /**
     * Finds part with specified signature and appends its placeholder into response content.
     * 
     * $partSignature can be either a string - a class name; an array - an Ac_Legacy_Controller_Response_Part prototype; or a ready
     * Ac_Legacy_Controller_Response_Part instance itself.
     * 
     * @param $data Part data
     * @param string|array|Ac_Legacy_Controller_Response_Part $partSignature
     * @param bool $appendToContent If TRUE, result will be immediately appended to response content
     * @return string Placeholder content
     */
    function createPlaceholder($data, $partSignature = 'Ac_Legacy_Controller_Response_Part', $appendToContent = false) {
        $p = $this->findPart($partSignature);
        if (!$p) {
            $p = Ac_Legacy_Controller_Response_Part::factory($partSignature);
            $this->parts[] = $p;
        }
        $res = $p->createPlaceholder($data);
        if ($appendToContent) $this->content .= $p;
        return $res;
    }
    
    /**
     * @param string|array|Ac_Legacy_Controller_Response_Part $partSignature
     * @return Ac_Legacy_Controller_Response_Part
     */
    function findPart($partSignature) {
        $res = false;
        foreach ($this->parts as $part) {
            if ($part->matches($partSignature)) {
                $res = $part;
                break;
            }
        }
        return $res;
    }
    
    function isObsolete() {
        return $this->obsolete;
    }

    /**
     * @param bool $cachedOnly Expand only cacheable placeholders (as it is done before serialization)
     * @param bool $dontChangeContent If TRUE, $content property won't be altered and placeholders would not be removed from $placeholders array  
     * @param string|FALSE $content Content in which we are going to replace placeholders
     * @return string response content (with expansions)
     */
    function replacePlaceholders($cachedOnly = false, $dontChangeContent = false, $content = false) {
        $res = $content !== false? $content : $this->content;
        $list = array();
        if (is_array($this->parts)) foreach ($this->parts as $i => $ph) {
            if (!$cachedOnly || $ph->isCacheable()) {
                $ph->response = $this;
                $res = $ph->replacePlaceholders($res);
                if (!$dontChangeContent) $list[] = $i;
            }
        }
        foreach ($list as $i) unset($this->parts[$i]);
        if (!$dontChangeContent) $this->content = $res;
        return $res;
    }
    
    function __sleep() {
        $this->storeTs = (int) gmdate('U');
        $this->replacePlaceholders(true, false);
        return array_keys(get_object_vars($this));
    }
    
    function __wakeup() {
        foreach ($this->parts as $p) $p->handleResponseWakeup($this);
    }
    
    /**
     * @var Ac_Application
     */
    protected $application = false;

    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        if ($this->application) $res = $this->application;
            else $res = Ac_Application::getDefaultInstance ();
        return $res;
    }    
    
}

