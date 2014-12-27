<?php

class Ac_Sql_NestedSets extends Ac_Prototyped {
    
    const debugStmtCallback = 'Ac_Sql_NestedSets::debugStmtCallback';
    const debugBeforeQuery = 'beforeQuery';
    const debugAfterQuery = 'afterQuery';
    
    const FREAK_OUT_NONE = 0;
    const FREAK_OUT_WHEN_ERRORS = 1;
    const FREAK_OUT_ALWAYS = 2;
    
    var $freakOut = self::FREAK_OUT_NONE;
    var $freakExtraColumns = '';
    var $freakExtraJoins = '';
    
    /**
     * @var Ac_Sql_Db
     */
    var $_db = false;
    
    /**
     * @var Ac_Sql_Blocker
     */
    var $_blocker = false;

    var $alias = false;
    var $tableName = false;
    var $leftCol = 'leftCol';
    var $rightCol = 'rightCol';
    var $idCol = 'id';
    var $ignoreCol = 'ignore';
    var $treeCol = 'treeId';
    var $parentCol = 'parentId';
    var $orderingCol = 'ordering';
    var $levelCol = 'depth';

    var $treeId = false;
    
    var $autoLock = true;
    var $parentIdOfRoot = null;
    var $idIsUniqueForAllTrees = false;
    var $idIsAutoInc = false;
    var $cacheStatements = true;
    
    var $_stmtCache = array();
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function setDb($db) {
        if (!is_null($db) && !is_a($db, 'Ac_Sql_Db') && !is_a($db, 'Ac_Legacy_Database'))
            trigger_error('\$db must be either null, Ac_Legacy_Database or Ac_Sql_Db instance', E_USER_ERROR);
            
        if (is_a($db, 'Ac_Legacy_Database')) {
            $this->_db = new Ac_Sql_Db_Ae($db);
        }
        $this->_db = $db;
    }
    
    /**
     * @return Ac_Sql_Db
     */
    function getDb() {
        return $this->_db;
    }
    
    /**
     * @param Ac_Sql_Blocker $blocker
     */
    function setBlocker($blocker) {
        if (!is_a($blocker, 'Ac_Sql_Blocker')) trigger_error("\$blocker must be an instance of Ac_Sql_Blocker", E_USER_ERROR);
        $this->_blocker = $blocker;
    }
    
    function ensureTransaction() {
        if (!$this->_blocker)
            trigger_error("blocker object must be set with setBlocker() call", E_USER_ERROR);
        $res = $this->_blocker->isLocked() || $this->autoLock && $this->_blocker->lock();
        return $res;
    }
    
    function clearTree() {
        return $this->query($this->_stmt(
            'DELETE FROM [[origTableName]] WHERE 1 [[origTc]]'
        ));
    }
    
    function _stmt($parts, $params = array(), $useCache = null) {
        if (is_null($useCache)) $useCache = $this->cacheStatements;
        if ($useCache) {
            $md = md5(serialize($parts));
            if (!isset($this->_stmtCache[$md])) {
                $this->_stmtCache[$md] = Ac_Sql_Statement::create($parts, $this->_getCommonParams()); 
            }
            $this->_stmtCache[$md]->applyParams($params);
            $res = $this->_stmtCache[$md];
        } else {
            $res = Ac_Sql_Statement::create($parts, $this->_getCommonParams($params));
        }
        return $res;
    }
    
    function _getParamMap() {
        $res = array(
            'left' => $this->leftCol,
            'right' => $this->rightCol,
            'id'=> $this->idCol,
            'tree' => $this->treeCol,
            'parent' => $this->parentCol,
            'ordering' => $this->orderingCol,
            'level' => $this->levelCol,
            'ignoreCol' => $this->ignoreCol,
        );
        return $res;
    }
    
    /**
     * @param array $externalRow ($this->leftCol => value, $this->rightCol => value...)
     * @return array ('left'=>value, 'right'=>value, 'id'=>..., 'tree'=>..., 'parent'=>..., 'ordering' => ...)
     */
    function getInternalFields($externalRow) {
        if (is_array($externalRow)) {
            $res = array();
            foreach ($this->_getParamMap() as $k => $v) {
                if (strlen($v) && array_key_exists($v, $externalRow)) $res[$k] = $externalRow[$v];
            }
            return $res;
        } else {
            $res = $externalRow;
        }
        return $res;
    }
    
    /**
     * @param array $internalFields ('left'=>value, 'right'=>value, 'id'=>..., 'tree'=>..., 'parent'=>..., 'ordering' => ...)
     * @return array ($this->leftCol => value, $this->rightCol => value...)
     */
    function getExternalRow($internalFields) {
        if (is_array($internalFields)) {
            $res = array(); 
            foreach ($this->_getParamMap() as $k => $v) {
                if (strlen($v) && array_key_exists($k, $internalFields)) $res[$v] = $intenalFields[$k];
            }
        } else {
            $res = $internalFields;
        }
        return $res;
    }
    
    function _getCommonParams($extraParams = array()) {
        $res = array(
            'origTableName' => $this->tableName,
        );
        if (strlen($this->alias)) {
            $res['alias'] = $this->alias;
            $res['tableName'] = new Ac_Sql_Statement('[[origTableName]] AS [[alias]]');
        } else {
            $res['alias'] = $this->tableName;
            $res['tableName'] = $this->tableName;
        }
        if (strlen($this->treeId)) $res['treeId'] = $this->treeId;
        foreach (array('leftCol', 'rightCol', 'idCol', 'treeCol', 'parentCol', 'orderingCol', 'levelCol', 'ignoreCol') as $k) {
            if (strlen($this->$k)) $res[$k] = $this->alias? array($this->alias, $this->$k) : $this->$k;
            if (strlen($this->$k)) $res['orig'.ucfirst($k)] = $this->$k;
        }
        if ($this->treeCol && strlen($this->treeId)) {
            $res['tc'] = new Ac_Sql_Statement('AND [[treeCol]] = {{treeId}}');
            $res['origTc'] = new Ac_Sql_Statement('AND [[origTreeCol]] = {{treeId}}');
        } else {
            $res['tc'] = new Ac_Sql_Expression('');
            $res['origTc'] = new Ac_Sql_Expression('');
        }
        $res = array_merge($res, $extraParams);
        return $res;
    }
    
    function deleteNode($id, $dontDeleteRows = false) {
        $res = false;
        if ($this->ensureTransaction()) {
            if (($row = $this->_db->fetchRow($this->_stmt(
                'SELECT * FROM [[tableName]] WHERE [[idCol]] = {{id}} [[tc]]', array('id' => $id)
            )))) {
                $int = $this->getInternalFields($row);
                
                $diff = $int['right'] - $int['left'] + 1;
                
                if (!$dontDeleteRows) $this->query($this->_stmt('
                    DELETE FROM [[origTableName]] 
                        WHERE [[origLeftCol]] BETWEEN {{left}} AND {{right}}
                        [[origTc]]
                    ', array('left' => $int['left'], 'right' => $int['right'],)
                ));
                
                $this->query($this->_stmt('
                    UPDATE [[tableName]] 
                        SET [[leftCol]] = [[leftCol]] - {{diff}} 
                        WHERE [[leftCol]] > {{right}}
                        [[origTc]]
                    ', array('left' => $int['left'], 'right' => $int['right'], 'diff' => $diff,)
                ));
                
                $this->query($this->_stmt('
                    UPDATE [[tableName]] 
                        SET [[rightCol]] = [[rightCol]] - {{diff}} 
                        WHERE [[rightCol]] > {{right}}
                        [[origTc]]
                    ', array('left' => $int['left'], 'right' => $int['right'], 'diff' => $diff,)
                ));
                $res = true;
            }
            
        }
        return $res;
    }
    
    function getRootNode() {
        $res = $this->_db->fetchRow($this->_stmt(
            'SELECT * FROM [[tableName]] WHERE [[leftCol]] = 0 [[tc]]'
        )); 
        return $res;
    }
    
    function getNode($idOrData) {
        if (is_array($idOrData)) {
            $data = $idOrData;
        } else {
            $data = array($this->idCol => $idOrData);
        }
        
        $res = $this->_db->fetchRow($this->_stmt(
            'SELECT * FROM [[tableName]] WHERE [[crit]] [[tc]]',
            array('crit' => new Ac_Sql_Expression($this->_db->valueCriterion($data)))
        ));
        return $res;
    }
    
    function getNextId() {
        $s = 'SELECT MAX([[idCol]]) FROM [[tableName]] WHERE 1';
        if (!$this->idIsUniqueForAllTrees) $s .= ' [[tc]]';
        $maxId = $this->_db->fetchValue($this->_stmt($s), 0, 0);
        if (is_null($maxId)) $res = 0; else $res = $maxId + 1;
        return $res;
    }
    
    function addRootNode($checkIfWeAlreadyHaveRoot = true, $data = array()) {
        $res = false;
        if ($this->ensureTransaction() && (!$checkIfWeAlreadyHaveRoot || !$this->getRootNode())) {
            $insRow = $data;
            $insRow[$this->leftCol] = 0;
            $insRow[$this->rightCol] = 1;
            if (strlen($this->treeCol)) $insRow[$this->treeCol] = $this->treeId;
            if (strlen($this->levelCol)) $insRow[$this->levelCol] = 0;
            if (strlen($this->parentCol)) $insRow[$this->parentCol] = $this->parentIdOfRoot;
            if (strlen($this->orderingCol)) $insRow[$this->orderingCol] = 1;
            if (!$this->idIsAutoInc && !isset($insRow[$this->idCol]))
                $insRow[$this->idCol] = $this->getNextId();
            $this->query($stmt = $this->_db->insertStatement($this->tableName, $insRow));
            if (isset($insRow[$this->idCol])) $res = $insRow[$this->idCol];
                else $res = $this->_db->getLastInsertId();
        }
        return $res;
    }
    
    function addNode($parentId, $order = false, $data = array(), $update = false, $dontInsert = false, & $insRow = array()) {
        $res = false;
        if ($this->ensureTransaction()) {
            if (($parentNode = $this->getInternalFields($this->getNode($parentId)))) {
                
                if ($update && isset($data[$this->idCol])) {
                    $ignoreCrit = ' AND [[idCol]] <> {{key}}';
                    $key = $data[$this->idCol];
                } else {
                    $ignoreCrit = '';
                    $key = null;
                }
                
                $left = $parentNode['left'] + 1;
                
                if ($order === false) {
                    $order = $this->_db->fetchValue($this->_stmt('
                        SELECT MAX([[orderingCol]]) FROM [[tableName]] WHERE [[parentCol]] = {{parentId}} [[tc]] '.$ignoreCrit.'
                    ', array('parentId' => $parentId, 'key' => $key)), 0, 0) + 1;
                }
                
                $this->query($this->_stmt('
                        UPDATE [[tableName]] 
                        SET [[orderingCol]] = [[orderingCol]] + 1
                        WHERE [[parentCol]] = {{parentId}} AND [[orderingCol]] >= {{order}} [[tc]] '.$ignoreCrit.'
                    ', array('parentId' => $parentId, 'order' => $order, 'key' => $key)
                ));
                
                if (($leftSibling = $this->getInternalFields($this->_db->fetchRow(
                    $this->_stmt('
                        SELECT * FROM [[tableName]]
                        WHERE [[parentCol]] = {{parentId}} AND [[orderingCol]] < {{order}} [[tc]] '.$ignoreCrit.'
                        ORDER BY [[orderingCol]] DESC
                        LIMIT 1
                    ', array('parentId' => $parentId, 'order' => $order, 'key' => $key))
                )))) $left = $leftSibling['right'] + 1;
                
                $right = $left + 1;
                
                $this->query($this->_stmt('
                    UPDATE [[tableName]] SET [[leftCol]] = [[leftCol]] + 2 
                    WHERE [[leftCol]] >= {{left}} [[tc]] '.$ignoreCrit.'
                ', array('left' => $left, 'key' => $key)));
                
                $this->query($this->_stmt('
                    UPDATE [[tableName]] SET [[rightCol]] = [[rightCol]] + 2
                    WHERE [[rightCol]] >= {{left}} [[tc]] '.$ignoreCrit.'
                ', array('left' => $left, 'key' => $key)));
                
                $insRow = $data;
                if (strlen($this->treeCol)) $insRow[$this->treeCol] = $this->treeId;
                $insRow[$this->parentCol] = $parentId;
                $insRow[$this->leftCol] = $left;
                $insRow[$this->rightCol] = $right;
                $insRow[$this->orderingCol] = $order;
                if (strlen($this->levelCol) && isset($parentNode['level'])) {
                    $insRow[$this->levelCol] = $parentNode['level'] + 1;
                }
                if (!$this->idIsAutoInc && !isset($insRow[$this->idCol]))
                    $insRow[$this->idCol] = $this->getNextId();
                if ($dontInsert) $res = $insRow;
                else {
                    if ($update && isset($data[$this->idCol])) {
                        $data[$this->parentCol] = $parentId;
                        $data[$this->leftCol] = $left;
                        $data[$this->rightCol] = $right;
                        $data[$this->orderingCol] = $order;
                        if (strlen($this->levelCol) && isset($parentNode['level'])) {
                            $data[$this->levelCol] = $parentNode['level'] + 1;
                        }
                        $stmt = $this->_db->updateStatement($this->tableName, $data, $this->idCol);
                        if ($this->query($stmt) !== false) {
                            if (isset($insRow[$this->idCol])) $res = $insRow[$this->idCol];
                                else $res = $this->_db->getLastInsertId();  
                        } else {
                            $res = false;
                        }
                    } else {
                        $stmt = $this->_db->insertStatement($this->tableName, $insRow);
                        if ($this->query($stmt) !== false) {
                            if (isset($insRow[$this->idCol])) $res = $insRow[$this->idCol];
                                else $res = $this->_db->getLastInsertId();  
                        } else {
                            $res = false;
                        }
                    }
                }
            }
        }
        return $res;
    }
    
    function isParentOf($nodeId, $possibleChildId) {
        $node = $this->getInternalFields($this->getNode($possibleChildId));
        $parent = $this->getInternalFields($this->getNode($nodeId));
        $res = $node && $parent && ($parent['left'] < $node['left']) && ($parent['right'] > $node['right']);
        return $res;
    }
    
    // TODO: optimize the implementation; 
    // I'm more than sure that we can use at most one UPDATE with proper SWITCH
    function moveNode($id, $parentId, $order = false, $dontCheckForParent = false, 
        & $actualOrder = null, $oldParent = false) {
        
        $res = false;
        if ($this->ensureTransaction() && ($node = $this->getInternalFields($this->getNode($id)))) {
            $parent = $this->getInternalFields($this->getNode($parentId));
            if ($parent && !(($parent['left'] > $node['left']) && ($parent['right'] < $node['right']))) {
                
                // Find out target node
                
                if ($order === 0) $order = 1; // we count from 1

                if ($parent['right'] == $parent['left'] + 1) { // we don't have target node
                    $hasTargetNode = false;
                    $order = 0;
                } elseif ($parent['right'] == $parent['left'] + 3) { // we have node inside the parent
                    if ($order == 1) {
                        $hasTargetNode = true;
                        $targetNodeLeft = $parent['left'] + 1;
                        $targetNodeRight = $parent['right'] - 1;
                    } else {
                        $hasTargetNode = false;
                    }
                } else {
                    
                    // let's find target node
                    
                    $nodes = $this->_db->fetchArray($this->_db->applyLimits($this->_stmt('
                        SELECT [[leftCol]] AS lft, [[rightCol]] AS rgt 
                        FROM [[tableName]] 
                        WHERE [[parentCol]] = {{parentId}} [[tc]] ORDER BY [[leftCol]]
                    ', array('parentId' => $parent['id'])), $order));
                    if (count($nodes) >= $order) {
                        $lastNode = $nodes[$order - 1];
                        $hasTargetNode = true;
                        $targetNodeLeft = $lastNode['lft'];
                        $targetNodeRight = $lastNode['rgt'];
                    } else {
                        $hasTargetNode = false;
                    }
                    
                    
                }
                    
                if ($oldParent === false) $oldParent = $node['parent'];

                $sameParent = $node['parent'] == $parentId;

                // make computations
                
                /**
                 * The idea is...
                 * 
                 * There are two groups of rows:
                 * - our node row group is moved by $nDelta (both left and right columns)
                 * - two other sequences of LEFT (between oLeftMin and oLeftMax) and RIGHT (between oRightMin and oRightMax) 
                 *   values are moved towards node group by ~$oDelta
                 */

                $width = $node['right'] - $node['left'];
                
                // targetLeft
                if ($hasTargetNode) {
                    if ($node['left'] < $targetNodeLeft) {
                        if ($sameParent) { // swap with node to the right
                            $targetLeft = $targetNodeRight - $width;
                        } else {
                            $targetLeft = $targetNodeLeft - 1 - $width;
                        }
                    } else {
                        $targetLeft = $targetNodeLeft;
                    }
                } else {
                    if ($node['left'] < $parent['right']) {
                        $targetLeft = $parent['right'] - 1 - $width;
                    } else {
                        $targetLeft = $parent['right'];
                    }
                }
                
                if ($targetLeft == $node['left']) {
                    // nothing to do
                    $actualOrder = $order;
                    
                } else {
                    
                    $nDelta = $targetLeft - $node['left'];
                    
                    if ($nDelta > 0) { // move to the right
                        
                        $oLeftMin = $oRightMin = $node['right'] + 1;
                        
                        if ($hasTargetNode) {
                            if ($sameParent) {
                                $oLeftMax = $targetNodeRight;
                                $oRightMax = $targetNodeRight;
                            } else {
                                $oLeftMax = $tagetNodeLeft - 1;
                                $oRightMax = $targetLeft - 1;
                            }
                        } else {
                            $oLeftMax = $oRightMax = $parent['right'] - 1;
                        }
                        
                        $oDelta = - $width - 1;
                        
                    } else { // move to the left
                        
                        $oLeftMax = $oRightMax = $node['left'] - 1;
                        
                        if ($hasTargetNode) {
                            $oLeftMin = $oRightMin = $targetNodeLeft;
                        } else {
                            $oLeftMin = $parent['right'] + 1;
                            $oRightMin = $parent['right'];
                        }
                        $oDelta = $width + 1;
                        
                    }
                    
                    $params = compact('nDelta', 'oDelta', 'oLeftMin', 'oLeftMax', 'oRightMin', 'oRightMax');
                    $params['nodeLeft'] = $node['left'];
                    $params['nodeRight'] = $node['right'];
                    
                    if ($this->freakOut) {
                        
                        $freakExtraColumns = $this->freakExtraColumns;
                        $freakExtraJoins = $this->freakExtraJoins;
                        if (strlen($freakExtraColumns)) $freakExtraColumns .= ', ';
                        
                        $extra = compact ('parent', 'node', 'order', 'width', 'sameParent', 'hasTargetNode', 'targetLeft', 'targetRight');
                        $ds = $this->_stmt("
                            SELECT {$freakExtraColumns} [[idCol]], [[orderingCol]], [[parentCol]], [[leftCol]] AS `oL`, [[rightCol]] AS `oR`,

                                CASE 
                                    WHEN [[leftCol]] BETWEEN {{nodeLeft}} AND {{nodeRight}} THEN CONCAT('N ', {{nDelta}})
                                    WHEN [[leftCol]] BETWEEN {{oLeftMin}} AND {{oLeftMax}} THEN CONCAT('L ', {{oDelta}})
                                    ELSE ''
                                END AS lClass,

                                CASE 
                                    WHEN [[rightCol]] BETWEEN {{nodeLeft}} AND {{nodeRight}} THEN CONCAT('N ', {{nDelta}})
                                    WHEN [[rightCol]] BETWEEN {{oRightMin}} AND {{oRightMax}} THEN CONCAT('R ', {{oDelta}})
                                    ELSE ''
                                END AS rClass,

                                [[leftCol]] + CASE 
                                    WHEN [[leftCol]] BETWEEN {{nodeLeft}} AND {{nodeRight}} THEN {{nDelta}} 
                                    WHEN [[leftCol]] BETWEEN {{oLeftMin}} AND {{oLeftMax}} THEN {{oDelta}} 
                                    ELSE '' 
                                END AS `nL`,
                                
                                [[rightCol]] + CASE 
                                    WHEN [[rightCol]] BETWEEN {{nodeLeft}} AND {{nodeRight}} THEN {{nDelta}} 
                                    WHEN [[rightCol]] BETWEEN {{oRightMin}} AND {{oRightMax}} THEN {{oDelta}} 
                                    ELSE 0
                                END AS `nR`

                            FROM [[tableName]] WHERE 1 [[tc]]
                            ORDER BY [[leftCol]]
                            ", $params
                        );
                        $rows = $this->_db->fetchArray($ds);

                        $counts = array('oL' => array(), 'oR' => array(), 'nL' => array(), 'nR' => array());
                        foreach ($rows as $row) {
                            foreach (array_keys($counts) as $item) {
                                if (!isset($counts[$item][$row[$item]])) $counts[$item][$row[$item]] = 1;
                                else $counts[$item][$row[$item]]++;
                            }
                        }
                        $hasBugs = false;
                        foreach ($rows as $k => $row) {
                            foreach (array_keys($counts) as $item) {
                                if ($counts[$item][$row[$item]] > 1) {
                                    $rows[$k][$item] .= ' !! '.$counts[$item][$row[$item]];
                                    $hasBugs = true;
                                }
                            }
                        }
                        
                        if ($this->freakOut == self::FREAK_OUT_ALWAYS || $hasBugs) {
                    
                            while(ob_get_level()) ob_end_clean();
                    
?>
                            <style type="text/css">
                                .tDebug td {
                                    vertical-align: top;
                                    border: 1px solid black;
                                    padding: 5px;
                                }
                            </style>
<?php
                        
                            echo "<table class='tDebug'><tr><td>"; var_dump($params); echo "</td><td>"; var_dump($extra); echo "</td><td>";
                            Ac_Util::showCoolTable($rows, array_combine(array_keys($rows[0]), array_keys($rows[0])), array());
                            echo "</td></tr></table>";

                            die();
                        }
                    }
                    
                    // let's DO it
                    $this->query($this->_stmt('
                        UPDATE [[tableName]] 
                        SET 
                            [[leftCol]] = [[leftCol]] + CASE 
                                WHEN [[leftCol]] BETWEEN {{nodeLeft}} AND {{nodeRight}} THEN {{nDelta}} 
                                WHEN [[leftCol]] BETWEEN {{oLeftMin}} AND {{oLeftMax}} THEN {{oDelta}} 
                                ELSE 0 
                            END, 
                            
                            [[rightCol]] = [[rightCol]] + CASE 
                                WHEN [[rightCol]] BETWEEN {{nodeLeft}} AND {{nodeRight}} THEN {{nDelta}} 
                                WHEN [[rightCol]] BETWEEN {{oRightMin}} AND {{oRightMax}} THEN {{oDelta}} 
                                ELSE 0 END 
                                
                        WHERE 
                            (
                                [[leftCol]] BETWEEN {{nodeLeft}} AND {{nodeRight}}
                                OR [[leftCol]] BETWEEN {{oLeftMin}} AND {{oLeftMax}} 
                                OR [[rightCol]] BETWEEN {{oRightMin}} AND {{oRightMax}}
                            ) [[tc]]
                            
                        ', $params
                    ));
                    
                    // Change ordering values

                    if ($sameParent) {
                        
                        if ($order > $node['ordering']) {
                            $rightOrder = $order;
                            $leftOrder = $node['ordering'];
                            $delta = '-1';
                        } else {
                            $rightOrder = $node['ordering'];
                            $leftOrder = $order;
                            $delta = '+ 1';
                        }
                        
                        $this->query($this->_stmt('
                                UPDATE [[tableName]] 
                                SET [[orderingCol]] = IF ([[idCol]] = {{id}}, {{newOrder}}, [[orderingCol]] {{delta}})
                                WHERE ([[orderingCol]] BETWEEN {{leftOrder}} AND {{rightOrder}}) AND [[parentCol]] = {{parentId}} [[tc]]
                            ', array('parentId' => $parentId, 'leftOrder' => $leftOrder, 'rightOrder' => $rightOrder, 'newOrder' => $order, 'id' => $id, 'delta' => new Ac_Sql_Expression($delta))
                        ));
                        
                    } else {
                        
                        $this->query($this->_stmt('
                                UPDATE [[tableName]] 
                                SET [[orderingCol]] = [[orderingCol]] + 1
                                WHERE [[parentCol]] = {{newParentId}} AND [[orderingCol]] >= {{newOrder}} [[tc]]
                            ', array('newParentId' => $parentId, 'newOrder' => $order)
                        ));
                        
                        $this->query($this->_stmt('
                                UPDATE [[tableName]] 
                                SET [[orderingCol]] = [[orderingCol]] - 1
                                WHERE [[parentCol]] = {{oldParentId}} AND [[orderingCol]] >= {{oldOrder}} [[tc]]
                            ', array('oldParentId' => $node['parent'], 'oldOrder' => $node['ordering'])
                        ));
                    }
                     
                }

                $res = true;
                $actualOrder = $order;
                
            }
                
        }
        return $res;
    }
    
    function getPath($idOrNode, $byIds = false) {
        if (is_array($idOrNode))
            $node = $idOrNode; 
        else 
            $node = $this->getInternalFields($this->getNode($idOrNode));
            
        if ($node) {
            $res = $this->_db->fetchArray($this->_stmt('
                SELECT * FROM [[tableName]] 
                WHERE 
                    [[leftCol]] <= {{left}} 
                    AND [[rightCol]] >= {{right}}
                    [[tc]]
                ORDER BY [[leftCol]]
            ', $node), $byIds? $this->idCol : false);
        } else {
            $res = false;
        }
        return $res;
    }
    
    function getChildren ($idOrNode, $withNode = false, $nLevels = false, $orderByLevel = false, $groupById = false) {
        $res = false;
        if (is_array($idOrNode))
            $node = $idOrNode; 
        else 
            $node = $this->getInternalFields($this->getNode($idOrNode));
        
        if ($node) {
            if ($nLevels !== false)
                $maxLevelExpression = Ac_Sql_Statement::create(
                    ' AND [[levelCol]] <= {{maxLevel}}', 
                    array('maxLevel' => $node['level'] + $nLevels) 
                );
            else
                $maxLevelExpression = new Ac_Sql_Expression(' AND 1');
                
            if ($withNode) $delta = 0; else $delta = 1;
                
            $res = $this->_db->fetchArray($this->_stmt('
                    SELECT [[alias]].* FROM [[tableName]] 
                    WHERE
                        [[leftCol]] >= {{left}}
                        AND [[rightCol]] <= {{right}}
                        [[maxLevelExpression]]
                        [[tc]]
                    ORDER BY
                        '.($orderByLevel? '[[levelCol]], [[orderingCol]]' : '[[leftCol]]').'
                ', array(
                    'left' => $node['left'] + $delta, 
                    'right' => $node['right'] - $delta, 
                    'maxLevelExpression' => $maxLevelExpression,
                )
            ), $groupById? $this->idCol : false);
        }
        return $res;
    }
    
    function getChildrenExtended($idOrNode, $extraJoins = false, $extraWhere = false, $orderBy = false, $groupBy = false, $withNode = false, $nLevels = false) {
        $res = false;
        if (is_array($idOrNode))
            $node = $idOrNode; 
        else 
            $node = $this->getInternalFields($this->getNode($idOrNode));
        
        if ($node) {
            
            
            
            
            if ($extraJoins !== false) {
                if (is_object($extraJoins) && $extraJoins instanceof Ac_I_Sql_Expression) $extraJoinsParam = $extraJoins;
                    else $extraJoinsParam = new Ac_Sql_Expression($extraJoins);
            } else {
                $extraJoinsParam = new Ac_Sql_Expression('');
            }
            
            
            
            if ($orderBy !== false) {
                if (is_object($orderBy) && $orderBY instanceof Ac_I_Sql_Expression) $orderByParam = array('ORDER BY ', $orderBy);
                    else $orderByParam = new Ac_Sql_Expression('ORDER BY '.$orderBy);
            } else {
                $orderByParam = new Ac_Sql_Expression('');
            }
            
            
            
            if ($extraWhere !== false) {
                if (is_object($extraWhere) && ($extraWhere instanceof Ac_I_Sql_Expression)) $extraWhereParam = array('AND ', $extraWhere);
                    else $extraWhereParam = new Ac_Sql_Expression('AND '.$extraWhere);
            } else {
                $extraWhereParam = new Ac_Sql_Expression('');
            }
            
            
            
            if ($groupBy !== false) {
                if (is_object($groupBy) && $groupBy instanceof Ac_I_Sql_Expression) $groupByParam = array('GROUP BY ', $groupBy);
                    else $groupByParam = new Ac_Sql_Expression('GROUP BY '.$groupBy);
            } else {
                $groupByParam = new Ac_Sql_Expression('');
            }
            
            
            
            
            if ($nLevels !== false)
                $maxLevelExpression = Ac_Sql_Statement::create(
                    ' AND [[levelCol]] <= {{maxLevel}}', 
                    array('maxLevel' => $node['level'] + $nLevels) 
                );
            else
                $maxLevelExpression = new Ac_Sql_Expression(' AND 1');
                
            if ($withNode) $delta = 0; else $delta = 1;
                
            $res = $this->_db->fetchArray($this->_stmt('
                    SELECT [[alias]].* FROM [[tableName]] [[extraJoinsParam]] 
                    WHERE
                        [[leftCol]] >= {{left}}
                        AND [[rightCol]] <= {{right}}
                        [[maxLevelExpression]]
                        [[tc]]
                        [[extraWhereParam]]
                    [[orderByParam]]
                    [[groupByParam]]
                ', array(
                    'left' => $node['left'] + $delta, 
                    'right' => $node['right'] - $delta, 
                    'maxLevelExpression' => $maxLevelExpression,
                    'extraJoinsParam' => $extraJoinsParam,
                    'extraWhereParam' => $extraWhereParam,
                    'groupByParam' => $groupByParam,
                    'orderByParam' => $orderByParam, 
                )
            ), $groupById? $this->idCol : false);
        }
        return $res;
    }
    
    function getDirectChild($nodeId, $childId) {
        $res = $this->_db->fetchRow($this->_stmt(
            'SELECT * FROM [[tableName]] WHERE [[parentCol]] = {{nodeId}} AND [[idCol]] = {{childId}} [[tc]]', 
            array('nodeId' => $nodeId, 'childId' => $childId)
        ));
        return $res;
    }
    
    
    function getDirectParent($nodeIdOrNode) {
        if (!is_array($nodeIdOrNode)) $node = $this->getInternalFields($this->getNode($nodeIdOrNode));
            else $node = $this->getInternalFields($nodeIdOrNode);
        if ($node && $node['parent']) {
            $res = $this->_db->fetchRow($this->_stmt(
                'SELECT * FROM [[tableName]] WHERE [[idCol]] = {{parentId}} [[tc]]', 
                array('parentId' => $node['parent'])
            ));
        } else {
            $res = false;
        }
        return $res;
    }
    
    function makeTree(array $flatNodes, $childMemberName = 'children', $preserveKeys = false) {
        $res = array();
        $nodes = $flatNodes;
        $top = array();
        $minLevel = false;
        foreach (array_keys($nodes) as $k) {
            $nodes[$k][$childMemberName] = $this->_findChildren($nodes, $nodes[$k], $preserveKeys);
            $level = intval($nodes[$k][$this->levelCol]);
            if ($minLevel === false) $minLevel = $level;
                else $minLevel = $minLevel < $level? $minLevel : $level;
        }
        foreach ($nodes as $k => & $v) {
            if (intval($v[$this->levelCol]) === $minLevel) {
                if ($preserveKeys) $top[$k] = $v;
                    else $top[] = $v;
            }
        }
        return $top;
    }
    
    function _findChildren($nodes, $parentNode, $preserveKeys) {
        $res = array();
        $parentId = $parentNode[$this->idCol];
        foreach (array_keys($nodes) as $k) {
            if ($nodes[$k][$this->parentCol] === $parentId) {
                if ($preserveKeys) $res[$k] = $nodes[$k];
                    else $res[] = $nodes[$k];
            }
        }
        return $res;
    }
    
    function getJoinClause ($modelColumn, $nsTableAlias = null, $joinType = 'INNER JOIN') {
        $db = $this->_db;
        if (is_null($nsTableAlias)) $nsTableAlias = $this->alias;
        if (strlen($nsTableAlias)) $a = $nsTableAlias;
            else $a = $this->tableName;
        $res = ' '.$joinType.' '.$db->n($this->tableName).(strlen($nsTableAlias)? ' AS '.$db->n($nsTableAlias) : '')
            .' ON '.$db->n($modelColumn).' = '.$db->n(array($a, $this->idCol));
        if (strlen($this->treeId) && strlen($this->treeCol)) $res .= ' AND '.$db->n(array($a, $this->treeCol)). ' = '.$db->q($this->treeId);
        return $res;
    }
    
    function getOrderByPart ($nsTableAlias = null, $withOrderBy = false) {
        if (is_null($nsTableAlias)) $nsTableAlias = $this->alias;
        if (!strlen($nsTableAlias)) $nsTableAlias = $this->tableName;
        $res = $this->_db->n(array($nsTableAlias, $this->leftCol));
        if ($withOrderBy) $res = $withOrderBy . ' '.$res;
        return $res;
    }
    
    protected function query($stmt) {
        Ac_Callbacks::getInstance()->call(self::debugStmtCallback, $this, $stmt, self::debugBeforeQuery);
        $res = $this->_db->query($stmt);
        Ac_Callbacks::getInstance()->call(self::debugStmtCallback, $this, $stmt, self::debugAfterQuery);
        return $res;
    }
        
}