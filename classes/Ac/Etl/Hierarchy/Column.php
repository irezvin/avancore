<?php

class Ac_Etl_Hierarchy_Column extends Ac_Etl_Column {
    
    protected $listSeparator = false;

    protected $pathSeparator = '/';

    protected $parentChildTableId = false; // <- ccImportId

    protected $catNameCol = 'categoryName'; // <- ccImportCategoryNameCol

    protected $parentNameCol = 'parentName'; // <- ccImportParentNameCol

    protected $itemToCatTableId = false; // <- itemCategoryImportId

    protected $itemCatNameCol = false; // <- itemCategoryNameCol
    
    protected $operationId = false;
    
    protected $getValueFromDestColumn = false;
    
    /**
     * @var bool
     */
    protected $leaveOriginalValue = false;
    

    function setGetValueFromDestColumn($getValueFromDestColumn) {
        $this->getValueFromDestColumn = $getValueFromDestColumn;
    }

    function getGetValueFromDestColumn() {
        return $this->getValueFromDestColumn;
    }
    
    function apply(Ac_I_Param_Source $source, array & $destRecords, array & $errors = array()) {
        $dc = $this->getDestColName();
        $t = $this->getDestTableId();
        
        $itemToCatTableId = $this->getItemToCatTableId();
        $itemCatNameCol = $this->getItemCatNameCol();
        $catNameCol = $this->getCatNameCol();
        $parentNameCol = $this->getParentNameCol();
        $parentChildTableId = $this->getParentChildTableId();
        
        if ($this->getValueFromDestColumn) {
            $cc = Ac_Util::getArrayByPath($destRecords, array($t, 0, $dc));
            $res = true;
        } else {
            $res = parent::apply($source, $destRecords, $errors);            
            $cc = Ac_Util::getArrayByPath($destRecords, array($t, 0, $dc));
        }
        
        if ($res) {
            if (!$this->leaveOriginalValue) unset($destRecords[$t][0][$dc]);
            if (strlen($cc)) {
                $cc = trim($cc);
                if (strlen($this->listSeparator)) {
                    $cats = preg_split('/\s*'.preg_quote($this->listSeparator).'\s*/', $cc);
                    
                } else {
                    $cats = strlen($cc)? array($cc) : array();
                }
                foreach ($cats as $i => $path) {
                    if (strlen($this->pathSeparator)) {
                        $segments = preg_split('#\s*'.preg_quote($this->pathSeparator, '/').'\s*#', $path);
                    } else {
                        $segments = array($path);
                    }
                    $parent = false;
                    foreach ($segments as $j => $segment) {
                        if (strlen($itemToCatTableId) && strlen($itemCatNameCol)) 
                            $destRecords[$itemToCatTableId][$i][$itemCatNameCol] = $segment;
                        
                        if (strlen($parentChildTableId)) {
                            if (strlen($catNameCol)) {
                                $destRecords[$parentChildTableId][$i.' '.$j][$catNameCol] = $segment;
                            }
                            if (strlen($parentNameCol)) {
                                $destRecords[$parentChildTableId][$i.' '.$j][$parentNameCol] = strlen($parent)? $parent : null;
                            }
                        }
                        $parent = $segment;
                    }
                }
            }
        }
        
        return $res;
    }    

    protected function getOwnOrOperationProperty($propName, $procPropName = false) {
        if ($this->$propName === false) {
            if ($this->operationId) {
                $proc = $this->import->getOperation($this->operationId);
                if (!($proc instanceof Ac_Etl_Hierarchy_Operation))
                    throw new Ac_E_Etl("Operation with '$this->operationId' should be Ac_Etl_Hierarchy_Operation");
                $res = $this->import->getOperation($this->operationId)->getCalculatedProperty(strlen($procPopName)? $propName : $propName);
            } else {
                $res = false;
            }
        } else {
            $res = $this->$propName;
        }
        return $res;
    }
    
    function setOperationId($operationId) {
        $this->operationId = $operationId;
    }

    function getOperationId() {
        return $this->operationId;
    }    
    
    function getDestTableId() {
        return $this->destTableId;
    }
    
    function setListSeparator($listSeparator) {
        $this->listSeparator = $listSeparator;
    }

    function getListSeparator() {
        return $this->listSeparator;
    }

    function setPathSeparator($pathSeparator) {
        $this->pathSeparator = $pathSeparator;
    }

    function getPathSeparator() {
        return $this->pathSeparator;
    }

    function setParentChildTableId($parentChildTableId) {
        $this->parentChildTableId = $parentChildTableId;
    }

    function getParentChildTableId() {
        return $this->getOwnOrOperationProperty('parentChildTableId', 'ccImportId');
    }

    function setCatNameCol($catNameCol) {
        $this->catNameCol = $catNameCol;
    }

    function getCatNameCol() {
        return $this->getOwnOrOperationProperty('catNameCol', 'ccImportCategoryNameCol');
    }

    function setParentNameCol($parentNameCol) {
        $this->parentNameCol = $parentNameCol;
    }

    function getParentNameCol() {
        return $this->getOwnOrOperationProperty('parentNameCol', 'ccImportParentNameCol');
    }

    function setItemToCatTableId($itemToCatTableId) {
        $this->itemToCatTableId = $itemToCatTableId;
    }

    function getItemToCatTableId() {
        $res = $this->getOwnOrOperationProperty('itemToCatTableId', 'itemCategoryImportId');
        if ($res === false && !strlen($this->getListSeparator())) {
            $res = $this->getDestTableId();
        }
        return $res;
    }

    function setItemCatNameCol($itemCatNameCol) {
        $this->itemCatNameCol = $itemCatNameCol;
    }

    function getItemCatNameCol() {
        return $this->getOwnOrOperationProperty('itemCatNameCol', 'itemCategoryNameCol');
    }    

    function setLeaveOriginalValue($leaveOriginalValue) {
        $this->leaveOriginalValue = (bool) $leaveOriginalValue;
    }

    function getLeaveOriginalValue() {
        return $this->leaveOriginalValue;
    }
   
}