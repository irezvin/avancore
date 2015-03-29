<?php

require_once(dirname(__FILE__).'/deploy.settings.php');

if (class_exists('Ac_Url')) {
    $u = Ac_Url::guess();
    $u->query = array();
    if (substr($u->path, -1) !== '/') $u->path = dirname($u->path).'/';
} else {
    $u = false;
}

$config = array(
    'host' => _DEPLOY_DB_HOST,
    'db' => _DEPLOY_DB_NAME,
    'user' => _DEPLOY_DB_USER,
    'cachePath' => _DEPLOY_CACHE_PATH,
    'absolutePath' => dirname(__FILE__),
    'liveSite' => $u,
    'frontendUrl' => $u,
    'password' => _DEPLOY_DB_PASSWORD,
    'charset' => 'utf8',
    'listLimit' => 50,
    'cacheLifeTime' => 600,
    'prefix' => _DEPLOY_DB_PREFIX,
    'assetPlaceholders' => array(
        '{ACCSS}' => _DEPLOY_AVANCORE_SITE.'/',
        '{AC}' => _DEPLOY_AVANCORE_SITE."/js/",
        '{PAX}' => _DEPLOY_SITE.'/assets/',
    ),
    'sendEmails' => defined('_DEPLOY_SEND_EMAILS')? _DEPLOY_SEND_EMAILS : false,
);

