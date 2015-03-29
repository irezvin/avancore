<?php

class Ac_Admin_Feature_ShowColumnsByFilter extends Ac_Admin_Feature {
    
    /**
     * @var array of column names to show
     */
    var $colNames = array();
    
    /**
     * Should *hide* instead of *showing* the columns when filter pattern is matched
     * @var bool
     */
    var $shouldHide = false;
    
    /**
     * filterPath => value or set of values
     * @var array
     */
    var $filterPattern = array();
    
    /**
     * filterPath => decorator (applied before pattern checking)
     * @var array
     */
    var $decorators = array();
    
    var $sqlSelectSettings = array();
    
    function getFilterValues() {
        $values = $this->manager->getFilterForm()->getValue();
        foreach ($this->decorators as $path => $decorator) {
            $path = Ac_Util::pathToArray($path);
            $val = Ac_Util::getArrayByPath($values, $path, null, $found);
            if ($found) {
                $res = Ac_Decorator::decorate($decorator, $val);
                Ac_Util::setArrayByPath($values, $path, $res);
            }
        }
        return $values;
    }
    
    function checkFiltersMatch() {
        $values = $this->getFilterValues();
        $res = true;
        foreach ($this->filterPattern as $path => $crit) {
            $crit = Ac_Util::toArray($crit);
            $val = Ac_Util::getArrayByPath($values, Ac_Util::pathToArray($path));
            if (!in_array($val, $crit)) {
                $res = false;
                break;
            }
        }
        return $res;
    }
    
    function getColumnSettings() {
        $res = array();
        if ($this->colNames) {
            $hidden = $this->shouldHide? false: true;
            if ($this->checkFiltersMatch()) $hidden = !$hidden;
            foreach (Ac_Util::toArray($this->colNames) as $colName) {
                $res[$colName] = array('hidden' => $hidden);
            }
        }
        return $res;
    }
    
    function getSqlSelectSettings() {
        $res = parent::getSqlSelectSettings();
        if ($this->shouldHide? !$this->checkFiltersMatch() : $this->checkFiltersMatch()) Ac_Util::ms($res, $this->sqlSelectSettings);
        return $res;
    }
    
    
}