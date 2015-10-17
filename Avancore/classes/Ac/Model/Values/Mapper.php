<?php

/**
 * Implements values list from the Mapper
 */
class Ac_Model_Values_Mapper extends Ac_Model_Values {
    
    /**
     * mapper identifier
     */
    protected $mapperClass = false;

    /**
     * mapper that is a source of the items
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;

    /**
     * query to filter record titles
     * @var array
     */
    protected $query = array();

    /**
     * mapping between the fields of source items and query keys
     * @var array
     */
    protected $queryMap = array();

    /**
     * sort mode
     */
    protected $sort = false;

    /**
     * title field (FALSE to use mapper-provided value)
     */
    protected $titleFieldName = false;

    /**
     * value field (FALSE to use mapper-provided value)
     */
    protected $valueFieldName = false;
    
    protected $cachedExistingValues = array();

    function hasPublicVars() {
        return true;
    }
    
    protected function doDefaultGetValueList() {
        $query = $this->getAppliedQuery();
        if (!$this->mapper) $this->getMapper(true);
        $res = $this->mapper->getTitles($query, $this->sort, $this->titleFieldName, $this->valueFieldName);
        return $res;
    }

    function __sleep() {
        $this->mapper = false;
        return array_keys(get_object_vars($this));
    }
    
    function __wakeup() {
        $this->mapper = Ac_Model_Mapper::getMapper($this->mapperClass);        
    }
    
    function getTitles(array $values) {
        $query = $this->getAppliedQuery();
        $cr = $this->valueFieldName;
        if ($cr === false) $cr = Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION;
        $query[$cr] = $values;
        if (!$this->mapper) $this->getMapper(true);
        $res = $this->mapper->getTitles($query, false, $this->titleFieldName);
        return $res;
    }
    
    protected function getAppliedQuery() {
        $query = $this->query;
        if ($this->queryMap) {
            foreach ($this->queryMap as $src => $dest) {
                if (is_numeric($src)) {
                    $src = $dest;
                }
                $query[$dest] = $this->data->getField($src);
            }
        }
        return $query;
    }
    
    /**
     * Removes all values not in the list. Default implementation filters out all non-scalar values
     * @param array $values List of values to check
     * @return array Array without improper values
     */
    function filterValuesArray(array $values) {
        $res = array();
        if ($this->cache && is_array($this->cachedValueList)) {
            $res = parent::filterValuesArray($values);
        } else {
            foreach ($values as $v) {
                if (is_scalar(($v))) $res[] = $v;
            }
            $valuesToCheck = $res;
            if ($this->cache && $this->cachedExistingValues) {
                foreach ($valuesToCheck as $i => $v) {
                    if (isset($this->cachedExistingValues[$v])) {
                        if (!$this->cachedExistingValues[$v]) unset($res[$i]);
                        unset($valuesToCheck[$i]);
                    }
                }
            }
            if ($valuesToCheck) {
                if (!$this->mapper) $this->getMapper(true);
                $query = $this->getAppliedQuery();
                if ($this->valueFieldName === false) {
                    $valueFieldName = Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION;
                } else {
                    $valueFieldName = $this->valueFieldName;
                }
                if (strlen($valueFieldName)) {
                    $query[$valueFieldName] = $valuesToCheck;
                    // we could use count instead and check if count is the same as number of unique values... 
                    // But we won't know which values are bad then and we will need to requery
                    $existing = $this->mapper->getTitles($query, false, $this->titleFieldName, $this->valueFieldName);
                } else {
                    $existing = array();
                    if ($this->titleFieldName === false) {
                        $titleFieldName = $this->mapper->getTitleFieldName();
                    } else {
                        $titleFieldName = $valueFieldName;
                    }
                    if (strlen($titleFieldName)) {
                        foreach ($this->mapper->loadRecordsArray($valuesToCheck) as $k => $rec) {
                            $existing[$k] = $rec->getField($titleFieldName);
                        }
                    } else {
                        foreach ($this->mapper->loadRecordsArray($valuesToCheck) as $k => $rec) {
                            $existing[$k] = $this->mapper->getIdentifier($rec);
                        }
                    }
                }
                foreach ($valuesToCheck as $i => $v) {
                    $has = isset($existing[$v]);
                    $this->cachedExistingValues[$v] = $has;
                    if (!$has) {
                        unset($res[$i]);
                    }
                }
            }
        }
        return $res;
    }
    
    function resetCacheOnDataChange($oldData, $newData) {
        if (($oldData !== $newData) && $this->queryMap) $this->resetCache();
    }
    
    protected function doDefaultCheck($value) {
        $res = count($this->filterValuesArray(array($value)));
        return $res;
    }
    
    function resetCache() {
        parent::resetCache();
        $this->cachedExistingValues = array();
    }

    /**
     * Sets mapper identifier
     */
    function setMapperClass($mapperClass) {
        if ($mapperClass !== ($oldMapperClass = $this->mapperClass)) {
            $this->mapperClass = $mapperClass;
            $this->mapper = false;
            $this->resetCache();
        }
    }

    /**
     * Returns mapper identifier
     */
    function getMapperClass() {
        return $this->mapperClass;
    }

    /**
     * Sets mapper that is a source of the items
     */
    function setMapper(Ac_Model_Mapper $mapper) {
        if ($mapper !== ($oldMapper = $this->mapper)) {
            $this->mapper = $mapper;
            $this->mapperClass = $mapper->getId();
            $this->resetCache();
        }
    }

    /**
     * Returns mapper that is a source of the items
     * @param bool $require Throw an exception if there's no Mapper assigned
     * @param bool $asIs Throw an exception if there's no Mapper assigned
     * @return Ac_Model_Mapper
     */
    function getMapper($require = false, $asIs = false) {
        if (!$this->mapper && !$asIs) {
            if ($this->mapperClass) $this->mapper = Ac_Model_Mapper::getMapper($this->mapperClass);
            if ($require && !$this->mapper) throw new Ac_E_InvalidUsage("setMapper() or setMapperClass() first");
        }
        return $this->mapper;
    }

    /**
     * Sets query to filter record titles
     */
    function setQuery(array $query) {
        if ($query !== ($oldQuery = $this->query)) {
            $this->query = $query;
            $this->cachedExistingValues = false;
        }
    }

    /**
     * Returns query to filter record titles
     * @return array
     */
    function getQuery() {
        return $this->query;
    }

    /**
     * Sets mapping between the fields of source items and query keys
     */
    function setQueryMap(array $queryMap) {
        if ($queryMap !== ($oldQueryMap = $this->queryMap)) {
            $this->queryMap = $queryMap;
            $this->resetCache();
        }
    }

    /**
     * Returns mapping between the fields of source items and query keys
     * @return array
     */
    function getQueryMap() {
        return $this->queryMap;
    }

    /**
     * Sets sort mode
     */
    function setSort($sort) {
        if ($sort !== ($oldSort = $this->sort)) {
            $this->sort = $sort;
            $this->cachedValueList = false;
        }
    }

    /**
     * Returns sort mode
     */
    function getSort() {
        return $this->sort;
    }

    /**
     * Sets title field (FALSE to use mapper-provided value)
     */
    function setTitleFieldName($titleFieldName) {
        if ($titleFieldName !== ($oldTitleFieldName = $this->titleFieldName)) {
            $this->titleFieldName = $titleFieldName;
            $this->cachedValueList = false;
        }
    }

    /**
     * Returns title field (FALSE to use mapper-provided value)
     */
    function getTitleFieldName() {
        return $this->titleFieldName;
    }

    /**
     * Sets value field (FALSE to use mapper-provided value)
     */
    function setValueFieldName($valueFieldName) {
        if ($valueFieldName !== ($oldValueFieldName = $this->valueFieldName)) {
            $this->valueFieldName = $valueFieldName;
            $this->resetCache();
        }
    }

    /**
     * Returns value field (FALSE to use mapper-provided value)
     */
    function getValueFieldName() {
        return $this->valueFieldName;
    }
    
}

