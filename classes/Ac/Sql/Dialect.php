<?php

abstract class Ac_Sql_Dialect {
    
    protected $nameQuoteChar = '"';
    
    protected $escapeChar = "\\";
    
    protected $ifNullFunction = 'COALESCE';
    
    protected $dateStoreFormats = array('date' => 'Y-m-d', 'time' => 'H:i:s', 'dateTime' => 'Y-m-d H:i:s', 'timestamp' => 'Y-m-d H:i:s');
    
    protected $zeroDates = array('date' => '0000-00-00', 'time' => '00:00', 'dateTime' => '0000-00-00 00:00:00', 'timestamp' => '0000-00-00 00:00:00');
    
    protected $inspectorClass = false;
    
    function hasPublicVars() {
        return false;
    }
    
    function nameQuote($name) {
        return $this->nameQuoteChar.str_replace($this->nameQuoteChar, "\\".$this->nameQuoteChar, $name).$this->nameQuoteChar;
    }
    
    function nameUnquote($name) {
        if ($this->isNameQuoted($name)) {
            return str_replace("\\".$this->nameQuoteChar, $this->nameQuoteChar, substr($name, 1, strlen($name) - 2));
        } else {
            return $name;
        }
    }
    
    function getNameQuoteChar() {
        return $this->nameQuoteChar;
    }
    
    function isNameQuoted($name) {
        return $name[0] == $this->nameQuoteChar && $name[strlen($name) - 1] == $this->nameQuoteChar;
    }
    
    function returnsLastInsertId() {
        return false;
    }
    
    function getLastInsertId(Ac_Sql_Db $db) {
        return null;
    }
    
    function getIfNullFunction() {
        return $this->ifNullFunction;
    }
	
	function hasToConvertDatesOnLoad() {
	    return false;
	}
	
	function hasToConvertDatesOnStore() {
	    return false;
	}
	
	function getDateStoreFormats() {
	    return $this->dateStoreFormats;
	}
	
	function getZeroDates() {
	    return $this->zeroDates;
	}
	
	function convertDates($row, $columnFormats) {
	    foreach ($columnFormats as $column => $format) {
	        if (isset($row[$column])) $row[$column] = Ac_Util::date($row[$column], $format); 
	    } 
	    return $row;
	}
	
	function convertDateForStore($date, $type) {
	    $dsf = $this->getDateStoreFormats();
	    $zd = $this->getZeroDates();
	    if (isset($dsf[$type])) {
	        if (is_null($date) || $date === false) $res = null;
	        else {
	            $ts = Ac_Util::date($date);
	            if ($ts === 0 && isset($zd[$type])) $res = $zd[$type];
	                else $res = Ac_Util::date($date, $dsf[$type]);
	        }
	    }
	    else {
                $res = Ac_Util::date($date, $type);
	    }
	    return $res;
	}
    
    /**
     * @return string 
     */
    abstract function getConcatExpression(array $expressions);
	
    /**
     * @return string 
     */
	abstract function applyLimits($statement, $count, $offset, $orderBy = false);
	
    /**
     * @return string 
     */
    abstract function ifStatement($if, $then, $else, $close = true);
	
    /**
     * @return string 
     */
	abstract function ifClose();
    
    /**
     * @return Ac_Sql_Dbi_Inspector
     */
    function createInspector(Ac_Sql_Db $db) {
        $c = $this->inspectorClass;
        $dbi = new $c ($db, $db->getDbName());
        return $dbi;
    }
    
    function replacePrefix($withString, $inString) {
        return $this->prepareSql($inString, $withString);
    }
    
    
    /**
     * @param string $sql
     * @param string $replacePrefixWith What to replace '#__' to
     * @param array $quotedPosArgs Positional arguments, already escaped & quoted (? in statement)
     * @param array $quotedNamedArgs Named arguments, already escaped & quoted (:name in statement)
     * @return string
     * @throws Ac_E_InvalidUsage If positional or named arg not found
     */
    
    function prepareSql($sql, $replacePrefixWith, array $quotedPosArgs = array(), array $quotedNamedArgs = array()) {
        
        // Do we have need to parse positional or numerical arguments
        $hasArgs = func_num_args() > 2;
        
        // Tokens we are interested in
        $rx = $hasArgs? '/([\'\\\\?"`])|(#__)|(:\w+)/' : '/([\'\\\\"`])|(#__)/'; 
        
        $tokens = preg_split($rx, $sql, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $escapeChar = $this->escapeChar;
        $nameQuoteChar = $this->nameQuoteChar;
        
        
        $qChar = false; // Current outermost quote char
        $escape = false; // Is next character escaped
        $posIdx = 0; // Idx of current positional param

        $res = ''; // Result string

        foreach($tokens as $token) {

            $wasEscape = false;    
            if ($token == $escapeChar) {
                    $escape = !$escape;
                    $wasEscape = $escape;
            } elseif ($token == "'" || $token == '"' || $token == $nameQuoteChar) {
                if (!$escape) {
                    if ($qChar == $token) $qChar = false;
                    elseif ($qChar === false) $qChar = $token;
                }
            } elseif ($token == '#__') {
                if ($qChar === false || $qChar == $nameQuoteChar) { // should replace?
                    if ($escape) // it's escaped - ignore escape character, don't replace prefix
                        $res = substr($res, 0, -1); // remove previous escape character from the output stream
                    else
                        $token = $replacePrefixWith; 
                }    
            } elseif ($hasArgs && $token == '?') {
                if ($qChar === false) {
                    if ($escape) // it's escaped - ignore escape character, don't replace prefix
                        $res = substr($res, 0, -1); // remove previous escape character from the output stream
                    else {
                        if (array_key_exists($posIdx, $quotedPosArgs)) {
                            $token = $quotedPosArgs[$posIdx];
                            $posIdx++;
                        } else {
                            throw new Ac_E_InvalidUsage("No positional argument #{$posIdx}");
                        }
                    }
                }
            } elseif ($hasArgs && $token[0] == ':' && $qChar === false && strlen($token) > 1 && preg_match('/^:\w+$/', $token)) {
                if (array_key_exists($argName = substr($token, 1), $quotedNamedArgs)) {
                    $token = $quotedNamedArgs[$argName];
                } else throw new Ac_E_InvalidUsage("No such named argument: {$argName}");
            }

            $escape = $wasEscape? $escape : false;

            $res .= $token;
        }
        return $res;
    }   
    
    function getSupportsLimitClause() {
        return true;
    }
    
    function getLimitClause($count, $offset = false, $withLimitKeyword = true) {
        if (!$this->getSupportsLimitClause())
            throw new Ac_E_InvalidCall("getLimitClause() isn't supported by ".get_class($this)."; use applyLimits() instead");
        
        if (strlen($count)) {
            $res = $count;
            if (strlen($offset)) $res = $offset.', '.$res;
        }
        if ($withLimitKeyword && strlen($res)) $res = 'LIMIT '.$res;
        return $res;
    }
    
}