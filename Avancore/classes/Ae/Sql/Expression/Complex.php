<?php

class Ae_Sql_Expression_Complex extends Ae_Sql_Expression {
    
    var $implode = '';
    
    function __construct($expression, $options = array()) {
        parent::__construct($expression);
        if ($options) Ae_Autoparams::setObjectProperty ($this, $options);
    }
    
    /**
     * @param Ae_Legacy_Database|Ae_Sql_Db $db
     */
    function getExpression($db) {
        if (is_array($this->expression)) {
            $r = array();
            foreach ($this->expression as $e) {
                if (is_string($e)) $r[] = $e;
                    else {
                        if (!$db) $db = new Ae_Sql_Db_Ae();
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