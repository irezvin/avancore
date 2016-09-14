<?php

class Sample_Shop_Spec_Mapper extends Sample_Shop_Spec_Base_Mapper {

    function doGetCoreMixables() {
        return Ac_Util::m(parent::doGetCoreMixables(), array(
            'Ac_Model_Typer_Abstract' => array(
                'class' => 'Ac_Model_Typer_Simple',
                'objectTypeField' => 'specsType',
            ),
        ));
    }
    
//  protected function doGetInfoParams() {
//        
//      $res = Ac_Util::m(parent::doGetInfoParams(), array(
//          'singleCaption' => '',
//          'pluralCaption' => '',
//      
//          'adminFeatures' => array(
//              'Ac_Admin_Feature_Default' => array(
//      
//                  'actionSettings' => array(
//                      '' => array(
//                          'id' => '',
//                          'scope' => 'any',
//                          'image' => 'stop_f2.png', 
//                          'disabledImage' => 'stop.png',
//                          'caption' => '',
//                          'description' => '',
//                          'managerProcessing' => 'procName',
//                          'listOnly' => true,
//                      ), 
//                  ),
//                  
//                  'processingSettings' => array(
//                      'procName' => array(
//                          'class' => 'Proc_Class',
//                      ),
//                  ),
//      
//                  'columnSettings' => array(
//      
//                        'col1' => array(
//                            'class' => '',
//                            'order' => -10,
//                            'title' => '',
//                        ),
//                        
//                  ),
//                  
//                    'formFieldDefaults' => array(
//                    ),
//                    
//                    'displayOrderStart' => 0,
//                    
//                    'displayOrderStep' => 10,
//                    
//                  'formSettings' => array(
//                      'controls' => array(
//                            '' => array(
//                            ),
//                      ),
//                  ),
//                  
//                  'filterPrototypes' => array(
//                  ),
//                  
//                  'orderPrototypes' => array(
//                  ),
//                  
//                  'filterFormSettings' => array(
//                      'controls' => array(
//                          'substring' => array(
//                              'class' => 'Ac_Form_Control_Text',
//                              'caption' => 'Filter',
//                              'htmlAttribs' => array( 
//                                  'onchange' => 'document.aForm.submit();',
//                                  'size' => 20,
//                              ),
//                              'description' => '',                                
//                          ),
//                      ),
//                  ),
//                    
//                    'sqlSelectSettings' => array(
//                        'tables' => array(
//                        ),
//                    ),
//                  
//              ),
//          ),
//      ));
//      return $res;
//  }    
//    
//    protected function doGetRelationPrototypes() {
//        return Ac_Util::m(parent::doGetRelationPrototypes(), array(
//            '' => array(
//                'srcMapperClass' => 'Sample_Shop_Spec_Mapper',
//                'destMapperClass' => '',
//                'fieldLinks' => array(),
//                'srcIsUnique' => false,
//                'destIsUnique' => false,
//            ),
//        ));
//    }
  
    
}
    
