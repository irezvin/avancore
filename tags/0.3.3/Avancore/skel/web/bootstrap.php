<?php

require(dirname(__FILE__).'/deploy.settings.php');
ini_set('include_path', _DEPLOY_OWN_PATH.PATH_SEPARATOR._DEPLOY_GEN_PATH.PATH_SEPARATOR._DEPLOY_AVANCORE_PATH.PATH_SEPARATOR.'.');
ini_set('error_reporting', E_ALL);
ini_set('html_errors', 1);

require_once('Ac/Util.php');
Ac_Util::registerAutoload();
