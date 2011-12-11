<?php

if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass('Ae_Sql_Filter');
elseif (!class_exists('Ae_Sql_Filter')) require('Ae/Sql/Filter.php');

class Ae_Sql_Filter_Substring extends Ae_Sql_Filter {

    var $colNames = array();
    var $patBefore = '%';
    var $patAfter = '%';
    var $ifNullFunction = 'IFNULL';
    var $likeOperator = 'LIKE';
    var $caseFunction = false;
    
    /**
     * Whether to CONCAT columns if there are several of them instead of using OR criteria
     *
     * @var bool
     */
    var $useConcat = true;
    var $allowWildcards = true;
    var $value = false;
    var $inputMultipleValues = false;
    var $multipleValuesOperator = ' OR ';
    
    /**
     * Prefix of the value that indicates to suppress patBefore and patAfter params;
     * is usefull to improve search in loose substring-comparison filters. For example, set this to '=' if you want
     * to make user able to indicate strict search by entering string '=value'.
     * @var bool|false
     */
    var $discardPatsPrefix = false;
    
    function _colCriteria($colNames, $value = false) {
        
        if ($value === false) $value = $this->value;
        if (is_array($value)) {
            
            $res = '';
            $r = array();
            foreach ($value as $v) $r[] = $this->_colCriteria($colNames, $v);
            $sql =  "(".implode($this->multipleValuesOperator, $r).")";
            
        } else {

            if (strlen($this->ifNullFunction)) {
                foreach ($colNames as $k => $c) 
                    $colNames[$k] = "{$this->ifNullFunction}($c,'')";
            }
            if (strlen($this->caseFunction)) {
                foreach ($colNames as $k => $c) { 
                    $colNames[$k] = "{$this->caseFunction}($c)";
                }
            }
            $discardPats = (($l = strlen($this->discardPatsPrefix)) && !strncmp($this->discardPatsPrefix, $value, $l)); 
            if (!$discardPats && $this->useConcat) {
                if (count($colNames) > 1) {
                    $cn = "CONCAT(".implode(", ", $colNames).")";
                } else {
                    $cn = implode("", $colNames);
                }
                    $sql = $cn." ".$this->likeOperator." ".$this->_db->quote($this->patBefore.$value.$this->patAfter);
            } else {
                $c = array();
                if ($discardPats) {
                    $q = $this->_db->quote(substr($this->value, $l));
                }
                else $q = $this->_db->quote($this->patBefore.$value.$this->patAfter);

                foreach ($colNames as $cn) {
                    $c[] = $cn." {$this->likeOperator} ".$q;
                }

                $sql = '('.implode(') OR (', $c).')';
            }
        }
        return $sql;
    }
    
    // ---------------------------------- template methods ------------------------------

    function _filter($value) {
        if (!$this->allowWildcards) {
            $value = str_replace('%', '\\%', $value);
            $value = str_replace('_', '\\_', $value);
        }
        return $value;
    }
    
    /**
     * @access protected
     */
    function _doBind($input, $return = false) {
        if (is_array($input)) {
            if ($this->inputMultipleValues) {
                $this->value = array();
                foreach ($input as $v) if (strlen($v)) $this->value[] = $this->_filter($v);
            }
            elseif (isset($input['value'])) {
                $this->value = $input['value'];
            }
        } else {
            if (is_scalar($input)) $this->value = $input;            
        }
        if (!is_array($this->value)) {
            $this->value = $this->_filter($this->value);
            $this->applied = !!strlen($this->value);
        } else {
            $this->applied = count($this->value);
        }
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedWhere() {
        if ($this->colNames && !$this->isHaving) {
            $res = array($this->_colCriteria($this->colNames));
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        if ($this->colNames && $this->isHaving) {
            $res = array($this->_colCriteria($this->havingColNames));
        } else {
            $res = array();
        }
        return $res;
    }
    
}
?>