<?php

require_once('../testsStartup.php');
$f = new Cg_Frontend();
$f->genDeployPath = dirname(__FILE__).'/../sampleApp/gen';
$f->processWebRequest();
