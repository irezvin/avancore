<?php

require(dirname(__FILE__).'/../app.config.php');

$config = array(
    'generator' => array(
        //'php5' => true,
        'user' => $config['user'],
        'password' => $config['password'],
        'host' => $config['host'],
        'inspectorClass' => 'Cg_Dbs_Inspector_Mysql5',
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
    'domains.AeTestModel' => array(
        'strategyClass' => 'Cg_Strategy',
        'appName' => 'Ae_Test_Model',
        'dbName' => $config['db'],
        'caption' => 'Test_Avancore',
        'josComId' => 'ac',
        'tablePrefix' => 'ac_',
        'subsystemPrefixes' => array(),
        'dontLinkSubsystems' => array(
        ),
        'autoTablesAll' => true,
		'autoTablesIgnore' => array(
        ),
        'defaultTitleColumn' => 'title',
        'dictionary' => array(
            'data' => array(
            ),
        ),

        'schemaExtras' => array(
        ),

        'modelDefaults' => array(
            'generateMethodPlaceholders' => true,
            'noUi' => true,
        	'tracksChanges' => true,
        	'hasUniformPropertiesInfo' => true,
        ),
        
        'models' => array(
        	'relations' => array(
        		'properties' => array(
        			'_rel_FK_ac_relations_1' => array(
        				'otherModelIdInMethodsSingle' => 'person',
        				'otherModelIdInMethodsPlural' => 'people',
        			),
        			'_rel_FK_ac_relations_2' => array(
        				'otherModelIdInMethodsSingle' => 'otherPerson',
        				'otherModelIdInMethodsPlural' => 'otherPeople',
        			),
        		),
        	),
        	'people' => array(
        		'properties' => array(
        			'_rel_FK_ac_relations_1' => array(
        				'otherModelIdInMethodsSingle' => 'outgoingRelation',
        				'otherModelIdInMethodsPlural' => 'outgoingRelations',
        			),
        			'_rel_FK_ac_relations_2' => array(
        				'otherModelIdInMethodsSingle' => 'incomingRelation',
        				'otherModelIdInMethodsPlural' => 'incomingRelations',
        			),
        		),
        	),
        ),
    ),
);

?>