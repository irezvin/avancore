<?php

require(dirname(__FILE__).'/../sampleApp/deploy/app.config.php');

$dbConf = $config['legacyDatabasePrototype']['__construct'][0];

$config = array(
    'generator' => array(
        //'php5' => true,
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
        'appName' => 'Sample',
        'dbName' => $dbConf['db'],
        'caption' => 'Avancore_Sample',
        'josComId' => 'ac',
        'tablePrefix' => $dbConf['prefix'],
        'subsystemPrefixes' => array(),
        'dontLinkSubsystems' => array(
        ),
        'autoTablesAll' => true,
		'autoTablesIgnore' => array(
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
        	'tracksChanges' => true,
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
        ),
    ),
);

?>
