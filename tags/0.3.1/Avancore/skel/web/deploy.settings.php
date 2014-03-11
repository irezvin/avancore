<?php

define('_DEPLOY_AVANCORE_PATH', '/home/nivzer/Work/Avancore/classes');
define('_DEPLOY_OWN_PATH', dirname(__FILE__).'/classes');
define('_DEPLOY_GEN_PATH', dirname(__FILE__).'/gen/classes');
define('_DEPLOY_CACHE_PATH', dirname(__FILE__).'/var');

define('_DEPLOY_DB_PREFIX', '{DBPREFIX}');
define('_DEPLOY_DB_HOST', 'localhost');
define('_DEPLOY_DB_USER', '{DBUSER}');
define('_DEPLOY_DB_PASSWORD', '{DBPASSWORD}');
define('_DEPLOY_DB_NAME', '{DBNAME}');

if (!defined('_DEPLOY_SITE'))
    define('_DEPLOY_SITE', 'http://'.$_SERVER['HTTP_HOST'].'/'.dirname($_SERVER['SCRIPT_NAME']));

define('_DEPLOY_AVANCORE_SITE', 'http://'.$_SERVER['HTTP_HOST']."/Avancore");