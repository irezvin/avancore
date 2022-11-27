<?php

/**
 * Tool that generates SQL statements with placeholders.
 * Statement consists from one or more parts and params, where each 'part' is a string with a placeholders and each 'param' is a value or Ac_Sql_Expression.
 * There are two types of placeholders: 
 * - ones that are escaped as a name: [[foo]]
 * - ones that are escaped as a value: {{value}}
 *
 * Example: 
 * 
 * $db->query(Ac_Sql_Statement::factory("UPDATE foobar WHERE [[colName]] = {{value}}", array('colName' => 'id', 'value' => 'some unescaped string'));  
 */
class Ac_Sql_Statement extends Ac_Sql_Expression implements Ac_I_Prototyped {
    
    var $parts = array();
    
    var $params = array();
    
    var $_qParams = array();
    
    var $_qp = array();
    
    var $_expr = false;
    
    /**
     * @var Ac_Sql_Db
     */
    var $_db = false;

    /**
     * @return Ac_Sql_Statement
     */
    
    function hasPublicVars() {
        return true;
    }
    
    static function create($parts, $params = array(), $extraOptions = array()) {
        if (!is_array($parts)) $parts = array('expression' => $parts);
        $options = array(
            'parts' => $parts,
            'params' => $params,
        );
        if (is_array($extraOptions)) Ac_Util::ms($options, $extraOptions);
        $res = Ac_Prototyped::factory($options, 'Ac_Sql_Statement');
        return $res;
    }
    
    function __construct($options = array()) {
        if (is_string($options)) {
            $this->parts['expression'] = $options;
            if (func_num_args() > 1) {
                $p = func_get_arg(1);
                if (is_array($p)) $this->params = $p;
            }
        } elseif (is_array($options)) {
            if (isset($options['parts'])) $this->parts = $options['parts'];
            if (isset($options['params']) && is_array($options['params'])) $this->params = $options['params'];
        }
    }
    
    function listParts() {
        return array_keys($parts);  
    }
    
    function getPart($key) {
        if (isset($this->parts[$key])) $res = $this->parts[$key];
            else $res = false;
        return $res;
    }
    
    function deletePart($key) {
        if (isset($this->parts[$key])) {
            $res = $this->parts[$key];
            unset($this->parts[$key]);
            $this->_expr = false;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function movePartAfterKey($key, $afterKey) {
        if (($part = $this->deletePart($key)) !== false) {
            $newPos = $this->getKeyPos($key);
            if ($newPos === false) $newPos = count($this->parts);
            $res = $this->addPart($part, $key, $newPos);
            $this->_expr = false;
        } else {
            $res = false; 
        }
        return $res;
    }
    
    function movePartToPos($key, $pos) {
         $oldPos = $this->getKeyPos($key);
         if ($oldPos !== false) {
            if ($oldPos === $pos) $res = true;
            else {
                $i = 0;
                $newParts = array();
                foreach (array_keys($this->parts) as $k) {
                    if ($i === $pos) $newParts[$key] = $this->parts[$key];
                    if ($key !== $k) {
                        if (is_numeric($k)) $newParts[] = $this->parts[$k];
                            else $newParts[$k] = $this->parts[$k];
                    }
                    $i++;
                }
                $this->parts = $newParts;
                $this->_expr = false;
            }
         } else {
            $res = false;
         }
         return $res;
    }
    
    function getKeyPos($key) {
        $res = array_search($key, array_keys($this->parts), true);
        return $res;
    }
    
    function addPart($part, $key = false, $atPos = false, $params = array()) {
        $res = $this->addPartByRef($part, $key, $atPos, $params);
        $this->_expr = false;
        return $res;
    }
    
    function addPartByRef($part, $key = false, $atPos = false, $params = array()) {
        if ($key === false) {
            $key = count($this->parts);
        }
        $atPos = count($this->parts);
        $newParts = array();
        $i = 0;
        if (!is_numeric($key) && array_key_exists($key, $this->parts)) unset($this->parts[$key]);
        foreach (array_keys($this->parts) as $k) {
            if ($i === $atPos) $newParts[$key] = $part;
            if (is_numeric($k)) $newParts[] = $this->parts[$k];
                else $newParts[$k] = $this->parts[$k];
        }
        $this->parts = $newParts;
        if (count($params)) $this->applyParams($params);
        $this->_expr = false;
        return $key;
    }
    
    function applyGlobalParams($params = array(), $qParams = array(), $qp = array()) {
        $this->params = array_merge($params, $this->params);
        $this->_qParams = array_merge($qParams, $this->_qParams);
        $this->_qp = array_merge($qp, $this->_qp);
        $this->_expr = false;
    }
    
    function applyParams($params = array()) {
        $this->params = array_merge($this->params, $params);
        foreach (array_keys($params) as $k) {
            unset($this->_qp[$k]);
        }
        $this->_expr = false;
    }
    
    function setParams($params = array()) {
        $this->params = $params;
        $this->_qp = array();
        $this->_qParams = array();
        $this->_expr = false;
    }
    
    function listParams() {
        return array_keys($this->params);
    }
    
    function getParam($key) {
        if (!isset($this->params[$key])) trigger_error("No such param: '$key'", E_USER_ERROR);
        return $this->params[$key];
    }
    
    function deleteParam($key) {
        if (isset($this->params[$key])) {
            $res = $this->params[$key];
            unset($this->params[$key]);
            unset($this->_qp[$key]);
            unset($this->_qParams['{{'.$key.'}}']);
            unset($this->_qParams['[['.$key.']]']);
            $this->_expr = false;
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function setDb($db) {
        if (!is_null($db) && !is_a($db, 'Ac_Sql_Db') && !is_a($db, 'Ac_Legacy_Database'))
            trigger_error('\$db must be either null, Ac_Legacy_Database or Ac_Sql_Db instance', E_USER_ERROR);
            
        if (is_a($db, 'Ac_Legacy_Database')) {
            $this->_db = new Ac_Sql_Db_Ae($db);
        }
        $this->_db = $db;
        $this->_expr = false;
    }
    
    function __sleep() {
        return array_diff(array_keys(get_object_vars($this)), array('_db'));
    }
    
    function getExpression($db = null) {
        if ($this->_expr === false) {
            if (!is_null($db)) {
                $tmp = $this->_db;
                $this->setDb($db);
            }
            if (!$this->_db) {
                $this->_db = Ac_Sql_Db::getDefaultInstance();
                //trigger_error("getExpression(): database must be supplied either with \$db parameter or with setDb() call first", E_USER_ERROR);
            }
            $this->_expr = $this->_quotePart($this->parts, $this->_getQParams());
            if (!isset($GLOBALS['sum'])) $GLOBALS['sum'] = 0; 
            if (!is_null($db)) {
                $this->_db = $tmp;
            }
        }
        return $this->_expr;
    }
    
    function nameQuote($db) {
        return $this->getExpression($db);
    }
    
    function _getQParams() {
        $n = 0;
        foreach (array_diff(array_keys($this->params), array_keys($this->_qp)) as $k) {
            $n++;
            $v = $this->params[$k];
            if (is_object($v) && $v instanceof Ac_I_Sql_Expression && method_exists($v, 'applyGlobalParams')) {
                $gParams = $this->params;
                $qParams = $this->_qParams;
                unset($gParams[$k]);
                unset($qParams[$k]);
                $v->applyGlobalParams($gParams, $qParams);
            }
            $this->_qParams['{{'.$k.'}}'] = $this->_db->quote($v);
            $this->_qParams['[['.$k.']]'] = $this->_db->nameQuote($v);
            $this->_qp[$k] = true; 
        }
        return $this->_qParams;
    }
    
    function _quotePart($part, $qParams) {
        if (is_string($part)) {
            $res = strtr($part, $qParams);
        } elseif (is_array($part)) {
            $qParts = array();
            foreach (array_keys($part) as $p) $qParts[] = $this->_quotePart($part[$p], $qParams);
            $res = implode(' ', $qParts);
        } elseif (is_object($part) && $part instanceof Ac_I_Sql_Expression) {
            if (method_exists($part, 'applyGlobalParams')) $part->applyGlobalParams($this->params, $this->_getQParams());
            $res = $part->getExpression($this->_db);
        } else {
            trigger_error("Unsupported statement part type: ".(is_object($part)? get_class($part) : gettype($part))
                ."; only a string, an array or an Ac_Sql_Expression instance are allowed", E_USER_ERROR);
        }
        return $res;
    }
    
}

