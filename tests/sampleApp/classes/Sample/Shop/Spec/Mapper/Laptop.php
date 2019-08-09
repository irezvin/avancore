<?php

class Sample_Shop_Spec_Mapper_Laptop extends Sample_Shop_Spec_Mapper_Computer {
    
    var $id = 'Sample_Shop_Spec_Mapper_Laptop';
    
    protected $restriction = array(
        'specsType' => 'Sample_Shop_Spec_Mapper_Laptop',
    );
    
    function doGetCoreMixables() {
        $res = Ac_Util::m(parent::doGetCoreMixables(), array(
            'monitor' => array(
                'class' => 'Sample_Shop_Spec_Monitor_MapperMixable',
                'colMap' => array('productId' => 'productId')
            ),
            'laptop' => array(
                'class' => 'Sample_Shop_Spec_Laptop_MapperMixable',
                'colMap' => array('productId' => 'productId')
            ),
        ));
        unset($res['Ac_Model_Typer_Abstract']);
        return $res;
    }
    
}