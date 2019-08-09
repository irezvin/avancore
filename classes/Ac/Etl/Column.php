<?php

class Ac_Etl_Column extends Ac_Prototyped {

    protected $id = false;
    
    protected $param = array();
    
    protected $srcColName = false;
    
    protected $destTableId = false;
    
    protected $destColName = false;
    
    protected $ignoreIfNoValue = false;
    
    protected $import = false;
   
    /**
     * @var bool
     */
    protected $add = false;

    /**
     * @var array
     */
    protected $destTableExtra = false;
    
    function setImport(Ac_Etl_Import $import) {
        $this->import = $import;
    }
    
    /**
     * @return ImportImport
     */
    function getImport() {
        return $this->import;
    }
    
    function getSrcColName($srcColName) {
        if (strlen($this->srcColName)) $res = $this->srcColName;
            else $res = $this->id;
        return $res;
    }
    
    function setSrcColName($srcColName) {
        $this->srcColName = $srcColName;
        if ($this->param) $this->param->setPath($this->srcColName);
    }
    
    function setId($id) {
        if ($this->id && $this->id !== $id) throw new Exception("Can setId() only once");
        $this->id = $id;
        if ($this->param) $this->param->setId($this->id);
    }
    
    function getId() {
        return $this->id;
    }
    
    function hasPublicVars() {
        return true;
    }
 
    function setParam($param) {
        $this->param = Ac_Prototyped::factory($param, 'Ac_Param');
        $this->param->setId($this->id);
        if (strlen($this->srcColName))
            $this->param->setPath($this->srcColName);
    }
    
    /**
     * @return Ac_Param 
     */
    function getParam() {
        return $this->param;
    }
    
    function apply(Ac_I_Param_Source $source, array & $destRecords, array & $errors = array()) {
        if (!$this->param) {
            $options = array('id' => $this->id);
            if ($this->srcColName) $options['path'] = $this->srcColName;
            $this->param = new Ac_Param($options);
        }
        $this->param->reset();
        $this->param->setSource($source);
        $value = $this->param->getValue();
        $errors = $this->param->getErrors();
        if (!strlen($this->destColName)) $this->destColName = $this->id;
        if ($this->param->hasValue() || !$this->ignoreIfNoValue) {
            $this->putData($value, $destRecords);
        }
        return !$this->param->getErrors();
    }
    
    function putData($value, array & $destRecords, $colName = false) {
        if ($this->destTableId) {
            if ($colName === false) $colName = $this->destColName;
            $idx = 0;
            if ($this->add && isset($destRecords[$this->destTableId])) {
                while(isset($destRecords[$this->destTableId][$idx])) $idx++;
            }
            if ($this->destTableExtra) {
                if (!isset($destRecords[$this->destTableId])) $destRecords[$this->destTableId] = array();
                if (!isset($destRecords[$this->destTableId][$idx])) $destRecords[$this->destTableId][$idx] = array();
                Ac_Util::ms($destRecords[$this->destTableId][$idx], $this->destTableExtra);
            }
            $destRecords[$this->destTableId][$idx][$colName] = $value;
        }
    }
    

    function setDestTableId($destTableId) {
        $this->destTableId = $destTableId;
    }

    function getDestTableId() {
        return $this->destTableId;
    }

    function setDestColName($destColName) {
        $this->destColName = $destColName;
    }

    function getDestColName() {
        if (strlen($this->destColName)) $res = $this->destColName;
            else $res = $this->id;
        return $res;
    }

    function setIgnoreIfNoValue($ignoreIfNoValue) {
        $this->ignoreIfNoValue = $ignoreIfNoValue;
    }

    function getIgnoreIfNoValue() {
        return $this->ignoreIfNoValue;
    }    

    function setDestTableExtra(array $destTableExtra) {
        $this->destTableExtra = $destTableExtra;
    }

    /**
     * @return array
     */
    function getDestTableExtra() {
        return $this->destTableExtra;
    }    
 
    function setAdd($add) {
        $this->add = (bool) $add;
    }

    /**
     * @return bool
     */
    function getAdd() {
        return $this->add;
    }    
    
}