<?php

require(__DIR__.'/app.config.php');

$dbConf = $config['legacyDatabasePrototype']['__construct'][0];

$config = array(
    'generator' => array(
        'user' => $dbConf['user'],
        'password' => $dbConf['password'],
        'host' => isset($dbConf['host'])? $dbConf['host'] : 'localhost',
//        'inspectorClass' => 'Ac_Sql_Dbi_Dbs_Inspector_Mysql5',
        'clearOutputDir' => false,
        'overwriteLog' => true,
        'domainDefaults' => array(
            'defaultTitlePropName' => 'title',
            'defaultPublishedPropName' => 'published',
            'defaultOrderingPropName' => 'ordering',
        ),
        'otherDbOptions' => array(
            'charset' => 'utf8',
        ),
    ),
    'domains.Sample' => array(
        'strategyClass' => 'Ac_Cg_Strategy',
        'useLangStrings' => true,
        'appName' => 'Sample',
        'dbName' => $dbConf['db'],
        'caption' => 'Avancore_Sample',
        'josComId' => 'ac',
        'tablePrefix' => $dbConf['prefix'],
        'subsystemPrefixes' => array(),
        'dontLinkSubsystems' => array(
        ),
        'ignoredColumnsInJunctionTables' => array(
            '#__shop_product_related' => array('ignore'),
        ),
        'autoTablesAll' => '/^#__/',
		'autoTablesIgnore' => array(
            '#__tree_nested_sets',
            '#__shop_product_upc',
            '#__shop_meta',            
        ),
        'defaultTitleColumn' => 'title',
        'dictionary' => array(
            'singularForms' => array(
                'people' => 'person'
            ),
            'pluralForms' => array(
                'person' => 'people'
            ),
        ),

        'schemaExtras' => array(
        ),

        'modelDefaults' => array(
            'generateMethodPlaceholders' => true,
            'noUi' => true,
        	'hasUniformPropertiesInfo' => true,
        ),
        'models' => array(
        	'relations' => array(
        		'properties' => array(
        			'_rel_FK_ac_relations_outgoing' => array(
        				'otherModelIdInMethodsSingle' => 'person',
        				'otherModelIdInMethodsPlural' => 'people',
        			),
        			'_rel_FK_ac_relations_incoming' => array(
        				'otherModelIdInMethodsSingle' => 'otherPerson',
        				'otherModelIdInMethodsPlural' => 'otherPeople',
        			),
        		),
        	),
        	'people' => array(
                'single' => 'person',
        		'properties' => array(
        			'_rel_FK_ac_relations_outgoing' => array(
        				'otherModelIdInMethodsSingle' => 'outgoingRelation',
        				'otherModelIdInMethodsPlural' => 'outgoingRelations',
        			),
        			'_rel_FK_ac_relations_incoming' => array(
        				'otherModelIdInMethodsSingle' => 'incomingRelation',
        				'otherModelIdInMethodsPlural' => 'incomingRelations',
        			),
        		),
        	),
            'publish' => array(
                'class' => 'Ac_Cg_Model_Part',
                'masterFkIds' => array(
                    'fkPersonPublish',
                    'fkCategoryPublish',
                    'fkPostPublish',
                ),
                'skipMapperMixables' => array(
                    //'fkPostPublish',
                ),
                'objectTypeField' => 'sharedObjectType',
                'perModelMapperMixableExtras' => array(
                    'shopProducts' => array(
                        'fieldNames' => array(
                            'sharedObjectType' => false,
                        ),
                    ),
                ),
            ),
            'shopProductExtraCodes' => array(
                'class' => 'Ac_Cg_Model_Part',
                'masterFkIds' => array(
                    'fkExtraCodeProduct',
                ),
            ),
            'shopProductNotes' => array(
                'class' => 'Ac_Cg_Model_Part',
                'inline' => true,
                'masterFkIds' => array(
                    'fkProductNoteProduct',
                ),
            ),
            'shopSpecFood' => array(
                'class' => 'Ac_Cg_Model_Part',
                'skipMapperMixables' => true,
                /*'inline' => true,
                'masterFkIds' => array(
                    'fkSpecsFood',
                ),*/
            ),
            'shopSpecComputer' => array(
                'class' => 'Ac_Cg_Model_Part',
                'skipMapperMixables' => true,
                'masterFkIds' => array('fkMonitorSpec'),
            ),
            'shopSpecMonitor' => array(
                'class' => 'Ac_Cg_Model_Part',
                'skipMapperMixables' => true,
                'masterFkIds' => array('fkMonitorSpec'),
            ),
            'shopSpecLaptop' => array(
                'class' => 'Ac_Cg_Model_Part',
                'skipMapperMixables' => true,
                'masterFkIds' => array('fkMonitorSpec'),
            ),
        ),
    ),
    
    'domains.Child' => array(
        'parentDomainName' => 'Sample',
        
        'appName' => 'Child',
        'caption' => 'Avancore_Child',
        'josComId' => 'child',
    ),
);

