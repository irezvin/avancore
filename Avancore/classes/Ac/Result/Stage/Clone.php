<?php

class Ac_Result_Stage_Clone extends Ac_Result_Stage_Morph {

    /**
     * @var array oldStringObjectMark => newStringObjectInstance
     */
    protected $cloneList = array();

    protected $defaultTraverseClasses = array ('Ac_Result', 'Ac_I_Deferred', 'Ac_I_StringObject_ClonedWithBuffer');
    
    protected static $isRunning = 0;
    
    static function getIsRunning() {
        return self::$isRunning;
    }
    
    function resetTraversal($classes = null) {
        parent::resetTraversal($classes);
        $this->cloneList = array();
    }
    
    protected function checkOverride() {
        if ($this->getIsChangeable()) {
            $item = $this->current;
            $mark = $item->getStringObjectMark();
            if (!isset($this->cloneList[$mark]))
                $this->cloneList[$mark] = clone $item;
            $this->replaceCurrentObject($this->cloneList[$mark]->getStringObjectMark());
            $this->current = $this->cloneList[$mark];
        }
        return parent::checkOverride();
    }
        
    function traverse($classes = null) {
        self::$isRunning++;
        parent::traverse($classes);
        $this->cloneList = array();
        self::$isRunning--;
    }
    
}