#!/usr/bin/php
<?php

$a = '
Ac_Admin
Ac_Finder
Ac_Form
Ac_I_Tree
Ac_Image
Ac_Joomla
Ac_Legacy
Ac_Model
Ac_Table
Ac_Template
Ac_Upload
Ac_Finder
Ac_Form
Ac_Mail
Ac_Table
Ac_Template
Cg_
';

$x = preg_split("#[\r\n]+#", trim($a));
foreach ($x as $str) {
    $str = str_replace("_", "/", $str);
    $cmd = "s#\\bclasses/{$str}#obsolete/{$str}#g";
    echo $cmd."\n";
}

