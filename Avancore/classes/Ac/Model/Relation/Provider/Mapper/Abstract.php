<?php

/**
 * 
 */

/**
 * Base class for a set of storage-independent relation providers.
 * Records are retrieved using exclusively mapper methods.
 */
abstract class Ac_Model_Relation_Provider_Mapper_Abstract extends Ac_Model_Relation_Provider {
    
    /**
     * mapper that provides destination objects (null or FALSE if no mapper used)
     * @var Ac_Model_Mapper
     */
    protected $mapper = null;
    
    /**
     * additional restrictions to add to $query arg of Mapper's find() method
     * @var array
     */
    protected $query = array();

    /**
     * value to Mapper's find() $sort
     */
    protected $sort = false;

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
     * Sets additional restrictions to add to $query arg of Mapper's find() method
     */
    function setQuery(array $query) {
        $this->query = $query;
    }

    /**
     * Returns additional restrictions to add to $query arg of Mapper's find() method
     * @return array
     */
    function getQuery() {
        return $this->query;
    }

    /**
     * Sets value to Mapper's find() $sort
     */
    function setSort($sort) {
        $this->sort = $sort;
    }

    /**
     * Returns value to Mapper's find() $sort
     */
    function getSort() {
        return $this->sort;
    }
    
    protected function extractSingleValues(array $values) {
        $res = array();
        foreach ($values as $k => $v) {
            if (is_array($v)) {
                $res[$k] = array_pop($v);
                if (count($v)) throw new Exception("Composite foreign keys are not supported");
            } else {
                $res[$k] = $v;
            }
        }
        return $res;
    }

}