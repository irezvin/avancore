<?php

class Ac_Cg_Template_Domain extends Ac_Cg_Template {
    
    var $domainClass = false;
    var $domainGenClass = false;
    var $domainBaseClass = false;
    var $mappers = array();
    var $mappersOverride = array();
    var $mapperPrototypes = array();
    var $modelClasses = array();
    var $modelMethodSuffixes = array();
    var $adminMenu = array();
    var $mapperAliases = array();
    
    function doInit() {
        
        $this->domainClass = $this->domain->getAppClass();
        $this->domainGenClass = $this->domain->appName.'_DomainBase';
        $this->domainBaseClass = $this->domain->getParentAppClass();
        $this->mappers = array();
        $this->mapperAliases = $this->domain->getMapperAliases();
        
        foreach ($this->domain->listModels() as $m) {
            $mod = $this->domain->getModel($m);
            $mapperClass = $mod->getMapperClass();
            $mapperMethodSuffix = str_replace ("_", "", $mapperClass);
            $this->mapperPrototypes[$mod->getMapperClass()] = array('class' => $mod->getMapperClass());
            $this->mappers[$mapperMethodSuffix] = $mod->getMapperClass();
            if ($pm = $mod->getParentModel()) {
                $parentMapperMethodSuffix = str_replace("_", "", $pm->getMapperClass());
                $this->mappersOverride[$parentMapperMethodSuffix] = array(
                    'method' => 'get'.$mapperMethodSuffix, 
                    'class' => $mod->getMapperClass()
                );
            }
            $this->modelClasses[$mod->getMapperClass()] = $mod->className;  
            $this->modelMethodSuffixes[$mod->getMapperClass()] = str_replace("_", "", $mod->className);
        }
    }
    
    function _generateFilesList() {
        $domDat = trim(str_replace('\\', '_', $this->domainClass), '_').'.json';
        $res = array();
        $res['domainFile'] = array(
            'relPath' => $this->classToFile($this->domainClass),
            'isEditable' => true,
            'templatePart' => 'domainFile',
        );
        $res['domainGenFile'] = array(
            'relPath' => $this->classToFile($this->domainGenClass, true),
            'isEditable' => false,
            'templatePart' => 'domainGenFile',
        );
        $res['domainDumpFile'] = array(
            'relPath' => $this->generator->genDir.'/data/'.$domDat,
            'isEditable' => false,
            'templatePart' => 'domainDump',
        );
        $res['domainDumpHtaccess'] = array(
            'relPath' => $this->generator->genDir.'/data/.htaccess',
            'isEditable' => false,
            'templatePart' => 'denyHtaccess',
        );
        return $res;
    }
    
    // --------------------------- domainFile -------------------------
    
    function showDomainFile() {
?><?php $this->phpOpen(); ?> 

class <?php $this->d($this->domainClass); ?> extends <?php $this->d($this->domainGenClass); ?> {
    
    function getAppClassFile() {
        return __FILE__;
    }

    /**
     * @return <?php echo $this->domainClass; ?> 
     */
    static function getInstance($id = null) {
        return Ac_Application::getApplicationInstance(<?php $this->export($this->domainClass); ?>, $id);
    }

}
<?php //$this->phpClose(); ?>
<?php } 

    // --------------------------- domainGenFile -----------------------

    function showDomainGenFile() {
?><?php $this->phpOpen(); ?> 

abstract class <?php $this->d($this->domainGenClass); ?> extends <?php $this->d($this->domainBaseClass); ?> {

    protected function doOnInitialize() {
        parent::doOnInitialize();
<?php   if ($this->mapperAliases) { ?> 
        $this->setMapperAliases(<?php echo $this->export($this->mapperAliases, 8); ?>, true);
<?php   } ?>
    }

<?php if (count($this->mappers)) { ?>

    protected function doGetMapperPrototypes() {
        return <?php $this->exportArray($this->mapperPrototypes, 8); ?>;
    }
<?php   foreach ($this->mappers as $method => $class) { ?>
    
    /**
     * @return <?php echo $class ?> 
     */
    function get<?php echo $method; ?>() {
        return $this->getMapper(<?php $this->export($class); ?>);
    }
<?php   } ?>
<?php   foreach ($this->mappersOverride as $method => $details) { ?>
    
    /**
     * @return <?php echo $details['class'] ?> 
     */
    function get<?php echo $method; ?>() {
        return $this-><?php echo $details['method']; ?>();
    }
<?php   } ?>
    
<?php } ?>
<?php if (count($this->modelClasses)) { ?>
<?php foreach ($this->modelClasses as $mapperClass => $modelClass) { ?> 
    /**
     * @return <?php $this->d($modelClass); ?> 
     */
    static function <?php $this->d($modelClass); ?> ($object = null) {
        return $object;
    }
    
    /**
     * @return <?php $this->d($modelClass); ?> 
     */
    function create<?php $this->d($this->modelMethodSuffixes[$mapperClass]); ?> () {
        return $this->getMapper(<?php $this->str($mapperClass); ?>)->createRecord();
    }
    
<?php } ?>

<?php } ?>
}
<?php } 

    function showDomainDump() {
        $k = 0;
        if (defined('JSON_PRETTY_PRINT')) $k |= JSON_PRETTY_PRINT;
        if (defined('JSON_UNESCAPED_UNICODE')) $k |= JSON_UNESCAPED_UNICODE;
        echo json_encode($this->domain->serializeToArray(), $k);
    }

}
