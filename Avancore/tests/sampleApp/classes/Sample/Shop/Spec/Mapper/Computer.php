<?php

class Sample_Shop_Spec_Mapper_Computer extends Sample_Shop_Spec_Mapper {
    
    var $id = 'Sample_Shop_Spec_Mapper_Computer';
    
    protected $restriction = array(
        'specsType' => 'Sample_Shop_Spec_Mapper_Computer',
    );
    
    function doGetCoreMixables() {
        $res = Ac_Util::m(parent::doGetCoreMixables(), array(
            'computer' => array(
                'class' => 'Sample_Shop_Spec_Computer_MapperMixable',
                'colMap' => array('productId' => 'productId')
            ),
        ));
        unset($res['Ac_Model_Typer_Abstract']);
        return $res;
    }
    
}