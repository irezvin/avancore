<?php

class Test_CategoriesColumn extends Ac_Etl_Column {
    
    function apply(Ac_I_Param_Source $source, array & $destRecords, array & $errors = array()) {
        if (($res = parent::apply($source, $destRecords, $errors))) {
            $cc = Ac_Util::getArrayByPath($destRecords, array('items', 0, 'categories'));
            unset($destRecords['items'][0]['categories']);
            if (strlen($cc)) {
                $cats = preg_split('/\s*;\s*/', $cc);
                foreach ($cats as $i => $path) {
                    $segments = preg_split('#\s*/\s*#', $path);
                    $parent = false;
                    foreach ($segments as $j => $segment) {
                        $destRecords['itemCategories'][$i]['categoryName'] = $segment;
                        $destRecords['categories'][$i.' '.$j]['categoryName'] = $segment;
                        $destRecords['categories'][$i.' '.$j]['parentName'] = strlen($parent)? $parent : null;
                        $parent = $segment;
                    }
                }
            }
        }
        return $res;
    }
    
}