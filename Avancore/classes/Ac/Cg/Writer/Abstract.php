<?php

abstract class Ac_Cg_Writer_Abstract extends Ac_Prototyped {

    protected $start = false;
    
    /**
     * @var int
     */
    protected $fileCount = false;

    /**
     * @var int
     */
    protected $totalSize = false;

    /**
     * @var float
     */
    protected $timeExec = false;

    /**
     * @return int
     */
    function getFileCount() {
        return $this->fileCount;
    }

    /**
     * @return int
     */
    function getTotalSize() {
        return $this->totalSize;
    }

    /**
     * @return float
     */
    function getTimeExec() {
        return $this->timeExec;
    }
    
    /**
     * @var Ac_Cg_Writer_Abstract
     */
    protected $nextWriter = false;

    function setNextWriterPrototype(array $prototype) {
        $this->nextWriter = Ac_Prototyped::factory($prototype, 'Ac_Cg_Writer');
    }
    
    function setNextWriter(Ac_Cg_Writer_Abstract $nextWriter = null) {
        $this->nextWriter = $nextWriter;
    }

    /**
     * @return Ac_Cg_Writer_Abstract
     */
    function getNextWriter() {
        return $this->nextWriter;
    }    
    
    function begin() {
        $this->fileCount = 0;
        $this->totalSize = 0;
        $this->timeExec = 0;
        $this->start = microtime(true);
        if ($this->nextWriter) $this->nextWriter->begin();
    }
    
    function end() {
        if ($this->nextWriter) $this->nextWriter->end();
    }
    
    function writeContent($reativePath, $content) {
        $this->fileCount++;
        $this->totalSize += strlen($content);
        $this->doWriteContent($reativePath, $content);
        if ($this->nextWriter) $this->nextWriter->writeContent($reativePath, $content);
    }
    
    abstract protected function doWriteContent($relativePath, $content);
    
}