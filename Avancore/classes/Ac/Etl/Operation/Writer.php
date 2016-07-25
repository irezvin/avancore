<?php

class Ac_Etl_Operation_Writer extends Ac_Etl_Operation {

    /**
     * SQL name of data table to write to
     * @var string
     */
    protected $targetSqlName = false;
    
    /**
     * @var array importNameColumn => dataNameColumn
     */
    protected $nameMap = array();
    
    /**
     * @var array importNameColumn => dataNameColumn
     */
    protected $keyMap = array();
    
    /**
     * @var array|Ac_Etl_I_Matcher
     * array ($importNameColumn => $dataNameColumn)
     */ 
    protected $contentMap = array();
    
    /**
     * @var array (dataContentColumn => defaultValueOrExpression) default values for data rows to be created
     */
    protected $defaults = array();
    
    /**
     * @var array (requirementName => instanceOrPrototypeOf_Ae_Sql_Filter)
     */
    protected $requirements = array();
    
    /**
     * @var array (requirementName)
     */
    protected $createOnlyRequirementKeys = array();
    
    /**
     * @var array (requirementName)
     */
    protected $updateOnlyRequirementKeys = array();

    /**
     * Whether to update records or not
     * @var bool
     */
    protected $updateAllowed = true;

    /**
     * Whether to create records or not
     * @var type 
     */
    protected $createAllowed = true;
    
    /**
     * @var bool Whether NULL fields from contentMap should be updated
     */
    protected $updateNulls = false;
    
    protected $selectorsCreated = false;
    
    protected $namesAreKeys = false;
    
    protected $debug = false;
    
    protected $debugRecordsEquality = false;
    
    protected $targetDbiTable = false;
    
    /**
     * Names of columns in target table that can be nullable
     * @var array (col1, col2)
     */
    protected $nullableTargetColumns = false;
    
    protected $nonNullableTargetColumns = false;
    
    /**
     * @var bool
     */
    protected $dontUpdateStatus = false;
    
    protected $nameProvider = false;
    
    protected $currStats = array();
    
    /**
     * @var bool
     * target columns from $this->nameMap won't be added to content map (useful for cases
     * when names are in foreign table)
     */
    protected $dontWriteNames = false;

    /**
     * @var FALSE|string
     * Name of Target table' AI column that will be populated with pre-created values
     */
    protected $aiColumn = false;
    
    /**
     * @var FALSE|string
     * Name of Importer table' column that will contain values of AI column for created records
     */
    protected $myAiColumn = false;

    protected $draftColumn = false;
    
    protected $toCreateStat = 'noKeysOrNames';
    
    protected $createdStat = 'keysOk';
    
    /**
     * @var bool
     */
    protected $useReplace = false;
    
    /**
     * @var array
     */
    protected $lockExtra = false;
    
    function setDebugRecordsEquality($debugRecordsEquality) {
        $this->debugRecordsEquality = (bool) $debugRecordsEquality;
    }

    function getDebugRecordsEquality() {
        return $this->debugRecordsEquality;
    }    
    
    /**
     * @return Ac_Sql_Dbi_Table
     */
    function getTargetDbiTable() {
        if ($this->targetDbiTable === false) {
            $this->targetDbiTable = $this->import->getTargetDbi()->getTable($this->targetSqlName);
        }
        return $this->targetDbiTable;
    }

    function getNullableTargetColumns() {
        if ($this->nullableTargetColumns === false) {
            $this->nullableTargetColumns = array();
            $t = $this->getTargetDbiTable();
            foreach ($t->listColumns() as $c) {
                $col = $t->getColumn($c);
                if ($col->nullable) $this->nullableTargetColumns[] = $c;
            }
        }
        return $this->nullableTargetColumns;
    }
    
    function getNonNullableTargetColumns() {
        if ($this->nonNullableTargetColumns === false) {
            $this->nonNullableTargetColumns = array_diff($this->getTargetImporterDbiTable()->listColumns(), $this->getNullableTargetColumns());
        }
        return $this->nonNullableTargetColumns;
    }
    
    protected function doProcess() {

        $res = true;
        
        $oldStats = array();
        
        $allProcessed = false;
        
        $this->currStats = array();
        
        $this->listSelectors(); // We have to create selectors since that will set our $toCreateStat and $createdStat columns
        
        do {
            
            //$stats = $this->getStatistics(array('canCreate', 'namesNotFound', 'namesFound', 'keysOk', 'changed', 'notChanged'));
        
            if ($this->debug) var_dump('At begin: ', $this->getStatistics());
            
            if ($this->currStats && (serialize($oldStats) == serialize($this->currStats))) {
                throw new Ac_E_Etl("Algorhythm stalled: stats not changed after an iteration!");
            }
            
            $oldStats = $this->currStats;
            
            // TODO: check if create is allowed; if not and there are records 'to create', flag them as cantCreate
            
            $nf = null;
            
            $this->checkAiConfig();
            if ($this->myAiColumn) $this->createdStat = 'keysOk';
            
            if ($this->getCreateRequirements()) {
                
                $numToCreate = $this->gStats('numToCreate');
                
                if ($this->updatesStatus(true)) {
                    $cantCreate = $this->gStats('cantCreate');
                    $this->flagBad($this->getSelector('cantCreate')->createSelect());
                }
                
                
            } else {
                $numToCreate = $this->gStats($this->toCreateStat);
            }
            
            if ($numToCreate) {
                
                if ($this->getCreateAllowed()) {

                    $oldNamesFound = $this->gStats($this->createdStat);
                    if ($this->createRecords()) {
                        $newNamesFound = $nf = $this->gStats($this->createdStat);
                        if (($newNamesFound <= $oldNamesFound) && !$this->dontWriteNames)
                            throw new Ac_E_Etl("createRecords() didn't led to name resolution");
                    } else {
                        $res = false;
                    }
                    
                } else {
                    if ($this->updatesStatus(true)) $this->flagBad($this->getSelector($this->toCreateStat)->createSelect(), 'cantCreate');
                }
                
            }
            
            if ($res) {

                if ($this->namesAreKeys || !$this->nameMap) {
                    
                    $numToCopyKeys = 0;
                    
                } else {
                    
                    if ($this->aiColumn) $nf = null;
                        
                    $numToCopyKeys = $nf === null? $this->gStats('namesFound') : $nf; 

                    if ($numToCopyKeys) {
                        
                        $oldMatchingKeys = $this->gStats('keysOk');
                        
                        if ($this->updateKeys()) {

                            $newMatchingKeys = $this->gStats('keysOk');

                            if ($newMatchingKeys <= $oldMatchingKeys)
                                throw new Ac_E_Etl("updateKeys() didn't led to key resolution");

                            $numToCopyKeys = $this->gStats('namesFound');

                        } else {
                            $res = false;
                        }
                    }

                    if ($this->updatesStatus(true) && $this->gStats('wrongKeys')) {
                        $this->flagBad($this->getSelector('wrongKeys')->createSelect(), 'notFound');
                    }
                }
            }

            
            // TODO: don't process records that were created
            
            if ($res) {
                
                if ($this->getUpdateAllowed()) {
                    
                    $numToUpdate = $this->gStats('changed');

                    if ($this->getUpdateRequirements()) {

                        if ($this->updatesStatus(true) && $this->gStats('cantUpdate')) {
                            if (!$this->dontUpdateStatus) $this->flagBad($this->getSelector('cantUpdate')->createSelect(), 'cantUpdate');
                        }

                    }
                    
                    if ($numToUpdate) {
                        if ($this->updateRecords()) {
                            $newNumToUpdate = $this->gStats('changed');
                            if ($newNumToUpdate >= $numToUpdate)
                                throw new Ac_E_Etl("updateRecords() didn't decrease number of to-update records");
                            $numToUpdate = $newNumToUpdate;
                        } else {
                            $res = false;
                        }
                    }
                    
                    if ($this->updatesStatus() && $this->gStats('notChanged')) {
                        $this->flag($this->getSelector('notChanged')->createSelect(), 'unchanged', false);
                    }
                    
                } else {
                    $numToUpdate = 0;
                    $this->flag($this->getSelector('keysOk')->createSelect(), 'cantUpdate');
                }
                
            }
                        
                
            if ($res && !$numToCreate && !$numToCopyKeys && !$numToUpdate) {
                $allProcessed = true;
            }
        
        } while ($res && !$allProcessed);
        
        return $res;
        
    }
    
    protected function updateKeys() {
        $sel = $this->getSelector('namesFound')->createSelect();
        $kMap = $this->mkMap($this->keyMap, 't', 'dataN');
        
        $idPath = $this->getIdPath();
        $tags = "-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/updateKeys\n";
        $res = $this->db->query($stmt = $tags.$kMap->updateStmt($sel, true));
        $this->addAffected("modify.updateKeys");
        
        return $res;
    }

    protected function reserveAi(Ac_Sql_Select $sel) {
        $db = $this->getDb();
        
        // determine locks
        $locks = array();
        $locks[] = $this->import->tableOfTargetDb($this->targetSqlName, 'string'); // lock this in first place
        /*foreach ($sel->getAllAliases() as $alias) {
            $locks[$alias] = $sel->getTable($alias)->name;
        }
        $locks = array_merge($locks, $this->extraLocks);*/
        $strLocks = array();
        foreach ($locks as $alias => $lock) {
            //$tbl = $db->n($lock);
            $tbl = $lock;
            if (!is_numeric($alias)) $tbl .= ' AS '.$db->n($alias);
            $tbl .= ' WRITE';
            $strLocks[] = $tbl;
        }
        $strLocks = implode(", ", $strLocks);
        
        $count = $this->gStats($this->toCreateStat);
        
        $db->query("LOCK TABLES ".$strLocks);
        
        $ai = $db->fetchValue("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ".$db->q($db->replacePrefix($this->targetSqlName))." AND TABLE_SCHEMA = ".$db->q($this->import->getTargetDbName()));
        
        // If we don't have ALTER TABLE privilege, we have to lock ALL tables that will be used by creation statement (code is commented above) and unlock after everything is done.
        
        $db->query("ALTER TABLE ". $this->import->tableOfTargetDb($this->targetSqlName, 'string').' AUTO_INCREMENT = '.($ai + $count));
        $db->query("UNLOCK TABLES");
        
        $db->query("SET @ai := IFNULL(".$db->q($ai - 1).", 0)");
        $stmt = "UPDATE ".$sel->getFromClause(false).' SET t.'.$this->db->n($this->draftColumn).' = 1, t.'.$this->db->n($this->myAiColumn).' = (@ai := @ai + 1)'." ".$sel->getWhereClause(true);
        $db->query($stmt);
    }
    
    protected function createRecords() {
        // map dest => src
        
        if ($this->dontWriteNames) {
            $allMap = $this->mkMap($this->getContentMap(true), $this->targetTableName(), 't')
                ->applyDefaults($this->defaults);
        } else {
            $allMap = $this->mkMap($this->nameMap, 't', $this->targetTableName())
                ->union($this->getContentMap(true))
                ->flip()
                ->applyDefaults($this->defaults);
        }
        
        $sel = $this->getSelector($this->toCreateStat)->createSelect();
        
        if ($this->myAiColumn) {
            $this->reserveAi($sel);
            $allMap->union(array($this->aiColumn => $this->myAiColumn));
            $sel = $this->getSelector('drafts')->createSelect();
        }
        
        if (!$this->dontUpdateStatus) $this->flag ($sel, 'created');
                
        //$stmt = $this->copyStmt($sel, $map, $this->targetSqlName, 't');
        
        $stmt = $allMap->copyStmt($sel, $this->insertIgnore, $this->useReplace);
        
        $idPath = $this->getIdPath();
        $tags = "-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/createRecords\n";
        
        $res = $this->db->query($tags.$stmt);
        $this->addAffected("modify.createRecords");
        
        if ($this->myAiColumn) {
            $sel = $this->getSelector('formerDrafts')->createSelect();
            $stmt = "UPDATE ".$sel->getFromClause(false).' SET t.'.$this->db->n($this->draftColumn).' = 0'
                    .' '.$sel->getWhereClause(true);
            $this->db->query($stmt);
        }
        
        if ($this->debug) var_dump($stmt);
        
        return $res;
    }
    
    protected function updateRecords() {
        
        if ($this->namesAreKeys) {
            $map = $this->mkMap($this->getContentMap(true), 't', 'dataK');
        } else {
            $map = $this->mkMap($this->nameMap, 't', 'dataK')->union($this->getContentMap(true));
            //$map = array_merge(array_flip($this->nameMap), $this->contentMap);
        }
        
        $map->flip();
        
        $sel = $this->getSelector('changed')->createSelect();
        
        if (!$this->dontUpdateStatus) $this->flag ($sel, 'updated', false);
        
        $stmt = $map->updateStmt($sel, !$this->updateNulls);
        
        $idPath = $this->getIdPath();
        $tags = "-- tags: operations/{$idPath}/modify/all operations/{$idPath}/modify/updateRecords\n";
        $res = $this->db->query($tags.$stmt);
        $this->addAffected("modify.updateRecords");
        
        return $res;
    }
    
    function getSelectPrototype($full = true, $alias = 't') {
        $res = parent::getSelectPrototype($full, $alias);
        if ($full) {
            
            $nMap = $this->mkMap($this->nameMap, 't', 'dataN');
            $kMap = $this->mkMap($this->keyMap, 't', 'dataK');
            
            $nameProvider = $this->nameProvider? $this->nameProvider: $this->targetTableName('object');
            
            if ($this->targetSqlName) {
                
                $db = $this->getDb();
                
                if ($this->nameMap) {
                    $res['tables']['dataN'] = array(
                        'name' => $nameProvider,
                        'joinsAlias' => 't',
                        'joinsOn' => $nMap->eq(' = ', ' AND '),
                        'joinType' => 'LEFT JOIN',
                    );
                }
                
                if ($this->keyMap) {
                    $res['tables']['dataK'] = array(
                        'name' => $this->targetTableName('object'),
                        'joinsAlias' => 't',
                        'joinsOn' => $kMap->eq(' = ', ' AND '),
                        'joinType' => 'LEFT JOIN',
                    );
                }
                
            }
        }
        return $res;
    }
    
    protected function nullCriterion(Ac_Etl_ColMap $colMap, $leftOrRight, $isNotNull = false, $alias = 't') {
        
        $db = $this->getDb();
        $cr = array();
        $f = $leftOrRight? 'left' : 'right';
        $cr[] = $colMap->$f(false, $alias, $isNotNull? '| IS NULL' : '| IS NOT NULL');
        $res = implode(' AND ', $cr);
        if (count($cr) > 1) $res = '('.$res.')';
        return $res;
        
    }
    
    protected function getDefaultSelectorPrototypes() {
        
        if (!$this->keyMap) {
            $this->keyMap = $this->nameMap;
            $this->namesAreKeys = true;
        }
        
        if (!strlen($this->targetSqlName)) throw new Exception("setTargetSqlName() first");
        $db = $this->getDb();
        
        $kMap = $this->mkMap($this->keyMap, 't', 'dataK');
        $nMap = $this->mkMap($this->nameMap, 't', 'dataN');
        
        // NOTE: indentation is used to show parentId-based hierarchy, not array hierarchy (the prototype list itself is flat)
        $res = array(
                
            'allRecords' => array( // our root - will have defaults from $this
            ),

                'badRecords' => array(
                    'parentId' => 'allRecords',
                    'selectPrototype' => array(
                        'where' => array('status_bad' => "t.".($this->statusColName)." = 'bad'")
                     ),
                ),

                'keysProvided' => array( // names will be ignored if keys are provided
                    'parentId' => 'allRecords',
                    'selectPrototype' => array(
                        'where' => array(
                            'withKeys' => $kMap->nullCriterion(true),
                        ),
                    ),
                ),

                    'wrongKeys' => array(
                        'parentId' => 'keysProvided',
                        'putSqlToStats' => true,
                        'selectPrototype' => array(
                            'where' => array(
                                'destMissingByKey' => $kMap->nullCriterion(false, true),
                            ),
                            'usedAliases' => array('dataK'),
                        ),
                    ),
                    'keysOk' => array(
                        'parentId' => 'keysProvided',
                        'putSqlToStats' => true,
                        'selectPrototype' => array(
                            'where' => array(
                                'destFoundByKey' => $kMap->nullCriterion(true, true)
                            ),
                            'usedAliases' => array('dataK'),
                        ),
                    ),
            
                'noKeysOrNames' => array(
                    'parentId' => 'allRecords',
                    'selectPrototype' => array(
                        'where' => array(
                            'noKeys' => $kMap->nullCriterion(false, false, ' OR '),
                            // noNames will be added in the clause below
                        ),
                    ),
                ),
        );
        
        if ($this->nameMap) {
            
            $this->toCreateStat = 'namesNotFound';
    
            $this->createdStat = 'namesFound';
            
            Ac_Util::ms($res, array(

                'noKeysOrNames' => array(
                    'selectPrototype' => array(
                        'where' => array(
                            'noNames' => $nMap->nullCriterion(),
                        ),
                    ),
                ),


                'namesProvided' => array(
                    'parentId' => 'allRecords',
                    'selectPrototype' => array(
                        'where' => array(
                            'noKeys' => $kMap->nullCriterion(),
                            'withNames' => $nMap->nullCriterion(true)
                        ),
                    ),
                ),

                    'namesNotFound' => array(
                        'parentId' => 'namesProvided',
                        'putSqlToStats' => true,
                        'selectPrototype' => array(
                            'where' => array(
                                'status_notProcessed' => 't.'.$this->statusColName." IN ('unprocessed', 'created')",
                                'destMissingByName' => $nMap->nullCriterion(false, true)
                            ),
                            'usedAliases' => array('dataN'),
                        ),
                    ),

                    'namesFound' => array(
                        //'putSqlToStats' => true,
                        'parentId' => 'namesProvided',
                        'selectPrototype' => array(
                            'where' => array(
                                'destFoundByName' => $nMap->nullCriterion(true, true)
                            ),
                            'usedAliases' => array('dataN'),
                        )
                    ),
            ));
        }
        
        if ($this->draftColumn) {
            $res['wrongKeys']['selectPrototype']['where']['draft'] = $this->db->n($this->draftColumn).' = 0';
            $res['drafts'] = array(
                'parentId' => 'wrongKeys',
                'putSqlToStats' => true,
                'selectPrototype' => array(
                    'where' => array(
                        'draft' => $this->db->n($this->draftColumn).' = 1',
                    ),
                ),
            );
            $res['formerDrafts'] = array(
                'parentId' => 'keysOk',
                'putSqlToStats' => true,
                'selectPrototype' => array(
                    'where' => array(
                        'draft' => $this->db->n($this->draftColumn).' = 1',
                    ),
                ),
            );
        }
        
        if ($this->namesAreKeys) {
            unset($res['namesProvided']['selectPrototype']['where']['noKeys']);
        }
        
        if ($rq = $this->getCreateRequirements()) {
            list($createParts, $cantCreateParts, $createWhere, $cantCreateWhere) = $this->mkRqParts($rq);
            $res['canCreate'] = array(
                'parentId' => $this->toCreateStat,
                'selectPrototype' => array(
                    'parts' => $createParts,
                    'where' => $createWhere,
                ),
            );
            $res['cantCreate'] = array(
                'parentId' => $this->toCreateStat,
                'selectPrototype' => array(
                    'parts' => $negativeParts,
                    'where' => $negativeWhere,
                ),
            );
            $this->toCreateStat = 'canCreate';
        }
        
        if ($this->getUpdateAllowed()) {
            
            $cMap = $this->mkMap($this->getContentMap(true), 't', 'dataK');
            
            if (!$this->namesAreKeys) $cMap->union($nMap);
            
            $cMap->flip(); // we have to have target columns on the left
            
            $sameRecordsCriterion = $cMap->sameRecordsCriterion($this->getNullableTargetColumns(), !$this->updateNulls, "\n    AND ", $debugColumns );
            
            $sameRecordsCriterion = "($sameRecordsCriterion)";
            
            $notSameRecordsCriterion = "NOT $sameRecordsCriterion";
            
            $res['canUpdate'] = array(
                'parentId' => 'keysOk',
                'putSqlToStats' => true,
                'selectPrototype' => array(
                    'where' => array(
                        'status_notProcessed' => 't.'.$this->statusColName." IN ('unprocessed','updated')",
                    ),
                ),
            );
            
            $res['changed'] = array(
                'parentId' => 'canUpdate',
                'putSqlToStats' => true,
                'selectPrototype' => array(
                    'where' => array('notSameRecords' => $notSameRecordsCriterion),
                ),
            );
            
            $res['notChanged'] = array(
                'parentId' => 'canUpdate',
                'putSqlToStats' => true,
                'selectPrototype' => array(
                    'where' => array(
                        'sameRecords' => $sameRecordsCriterion,
                        //'notProcessed' => 't.'.$this->statusColName." IN ('unprocessed')",
                    )
                ),
            );

            if ($this->debugRecordsEquality) {
                $res['changed']['sqlComments'] = implode(", \n", $debugColumns);
                $res['notChanged']['sqlComments'] = implode(", \n", $debugColumns);
            }
            
            if ($rq = $this->getUpdateRequirements()) {
            
                list($updateParts, $cantUpdateParts, $updateWhere, $cantUpdateWhere) = $this->mkRqParts($rq);
                
                Ac_Util::ms($res, array(
                    'canUpdate' => array(
                        'selectPrototype' => array(
                            'parts' => $updateParts,
                            'where' => $updateWhere,
                        ),
                    ),
                    'cantUpdate' => array(
                        'selectPrototype' => array(
                            'parts' => $cantUpdateParts,
                            'where' => $cantUpdateWhere,
                        )
                    ),
                ));
                
            }
            
        }
        
        // If this processing doesn't have dedicated status column, remove its occurances from 
        // WHERE clauses (by unsetting all WHERE sub-keys starting with 'status_'
        
        if (!strlen($this->statusColName)) {
            unset($res['badRecords']); // we can't know which records are bad... since we don't have status column
            foreach(array_keys($res) as $i) {
                $where = Ac_Util::getArrayByPath($res, $p = array($i, 'selectPrototype', 'where'));
                if (is_array($where)) {
                    foreach (preg_grep('/^status_/', array_keys($where)) as $key) {
                        Ac_Util::unsetArrayByPath($res, array_merge($p, array($key)));
                    }
                }
            }
        }
        
        return $res;
    }
    
    protected function mkRqParts($rq) {
        $partPrototypes = array();
        $wheres = array();
        foreach ($rq as $k => $v) {
            if (is_array($v) || is_object($v) && $v instanceof Ac_Sql_Part) $partPrototypes[$k] = $v;
            else $wheres[] = $v;
        }
        if ($partPrototypes) {
            $parts = $partPrototypes;
            $negativeParts = array('cant' => array(
                    'class' => 'Ac_Sql_Filter_Multiple',
                    'applied' => true,
                    'isNot' => true,
                    'filters' => $partPrototypes,
                ),
            );
        } else {
            $parts = array();
            $negativeParts = array();
        }
        if ($wheres) {
            $where = array('can' => array($wheres));
            $negativeWhere = array('cant' => array('NOT (('.implode(') AND (', $wheres).')'));
        } else {
            $where = array();
            $negativeWhere = array();
        }
        return array($parts, $negativeParts, $where, $negativeWhere);
    }
    
    protected function getCreateRequirements() {
        return array_diff_key($this->requirements, array_flip($this->updateOnlyRequirementKeys));
    }
    
    protected function getUpdateRequirements() {
        return array_diff_key($this->requirements, array_flip($this->createOnlyRequirementKeys));
    }
    
    
    // ---- getters and setters ----
    
    function setTargetSqlName($targetSqlName) {
        $this->targetSqlName = $targetSqlName;
    }

    function getTargetSqlName() {
        return $this->targetSqlName;
    }

    function setNameMap(array $nameMap) {
        $this->nameMap = $nameMap;
        if ($this->namesAreKeys) $this->keyMap = $this->nameMap;
    }

    /**
     * @return array
     */
    function getNameMap() {
        return $this->nameMap;
    }
    
    /**
     * @param bool $namesAreKeys
     */
    function setNamesAreKeys($namesAreKeys) {
        $this->namesAreKeys = $namesAreKeys;
    }

    /**
     * @return bool
     */
    function getNamesAreKeys() {
        return $this->namesAreKeys;
    }    

    function setKeyMap(array $keyMap) {
        if (!$keyMap) {
            $this->keyMap = $this->nameMap;
            $this->namesAreKeys = true;
        } else {
            $this->keyMap = $keyMap;
            $this->namesAreKeys = false;
        }
    }

    /**
     * @return array
     */
    function getKeyMap() {
        return $this->keyMap;
    }

    /**
     * @param array|Ac_Etl_I_Matcher $contentMap
     */
    function setContentMap($contentMap) {
        if (!(is_array($contentMap) || ($contentMap instanceof Ac_Etl_I_Matcher))) 
            throw new Exception("\$contentMap must be an instance of Ac_Etl_I_Matcher or an array");
        if ($contentMap instanceof Ac_Etl_I_Matcher) {
            $contentMap->setTablePair($this);
        }
        $this->contentMap = $contentMap;
    }

    /**
     * @return array|Ac_Etl_I_Matcher
     */
    function getContentMap($asArray = false) {
        if ($asArray && $this->contentMap instanceof Ac_Etl_I_Matcher) {
            $this->contentMap->setTablePair($this);
            $this->contentMap->setSqlDb($this->getDb());
            if ($this->debug) {
                Ac_Debug::savageMode();
                var_dump($this->contentMap->getColMatches());
            }
            return $this->contentMap->getColMatches();
        }
        return $this->contentMap;
    }
    
    function setDb(Ac_Sql_Db $db) {
        parent::setDb($db);
        if ($this->contentMap instanceof Ac_Etl_I_Matcher) $this->contentMap->setSqlDb($db);
    }

    function setDefaults(array $defaults) {
        $this->defaults = $defaults;
    }

    /**
     * @return array
     */
    function getDefaults() {
        return $this->defaults;
    }

    function setRequirements(array $requirements) {
        $this->requirements = $requirements;
    }

    /**
     * @return array
     */
    function getRequirements() {
        return $this->requirements;
    }

    function setCreateOnlyRequirementKeys(array $createOnlyRequirementKeys) {
        $this->createOnlyRequirementKeys = $createOnlyRequirementKeys;
    }

    /**
     * @return array
     */
    function getCreateOnlyRequirementKeys() {
        return $this->createOnlyRequirementKeys;
    }

    function setUpdateOnlyRequirementKeys(array $updateOnlyRequirementKeys) {
        $this->updateOnlyRequirementKeys = $updateOnlyRequirementKeys;
    }

    /**
     * @return array
     */
    function getUpdateOnlyRequirementKeys() {
        return $this->updateOnlyRequirementKeys;
    }

    function setUpdateNulls($updateNulls) {
        $this->updateNulls = $updateNulls;
    }

    function getUpdateNulls() {
        return $this->updateNulls;
    }    

    function setSelectorTreeExtra(array $selectorTreeExtra) {
        $this->selectorTreeExtra = $selectorTreeExtra;
    }

    /**
     * @return array
     */
    function getSelectorTreeExtra() {
        return $this->selectorTreeExtra;
    }    
    
    function setDebug($debug) {
        $this->debug = (bool) $debug;
    }

    function getDebug() {
        return $this->debug;
    }    
    
    function setCreateAllowed($createAllowed) {
        $this->createAllowed = (bool) $createAllowed;
    }

    function getCreateAllowed() {
        return $this->createAllowed;
    }    

    function setUpdateAllowed($updateAllowed) {
        $this->updateAllowed = (bool) $updateAllowed;
    }

    function getUpdateAllowed() {
        return $this->updateAllowed && ($this->contentMap || ($this->nameMap && !$this->namesAreKeys));
    }

    function setDontUpdateStatus($dontUpdateStatus) {
        $this->dontUpdateStatus = (bool) $dontUpdateStatus;
    }

    /**
     * @return bool
     */
    function getDontUpdateStatus() {
        return $this->dontUpdateStatus;
    }    
    
    function targetTableName($kind = 'array') {
        $res = $this->import->tableOfTargetDb($this->targetSqlName, $kind);
        return $res;
    }

    function setNameProvider($nameProvider) {
        $this->nameProvider = $nameProvider;
    }

    function getNameProvider() {
        return $this->nameProvider;
    }
    
    protected function updatesStatus($withProblems = false) {
        return !$this->dontUpdateStatus && (strlen($this->statusColName) || $withProblems && strlen($this->problemsColName));
    }
    
    protected function gStats ($prop, $lazy = false) {
        if ($lazy && isset($this->currStats[$prop])) return $this->currStats[$prop];
        $res = $this->currStats[$prop] = $this->getStatistics($prop, true);
        return $res;
    }
    
    function getRightDbName() {
        return $this->import->getTargetDbName();
    }
    
    function getRightTableName() {
        return $this->targetSqlName;
    }
    
    function getRightDbPrefix() {
        return $this->import->getTargetDbPrefix();
    }

    function setDontWriteNames($dontWriteNames) {
        $this->dontWriteNames = (bool) $dontWriteNames;
    }

    /**
     * @return bool
     */
    function getDontWriteNames() {
        return $this->dontWriteNames;
    }

    function setAiColumn($aiColumn) {
        $this->aiColumn = $aiColumn;
    }

    function getAiColumn() {
        return $this->aiColumn;
    }

    function setDraftColumn($draftColumn) {
        $this->draftColumn = $draftColumn;
    }

    function getDraftColumn() {
        return $this->draftColumn;
    }

    function setExtraLocks(array $extraLocks) {
        $this->extraLocks = $extraLocks;
    }

    /**
     * @return array
     */
    function getExtraLocks() {
        return $this->extraLocks;
    }    
 
    function setMyAiColumn($myAiColumn) {
        $this->myAiColumn = $myAiColumn;
    }

    function getMyAiColumn() {
        return $this->myAiColumn;
    }    
    
    protected function checkAiConfig() {
        $set = array();
        $notSet = array();
        foreach (array('aiColumn', 'draftColumn', 'myAiColumn') as $prop) {
            if (strlen($this->$prop)) $set[] = $prop;
            else $notSet[] = $prop;
        }
        if ($set && $notSet) throw new Exception("Following properties must be set for proper AI mapping: ".implode(", ", $notSet));
    }

    function setUseReplace($useReplace) {
        $this->useReplace = $useReplace;
    }

    /**
     * @return bool
     */
    function getUseReplace() {
        return $this->useReplace;
    }    
   
    
}