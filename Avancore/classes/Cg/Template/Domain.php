<?php

Ae_Dispatcher::loadClass('Cg_Template');

class Cg_Template_Domain extends Cg_Template {
    
    var $integralPagemapFile = false;
    var $joomlaAdminScriptFile = false;
    var $domainClass = false;
    var $domainGenClass = false;
    var $pagemaps = array();
    var $mappers = array();
    var $modelClasses = array();
    var $optName = false;
    var $adminMenu = array();
    
    function doInit() {
        $this->integralPagemapFile = 'pagemap/'.$this->domain->josComId.'.models.config.php';
        $this->joomlaAdminScriptFile = 'install.'.$this->domain->josComId.'.php';
        $this->domainClass = $this->domain->appName;
        $this->domainGenClass = $this->domain->appName.'_DomainBase';
        $this->mappers = array();
        foreach ($this->domain->listModels() as $m) {
            $mod = & $this->domain->getModel($m);
            $modName = $mod->getModelBaseName();
            if (!$mod->noUi) {
                $this->adminMenu[$modName.'_List'] = $mod->pluralCaption;
                $pmFile = $this->strategy->getPagemapFileName($mod);
                $pmFile = str_replace('pagemap/', '', $pmFile);
                $this->pagemaps[] = $pmFile;
            }
            $mapperClass = $mod->getMapperClass();
            $mapperMethodSuffix = str_replace ("_", "", $mapperClass);
            //$mapperMethodSuffix{0} = strtolower($mapperMethodSuffix{0});
            $this->mappers[$mod->getMapperClass()] = $mapperMethodSuffix;
            $this->modelClasses[] = $mod->className;  
        }
        $this->optName = 'com_'.$this->domain->josComId;
    }
    
    function _generateFilesList() {
        $res = array();
        if ($this->pagemaps) {
            $res['integralPagemapFile'] = array(
                    'relPath' => $this->integralPagemapFile, 
                    'isEditable' => false, 
                    'templatePart' => 'integralPagemapFile',
            );
            $res['joomlaAdminScriptFile'] = array(
                    'relPath' => $this->joomlaAdminScriptFile, 
                    'isEditable' => false, 
                    'templatePart' => 'joomlaAdminScriptFile',
            );
        }
        $res['domainFile'] = array(
            'relPath' => Cg_Util::className2fileName($this->domainClass),
            'isEditable' => true,
            'templatePart' => 'domainFile',
        );
        $res['domainGenFile'] = array(
            'relPath' => 'gen/'.Cg_Util::className2fileName($this->domainGenClass),
            'isEditable' => false,
            'templatePart' => 'domainGenFile',
        );
        return $res;
    }
    
    // --------------------------- integralPagemapFile -------------------------
    
    function showIntegralPagemapFile() {
?><?php $this->phpOpen(); ?>

<?php foreach($this->pagemaps as $pm) { ?>
    require(dirname(__FILE__).<?php $this->str('/'.$pm); ?>);
<?php } ?>
<?php $this->phpClose(); ?><?php        
    }
    
    // --------------------------- joomlaAdminScriptFile -------------------------
    
    function showJoomlaAdminScriptFile() {
?><?php $this->phpOpen(); ?>

        global $database;

        $database->setQuery('DELETE FROM #__components WHERE `option` = <?php $this->str2($this->optName); ?>'); 
        $database->query();
        
        $database->setQuery(
             ' INSERT INTO #__components ' 
            .' (`name`, `link`, `menuid`, `parent`, `admin_menu_link`, `admin_menu_alt`, `option`, `ordering`, `admin_menu_img`, `iscore`, `params`) '
            .' VALUES ( '
            .'  <?php $this->str2($this->domain->caption); ?>, \'\', 0, 0 '   
            .', <?php $this->str2("option={$this->optName}"); ?>, <?php $this->str2($this->domain->caption); ?> '
            .', <?php $this->str2($this->optName); ?>, 0, \'js/ThemeOffice/component.png\', 0, \'\' '
            .' ) '
        ); 
        $database->query();
        $id = $database->insertid();
        
        $database->setQuery
        (
             ' INSERT INTO #__components '
            .' (`name`, `link`, `menuid`, `parent`, `admin_menu_link`, `admin_menu_alt`, `option`, `ordering`, `admin_menu_img`, `iscore`, `params`) ' 
            .' VALUES ' 
        <?php $n = count($this->adminMenu); $i = 0; foreach ($this->adminMenu as $task => $caption) { ?>
        
            .' ( <?php $this->str2($caption); ?>, \'\', 0, '.$id   
            .', <?php $this->str2("option={$this->optName}&task={$task}"); ?>, <?php $this->str2($caption); ?> '
            .', <?php $this->str2($this->optName); ?>, 0, \'js/ThemeOffice/component.png\', 0, \'\' )<?php if (++$i < $n) { ?>, <?php } ?> '
            
<?php } ?>
        );
        
        $database->query();

<?php $this->phpClose(); ?><?php        
    }

    // --------------------------- domainFile -------------------------
    
    function showDomainFile() {
?><?php $this->phpOpen(); ?>

Ae_Dispatcher::loadClass(<?php $this->str($this->domainGenClass); ?>);

class <?php $this->d($this->domainClass); ?> extends <?php $this->d($this->domainGenClass); ?> {
    
}
<?php $this->phpClose(); ?>
<?php } 

    // --------------------------- domainGenFile -----------------------

    function showDomainGenFile() {
?><?php $this->phpOpen(); ?>

class <?php $this->d($this->domainGenClass); ?> {
<?php if (count($this->mappers)) { ?>
<?php foreach ($this->mappers as $mapperClass => $mapperMethodSuffix) { ?> 
    /**
     * @return <?php $this->d($mapperClass); ?> 
<?php if (!$this->generator->php5) { ?>
     * @static
<?php } ?>
     */
    <?php if ($this->generator->php5) echo "static "; ?>function & get<?php $this->d($mapperMethodSuffix); ?> () {
        $res = & Ae_Dispatcher::getMapper(<?php $this->str($mapperClass) ?>);
        return $res;
    }
<?php } ?>

<?php } ?>
<?php if (count($this->modelClasses)) { ?>
<?php foreach ($this->modelClasses as $modelClass) { ?> 
    /**
     * @return <?php $this->d($modelClass); ?> 
<?php if (!$this->generator->php5) { ?>
     * @static
<?php } ?>
     */
    <?php if ($this->generator->php5) echo "static "; ?>function & <?php $this->d($modelClass); ?> (& $object) {
        return $object;
    }
<?php } ?>

<?php } ?>
}
<?php $this->phpClose(); ?>
<?php } 

}

?>