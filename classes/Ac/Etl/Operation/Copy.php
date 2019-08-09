<?php

class Ac_Etl_Operation_Copy extends Ac_Etl_Operation {
    
    /**
     * @var array targetColumn => srcColumnOrExpression
     */
    protected $colMatches = array();
    
    protected $targetTableId = false;
    
    protected $ignoreLineNumbers = false;
    
    protected $forwardSelectorId = false;
    
    protected $reverseSelectorId = false;

    /**
     * @var array srcColumn => targetColumnOrExpression
     */
    protected $reverseMatches = false;
    
    /**
     * @var array targetKeyColumnOrExpression => srcColumnOrExpression
     * Keys to determine 'existing' rows on copyForward
     */
    protected $forwardKeys = array();
    
    /**
     *  Value for $handleExisting property: will do in a way as no existing rows possible
     */
    const handleExistingIgnore = 0;
    
    /**
     *  Value for $handleExisting property: won't touch rows matched by $forwardKeys
     */
    const handleExistingSkip = 1;
    
    /**
     *  Value for $handleExisting property: will update rows matched by $forwardKeys
     */
    const handleExistingUpdate = 2;
    
    protected $handleExisting = self::handleExistingIgnore;
    
    /**
     * @var array srcKey => targetKey
     */
    protected $reverseKeys = array();

    protected $innerOperations = array();
    
    protected $distinct = true;
    
    protected $cleanTargetTable = false;

    function getSelectPrototype($full = true, $alias = 't') {
        $res = parent::getSelectPrototype($full, $alias);
        if ($full) {
            if ($this->targetTableId) {
                
                $joinMap = $this->mkMap(array('importId' => 'importId'), 'target', 't');
                $target = $this->getImport()->getTable($this->targetTableId);
                if (is_array($target->restriction)) {
                    $joinMap->union($this->mkMap($target->restriction, 'target', false)->rightAreValues());
                }
                if ($this->forwardKeys) {
                    $forwardJoin = $joinMap->cloneObject()->union($this->forwardKeys);
                } else {
                    $forwardJoin = $joinMap;
                }
                
                $res['tables']['target'] = array(
                    'name' => $target->tableName(),
                    'joinsAlias' => 't',
                    'joinsOn' => $forwardJoin->eq(' = ', ' AND '),
                    'joinType' => 'LEFT JOIN',
                );
                
                if ($this->reverseMatches && $this->reverseKeys) {
                    
                    $revJoinMap = $this->mkMap($this->reverseKeys, 't', 'reverse')->flip()->union($joinMap);
                    
                    $res['tables']['reverse'] = array(
                        'name' => $target->tableName(),
                        'joinsAlias' => 't',
                        'joinsOn' => $revJoinMap->eq(' = ', ' AND '),
                        'joinType' => 'INNER JOIN',
                    );
                    
                }
                
            }
        }
        return $res;
    }
    
    protected function doProcess() {
        $res = true;
        if ($this->colMatches) $res = $this->copyForward() !== false;
        if ($res) {
            foreach ($this->innerOperations as $p)
                if (!$p->process()) {
                    $res = false; 
                    break;
                }
            if ($res) $res = $this->copyReverse() !== false;
        }
        return $res;
    }
    
    protected function copyForward() {
        
        $db = $this->getDb();
        $targetTable = $this->getImport()->getTable($this->targetTableId, true);
        
        $copyMap = $this->mkMap($this->colMatches, $targetTable->tableName(), 't');

        if (is_array($targetTable->restriction)) {
            $restMap = $this->mkMap($targetTable->restriction, $targetTable->tableName(), false)->rightAreValues();
            $copyMap->union($restMap);
        }
        
        if ($this->forwardSelectorId !== false) $sel = $this->getSelector($this->forwardSelectorId)->createSelect();
            else $sel = new Ac_Sql_Select($this->getDb(), $this->getSelectPrototype());
           
        $sel->distinct = $this->distinct;
        
        $extra = array('importId' => 'importId');
        
        if (!$this->ignoreLineNumbers) {
            $extra['lineNo'] = 'lineNo';
        }
        
        $copyMap->union($extra);
        
        if ($this->cleanTargetTable) $targetTable->cleanTmpData();
        
        $idPath = $this->getIdPath();
        
        if ($this->handleExisting === self::handleExistingIgnore) {
            $stmt = $copyMap->copyStmt($sel, $this->insertIgnore);
            $res = $db->query("-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/copyForward\n".$stmt);
            $this->addAffected('copyForward');
        } else {
            if (!$this->forwardKeys) 
                throw new Ac_E_Etl("\$forwardKeys must be provided when \$handleExisting property is other than Ac_Etl_Operation_Copy::handleExistingIgnore");

            // update 'existing' firstly, otherwise we won't be able do distinguish them from 'non-existing'
            
            $res = true;
            
            if ($this->handleExisting === self::handleExistingUpdate) { 
                $sel->useAlias('target');
                $sel->where['target'] = 'NOT ISNULL(target.id)';
                $cm2 = $copyMap->cloneObject();
                $cm2->setLeftAlias('target');
                $stmt = $cm2->updateStmt($sel, $this->insertIgnore);
                $res = $db->query("-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/copyForward/updateExisting\n".$stmt);
                $this->addAffected('copyForward.updateExisting');
                $this->addAffected('copyForward');
            }
            
            if (in_array($this->handleExisting, array(self::handleExistingSkip, self::handleExistingUpdate))) {
                // same step for skip and update: copy non-existing
                $sel->useAlias('target');
                $sel->where['target'] = 'ISNULL(target.id)';
                $stmt = $copyMap->copyStmt($sel, $this->insertIgnore);
                $res = $res && $db->query("-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/copyForward/copyNonExisting\n".$stmt);
                $this->addAffected('copyForward.copyNonExisting');
                $this->addAffected('copyForward');
            }
        }
        
        
        return $res;
    }
    
    protected function copyReverse() {
        $res = true;
        
        if ($this->reverseKeys && $this->reverseMatches) {
            if ($this->reverseSelectorId !== false) $sel = $this->getSelector($this->reverseSelectorId)->createSelect();
                else $sel = new Ac_Sql_Select($this->getDb(), $this->getSelectPrototype());
            $sel->setUsedAliases('reverse');
            
            $stmt = $this->mkMap($this->reverseMatches, 't', 'reverse')->updateStmt($sel);
            
            $idPath = $this->getIdPath();
            $res = $this->db->query("-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/copyReverse\n".$stmt);
            $this->addAffected('copyReverse');
            
        }
        return $res;
    }
    
    function setDistinct($distinct) {
        $this->distinct = (bool) $distinct;
    }

    function getDistinct() {
        return $this->distinct;
    }
    
    function setInnerOperations(array $innerOperations) {
        foreach ($this->innerOperations = Ac_Prototyped::factoryCollection($innerOperations, 'Ac_Etl_Operation', array('import' => $this->getImport(), 'parentOperation' => $this, 'parentOperationRelation' => 'inner'), 'id', true, true) as $p) {
            //if (!$p->getImport()) $p->setImport($this->getImport());
            if (!$p->getTable()) {
                if ($t = $this->getTable()) $p->setTable($this->getTable());
                else $p->setTableId($this->getTableId());
            }
        }
    }

    /**
     * @return array
     */
    function getInnerOperations() {
        return $this->innerOperations;
    }
    
    function setColMatches(array $colMatches) {
        $this->colMatches = $colMatches;
    }

    function getColMatches() {
        return $this->colMatches;
    }

    function setReverseMatches(array $reverseMatches) {
        $this->reverseMatches = $reverseMatches;
    }

    function getReverseMatches() {
        return $this->reverseMatches;
    }
    
    function setReverseKeys(array $reverseKeys) {
        $this->reverseKeys = $reverseKeys;
    }

    function getReverseKeys() {
        return $this->reverseKeys;
    }    
    
    function setTargetTableId($targetTableId) {
        $this->targetTableId = $targetTableId;
    }

    function getTargetTableId() {
        return $this->targetTableId;
    }

    function setIgnoreLineNumbers($ignoreLineNumbers) {
        $this->ignoreLineNumbers = (bool) $ignoreLineNumbers;
    }

    function getIgnoreLineNumbers() {
        return $this->ignoreLineNumbers;
    }    
    
    function setForwardSelectorId($forwardSelectorId) {
        $this->forwardSelectorId = $forwardSelectorId;
    }

    function getForwardSelectorId() {
        return $this->forwardSelectorId;
    }

    function setReverseSelectorId($reverseSelectorId) {
        $this->reverseSelectorId = $reverseSelectorId;
    }

    function getReverseSelectorId() {
        return $this->reverseSelectorId;
    }
    
    function setCleanTargetTable($cleanTargetTable) {
        $this->cleanTargetTable = (bool) $cleanTargetTable;
    }

    function getCleanTargetTable() {
        return $this->cleanTargetTable;
    }
    
    function setForwardKeys(array $forwardKeys) {
        $this->forwardKeys = $forwardKeys;
    }

    function getForwardKeys() {
        return $this->forwardKeys;
    }

    function setHandleExisting($handleExisting) {
        if (!in_array($handleExisting, $a = array(self::handleExistingIgnore, self::handleExistingSkip, self::handleExistingUpdate))) {
            throw new Ac_E_Etl("Invalid \$handleExisting value, must be one of ".implode(", ", $a));
        }
        $this->handleExisting = $handleExisting;
    }

    function getHandleExisting() {
        return $this->handleExisting;
    }
    
    function getRightDbName() {
        return $this->getLeftDbName();
    }
    
    function getRightTableName() {
        return $this->import->getTable($this->targetTableId)->sqlTableName;
    }
    
    function getRightDbPrefix() {
        return $this->getLeftDbPrefix();
    }
    
}