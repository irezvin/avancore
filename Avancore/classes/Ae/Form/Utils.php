<?php

class Ae_Form_Utils {
    
    function getDropdownList ($listName, $value, $mapperClass, $dummyCaption = '', $extraAttribs = false, $titleColName = 'title', $idColName = false, $where = false, $ordering = false, $joins = false) {
        $mapper = & Ae_Model_Mapper::getMapper($mapperClass);
        if (!strlen($idColName)) {
            $idColName = $mapper->listPkFields();
            $idColName = $idColName[0];
        }
        $recs = $mapper->loadRecordsByCriteria($where, false, $ordering, $joins);
        $list = array();
        $list[] = mosHTML::makeOption ('', $dummyCaption, $idColName, $titleColName);
        $list = array_merge ($list, $recs);
        $res = mosHTML::selectList ($list, $listName, $extraAttribs, $idColName, $titleColName, $value );
        return $res;
    }
    
}

?>