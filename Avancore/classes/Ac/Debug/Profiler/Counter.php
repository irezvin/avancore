<?php

class Ac_Debug_Profiler_Counter extends Ac_Autoparams {
    
    protected $start = false;
    
    protected $last = array();
    
    protected $total = array();
    
    protected $max = array();
    
    protected $passes = array();
    
    protected $numPasses = 0;
    
    protected $id = false;
    
    var $details = false;
    
    var $track = false;
    
    var $autoStop = true;
    
    function __destruct() {
        if ($this->autoStop) $this->stop();
    }
    
    protected function setId($id) {
        $this->id = $id;
    }
    
    function getId() {
        return $this->id;
    }
    
    function getPassData($details = false) {
        $res = array('time' => microtime(true));
        if ($details !== false) $res['details'] = $details;
        return $res;
    }
    
    function start($details = false) {
        if ($this->start !== false) $this->stop();
        $this->start = $this->getPassData($details);
    }
    
    function stop() {
        if ($this->start !== false) {
            $curr = $this->getPassData(isset($this->start['details'])? $this->start['details'] : false);
            $this->add($curr);
            $this->start = false;
        }
    }
    
    function getMax() {
        return $this->max;
    }
    
    function getNumPasses() {
        return $this->numPasses;
    }
    
    function getTotal() {
        return $this->total;
    }
    
    function getLastPass() {
        return $this->last;
    }
    
    function getDetails() {
        $res = (is_array($this->last) && $this->last['details'])? $this->last['details'] : false;
        return $res;
    }
    
    protected function add(array $pass) {
        $s = $this->start;
        $this->numPasses++;
        $dTime = $pass[$k = 'time'] - $s[$k];
        $pass['time'] = $dTime;
        $this->last = $pass;
        $this->totalMax($k, $dTime);
        if ($this->track) $this->passes[] = $pass;
        $this->log();
    }
    
    protected function log() {
        Ac_Debug_Profiler::getInstance()->log($this);
    }
    
    protected function totalMax($key, $value) {
        if (!isset($this->total[$key])) $this->total[$key] = 0;
        $this->total[$key] += $value;
        if (!isset($this->max[$key])) $this->max[$key] = $value;
            elseif ($this->max[$key] < $value) $this->max[$key] = $value;
    }
    
}