<?php

class Ac_Cg_Template_Domain extends Ac_Cg_Template {
    
    var $domainClass = false;
    var $domainGenClass = false;
    var $domainBaseClass = false;
    var $mappers = array();
    var $mapperPrototypes = array();
    var $modelClasses = array();
    var $modelMethodSuffixes = array();
    var $optName = false;
    var $adminMenu = array();
    
    function doInit() {
        
        $this->domainClass = $this->domain->getAppClass();
        $this->domainGenClass = $this->domain->appName.'_DomainBase';
        $this->domainBaseClass = $this->domain->appBaseClass;
        $this->mappers = array();
        foreach ($this->domain->listModels() as $m) {
            $mod = $this->domain->getModel($m);
            $modName = $mod->getModelBaseName();
            if (!$mod->noUi) {
                $this->adminMenu[$modName.'_List'] = $mod->pluralCaption;
            }
            $mapperClass = $mod->getMapperClass();
            $mapperMethodSuffix = str_replace ("_", "", $mapperClass);
            //$mapperMethodSuffix{0} = strtolower($mapperMethodSuffix{0});
            $this->mapperPrototypes[$mod->getMapperClass()] = array('class' => $mod->getMapperClass());
            $this->mappers[$mapperMethodSuffix] = $mod->getMapperClass();
            $this->modelClasses[$mod->getMapperClass()] = $mod->className;  
            $this->modelMethodSuffixes[$mod->getMapperClass()] = str_replace("_", "", $mod->className);
        }
        $this->optName = 'com_'.$this->domain->josComId;
    }
    
    function _generateFilesList() {
        $res = array();
        $res['domainFile'] = array(
            'relPath' => Ac_Cg_Util::className2fileName($this->domainClass),
            'isEditable' => true,
            'templatePart' => 'domainFile',
        );
        $res['domainGenFile'] = array(
            'relPath' => 'gen/'.Ac_Cg_Util::className2fileName($this->domainGenClass),
            'isEditable' => false,
            'templatePart' => 'domainGenFile',
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
    
<?php } ?>
<?php if (count($this->modelClasses)) { ?>
<?php foreach ($this->modelClasses as $mapperClass => $modelClass) { ?> 
    /**
     * @return <?php $this->d($modelClass); ?> 
<?php if (!$this->generator->php5) { ?>
     * @static
<?php } ?>
     */
    <?php if ($this->generator->php5) echo "static "; ?>function <?php $this->d($modelClass); ?> (<?php if (!$this->generator->php5) echo "& "; ?>$object = null) {
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

}
