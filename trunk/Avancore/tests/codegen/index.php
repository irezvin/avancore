<?php

require_once('../testsStartup.php');
$f = new Ac_Cg_Frontend();
$f->genDeployPath = dirname(__FILE__).'/../sampleApp/gen';
$f->processWebRequest();
