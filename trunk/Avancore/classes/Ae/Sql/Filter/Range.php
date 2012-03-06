<?php

/**
 * This filter accepts ranges in two formats:
 * - array(array('min' => value1, 'max' => value2), value3, array('min' => value4, 'max' => value5), value6, array('min' => value7), array('max' => value8) etc
 * - string: "value1 .. value2, value3, value4 .. value5, value6, value7.., ..value8"
 * In both cases filter will return expression "$colName IN (value3, value6) OR $colName BETWEEN (value1 AND value2) OR $colName BETWEEN (value4 and value5) OR $colName >= value7 OR $colName <= $value8"
 * Filter doesn't try to optimize criteria by excluding redundant bounds. 
 */
class Ae_Sql_Filter_Range extends Ae_Sql_Filter {
    
    var $colName = false;
    
    var $_range = false;
    
    var $minMaxSeparatorRx = '/\.\./';
    
    var $rangesSeparatorRx = '/[,;]/';
    
    var $valueRx = false;
    
    function _filterValue(& $item) {
        if ($this->valueRx !== false) {
            if (preg_match($this->valueRx, $item, $matches)) {
                $item = $matches[0];
                $res = true;
            } else {
                $res = false;
            }
        } else {
            $res = true;
        }
        return $res;
    }
    
    function checkRange($range, $triggerErrors = false) {
        $res = array();
        if (!is_array($range)) trigger_error("\$range must be an array", E_USER_ERROR);
        if (is_array($range) && count($range) && !array_diff(array_keys($range), array('min', 'max'))) $range = array($range);
        foreach ($range as $k => $item) {
            if (is_array($item)) {
                $itm = array();
                if (array_key_exists('min', $item) && $this->_filterValue($item['min'])) $itm['min'] = $item['min'];
                if (array_key_exists('max', $item) && $this->_filterValue($item['max'])) $itm['max'] = $item['max'];
                //if (!count($itm)) trigger_error("At least one of the keys 'min' and 'max' must be given in \$range item");
                if (count($itm)) $res[] = $itm;
            } elseif ($this->_filterValue($item)) {
                $res[] = $item;
            }
        }
        return $res;
    }
    
    function parseRange($string) {
        $res = array();
        $intervals = preg_split($this->rangesSeparatorRx, $string);
        foreach ($intervals as $interval) {
            $item = null;
            $minMax = preg_split($this->minMaxSeparatorRx, $interval, 2);
            if ($minMax[0] === $interval) $item = $interval; // we have one value...
            elseif (!strlen(trim($minMax[0]))) {
                $item = array('max' => trim($minMax[1]));
            } elseif (!strlen(trim($minMax[1]))) {
                $item = array('min' => trim($minMax[0]));
            } elseif (strlen(trim($minMax[0])) && strlen(trim($minMax[1]))) {
                $item = array('min' => trim($minMax[0]), 'max' => trim($minMax[1]));
            }
            if (!is_null($item)) $res[] = $item;
        }
        return $res;
    }
    
    function getRangeCriteria() {
        $cr = array();
        $in = array();
        $col = $this->colName;
        if (is_array($this->_range)) {
            foreach ($this->_range as $item) {
                if (!is_array($item)) $in[] = $this->_db->quote($item);
                else {
                    if (array_key_exists('min', $item) && array_key_exists('max', $item))
                        $cr[] =  $col.' BETWEEN '.$this->_db->quote($item['min']). ' AND '.$this->_db->quote($item['max']);
                    elseif (array_key_exists('min', $item))
                        $cr[] =  $col.' >= '.$this->_db->quote($item['min']);
                    elseif (array_key_exists('max', $item))
                        $cr[] =  $col.' <= '.$this->_db->quote($item['max']);
                }
            }
        }
        if (count($in)) $cr[] = count($in) === 1 ? ($col.' = '.$in[0]) : ($col.' IN ('.implode(', ', $in).')');
        if (!count($cr)) {
            $res = '1';
        } elseif (count($cr) === 1) {
            $res = $cr[0];
        } else {
            $res = '('.implode (' OR ', $cr).')';
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doBind($input) {
        if (is_array($input)) $this->_range = $this->checkRange($input);
            else $this->_range = $this->checkRange($this->parseRange($input));
        $this->applied = (!!count($this->_range));
    }
    
    
    
    /**
     * @access protected
     */
    function _doGetAppliedWhere() {
        if (!$this->isHaving && $this->_range && $this->colName) {
            $res = array($this->getRangeCriteria());
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        if ($this->isHaving && $this->_range && $this->colName) {
            $res = array($this->getRangeCriteria());
        } else {
            $res = array();
        }
        return $res;
    }    
    
}

?>