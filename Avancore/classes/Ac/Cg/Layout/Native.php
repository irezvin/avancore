<?php

class Ac_Cg_Layout_Native extends Ac_Cg_Layout {
    
    var $pathWeb = 'web';
    
    var $pathSql = 'sql';
    
    var $pathVar = 'var';
    
    function getAppType() {
        return 'native';
    }
    
    function listDetectPaths() {
        return array_merge(parent::listDetectPaths(), array('pathWeb'));
    }    
    
    protected function doGetSkelPrototype() {
        return array('class' => 'Ac_Cg_Template_Skel_Native');
    }

    protected function detectAppName($dir) {
        $cp = $this->getPathVar('pathClasses', $dir);
        $gl = glob($cp.'/*.php');
        $res = false;
        foreach ($gl as $fn) if (is_file($fn)) {
            $cnt = file_get_contents($fn);
            if (preg_match('/function\s*getAppClassFile\W/u', $cnt)) {
                $res = strtolower(basename($fn, '.php'));
                break;
            }
        }
        return $res;
    }
    
}