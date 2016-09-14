<?php

class Ac_Etl_Log_Item {
    
    var $message = '';
    
    /**
     * @var type debug|profile|error|warning
     */
    var $type = 'debug';
    
    var $tags = array();
    
    var $spentTime = 0;
    
    var $spentMemory = 0;
    
    var $dateTime;
    
    var $id = false;
    
    function __construct($message, $type = 'debug', $tags = array(), $extra = array(), $profile = false) {
        $this->message = $message;
        $this->type = $type;
        $this->tags = $tags;
        $this->id = uniqid('log');
       
        $this->dateTime = date('Y-m-d H:i:s');
        
        if ($profile) {
            $this->beginProfiling();
        }
        
        foreach($extra as $k => $v) {
            $this->$k = $v;
        }
    }
    
    function getStringDetails() {
        return '';
    }
    
    function getHtmlDetails() {
        return '';
    }
    
    function getDirectTags() {
        return array_unique($this->tags);
    }
    
    function getAllTags() {
        return Ac_Etl_Log_Stats::getAllTags($this->tags);
    }

    function beginProfiling() {
        $this->startTime = microtime(true);
        $this->startMem = memory_get_usage();
    }
    
    function endProfiling() {
        if (isset($this->startTime)) {
            $this->spentTime += microtime(true) - $this->startTime;
            unset($this->startTime);
        }
        if (isset($this->startMem)) {
            $this->spentMemory += (memory_get_usage() - $this->startMem)/1024/1024;
            unset($this->startMem);
        }
    }
    
    /**
     * Should be called by logger that first accepted it 
     */
    function acceptedBy(Ac_Etl_I_Logger $logger) {
        $this->endProfiling();
    }
    
}