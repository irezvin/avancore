<?php

class Cg_Template_PmtFinder extends Cg_Template {
    
    var $modelClass = false;
    var $mapperClass = false;
    var $finderClass = false;
    var $genFinderClass = false;
    var $parentFinderClass = 'Pmt_Finder';
    
    function _generateFilesList() {
        return array(
            'pmtFinder' => array(
                'relPath' => Cg_Util::className2fileName($this->finderClass), 
                'isEditable' => true, 
                'templatePart' => 'pmtFinder',
            ),
            
            'pmtGenFinder' => array(
                'relPath' => 'gen/'.Cg_Util::className2fileName($this->genFinderClass), 
                'isEditable' => false, 
                'templatePart' => 'pmtGenFinder',
            ),
    	);
    }
    
    function doInit() {
        $this->modelClass = $this->model->className;
        $this->mapperClass = $this->model->getMapperClass();
        $this->finderClass = $this->model->className.'_Finder';
        $this->genFinderClass = $this->model->className.'_Base_Finder';
        if (strlen($this->model->parentFinderClassName))
            $this->parentFinderClass = $this->model->parentFinderClassName;   
    }

        
    function showPmtFinder() {

    // ------------------------------------------- pmtFinder -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?> 

class <?php $this->d($this->finderClass); ?> extends <?php $this->d($this->genFinderClass); ?> {
}
    
<?php //$this->phpClose(); ?><?php
    }
    
    function showPmtGenFinder() {

    // ------------------------------------------- pmtGenFinder -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?> 

<?php if ($this->model->parentFinderClassIsAbstract) echo "abstract ";?>class <?php $this->d($this->genFinderClass); ?> extends <?php $this->d($this->parentFinderClass); ?> {

	protected $mapperClass = <?php $this->str($this->mapperClass) ?>;
	
	protected $primaryAlias = 't';
	
	protected function doOnGetSqlSelectPrototype(& $prototype) {
		parent::doOnGetSqlSelectPrototype($prototype);
		$m = Ac_Model_Mapper::getMapper($this->mapperClass);
		Ac_Util::ms($prototype, array(
			'tables' => array(
				$this->primaryAlias => array(
					'name' => $m->tableName, 
				),
			),
			'tableProviders' => array(
				'model' => array(
					'class' => 'Ac_Model_Sql_TableProvider',
					'mapperClass' => $this->mapperClass,
				),
			),
		));
	}
}
    
<?php //$this->phpClose(); ?><?php
    }
    
}

