<?php

class Ac_Model_Relation_Provider_Sql_Omni extends Ac_Model_Relation_Provider_Sql {
    
    /**
     * name of table to fetch items from
     * @var string
     */
    protected $tableName = false;

    /**
     * one or several keys A) in the table B) in mid table
     * @var array
     */
    protected $destKeys = false;

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
     * keys of mid-table that correspond to srcValues. Must be set along with midTableName and midSrcKeys to allow mixed src-dest loading
     * @var array
     */
    protected $midSrcKeys = array();

    /**
     * keys of mid-table that correspond to destValues/destKeys. Must be set along with midTableName and midDest keys to allow mixed src-dest loading
     * @var array
     */
    protected $midDestKeys = array();

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
    
    protected $restriction = array();
    
    /**
     * restriction for rows in many-to-many table
     */
    protected $midWhere = false;
    
    /**
     * extra joins to add to the SQL (alias of right table is `t`)
     */
    protected $extraJoins = false;

    function getAcceptsSrcValues() {
        $res = strlen($this->midTableName) && $this->midSrcKeys && $this->midDestKeys;
        return $res;
    }

    /**
     * Sets keys of mid-table that correspond to srcValues. Must be set along with midTableName and midSrcKeys to allow mixed src-dest loading
     */
    function setMidSrcKeys(array $midSrcKeys) {
        $this->midSrcKeys = $midSrcKeys;
    }

    /**
     * Returns keys of mid-table that correspond to srcValues. Must be set along with midTableName and midSrcKeys to allow mixed src-dest loading
     * @return array
     */
    function getMidSrcKeys() {
        return $this->midSrcKeys;
    }

    /**
     * Sets keys of mid-table that correspond to destValues/destKeys. Must be set along with midTableName and midDest keys to allow mixed src-dest loading
     */
    function setMidDestKeys(array $midDestKeys) {
        $this->midDestKeys = $midDestKeys;
    }

    /**
     * Returns keys of mid-table that correspond to destValues/destKeys. Must be set along with midTableName and midDest keys to allow mixed src-dest loading
     * @return array
     */
    function getMidDestKeys() {
        return $this->midDestKeys;
    }    
    
    protected function doGetWithValues (array $destValues = array(), $byKeys = true, array $srcValues = array()) {
        
        $ta = 't';
        $asTa = 'AS t';
        $cols = 't.*'; 
        
        $useMidTable = false;
        
        // return empty result
        if (!count($srcValues) && !count($destValues)) return array(array(), array());

        $srcIndexed = array();
        $destIndexed = array();
        $rows = array();
        
        if ($this->midTableName && $srcValues) {
            
            if (count($this->midDestKeys) !== count($this->destKeys)) 
                throw new Ac_E_InvalidUsage ("Number of elements in \$midDestKeys must be equal to one in \$destKeys");
            
            $useMidTable = true;
            $lta = $this->midTableAlias.'.';
            $crit = $this->makeSqlCriteria($srcValues, $this->midSrcKeys, $this->midTableAlias);
            $extraJoinCrit = false;
            if ($destValues) { // worst case: we need to combine left values (left keys of mid table) and right values
                $join = 'RIGHT';
                $notNullC = array();
                foreach ($this->midSrcKeys as $fieldName) {
                    $notNullC[$fieldName] = $this->midTableAlias.".".$fieldName." IS NOT NULL";
                }
                $notNullC = "(".implode(" AND ", $notNullC).")";
                $extraJoinCrit = $crit;
                if ($this->midWhere) {
                    $strMidWhere = $this->getStrMidWhere($this->midTableAlias);
                    if (strlen($extraJoinCrit)) $extraJoinCrit = "({$extraJoinCrit}) AND ({$strMidWhere})";
                        else $extraJoinCrit = $strMidWhere;
                }
                $crit = $notNullC." OR (".$this->makeSqlCriteria($destValues, $this->destKeys, strlen($ta)? $ta : $this->tableName).")";
            } else {
                $join = 'INNER';
                if ($this->midWhere) {
                    $strMidWhere = $this->getStrMidWhere($this->midTableAlias);
                    if (strlen($crit)) $crit = "({$crit}) AND ({$strMidWhere})";
                        else $crit = $strMidWhere;
                }
            }
            $fromWhere = ' FROM '.$this->db->n($this->midTableName).' AS '.$this->midTableAlias
                .' '.$this->getJoin($join, $this->midTableAlias, $this->tableName, $ta, array_combine($this->midDestKeys, $this->destKeys), $extraJoinCrit);
        } else {
            $fromWhere = ' FROM '.$this->db->n($this->tableName).$asTa;
            $lta = $this->db->n($this->tableName).'.';
            $crit = $this->makeSqlCriteria($destValues, $this->destKeys, $ta);
        }
        if ($this->extraJoins) $fromWhere .= ' '.$this->extraJoins;
        $fromWhere .= ' WHERE ('.$crit.')';
        if ($this->where || $this->restriction) $fromWhere .= ' AND ('.$this->getStrWhere().')'; 

        $sql = 'SELECT ';
        if ($this->midTableName && $useMidTable) {
            foreach ($this->midSrcKeys as $key) $qKeys[] = $lta.$this->db->n($key);
            $sKeys = implode(', ', $qKeys);
            $sql .= 'DISTINCT '.$sKeys.', '.$this->db->n($ta? $ta: $this->tableName).'.*'.$fromWhere;
        } else $sql = 'SELECT '.$cols.' '.$fromWhere;
        if ($this->ordering) {
            $sql .= ' ORDER BY '.$this->ordering;
        }
        
        if ($useMidTable) {
        
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
                if (count($this->midSrcKeys) === 1) {
                    $key = $this->midSrcKeys[0];
                    if ($this->unique) {
                        foreach ($mid as $i => $keyValue) 
                            $srcIndexed[$keyValue[$key]] = $objects[$i];
                    } else {
                        foreach ($mid as $i => $keyValue) 
                            $srcIndexed[$keyValue[$key]][] = $objects[$i];
                    }
                } else {
                    foreach ($mid as $i => $keyValue) {
                        Ac_Util::setArrayByPath($srcIndexed, array_values($keyValue), $objects[$i], $this->unique);
                    }
                }
            } else {
                $srcIndexed = $objects;
            }
            $this->db->resultFreeResource($rr);
        }
        if (!$useMidTable || $destValues) {
            $indexKeys = $this->destKeys;
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
            $destIndexed = $indexedItems;
        }
        $res = array($destIndexed, $srcIndexed);
        return $res;
    }
    
    protected function doCountWithValues (array $destValues, $separate = true, array $srcValues = array()) {

        // important: implies that mid and dest table are 1-1 linked, and have consistent referential integrity 
        // (when mid table is used, counts its' dest keys instead of dest records)
        
        if ($this->midTableName) {
            $countKeys = $this->midSrcKeys;
            $countTableAlias = $this->midTableAlias.'.';
            if ($destValues && !$separate) { // we don't need RIGHT JOIN trick with $separate
                
                if (count($this->midDestKeys) !== count($this->destKeys)) 
                    throw new Ac_E_InvalidUsage ("Number of elements in \$midDestKeys must be equal to one in \$destKeys");
                
                $extraJoinCrit = $this->makeSqlCriteria($srcValues, $this->midSrcKeys, $this->midTableAlias);
                $join = 'RIGHT';
                $notNullC = array();
                foreach ($this->midSrcKeys as $fieldName) {
                    $notNullC[$fieldName] = $this->midTableAlias.".".$fieldName." IS NOT NULL";
                }
                $notNullC = "(".implode(" AND ", $notNullC).")";
                $crit = $notNullC." OR (".$this->makeSqlCriteria($destValues, $this->destKeys, $this->tableName).")";
                if ($this->midWhere) {
                    $strMidWhere = $this->getStrMidWhere($this->midTableAlias);
                    if (strlen($extraJoinCrit)) $extraJoinCrit = "({$extraJoinCrit}) AND ({$strMidWhere})";
                        else $extraJoinCrit = $strMidWhere;
                }
                $fromWhere = ' FROM '.$this->db->n($this->midTableName).' AS '.$this->midTableAlias
                    .' '.$this->getJoin($join, $this->midTableAlias, $this->tableName, $this->tableName, array_combine($this->midDestKeys, $this->destKeys), $extraJoinCrit);
            } else {
                // when no destValues are provided, and we have mid-table, we count only right keys in mid table;
                // we don't even join the dest table
                if (!$srcValues) return array();
                $fromWhere = ' FROM '.$this->db->n($this->midTableName).' AS '.$this->midTableAlias;
                $crit = $this->makeSqlCriteria($srcValues, $this->midSrcKeys, $this->midTableAlias);
                if ($this->midWhere !== false) $crit = "( $crit ) AND (".$this->getStrMidWhere($this->midTableAlias).")";
            }
        } else {
            $countKeys = $this->destKeys;
            $countTableAlias = $this->db->n($this->tableName).'.';
            $fromWhere = ' FROM '.$this->db->n($this->tableName);
            $crit = $this->makeSqlCriteria($srcValues, $this->destKeys, '');
        }
        
        if ($this->extraJoins) $fromWhere .= ' '.$this->extraJoins;
        
        $fromWhere .= ' WHERE ('.$crit.')';

        if ($this->where) $fromWhere .= ' AND ('.$this->getStrWhere().')';

        if (!$separate) {
            $sql = 'SELECT COUNT(*) '.$fromWhere;
            $res = $this->db->fetchValue($sql);
        } else {
            $qKeys = array();
            foreach ($countKeys as $key) $qKeys[] = $countTableAlias.$this->db->n($key);
            $sKeys = implode(', ', $qKeys);
            $i = 0;
            while(in_array($cntColumn = '__count__'.$i, $this->destKeys)) $i++; 
            $sql = 'SELECT '.$sKeys.', COUNT(*) AS '.$this->db->n($cntColumn).$fromWhere.' GROUP BY '.$sKeys;
            $res = array();
            $rows = $this->db->fetchArray($sql);
            if ($separate) {
                if (count($countKeys) === 1) {
                    $key = $countKeys[0];
                    foreach ($rows as $row) 
                        $res[$row[$key]] = $row[$cntColumn];
                } else {
                    foreach ($rows as $row) 
                        $this->putRowToArray($row, $row[$cntColumn], $res, $countKeys, true);        
                }
            } else {
                foreach ($rows as $row)
                    $res[] = $row[$cntColumn];     
            }
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
    function setDestKeys(array $destKeys) {
        $this->destKeys = array_values($destKeys);
    }

    /**
     * Returns one or several referenced keys A in the table B in the many-to-many table
     * @return array
     */
    function getDestKeys() {
        return $this->destKeys;
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
        if ($this->mapper) $this->restriction = $this->mapper->getRestriction();
            else $this->restriction = array();
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
        if ($this->restriction) {
            $r = $this->db->valueCriterion($this->restriction, $alias);
            if (strlen($res)) $res = "({$res}) AND ({$r})";
                else $res = $r;
        }
        return $res;
    }
    
    protected function getStrMidWhere($alias = false) {
        if (is_array($this->midWhere)) $res = $this->db->valueCriterion($this->midWhere, $alias);
        else $res = $this->midWhere;
        return $res;
    }

    static function evaluatePrototype(array $relationProps) {
        // todo: replace with something better
        $res = array(
            'class' => __CLASS__,
            'db' => $relationProps['db'],
            'tableName' => $relationProps['destTableName'],
            'mapper' => isset($relationProps['destMapper'])? $relationProps['destMapper'] : false,
            'destKeys' => isset($relationProps['fieldLinks2']) && $relationProps['fieldLinks2']? 
                array_values($relationProps['fieldLinks2']) : array_values($relationProps['fieldLinks']),
            'ordering' => isset($relationProps['destOrdering'])? $relationProps['destOrdering'] : false,
            'extraJoins' => isset($relationProps['destExtraJoins'])? $relationProps['destExtraJoins'] : false,
            'where' => isset($relationProps['destWhere'])? $relationProps['destWhere'] : false,
            'unique' => isset($relationProps['destWhere'])? $relationProps['destIsUnique'] : false,
        );
        if (isset($relationProps['midTableName']) && $relationProps['midTableName']) {
            $res = array_merge($res, array(
                'midTableName' => $relationProps['midTableName'],
                'midSrcKeys' => array_values($relationProps['fieldLinks']),
                'midDestKeys' => array_keys($relationProps['fieldLinks2']),
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