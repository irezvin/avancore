<?php

class Ac_Model_Search extends Ac_Prototyped implements Ac_I_Search_FilterProvider {
    
    /**
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;
    
    protected $criteria = array();
    
    protected $sortCriteria = array();

    /**
     * @var array
     */
    protected $defaultFieldList = false;
    
    /**
     * @var Ac_Model_Search
     */
    protected $parentSearch = null;
    
    function deleteCriterion($id, $isSort = null) {
        if ($isSort === null || !$isSort) {
            if (isset($this->criteiria[$id])) unset($this->criteria[$id]);
        }
        if ($isSort === null || $isSort) {
            if (isset($this->sortCriteiria[$id])) unset($this->sortCriteria[$id]);
        }
    }
    
    function addCriterion($id, $criterion, $isSort = null, $allowOverwrite = false) {
        if (is_object($criterion) && $isSort === null && $criterion instanceof Ac_I_Search_Criterion_Sort) {
            $isSort = true;
        }
        if ($isSort) $this->setCriteria(array($id => $criterion), $allowOverwrite, true);
            else $this->setSortCriteria(array($id => $criterion), $allowOverwrite, true);
    }
    
    function setCriteria(array $criteria, $allowOverwrite = false, $add = false) {
        foreach ($criteria as $id => $v) {
            $throw = false;
            if (is_object($v)) {
                if (!$v instanceof Ac_I_Search_Criterion_Search && !is_callable($v)) {
                    $throw = true;
                }
            } elseif (is_callable($v)) $criteria[$id] = new Ac_Model_Criterion_Callback($v);
        }
        if ($throw) throw Ac_E_InvalidCall::wrongClass("criteria['{$id}']", $v, 
                    array('Ac_I_Search_Criterion_Search', 'Callable'));
        if ($add) {
            if (!$allowOverwrite && $ex = array_intersect_key($criteria, $this->criteria))
                throw Ac_E_InvalidCall::alreadySuchItem("Criteria", $ex, 'deleteCriterion');
            $this->criteria = array_merge($this->criteria, $criteria);
        } else {
            $this->criteria = $criteria;
        }
    }
    
    function setSortCriteria(array $sortCriteria, $allowOverwrite = false, $add = false) {
        foreach ($sortCriteria as $id => $v) {
            $throw = false;
            if (is_object($v)) {
                if (!$v instanceof Ac_I_Search_Criterion_Sort && !is_callable($v)) {
                    $throw = true;
                }
            } elseif (is_callable($v)) $sortCriteria[$id] = new Ac_Model_SortCriterion_Callback($v);
        }
        if ($throw) throw Ac_E_InvalidCall::wrongClass("sortCriteria['{$id}']", $v, 
                    array('Ac_I_Search_Criterion_Search', 'Callable'));
        if ($add) {
            if (!$allowOverwrite && $ex = array_intersect_key($sortCriteria, $this->sortCriteria))
                throw Ac_E_InvalidCall::alreadySuchItem("Criteria", $ex, 'deleteCriterion');
            $this->sortCriteria = array_merge($this->sortCriteria, $sortCriteria);
        } else {
            $this->sortCriteria = $sortCriteria;
        }
    }
    
    function listOwnCritera() {
        return array_keys($this->criteria);
    }
    
    function listAllCriteria() {
        $res = $this->listOwnCritera();
        if ($this->parentSearch) $res = array_unique(array_merge($res, $this->parentSearch->listAllCriteria()));
        return $res;
    }
    
    /**
     * @return Ac_I_Search_Criterion_Search
     */
    function getOwnCriterion($id) {
        if (isset($this->criteria[$id])) {
            if (!is_object($this->criteria[$id])) {
                $this->criteria[$id] = Ac_Prototyped::factory($this->criteria[$id], null);
                if (!is_callable($this->criteria[$id]) && !$this->criteria[$id] instanceof Ac_I_Search_Criterion_Search) {
                    throw Ac_E_InvalidCall::wrongClass("criteria['{$id}']", $this->criteria[$id], 
                        array('Ac_I_Search_Criterion_Search', 'Callable'));
                }
            }
        } else {
            throw Ac_E_InvalidCall::noSuchItem("Criterion", $id, "listOwnCriteria");
        }
        return $this->criteria[$id];
    }
    
    function getApplicableSearchCriteria(array $query, array & $remainingQuery = array(), $ignoreParent = false, $areByIds = false) {
        $res = array();
        $remainingQuery = $query;
        
        // extract AdHoc criteria
        foreach ($remainingQuery as $k => $v) {
            if (is_object($v)) {
                if ($v instanceof Ac_I_Search_Criterion_Search) {
                    $res[$k] = $v;
                    unset($remainingQuery[$k]);
                } elseif (is_callable($v)) {
                    $res[$k] = new Ac_Model_Criterion_Callback($v);
                    unset($remainingQuery[$k]);
                }
            }
        }
        // extract own criteria
        $own = array_intersect_key($remainingQuery, $this->criteria);
        foreach ($own as $k => $v) {
            $res[$k] = $this->getOwnCriterion($k);
            unset($remainingQuery[$k]);
        }
        // extract field matches
        if (isset($remainingQuery[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION])) {
            $res[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION] = new Ac_Model_Criterion_Identifier(
                array(
                    'mapper' => $this->mapper? $this->mapper : null,
                    'areByIds' => $areByIds,
                )
            );
            unset($remainingQuery[Ac_I_Search_FilterProvider::IDENTIFIER_CRITERION]);
        }
        if ($this->defaultFieldList && $remainingQuery)  {
            $fields = array_intersect(array_keys($remainingQuery), $this->defaultFieldList);
            foreach ($fields as $field) {
                $val = $query[$field];
                if (is_scalar($val) || is_array($val)) {
                    $res[$field] = new Ac_Model_Criterion_FieldEquals();
                    unset($remainingQuery[$field]);
                }
            }
        }
        // ask parent if something's left
        if (!$remainingQuery && $this->parentSearch && $ignoreParent) {
            foreach ($this->parentSearch->getApplicableSearchCriteria($remainingQuery, $remainingQuery, false) as $k => $v) {
                $res[$k] = $v;
            }
        }
        
        return $res;
    }
    
    function listOwnSortCriteria() {
        return array_keys($this->sortCriteria);
    }
    
    function listAllSortCriteria() {
        $res = $this->listOwnSortCriteria();
        if ($this->parentSearch) $res = array_unique(array_merge($res, $this->parentSearch->listAllSortCriteria()));
        return $res;
    }
    
    /**
     * @return Ac_I_Search_Criterion_Sort
     */
    function getOwnSortCriterion() {
        if (isset($this->sortCriteria[$id])) {
            if (!is_object($this->sortCriteria[$id])) {
                $this->sortCriteria[$id] = Ac_Prototyped::factory($this->sortCriteria[$id], null);
                if (!is_callable($this->sortCriteria[$id]) && !$this->sortCriteria[$id] instanceof Ac_I_Search_Criterion_Sort) {
                    throw Ac_E_InvalidCall::wrongClass("sortCriteria['{$id}']", $this->sortCriteria[$id], 
                        array('Ac_I_Search_Criterion_Sort', 'Callable'));
                }
            }
        } else {
            throw Ac_E_InvalidCall::noSuchItem("Criterion", $id, "listOwnCriteria");
        }
        return $this->sortCriteria[$id];
    }
    
    protected function getDefaultSortCriterion($sort, $bool = false) {
        $res = null;
        if (is_scalar($sort) && in_array($sort, $this->defaultFieldList)) {
            if ($bool) $res = true;
                else $res = new Ac_Model_SortCriterion_Field(array('field' => $sort));
        } elseif (is_array($sort) && $this->defaultFieldList) {
            $s = array();
            foreach ($sort as $k => $v) {
                if (is_numeric($k)) {
                    $k = $v;
                    $v = true;
                }
                $s[$k] = $v;
            }
            if ($s && !array_diff(array_keys($s), $this->defaultFieldList)) {
                if ($bool) $res = true;
                else $res = new Ac_Model_SortCriterion_Fields(array('fields' => $s));
            }
        } elseif (is_object($sort) && $sort instanceof Ac_I_Search_Criterion_Sort) {
            $res = $bool? true : $sort;
        } elseif (is_callable($sort)) $res = $bool? true : new Ac_Model_SortCriterion_Callback($sort);
        return $res;
    }
    
    protected function getApplicableSortCriterion($sort) {
        $res = null;
        if (is_scalar($sort) && array_key_exists($sort, $this->sortCriteria)) $res = $this->getSortCriterion($sort);
        else {
            if ($this->parentSearch) $res = $this->parentSearch->getApplicableSortCriterion($sort);
            if (!$res) $res = $this->getDefaultSortCriterion($sort);
        }
        return $res;
    }
    
    function setParentSearch(Ac_Model_Search $parentSearch = null) {
        $this->parentSearch = $parentSearch;
    }

    /**
     * @return Ac_Model_Search
     */
    function getParentSearch() {
        return $this->parentSearch;
    }
    
    function filter(array $records, array $query = array(), $sort = false, $limit = false, $offset = false, & $remainingQuery = true, & $sorted = false, $areByIds = false) {
        
        $strict = func_num_args() <= 5 || $remainingQuery === true;
        
        $remainingQuery = $query;
        $res = $records;
        
        if ($query && ($criteria = $this->getApplicableSearchCriteria($query, $remainingQuery, true, $areByIds))) {
            $adHoc = array();
            $bulk = array();
            foreach ($criteria as $k => $criterion) {
                $adHoc[$k]  = isset($query[$k]) && is_object($query[$k]) && $query[$k] instanceof Ac_I_Search_Criterion_Search;
                if ($criterion instanceof Ac_I_Search_Criterion_Bulk) {
                    $bulk[$k] = $criterion;
                }
            }
            
            // Apply bulk criteria
            foreach ($bulk as $k => $criterion) {
                if (($isAdHoc = $adHoc[$k])) $value = null;
                    else $value = $query[$k];
                $res = $criterion->filter($res, $k, $value, $isAdHoc);
                unset($criteria[$k]);
            }
            
            foreach ($res as $key => $record) {
                foreach ($criteria as $k => $criterion) {
                    if (($isAdHoc = $adHoc[$k])) $value = null;
                        else $value = $query[$k];
                    if (!$criterion->test($record, $k, $value, $isAdHoc)) {
                        unset($res[$key]);
                        continue 2;
                    }
                }
            }
        }
        
        if ($res) {
            if ($sort) {
                $sc = $this->getApplicableSortCriterion($sort);
                if ($sc) {
                    if ($sc instanceof Ac_I_Search_Criterion_ExtendedSort) $res = $sc->sort ($res);
                    uasort($res, $sc);
                    $sorted = true;
                }
            }
            
            $remSort = $sorted? false : $sort;
            if (($remSort || $remainingQuery)) {
                if ($this->parentSearch) { // still the work left? ask the daddy
                    $remQ = array();
                    if ($strict) $remQ = true;
                    $res = $this->parentSearch->filter($res, $remainingQuery, $remSort, $limit, $offset, $remQ, $sorted);
                } else {
                    if ($strict) { // curse our incompleteness cuz' demands too high
                        if ($remainingQuery) 
                            throw new Ac_E_InvalidUsage("Criterion ".implode(" / ", array_keys($remainingQuery))." is unknown to this search");
                        if (!$sorted && $sort) {
                            throw new Ac_E_InvalidUsage("Sort mode ".Ac_Model_Mapper::describeSort($sort)." is unknown to this search");
                        }
                    }
                }
            } else {
                // sorted and filtered? SLICE it
                if ((int) $offset || (int) $limit) {
                    $res = array_slice($res, (int) $offset, (int) $limit, true);
                }
            }
        } else {
            // no results - got the job done
            $sorted = true;
            $remainingQuery = array();
        }
        return $res;
    }
    
    function __clone() {
        foreach ($this->criteria as $k => $v) if (is_object($v)) $this->criteria[$k] = clone $v;
        foreach ($this->sortCriteria as $k => $v) if (is_object($v)) $this->sortCriteria[$k] = clone $v;
    }

    function setDefaultFieldList(array $defaultFieldList) {
        $this->defaultFieldList = $defaultFieldList;
    }

    /**
     * @return array
     */
    function getDefaultFieldList() {
        return $this->defaultFieldList;
    }

    function setMapper(Ac_Model_Mapper $mapper) {
        $this->mapper = $mapper;
    }

    /**
     * @return Ac_Model_Mapper
     */
    function getMapper() {
        return $this->mapper;
    }    
    
}