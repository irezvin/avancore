<?php

class Sample_Shop_Spec_Mapper_Food extends Sample_Shop_Spec_Mapper {
    
    var $id = 'Sample_Shop_Spec_Mapper_Food';
    
    protected $restriction = array(
        'specsType' => 'Sample_Shop_Spec_Mapper_Food',
    );
    
    function doGetCoreMixables() {
        $res = Ac_Util::m(parent::doGetCoreMixables(), array(
            'food' => array(
                'class' => 'Sample_Shop_Spec_Food_MapperMixable',
                'colMap' => array('productId' => 'productId')
            ),
        ));
        unset($res['Ac_Model_Typer_Abstract']);
        return $res;
    }
    
}