<?php

class Sample_Tree_Combo_Mapper extends Sample_Tree_Combo_Base_Mapper 
    implements Ac_I_Tree_Mapper_NestedSets {
    
    protected function doGetCoreMixables() {
        return array(
            'treeMapper' => array(
                'class' => 'Ac_Model_Tree_NestedSetsMapper',
                'nsPrototype' => array(
                ),
                'rootNodePrototype' => array(
                    'title' => 'root',
                    'tag' => '999',
                ),
            ),
        );
    }
    
    /*
    
	protected function doGetInfoParams() {
        
		$res = Ac_Util::m(parent::doGetInfoParams(), array(
        	'singleCaption' => '',
        	'pluralCaption' => '',
		
        	'adminFeatures' => array(
        		'Ac_Admin_Feature_Default' => array(
		
         			'actionSettings' => array(
			            '' => array(
			                'id' => '',
			                'scope' => 'any',
			                'image' => 'stop_f2.png', 
			                'disabledImage' => 'stop.png',
			                'caption' => '',
			                'description' => '',
			                'managerProcessing' => 'procName',
			                'listOnly' => true,
			            ), 
			        ),
			        
			        'processingSettings' => array(
			        	'procName' => array(
			        		'class' => 'Proc_Class',
			        	),
			        ),
		
        			'columnSettings' => array(
		
                        'col1' => array(
                            'class' => '',
                            'order' => -10,
                            'title' => '',
                        ),
                        
        			),
        			
                    'formFieldDefaults' => array(
                    ),
                    
                    'displayOrderStart' => 0,
                    
                    'displayOrderStep' => 10,
                    
			        'formSettings' => array(
			        	'controls' => array(
                            '' => array(
                            ),
				       	),
			        ),
			        
			        'filterPrototypes' => array(
			        ),
			        
			        'orderPrototypes' => array(
			        ),
			        
			        'filterFormSettings' => array(
			        	'controls' => array(
				        	'substring' => array(
			        			'class' => 'Ac_Form_Control_Text',
			        			'caption' => 'Filter',
			        			'htmlAttribs' => array(	
			        				'onchange' => 'document.aForm.submit();',
			        				'size' => 20,
			        			),
								'description' => '',			        			
				        	),
				        ),
			        ),
                    
                    'sqlSelectSettings' => array(
                        'tables' => array(
                        ),
                    ),
        			
        		),
        	),
		));
		return $res;
	}    
    
    protected function getRelationPrototypes() {
        return Ac_Util::m(parent::getRelationPrototypes(), array(
            '' => array(
                'srcMapperClass' => 'Sample_Tree_Combo_Mapper',
                'destMapperClass' => '',
                'fieldLinks' => array(),
                'srcIsUnique' => false,
                'destIsUnique' => false,
            ),
        ));
    }
    
    */
    
}
    
  
