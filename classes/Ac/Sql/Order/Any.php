<?php

/**
 * Allows sorting by any field in current or related tables
 */
class Ac_Sql_Order_Any extends Ac_Sql_Order {
    
    var $separator = ',';

    /**
     * For disambiguation when multiple tables are used - fields without 'path'
     * will have this table alias (i.e. "t" for default sqlSelects)
     */
    var $defaultAlias = null;
    
    protected $sortArray = [];
    
    function _doBind($input) {
        $sortArray = [];
        if (!is_null($input) && !is_array($input)) {
            $sortParamItems = explode(",", $input);
            foreach ($sortParamItems as $sortParamItem) {
                $sortField = $sortParamItem;
                $sortDir = 1;
                if (preg_match('/(-asc|-desc)$/i', $sortParamItem, $matches)) {
                    $sortField = substr($sortField, 0, -strlen($matches[0]));
                    if (strtolower($matches[0]) == '-desc') $sortDir = 0;
                }
                $sortField = Ac_Util::kebabPathToBrackets($sortField);
                $sortArray[$sortField] = $sortDir;
            }
        } else if (is_array($input) && count($input)) {
            $sortArray = $input;
        }
        $this->sortArray = $sortArray;
    }
    
    function doesApply() {
        return $this->applied && $this->enabled;
    }
    
    /**
     * sortArray: ['prop' => 1, 'rel[relatedProp]' => 0, 'rel[assoc][otherProp]' => 1] 
     * will return 'prop ASC', '`rel`.relatedProp DESC', '`rel[assoc]`.otherProp ASC'
     */
    function _doGetAppliedOrderBy() {
        if (!$this->sortArray) return '';
        $crit = [];
        foreach ($this->sortArray as $path => $dir) {
            $arrPath = Ac_Util::pathToArray($path);
            $tableField = [];
            $field = array_pop($arrPath);
            if ($path) $tableField[] = Ac_Util::arrayToPath($arrPath); // first add alias
            else if ($this->defaultAlias) $tableField[] = $this->defaultAlias;
            $tableField[] = $field; // then add field name
            $ord = $this->_db->nameQuote($tableField); // `alias`.`field`
            if (!$dir) $ord .= ' DESC';
            $crit[] = $ord;
        }
        return implode(", ", $crit);
    }
    
    /**
     * sortArray: ['prop' => 1, 'rel[relatedProp]' => 1, 'rel[assoc][otherProp]' => 1] 
     * will return aliases 'rel' and 'rel[assoc]' 
     * (which will be picked by relation-based table providers)
     */
    function _doGetAppliedAliases() {
        $res = [];
        if ($this->defaultAlias) $res[] = $this->defaultAlias;
        foreach (array_keys($this->sortArray) as $path) {
            $arrPath = Ac_Util::pathToArray($path);
            if (count($arrPath) > 1) $res[] = Ac_Util::arrayToPath(array_slice($arrPath, 0, -1));
        }
        return array_unique($res);
    }
        
}