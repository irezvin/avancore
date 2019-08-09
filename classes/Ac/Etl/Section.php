<?php

class Ac_Etl_Section extends Ac_Prototyped {

    var $id = false;
    
    /**
     * @var Ac_Etl_Import
     */
    protected $import = false;

    protected $loaderId = false;
    
    /**
     * @var Ac_Etl_Loader
     */
    protected $loader = false;

    protected $operationsAreSet = false;
    
    protected $operationIds = array();

    protected $operationGroups = array();
    
    /**
     * @var array
     */
    protected $operations = false;
    
    protected $operationsInit = false;

    protected $autoBeginLoad = true;

    function setImport(Ac_Etl_Import $import) {
        $this->import = $import;
    }

    /**
     * @return Ac_Etl_Import
     */
    function getImport() {
        return $this->import;
    }

    function setLoaderId($loaderId) {
        if ($this->loader && $loaderId !== $this->loaderId)
            throw new Exception("Cannot setLoaderId() after setLoader()");
        
        $this->loaderId = $loaderId;
    }

    function getLoaderId() {
        return $this->loaderId;
    }

    function setOperationIds(array $operationIds) {
        if ($this->operationsAreSet)
            throw new Exception("Cannot setOperationIds() after setOperations()");
        $this->operationIds = $operationIds;
    }

    function getOperationIds() {
        return $this->operationIds;
    }

    function setOperationGroups(array $operationGroups) {
        if ($this->operationsAreSet)
            throw new Exception("Cannot setOperationGroups() after setOperations()");
        $this->operationGroups = $operationGroups;
    }

    function getOperationGroups() {
        return $this->operationGroups;
    }

    /**
     * @return array
     */
    function getOperations() {
        
        if (!$this->operationsInit === false) {
            $this->operationsInit = true;
            
            if (!$this->import) throw new Exception("setImport() before trying to access any of operations");
            
            // Operations specified by ids or Groups
            if ($this->operationIds || $this->operationGroups) {
                $this->operations = array();
                foreach ($this->operationIds as $id) {
                    $this->operations[$id] = $this->import->getOperation($id);
                }
                foreach ($this->operationGroups as $group) {
                    foreach ($this->import->getOperations($group) as $p) {
                        $this->operations[$p->getId()] = $p;
                    }
                }
            } else {
                // Operations specified by prototypes and ids
                if (!is_array($this->operations)) $this->operations = array();
                $prototypes = $this->operations;
                foreach ($prototypes as $k => $v) {
                    if (is_string($v)) $prototypes[$k] = $this->import->getOperation($v);
                }
                $this->operations = Ac_Prototyped::factoryCollection($prototypes, 'Ac_Etl_Operation', array('import' => $this->import), 'id', true);
            }
        }
        return $this->operations;
    }
    
    function setOperations(array $operations) {
        $this->operationsAreSet = true;
        if ($this->operationIds || $this->operationGroups)
            throw new Exception("Cannot setOperations() after setOperationIds() or setOperationGroups()");
        $this->operationIds = false;
        $this->operationGroups = false;
    }

    function setLoader(Ac_Etl_Loader $loader) {
        $this->loader = $loader;
    }

    /**
     * @return Ac_Etl_Loader
     */
    function getLoader() {
        if ($this->loader === false) {
            if (strlen($this->loaderId)) $this->loader = $this->import->getLoader($this->loaderId);
        }
        return $this->loader;
    }
    
    function getNumReceivedLines() {
        return $this->getLoader()->getNumReceivedLines();
    }
    
    function beginLoad() {
        return $this->getLoader()->begin();
    }

    function setAutoBeginLoad($autoBeginLoad) {
        $this->autoBeginLoad = (bool) $autoBeginLoad;
    }

    function getAutoBeginLoad() {
        return $this->autoBeginLoad;
    }    
    
    function getBeganLoad() {
        return $this->getLoader()->getBegan();
    }
    
    function pushLine(array $line, $lineNo = null) {
        if ($this->autoBeginLoad && !$this->getLoader()->getBegan()) $this->beginLoad();
        return $this->getLoader()->pushLine($line, $lineNo);
    }
    
    function pushLines(array $lines, $lineNo = null, $beginAndEnd = false) {
        if ($this->autoBeginLoad && !$beginAndEnd && !$this->getLoader()->getBegan()) $this->beginLoad();
        return $this->getLoader()->pushLines($lines, $lineNo, $beginAndEnd);
    }
    
    function endLoad() {
        return $this->getLoader()->end();
    }
    
    function process(& $okOperationIds = array()) {
        return $this->import->process($this->getOperations(), $okOperationIds);
    }
    
    function setId($id) {
        $this->id = $id;
    }
    
    function getId() {
        return $this->id;
    }
    
}