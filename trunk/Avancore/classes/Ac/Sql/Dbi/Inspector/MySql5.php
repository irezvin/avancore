<?php

/**
 * Retrieves column comments and information about relations from INFORMATION_SCHEMA database (available from MySQL v. 5.0)
 */
class Ac_Sql_Dbi_Inspector_MySql5 extends Ac_Sql_Dbi_Inspector {
    
    function getColumnsForTable($tableName, $databaseName = false) {
        $cols = parent::getColumnsForTable($tableName, $databaseName);
        $q = 
            'SELECT `COLUMN_NAME`, `COLUMN_COMMENT`, `COLUMN_DEFAULT` FROM `INFORMATION_SCHEMA`.`COLUMNS` '
                .' WHERE `TABLE_SCHEMA` = '.$this->_db->Quote($this->_getDbName($databaseName))
                .' AND `TABLE_NAME` = '.$this->_db->Quote($tableName)
        ;
        foreach($al = $this->_db->fetchArray($q, 'COLUMN_NAME') as $colName => $colData) {
            if (isset($cols[$colName])) {
                if ($colData['COLUMN_COMMENT']) $cols[$colName]['comment'] = $colData['COLUMN_COMMENT'];
                $cols[$colName]['default'] = $colData['COLUMN_DEFAULT'];
            }
        }
        //var_dump($tableName, $cols);
        return $cols;
    }
    
    /**
     * @return array(relationName=>array('table'=>externalTableName,'columns'=>array('thisField1' => 'thatField1', 'thisField2' => 'thatField2'...)))
     */
    function getRelationsForTable($tableName, $databaseName = false) {
        $qdbn = $this->_db->q($this->_getDbName($databaseName));
        $res = array();
        $q = 
             'SELECT constraint_name AS con, column_name AS col, referenced_table_name AS tbl, referenced_column_name AS refcol'
            .' FROM information_schema.key_column_usage'
            .' WHERE table_schema = '.$qdbn.' AND referenced_table_schema = '.$qdbn
            .'      AND table_name='.$this->_db->q($tableName)
            .'      AND NOT ISNULL(referenced_table_name) '
            .' ORDER BY constraint_name, ordinal_position' 
        ;
        foreach ($this->_db->fetchArray($q) as $relData) {
            $cName = $relData['con'];
            if (!isset($res[$cName])) $res[$cName] = array(
                'table' => $relData['tbl'],
                'columns' => array()
            );
            $res[$cName]['columns'][$relData['col']] = $relData['refcol'];
        }
        return $res;
    }

}

?>