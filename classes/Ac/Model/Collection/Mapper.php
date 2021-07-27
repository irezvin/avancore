<?php

class Ac_Model_Collection_Mapper extends Ac_Model_Collection_Abstract {

    /**
     * query to restrict items returned by the Mapper
     * @var array
     */
    protected $query = array();

    /**
     * sort parameter passed to the Mapper
     */
    protected $sort = false;
    
    /**
     * @var Ac_Application
     */
    protected $application = false;

    /**
     * identifier of used mapper
     * @var string
     */
    protected $mapperId = false;
    
    /**
     * Mapper object to retrieve items
     * @var Ac_Model_Mapper
     */
    protected $mapper = false;

    /**
     * instance of Search object that will be used by the mapper instead of built-in one
     * @var Ac_Model_Search
     */
    protected $search = null;

    /**
     * prototype override of Mapper's own search
     * @var array
     */
    protected $searchPrototype = array();

    /**
     * whether set Mapper's search as parent of Collection's search
     * @var bool
     */
    protected $searchInheritsMapper = true;
    
    /**
     * property of the records that will be returned as Iterator's key()
     * @var string
     */
    protected $keyProperty = false;
    
    protected $appliedQuery = false;
    
    /**
     * Sets query to restrict items returned by the Mapper
     */
    function setQuery(array $query, $override = false) {
        if ($this->isOpen) {
            $this->close();
        }
        if ($override) Ac_Util::ms($this->query, $query);
            else $this->query = $query;
    }

    /**
     * Returns query to restrict items returned by the Mapper
     * @return array
     */
    function getQuery() {
        return $this->query;
    }

    /**
     * Sets sort parameter passed to the Mapper
     */
    function setSort($sort) {
        if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
        $this->sort = $sort;
    }

    /**
     * Returns sort parameter passed to the Mapper
     */
    function getSort() {
        return $this->sort;
    }    /**
     * Sets query to restrict items returned by the Mapper
     */
    
    function setApplication(Ac_Application $application) {
        if ($application !== ($oldApplication = $this->application)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->application = $application;
        }
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }    
    
    /**
     * Sets identifier of used mapper
     * @param string $mapperId
     */
    function setMapperId($mapperId) {
        if ($mapperId !== ($oldMapperId = $this->mapperId)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->mapperId = $mapperId;
            $this->mapper = false;
        }
    }

    /**
     * Returns identifier of used mapper
     * @return string
     */
    function getMapperId($asIs = false) {
        $res = $this->mapperId;
        if (!$asIs && !strlen($res) && $this->mapper) $res = $this->mapper->getId();
        return $res;
    }
    
    /**
     * Sets Mapper object to retrieve items
     */
    function setMapper(Ac_Model_Mapper $mapper) {
        if ($mapper !== ($oldMapper = $this->mapper)) {
        if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->mapper = $mapper;
            $this->mapperId = false;
        }
    }

    /**
     * Returns Mapper object to retrieve items. Will try to retrieve mapper from Application if $mapperId is set.
     * @param bool $asIs Don't try to retrieve mapper
     * @param bool $require throw an exception if no mapper available
     * @return Ac_Model_Mapper
     */
    function getMapper($asIs = false, $require = false) {
        if (!$this->mapper) {
            if (strlen($this->mapperId)) {
                if ($this->application) $this->mapper = $this->application->getMapper($this->mapperId);
                    else $this->mapper = Ac_Model_Mapper::getMapper($this->mapperId);
            }
            if ($require && !$this->mapper) {
                throw Ac_E_InvalidUsage("need to setMapper() or setMapperId() to proceed");
            }
        }
        return $this->mapper;
    }

    /**
     * Sets instance of Search object that will be used by the mapper instead of built-in one
     */
    function setSearch(Ac_Model_Search $search = null) {
        if ($search !== ($oldSearch = $this->search)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->search = $search;
            if ($this->search) $this->searchPrototype = array();
        }
    }

    /**
     * Returns instance of Search object that will be used by the mapper instead of built-in one
     * @return Ac_Model_Search
     */
    function getSearch() {
        return $this->search;
    }

    /**
     * Sets prototype override of Mapper's own search
     */
    function setSearchPrototype(array $searchPrototype) {
        if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
        $this->searchPrototype = $searchPrototype;
        if ($this->searchPrototype) $this->search = null;
    }

    /**
     * Returns prototype override of Mapper's own search
     * @return array
     */
    function getSearchPrototype() {
        return $this->searchPrototype;
    }

    /**
     * Sets whether set Mapper's search as parent of Collection's search
     * @param bool $searchInheritsMapper
     */
    function setSearchInheritsMapper($searchInheritsMapper) {
        if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
        $this->searchInheritsMapper = $searchInheritsMapper;
    }

    /**
     * Returns whether set Mapper's search as parent of Collection's search
     * @return bool
     */
    function getSearchInheritsMapper() {
        return $this->searchInheritsMapper;
    }
    
    /**
     * Sets property of the records that will be returned as Iterator's key()
     * @param string $keyProperty
     */
    function setKeyProperty($keyProperty) {
        if ($keyProperty !== ($oldKeyProperty = $this->keyProperty)) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot ".__FUNCTION__."() while isOpen(). close() first");
            $this->keyProperty = $keyProperty;
        }
    }

    /**
     * Returns property of the records that will be returned as Iterator's key()
     * @return string
     */
    function getKeyProperty() {
        return $this->keyProperty;
    }

    /**
     * @return Ac_Model_Search
     */
    function getSearchInstance() {
        if ($this->searchPrototype) {
            if ($this->isOpen) throw new Ac_E_InvalidUsage("Cannot instantiate search in ".__FUNCTION__."() "
                . "while isOpen(). close() first");
            $this->search = Ac_Prototyped::factory($this->searchPrototype, 'Ac_Model_Search');
            if ($this->searchInheritsMapper)  if ($this->searchInheritsMapper) $this->search->setParentSearch(
                $this->getMapper(false, true)->getSearch()
            );
            $this->searchPrototype = null;
        }
        if ($this->search) $res = $this->search;
            else $res = null;
        return $res;
    }

    /**
     * @return array
     */
    protected function listExtraCriteria() {
        $m = $this->getMapper(false, true);
        if (($stor = $m->getStorage()) instanceof Ac_Model_Storage_Sql) {
            $sel = $m->createSqlSelect();
            $res = $sel->listParts();
        } else {
            $res = array();
        }
        return $res;
    }
    
    function listPossibleCriteria() {
        $m = $this->getMapper(false, true);
        $s = $this->getSearchInstance();
        if ($s) $res = $s->listAllCriteria(); 
        else $res = $m->getSearch()->listAllCriteria();
        $res = array_merge($res, $this->listExtraCriteria());
        $res = array_merge($res, $m->getPrototype()->listProperties());
        $res = array_unique($res);
        return $res;
    }
    
    function listPossibleSortCriteria() {
        $m = $this->getMapper(false, true);
        $s = $this->getSearchInstance();
        if ($s) $res = $s->listAllSortCriteria(); 
        else $res = $m->getSearch()->listAllSortCriteria();
        $res = array_unique($res);
        return $res;
    }
    
    function setKeys(array $keys = array()) {
        $cr = Ac_Model_Search::IDENTIFIER_CRITERION;
        if ($keys) {
            $this->query[$cr] = $keys;
        } else {
            unset($this->query[$cr]);
        }
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function createSqlSelect() {
        $storage = $this->getMapper()->getStorage();
        if ($storage instanceof Ac_Model_Storage_Sql) {
            $res = $storage->createSqlSelect(array(), $this->query, $this->sort, $this->limit, $this->offset);
        } else {
            $res = null;
        }
        return $res;
    }
    
    protected function resetState() {
        parent::resetState();
        if ($this->cleanGroupOnAdvance && function_exists('gc_enable')) gc_enable();
        $this->appliedQuery = $this->query;
        if ($this->search) {
            if ($this->searchInheritsMapper) $this->search->setParentSearch(
                $this->getMapper(false, true)->getSearch()
            );
            $this->appliedQuery[Ac_Model_Mapper::QUERY_SEARCH] = $this->search;
        } elseif ($this->searchPrototype) {
            $this->appliedQuery[Ac_Model_Mapper::QUERY_SEARCH] = $this->searchPrototype;
        }
    }
    
    protected function doCount() {
        $res = $this->getMapper(false, true)->count($this->appliedQuery);
        return $res;
    }

    protected function doFetchGroup($offset, $length) {
        $res = $this->getMapper(false, true)->find($this->appliedQuery, false, $this->sort, $length, $offset);
        return $res;
    }
    
    protected function doCalcItemKey($item, $index) {
        if (strlen($this->keyProperty)) {
            $res = $item->{$this->keyProperty};
        } else {
            $res = $index;
        }
        return $res;
    }
    
    protected function cleanCurrentGroup() {
        foreach ($this->currentGroup as $item) {
            $item->cleanupMembers();
        }
        $this->currentGroup = array();
        gc_collect_cycles();
    }

}