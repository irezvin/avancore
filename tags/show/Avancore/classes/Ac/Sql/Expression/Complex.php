<?php

class Ac_Sql_Expression_Complex extends Ac_Sql_Expression {
    
    var $implode = '';
    
    function __construct($expression, $options = array()) {
        parent::__construct($expression);
        if ($options) Ac_Accessor::setObjectProperty ($this, $options);
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function getExpression($db) {
        if (is_array($this->expression)) {
            $r = array();
            foreach ($this->expression as $e) {
                if (is_string($e)) $r[] = $e;
                    else {
                        if (!$db) $db = new Ac_Sql_Db_Ae();
                        $r[] = $db->quote($e);
                    }
            }
            $res = implode($this->implode, $r);
        } else {
            $res = parent::getExpression($this->expression);
        }
        return $res;
    }
    
}