<?php

if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass('Ae_Sql_Filter');
elseif (!class_exists('Ae_Sql_Filter')) require('Ae/Sql/Filter.php');

class Ae_Sql_Filter_Multiple extends Ae_Sql_Filter {

    var $_filters = array();
    
    /**
     * How criteria are joined: TRUE = 'OR', FALSE = 'AND'
     * @var bool
     */
    var $isOr = false;
    
    var $differentFiltersForWhereAndHaving = false; // TODO: implement this...
    
    /**
     * Usually the Ae_Sql_Filter_Multiple accepts array (childName => childValue); but if this property is TRUE, it just sets assigned value to each child filter. 
     * @var bool
     */
    var $setSameValueForAllChildren = false;
    
    function listFilters() {
        return array_keys($this->_filters);
    }
    
    /**
     * @param string $id
     * @return Ae_Sql_Filter
     */
    function getFilter($id) {
        if (!isset($this->_filters[$id])) trigger_error("No such filter: '$id'", E_USER_ERROR);
        if (is_array($this->_filters[$id])) {
            $options = $this->_filters[$id];
            $options['id'] = $id;
            $options['parentPart'] = & $this;
            $options['db'] = & $this->_db;
            $this->_filters[$id] = & $this->factory($options, 'Ae_Sql_Filter');
        }
        $res = & $this->_filters[$id];
        return $res;
    }
    
    /**
     * @param Ae_Sql_Db $db
     */
    function setDb(& $db) {
        parent::setDb($db);
        foreach (array_keys($this->_filters) as $f) if (is_object($this->_filters[$f])) $this->_filters[$f]->setDb($db);
    }
    
    /**
     * @param array|Ae_Sql_Filter $filter
     * @param string $id
     */
    function addFilter(& $filter, $id = false) {
        assert(
                is_array($filter) && (strlen($id) || isset($filter['id']) && strlen($filter['id'])) 
            ||  is_a($filter, 'Ae_Sql_Filter') && (strlen($id) || strlen($filter->id))
        );
        $aId = is_array($filter)? (isset($filter['id'])? $filter['id'] : false) : $filter->id;
        if (!strlen($aId)) $aId = $id;
        if (isset($this->_filters[$aId])) trigger_error("Filter with id '{$id}' is already in the collection", E_USER_ERROR);
        $this->_filters[$aId] = & $filter;
        if (is_object($filter)) {
            $filter->setDb($this->_db);
        }
        return $aId;
    }
    
    function _colCriteria() {
        $res = 0;
        $crit = array();
        foreach ($this->listFilters() as $i) {
            $f = & $this->getFilter($i);
            $f->isHaving = $this->isHaving;
            $crit = array_merge($crit, $this->isHaving? $f->getAppliedHaving() : $f->getAppliedWhere());
        }
        if (count($crit) == 1) {
            $crit = array_slice($crit, 0, 1);
            $res = current($crit);
        } elseif (count($crit) > 1) {
            $res = $this->_db->indent("\n(".implode($this->isOr? ")\nOR (" : ")\nAND (", $crit).")");
        } else {
            $res = false;
        }
        return $res;
    }
    
    // ---------------------------------- template methods ------------------------------

    function _doOnInitialize($options) {
        if (isset($options['filters'])) {
            assert(is_array($options['filters']));
            foreach(array_keys($options['filters']) as $k) $this->addFilter($options['filters'][$k], is_numeric($k)? false : $k);  
        }
    }
    
    function _doBeforeExpandPaths(& $input) {
        $r = array();
        $c = false;
        foreach (array_keys($input) as $k) {
            if (is_numeric($k) && strlen($input[$k])) {
                $r[$input[$k]] = true;
                $c = true;
            } else {
                $r[$k] = & $input[$k];
            }
        }
        if ($c) $input = & $r;
    }
    
    /**
     * @access protected
     */
    function _doBind($input) {
        if ($this->setSameValueForAllChildren) {
            foreach ($this->listFilters() as $id) {
                $f = & $this->getFilter($id);
                $f->bind($input);
            }
        } else {
            if (is_array($input)) {
                $appliedFilters = array();
                foreach ($this->listFilters() as $id) {
                    if (isset($input[$id])) {
                        $f = & $this->getFilter($id);
                        $f->bind($input[$id]);
                        $appliedFilters[] = $id;
                    }
                }
                if (!count($appliedFilters)) $this->applied = false;
            } else {
                $this->applied = false;
            }
        }
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedWhere() {
        if (!$this->isHaving && count($this->_filters)) {
            $res = array($this->_colCriteria());
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        if ($this->isHaving && count($this->_filters)) {
            $res = array($this->_colCriteria());
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedAliases() {
        $res = $this->aliases;
        foreach ($this->listFilters() as $i) {
            $f = & $this->getFilter($i);
            $res = array_merge($res, $f->getAppliedAliases());
            //var_dump($f->id, $f->getAppliedAliases());
        }
        $res = array_unique($res);
        return $res;
    }
    
}
?>