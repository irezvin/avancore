<?php

class Ac_Debug_Profiler {
    
    private static $instance = null;
    
    protected $counters = array();
    
    var $enabled = true;

    function __destruct() {
        foreach ($this->counters as $c) if ($c->autoStop) $c->stop();
    }
    
    /**
     * @return Ac_Debug_Profiler
     */
    static final function getInstance() {
        if (!self::$instance) self::$instance = new Ac_Debug_Profiler();
        return self::$instance;
    }
    
    /**
     * Sets new instance; returns old one
     * 
     * @return Ac_Debug_Profiler
     */
    static final function setInstance(Ac_Debug_Profiler $instance) {
        $res = self::$instance;
        self::$instance = $instance;
        return $res;
    }
    
    function start($id, $details = false) {
        if (!$this->enabled) return;
        $this->getCounter($id)->start($details);
    }
    
    function stop($id) {
        if (!$this->enabled) return;
        if (isset($this->counters[$id])) $this->getCounter($id)->stop();
    }
    
    static function on($id, $details = false) {
        Ac_Debug_Profiler::getInstance()->start($id, $details);
    }
    
    static function off($id) {
        Ac_Debug_Profiler::getInstance()->stop($id);
    }
    
    /**
     * @param string $id 
     * @return 
     */
    function getCounter($id) {
        if (!isset($this->counters[$id]))
            $this->counters[$id] = new Ac_Debug_Profiler_Counter(array('id' => $id));
        return $this->counters[$id];
    }
    
    function deleteCounter($id) {
        unset($this->counters[$id]);
    }
 
    protected function f($passOrTotal) {
        if (isset($passOrTotal['time'])) $passOrTotal['time'] = sprintf('%0.5f', $passOrTotal['time']);
        return $passOrTotal;
    }
    
    function log(Ac_Debug_Profiler_Counter $counter) {
        Ac_Debug_Log::l('Profiler', $counter->getId(), array(
            'pass' => $counter->getNumPasses(),
            'last' => $this->f($counter->getLastPass()),
            'total' => $this->f($counter->getTotal()),
        ));
    }
    
}