<?php

/**
 * Generates INSERT...ON DUPLICATE KEY UPDATE statements
 */
class Ac_Sql_Updater extends Ac_Prototyped implements Ac_I_Sql_Expression {
    
    // source - Ac_Sql_Statement or part of Sql statement starting with FROM... keyword
    var $src = null;
    
    // dest table name
    var $tableName = null;
    
    // src table alias in select expression
    var $tableAlias = null;
    
    // target column => src column (string) or expression (Ac_Sql_Expression) or FALSE (don't update)
    var $colMap = array();
    
    // target column => expression (string) or FALSE (don't update) - what to add ON DUPLICATE KEY UPDATE; if not provided, defaults to VALUES(colName)
    var $updateMap = array();
    
    // special value TRUE means callback workflow will be applied (inserts will be created using selected rows),
    // but no actual callback method will be called
    
    var $batchCallback = false;
    
    /**
     * @var bool
     * If TRUE, $batchCallback will be called with argument of ALL rows to-be-insert
     * (not once per row)
     */
    var $batchCallbackForMany = false;
    
    var $batchCallbackOnMappedColumns = false;
    
    var $batchSize = 2000;
    
    function hasPublicVars() {
        return true;
    }
    
    function processBatch($insClause, $select, $strUpd, Ac_Sql_Db $db) {
        if ($this->batchCallback !== true && !is_callable($this->batchCallback)) 
            throw Ac_E_InvalidCall::wrongType('Ac_Sql_Updater->$batchCallback', $this->batchCallback, "callable");
        if (is_array($select)) $rows = $select;
        else $rows = $db->fetchArray($select);
        $s = $this->batchSize > 0? $this->batchSize : count($rows);
        $res = array();
        while ($slice = array_splice($rows, 0, $s)) {
            $write = array();
            if ($this->batchCallbackForMany) {
                $write = call_user_func($this->batchCallback, $slice);
            } else {
                foreach ($slice as $row) {
                    $newRow = call_user_func($this->batchCallback, $row);
                    if (isset($newRow[0]) && is_array($newRow[0])) {
                        foreach ($newRow as $item) if ($item) $write[] = $item;
                    } else {
                        if (!$newRow) continue;
                        $write[] = $newRow;
                    }
                }
            }
            if (!$write) continue;
            $write = $db->unifyInsertData($write);
            $insertStatement = $insClause."\nVALUES";
            foreach ($write as $writeRow) {
                $insertStatement .= "\n    (".$db->q($writeRow)."),";
            }
            $res[] = rtrim($insertStatement, ",").$strUpd;
        }
        return $res;
    }
    
    /**
     * @param Ac_Sql_Db $db
     */
    function getExpression($db, $asArray = false) {
        $insWhat = array();
        $selWhat = array();
        $upd = array();
        $alias = $this->tableAlias;
        if ($alias === null && is_object($this->src) && $this->src instanceof Ac_Sql_Select) {
            $alias = $this->src->getEffectivePrimaryAlias();
        }
        $fullUpdateMap = array();
        foreach ($this->colMap as $k => $v) {
            if (is_numeric($k)) {
                if (is_object($v) && $v instanceof Ac_I_Sql_Expression) throw new Ac_E_InvalidUsage("\$colMap: field name must not be an expression");
                $k = $v;
            }
            $fullUpdateMap[$k] = "VALUES(".$db->n($k).")";
            $insWhat[] = $db->nameQuote($k);
            $sel = $v;
            if (!(is_object($sel) && $sel instanceof Ac_I_Sql_Expression)) {
                $sel = $alias? $db->nameQuote (array($alias, $sel)) : $db->nameQuote($sel);
            }
            $selWhat[] = $db->quote(new Ac_Sql_Expression($sel)) . " AS ".$db->nameQuote($k);;
        }
        if ($this->updateMap === null) $upd = array();
        else {
            foreach ($this->updateMap as $k => $v) {
                $fullUpdateMap[$k] = $v;
            }
            foreach ($fullUpdateMap as $k => $v) {
                if ($v === false) continue;
                $upd[] = $db->nameQuote($k).' = '.$db->quote(new Ac_Sql_Expression($v));
            }
        }
        $strInsWhat = "\n    ".implode(",\n    ", $insWhat)."\n";
        $strSelWhat = implode(",\n    ", $selWhat);
        $strUpd = implode(",\n    ", $upd);
        if (is_object($this->src) && $this->src instanceof Ac_Sql_Select) {
            $src = clone $this->src;
            $src->setDb($db);
            if ($this->batchCallback && !$this->batchCallbackOnMappedColumns) {
                $src = ''.$src;
            } else {
                $src = $src->getStatementTail(true);
            }
        } elseif (is_array($src)) {
            if (!$this->batchCallback) {
                $this->batchCallback = true;
            }
        } else {
            $src = $this->src;
            
        }
        if ($this->batchCallback && !$this->batchCallbackOnMappedColumns || is_array($src)) {
            $select = $src;
        } else {
            $select = "\nSELECT\n    {$strSelWhat}\n{$src}";
        }
        
        if ($strUpd) $strUpd = "\nON DUPLICATE KEY UPDATE\n    {$strUpd}\n";
        
        $insClause = "INSERT INTO ".$db->n($this->tableName)." ({$strInsWhat})";
        
        if ($this->batchCallback) $res = $this->processBatch($insClause, $select, $strUpd, $db);
        else $res = $insClause.$select.$strUpd;
        
        if (!$asArray && is_array($res)) $res = implode("\n;\n", $res);
        else if ($asArray && !is_array($res)) $res = Ac_Util::toArray ($res);
        return $res;
    }

    function nameQuote($db) {
        throw new Ac_E_InvalidUsage("nameQuote isn't supported by ".__CLASS__);
    }
    
    function execute(Ac_Sql_Db $db = null) {
        if (is_null($db)) {
            if (is_object($this->src) && ($this->src instanceof Ac_Sql_Select)) {
                $db = $this->src->getDb();
            }
            if (!$db) $db = Ac_Application::getDefaultInstance()->getDb();
        }
        if (!$db) throw new Ac_E_InvalidUsage("Cannot obtain Ac_Sql_Db instance during execute(); please provide \$db argument");
        foreach ($this->getExpression($db, true) as $stmt) $db->query($stmt);
    }

}