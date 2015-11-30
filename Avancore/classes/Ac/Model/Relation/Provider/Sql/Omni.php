<?php

class Ac_Model_Relation_Provider_Sql_Omni extends Ac_Model_Relation_Provider_Sql {
    
    /**
     * name of table to fetch items from
     * @var string
     */
    protected $tableName = false;

    /**
     * name of many-to-many table (FALSE or empty string if none)
     * @var string
     */
    protected $midTableName = false;
    
    /**
     * alias of many-to-many table
     */
    protected $midTableAlias = false;

    /**
     * associative array of relations between many-to-many table and records table
     */
    protected $midLinks = false;
    
    /**
     * one or several keys A) in the table B) in mid table
     * @var array
     */
    protected $keys = false;
    
    

    /**
     * mapper that provides destination objects (null or FALSE if no mapper used)
     */
    protected $mapper = null;

    /**
     * ordering of loaded items
     */
    protected $ordering = false;

    /**
     * restriction for loaded items
     */
    protected $where = false;
    
    /**
     * restriction for rows in many-to-many table
     */
    protected $midWhere = false;

    /**
     * extra joins to add to the SQL (alias of right table is `t`)
     */
    protected $extraJoins = false;

    function getWithValues (array $values, $byKeys = true, array $nnValues = array()) {
        $ta = 't';
        $asTa = 'AS t';
        $cols = 't.*'; 
        
        $indexKeys = $this->keys;
        $useMidTable = false;
        
        // return empty result
        if (!count($values) && !count($nnValues)) return array(array(), array());

        $res1 = array();
        $res2 = array();
        $rows = array();
        
        if ($this->midTableName && $values) {
            $useMidTable = true;
            
            $lta = $this->midTableAlias.'.';
            $crit = $this->makeSqlCriteria($values, $indexKeys, $this->midTableAlias);
            $extraJoinCrit = false;
            if ($nnValues) {
                $join = 'RIGHT';
                $notNullC = array();
                foreach ($indexKeys as $fieldName) {
                    $notNullC[$fieldName] = $this->midTableAlias.".".$fieldName." IS NOT NULL";
                }
                $notNullC = "(".implode(" AND ", $notNullC).")";
                $extraJoinCrit = $crit;
                if ($this->midWhere) {
                    $strMidWhere = $this->getStrMidWhere($this->midTableAlias);
                    if (strlen($extraJoinCrit)) $extraJoinCrit = "({$extraJoinCrit}) AND ({$strMidWhere})";
                        else $extraJoinCrit = $strMidWhere;
                }
                $crit = $notNullC." OR (".$this->makeSqlCriteria($nnValues, array_values($this->midLinks), strlen($ta)? $ta : $this->tableName).")";
            } else {
                $join = 'INNER';
                if ($this->midWhere) {
                    $strMidWhere = $this->getStrMidWhere($this->midTableAlias);
                    if (strlen($crit)) $crit = "({$crit}) AND ({$strMidWhere})";
                        else $crit = $strMidWhere;
                }
            }
            $fromWhere = ' FROM '.$this->db->n($this->midTableName).' AS '.$this->midTableAlias
                .' '.$this->getJoin($join, $this->midTableAlias, $this->tableName, $ta, $this->midLinks, $extraJoinCrit);
        } else {
            $fromWhere = ' FROM '.$this->db->n($this->tableName).$asTa;
            $lta = $this->db->n($this->tableName).'.';
            if ($nnValues) {
                $indexKeys = array_values($this->midLinks);
                $crit = $this->makeSqlCriteria($nnValues, $indexKeys, $ta);
            } else {
                $crit = $this->makeSqlCriteria($values, $indexKeys, $ta);
            }
        }
        if ($this->extraJoins) $fromWhere .= ' '.$this->extraJoins;
        $fromWhere .= ' WHERE ('.$crit.')';
        if ($this->where) $fromWhere .= ' AND ('.$this->getStrWhere().')'; 

        foreach ($indexKeys as $key) $qKeys[] = $lta.$this->db->n($key);
        $sKeys = implode(', ', $qKeys);
        $sql = 'SELECT ';
        if ($this->midTableName && $useMidTable) {
            $sql .= 'DISTINCT '.$sKeys.', '.$this->db->n($ta? $ta: $this->tableName).'.*'.$fromWhere;
        } else $sql = 'SELECT '.$cols.' '.$fromWhere;
        if ($this->ordering) {
            $sql .= ' ORDER BY '.$this->ordering;
        }
        
        if ($this->midTableName && $useMidTable) {
        
            $rr = $this->db->getResultResource($sql);
            $fi = $this->db->resultGetFieldsInfo($rr);
            
            $prefix = $this->db->getDbPrefix();
            $tn = str_replace('#__', $prefix, $this->tableName);
            if ($ta) $tn = $ta;
            
            $rows = array();
            $mid = array();

            while($row = $this->db->resultFetchAssocByTables($rr, $fi))  {
                $rows[] = $row[$tn];
                $mid[] = $row[$this->midTableAlias];
            }
                
            // instantiate dest rows
            if ($this->mapper) {
                $objects = $this->mapper->loadFromRows($rows);
            } else {
                $objects = $rows;
            }
            
            if ($byKeys) {
                if (count($indexKeys) === 1) {
                    $key = $indexKeys[0];
                    if ($this->unique) {
                        foreach ($mid as $i => $keyValue) 
                            $res1[$keyValue[$key]] = $objects[$i];
                    } else {
                        foreach ($mid as $i => $keyValue) 
                            $res1[$keyValue[$key]][] = $objects[$i];
                    }
                } else {
                    foreach ($mid as $i => $keyValue) {
                        Ac_Util::simpleSetArrayByPathNoRef($res1, array_values($keyValue), $objects[$i], $this->unique);
                    }
                }
            } else {
                $res1 = $objects;
            }
            $this->db->resultFreeResource($rr);
            if ($nnValues) {
                // next group will index the items by the keys in right table
                $indexKeys = array_values($this->midLinks);
            }
        }
        if (!$useMidTable || $nnValues) {
            $indexedItems = array();
            if (!$useMidTable) {
                $rows = $this->db->fetchArray($sql);
                if ($this->mapper) {
                    $objects = $this->mapper->loadFromRows($rows);
                } else {
                    $objects = $rows;
                }
            }
            if ($byKeys) {
                if (count($indexKeys) === 1) {
                    $key = $indexKeys[0];
                    if ($this->unique) {
                        foreach ($rows as $i => $row)
                            $indexedItems[$row[$key]] = $objects[$i];
                    } else {
                        foreach ($rows as $i => $row)
                            $indexedItems[$row[$key]][] = $objects[$i];
                    }
                } else {
                    foreach ($rows as $i => $row)
                        $this->putRowToArray($row, $objects[$i], $indexedItems, $indexKeys, $this->unique);        
                }
            } else {
                $indexedItems = $objects;     
            }
            if ($nnValues) {
                $res2 = $indexedItems;
            } else {
                $res1 = $indexedItems;
            }
        }
        $res = array($res1, $res2);
        return $res;
    }
    
    function countWithValues (array $values, $separate = true, array $nnValues = array()) {
        
        if ($this->midTableName) {
            if ($nnValues) {
                $lta = $this->midTableAlias.'.';
                $extraJoinCrit = $this->makeSqlCriteria($values, $this->keys, $this->midTableAlias);
                $keys2 = array_values($this->midLinks);
                $join = 'RIGHT';
                $notNullC = array();
                foreach ($this->keys as $fieldName) {
                    $notNullC[$fieldName] = $this->midTableAlias.".".$fieldName." IS NOT NULL";
                }
                $notNullC = "(".implode(" AND ", $notNullC).")";
                $crit = $notNullC." OR (".$this->makeSqlCriteria($nnValues, $keys2, $this->tableName).")";
                if ($this->midWhere) {
                    $strMidWhere = $this->getStrMidWhere($this->midTableAlias);
                    if (strlen($extraJoinCrit)) $extraJoinCrit = "({$extraJoinCrit}) AND ({$strMidWhere})";
                        else $extraJoinCrit = $strMidWhere;
                }
                $fromWhere = ' FROM '.$this->db->n($this->midTableName).' AS '.$this->midTableAlias
                    .' '.$this->getJoin($join, $this->midTableAlias, $this->tableName, $this->tableName, $this->midLinks, $extraJoinCrit);
            } else {
                $fromWhere = ' FROM '.$this->db->n($this->midTableName).' AS '.$this->midTableAlias;
                $lta = $this->midTableAlias.'.';
                $crit = $this->makeSqlCriteria($values, $this->keys, $this->midTableAlias);
                if ($this->midWhere !== false) $crit = "( $crit ) AND (".$this->getStrMidWhere($this->midTableAlias).")";
            }
        } else {
            $fromWhere = ' FROM '.$this->db->n($this->tableName);
            $lta = $this->db->n($this->tableName).'.';
            $crit = $this->makeSqlCriteria($values, $this->keys, '');
        }
        
        if ($this->extraJoins) $fromWhere .= ' '.$this->extraJoins;
        
        $fromWhere .= ' WHERE ('.$crit.')';

        if ($this->where) $fromWhere .= ' AND ('.$this->getStrWhere().')';
        
        if (!$separate) {
            $sql = 'SELECT COUNT(*) '.$fromWhere;
            $res = $this->db->fetchValue($sql);
        } else {
            $qKeys = array();
            foreach ($this->keys as $key) $qKeys[] = $lta.$this->db->n($key);
            $sKeys = implode(', ', $qKeys);
            $i = 0;
            while(in_array($cntColumn = '__count__'.$i, $this->keys)) $i++; 
            $sql = 'SELECT '.$sKeys.', COUNT(*) AS '.$this->db->n($cntColumn).$fromWhere.' GROUP BY '.$sKeys;
            $res = array();
            $rr = $this->db->getResultResource($sql);
            if ($separate) {
                if (count($this->keys) === 1) {
                    $key = $this->keys[0];
                    while($row = $this->db->resultFetchAssoc($rr)) {
                        $res[$row[$key]] = $row[$cntColumn];        
                    }
                } else {
                    while($row = $this->db->resultFetchAssoc($rr)) {
                        $this->putRowToArray($row, $row[$cntColumn], $res, $this->keys, true);        
                    }
                }
            } else {
                while($row = $this->db->resultFetchAssoc($rr)) 
                    $res[] = $row[$cntColumn];     
            }
            $this->db->resultFreeResource($rr);
        }
        return $res;
    }
    
    /**
     * Sets name of table to fetch items from
     * @param string $tableName
     */
    function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    /**
     * Returns name of table to fetch items from
     * @return string
     */
    function getTableName() {
        return $this->tableName;
    }

    /**
     * Sets name of many-to-many table (FALSE or empty string if none)
     * @param string $midTableName
     */
    function setMidTableName($midTableName) {
        $this->midTableName = $midTableName;
    }

    /**
     * Returns name of many-to-many table (FALSE or empty string if none)
     * @return string
     */
    function getMidTableName() {
        return $this->midTableName;
    }

    /**
     * Sets alias of many-to-many table
     */
    function setMidTableAlias($midTableAlias) {
        $this->midTableAlias = $midTableAlias;
    }

    /**
     * Returns alias of many-to-many table
     */
    function getMidTableAlias() {
        return $this->midTableAlias;
    }    

    /**
     * Sets one or several referenced keys A in the table B in the many-to-many table
     */
    function setKeys(array $keys) {
        $this->keys = array_values($keys);
    }

    /**
     * Returns one or several referenced keys A in the table B in the many-to-many table
     * @return array
     */
    function getKeys() {
        return $this->keys;
    }

    /**
     * Sets mapper that provides destination objects (null or FALSE if no mapper used)
     */
    function setMapper($mapper) {
        if (!$mapper) $this->mapper = null;
        elseif ($mapper instanceof Ac_Model_Mapper) $this->mapper = $mapper;
        elseif (is_string($mapper)) {
            if ($this->application) {
                $this->mapper = $this->application->getMapper($mapper);
            } else {
                $this->mapper = Ac_Model_Mapper::getMapper($mapper);
            }
        } else {
            $def = array();
            if ($this->application) $def['application'] = $this->application;
            $this->mapper = Ac_Prototyped::factory($mapper, 'Ac_Model_Mapper', $def);
        }
    }

    /**
     * Returns mapper that provides destination objects (null)
     * @return Ac_Model_Mapper
     */
    function getMapper() {
        return $this->mapper;
    }

    /**
     * Sets ordering of loaded items
     */
    function setOrdering($ordering) {
        $this->ordering = $ordering;
    }

    /**
     * Returns ordering of loaded items
     */
    function getOrdering() {
        return $this->ordering;
    }

    /**
     * Sets restriction for loaded items
     */
    function setWhere($where) {
        $this->where = $where;
    }

    /**
     * Returns restriction for loaded items
     */
    function getWhere() {
        return $this->where;
    }

    /**
     * Sets restriction for rows in many-to-many table
     */
    function setMidWhere($midWhere) {
        $this->midWhere = $midWhere;
    }

    /**
     * Returns restriction for rows in many-to-many table
     */
    function getMidWhere() {
        return $this->midWhere;
    }

    /**
     * Sets extra joins to add to the SQL (alias of right table is `t`)
     */
    function setExtraJoins($extraJoins) {
        $this->extraJoins = $extraJoins;
    }

    /**
     * Returns extra joins to add to the SQL (alias of right table is `t`)
     */
    function getExtraJoins() {
        return $this->extraJoins;
    }
    
    protected function getStrWhere($alias = 't') {
        if (is_array($this->where)) $res = $this->db->valueCriterion($this->where, $alias);
        else $res = $this->where;
        return $res;
    }
    
    protected function getStrMidWhere($alias = false) {
        if (is_array($this->midWhere)) $res = $this->db->valueCriterion($this->midWhere, $alias);
        else $res = $this->midWhere;
        return $res;
    }

    /**
     * Sets associative array of relations between many-to-many table and records table
     */
    function setMidLinks($midLinks) {
        $this->midLinks = $midLinks;
    }

    /**
     * Returns associative array of relations between many-to-many table and records table
     */
    function getMidLinks() {
        return $this->midLinks;
    }
    
    static function evaluatePrototype(array $relationProps) {
        // todo: replace with something better
        $res = array(
            'class' => __CLASS__,
            'db' => $relationProps['db'],
            'tableName' => $relationProps['destTableName'],
            'mapper' => isset($relationProps['destMapper'])? $relationProps['destMapper'] : false,
            'keys' => array_values($relationProps['fieldLinks']),
            'ordering' => isset($relationProps['destOrdering'])? $relationProps['destOrdering'] : false,
            'extraJoins' => isset($relationProps['destExtraJoins'])? $relationProps['destExtraJoins'] : false,
            'where' => isset($relationProps['destWhere'])? $relationProps['destWhere'] : false,
            'unique' => isset($relationProps['destWhere'])? $relationProps['destIsUnique'] : false,
        );
        if (isset($relationProps['midTableName']) && $relationProps['midTableName']) {
            $res = array_merge($res, array(
                'midTableName' => $relationProps['midTableName'],
                'midLinks' => $relationProps['fieldLinks2'],
                'midWhere' => $relationProps['midWhere'],
                'midTableAlias' => $relationProps['midTableAlias'],
            ));
        }
        return $res;
        
    }
    
    /**
     * Creates JOIN clause ("$joinType JOIN $rightTable AS $rightAlias ON $leftAlias.$key0 = $rightAlias.$field0 AND $leftAlias.$key1 = $rightAlias.$field1"), 
     * $keyN and $fieldN are taken from $fieldNames 
     */
    protected function getJoin ($joinType, $leftAlias, $rightTable, $rightAlias, $fieldNames, $extraCrit = false) {
        $db = $this->db;
        $la = $db->NameQuote($leftAlias);
        $ra = $db->NameQuote($rightAlias);
        $res = $joinType.' JOIN '.$db->NameQuote($rightTable);
        if ($rightAlias) $res .= ' AS '.$ra.' ON ';
            else {
                $res .= ' ON ';
                $ra = $db->NameQuote($rightTable);
            }
        $on = array();
        foreach ($fieldNames as $leftField => $rightField) {
            $on[] = $la.'.'.$db->NameQuote($leftField).' = '.$ra.'.'.$db->NameQuote($rightField);
        }
        if (strlen($extraCrit)) $on[] = "($extraCrit)";
        $res .= implode(' AND ', $on);
        return $res;
    }    
    
    
}