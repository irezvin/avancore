<?php

/**
 * Response_Part is an aggregate of Ac_Controller_Response that is evaluated only on response output.
 * 
 * It means that non-cacheable response parts are stored in response and they are re-calculated every time when
 * response is loaded from cache.
 * 
 * So, for example, it is possible to store in the cache page with index of articles and update number of comments every
 * time when it's loaded (or do it more often then page updates, or even re-calculate presentation only of changed elements) 
 */
class Ac_Controller_Response_Part extends Ac_Prototyped {
    
    /**
     * Template method
     * If TRUE, response part will be evaluated before response serialization, and then deleted from response.
     * Default behavior is return FALSE. 
     * 
     * @return bool
     */
    function isCacheable() {
        return false;
    }
    
    /**
     * Template method
     * If TRUE, means same data will always return same output (and will be assigned same placeholder).
     * Default behavior is to return TRUE.
     * Otherwise any new data will be assigned it's unique ID 
     * 
     * @return bool
     */
    function isDeterministic() {
        return true;
    }

    /**
     * @var Ac_Controller_Response
     */
    var $response = false;
    
    /**
     * @var array placeholderId => placeholderData
     */
    protected $placeholders = array();

    /**
     * Signature of part initialization - is used by response::putPartPlaceholder to distinguish different part instances
     * @var string|array
     */
    protected $signature = false;
    
    protected $lock = 0;
    
    /**
     * @return Ac_Controller_Response_Part
     * @see Ac_Prototyped::factory()
     */
    static function factory($signature) {
        if (is_object($signature) && $signature instanceof Ac_Controller_Response_Part) $res = $signature;
            elseif (is_array($signature)) $res = Ac_Prototyped::factory($signature, 'Ac_Controller_Response_Part');
            else $res = Ac_Prototyped::factory(array('class' => (string) $signature), 'Ac_Controller_Response_Part');
        return $res;
    }
    
    function __construct(array $options = array()) {
        $this->signature = $options;
        parent::__construct($options);
    }
    
    function matches($signature) {
        if (is_object($signature)) $res = $this === $signature;
        elseif (is_string($signature)) $res = (get_class($this) === $signature);
        else $res = $this->signature == $signature;
        return $res;
    }
    
    function getSignature() {
        return $this->signature;
    }

    protected function doGetRandomPlaceholder() {
        $res = '{'.md5(microtime().rand()).md5(microtime().rand()).'}'; // it's even more random :)
        return $res;
    }
    
    /**
     * @return string Placeholder id to put it into response content
     */
    function createPlaceholder($data) {
        $id = false;
        if ($this->isDeterministic()) $id = array_search($data, $this->placeholders, true);
        if ($id === false) {
            $id = $this->doGetRandomPlaceholder();
            $this->placeholders[$id] = $data;
        } else {
        }
        return $id;
    }
    
    function evaluateAllPlaceholders() {
        $res = array();
        foreach ($this->placeholders as $id => $data) $res[$id] = $this->evaluatePlaceholder($data);
        return $res;
    }
    
    function evaluatePlaceholder($data) {
        return (string) $data;
    }
    
    protected function doReplacePlaceholders($content) {
        $res = strtr($content, $this->evaluateAllPlaceholders());
        return $res;
    }
    
    final function replacePlaceholders($content) {
        if (!$this->lock) {
            $this->lock++;
            $res = $this->doReplacePlaceholders($content);
            $this->lock--;
        } else {
            $res = $content;
        }
        return $res;
    }
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * Template method
     * This method is called from $response->__wakeup. Allows to set $response->obsolete flag when response is restored from cache.  
     */
    function handleResponseWakeup(Ac_Controller_Response $response) {
    }
    
    
    
}