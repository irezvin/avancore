<?php

class Ac_E_Registry extends Exception {

    const opSetRegistry = 'setRegistry';
     
    const opAddRegistry = 'addRegistry';
     
    const opMergeRegistry = 'addRegistry';
    
    protected $targetPath;
    
    protected $actualDepth;
    
    protected $opType;
    
    protected $regDescr;
    
    protected $details;
    
    function __construct(Ac_I_Registry $registry, array $targetPath, $actualDepth, $opType, $details = '') {
        $this->targetPath = $targetPath;
        if (!is_int($actualDepth)) throw Ac_E_InvalidCall::wrongType('actualDepth', $actualDepth, 'int');
        $this->actualDepth = $actualDepth;
        if (!in_array($opType, $a = array(self::opAddRegistry, self::opSetRegistry))) 
            throw Ac_E_InvalidCall::outOfSet ('opType', $opType, $a);
        $this->opType = $opType;
        $this->regDescr = get_class($registry);
        $this->details = $details;
        
        parent::__construct($this->calcMessage());
    }
    
    protected function calcMessage() {
        $strPath = Ac_Util::arrayToPath($this->targetPath);
        $strActualPath = Ac_Util::arrayToPath(array_slice($this->targetPath, 0, $this->actualDepth));
        $res = "Cannot {$this->regDescr}->{$this->opType}({$strPath}) at ";
        if (strlen($strActualPath)) $res .= $strActualPath; else $res .= "the root";
        if (strlen($this->details)) $res .= ": {$this->details}";
        return $res;
    }
    
    static function detailsWrongSegment($ptr) {
        $descr = is_object($ptr)? get_class($ptr) : gettype($ptr);
        return $descr.' found; expected array or Ac_I_Registry';
    }
    
    function getTargetPath() {
        return $this->targetPath;
    }
    
    function getActualDepth() {
        return $this->actualDepth;
    }
    
    function getOpType() {
        return $this->opType;
    }
    
    function getRegDescr() {
        return $this->regDescr;
    }
    
    function getDetails() {
        return $this->details;
    }
    
}