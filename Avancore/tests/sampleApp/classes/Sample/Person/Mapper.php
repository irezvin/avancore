<?php

class Sample_Person_Mapper extends Sample_Person_Base_Mapper {

    protected function doGetUniqueIndexData() {
        $res = Ac_Util::m(parent::doGetUniqueIndexData(), array('idxName' => array('name')));
        return $res;
    }
    
    function getTitleFieldName() {
        return 'name';
    }
    
    function getDefaultSort() {
        return 'birthDate';
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
                'srcMapperClass' => 'Sample_Person_Mapper',
                'destMapperClass' => '',
                'fieldLinks' => array(),
                'srcIsUnique' => false,
                'destIsUnique' => false,
            ),
        ));
    }
    
    */
    
    protected function doGetSqlSelectPrototype($primaryAlias = 't') {
        $res = Ac_Util::m(parent::doGetSqlSelectPrototype($primaryAlias), array(
            'parts' => array(
                'notTest' => array(
                    'class' => 'Ac_Sql_Filter_Custom',
                    'where' => "lcase(name) not like '%test%'",
                ),
                'birthYear' => array(
                    'class' => 'Ac_Sql_Filter_Equals',
                    'colName' => "DATE_FORMAT(t.birthDate, '%Y')",
//                    'php' => function($object, $crit) {
//                        return Ac_Util::date($object->birthDate, 'Y') == $crit->values[$value];
//                    }
                )
            ),
        ));
        return $res;
    }
    
}
