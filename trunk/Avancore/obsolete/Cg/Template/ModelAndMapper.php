<?php

class Cg_Template_ModelAndMapper extends Cg_Template {
    
    var $modelClass = false;
    var $genModelClass = false;
    var $parentClass = false;

    var $mapperClass = false;
    var $genMapperClass = false;
    var $parentMapperClass = false;
    
    var $mapperVars = array();
    
    var $vars = array();
    var $ownPropInfo = false;
    var $ownProperties = false;
    var $ownLists = false;
    var $ownAssociations = false;
    var $tableName = false;
    var $pkStr = false; // "null" / "'id'" / "array('foo', 'bar')" - PK param for Ac_Model_Object as it will be inserted into PHP code
    var $autoincFieldName = false;
    var $assocProperties = array();
    var $relationPrototypes = false;
    var $uniqueIndexData = false;
    
    function _generateFilesList() {
        return array(
            'modelObject' => array(
                'relPath' => Cg_Util::className2fileName($this->modelClass), 
                'isEditable' => true, 
                'templatePart' => 'modelObject',
            ),
            'genModelObject' => array(
                'relPath' => 'gen/'.Cg_Util::className2fileName($this->genModelClass), 
                'isEditable' => false, 
                'templatePart' => 'modelGenObject',
            ),
            'mapper' => array(
                'relPath' => Cg_Util::className2fileName($this->mapperClass), 
                'isEditable' => true, 
                'templatePart' => 'mapper',
            ),
            'genMapper' => array(
                'relPath' => 'gen/'.Cg_Util::className2fileName($this->genMapperClass), 
                'isEditable' => false, 
                'templatePart' => 'genMapper',
            ), 
        );
    }
    
    function doInit() {
        $this->modelClass = $this->model->className;
        $this->genModelClass = $this->model->className.'_Base_Object';
        $this->parentClass = $this->model->parentClassName;
        
        $this->mapperClass = $this->model->getMapperClass();
        $this->genMapperClass = $this->model->getGenMapperClass();
        $this->parentMapperClass = $this->model->parentMapperClassName;
        
        foreach ($this->model->listProperties() as $name) {
            $prop = $this->model->getProperty($name);
            if (!$prop->isEnabled()) continue;
            //foreach ($gacm = $prop->getAllClassMembers() as $cm) if (!$cm) var_dump($prop->name, $gacm);
            $this->vars = array_merge($this->vars, $prop->getAllClassMembers());
        }
        
        $pps = $this->model->getAeDataPropLists();
        $this->ownProperties = $pps['ownProperties'];
        $this->ownAssociations = $pps['ownAssociations'];
        $this->ownLists = $pps['ownLists'];
        $this->ownPropInfo = $pps['ownPropertiesInfo'];  
        $this->tableName = $this->model->tableObject->name;
        
        foreach ($this->model->tableObject->listColumns() as $cn) {
            $col = $this->model->tableObject->getColumn($cn);
            if ($col->autoInc) {
                $this->autoincFieldName = $cn;
                break;
            }
        }
        
        $pk = $this->model->tableObject->listPkFields();
        if (!count($pk)) $this->pkStr = 'null';
        elseif (count($pk) == 1) {
            $this->pkStr = $this->str($pk[0], true);
        } else {
            $this->pkStr = $this->exportArray($pk, 0, false, true, true);
        }
        
        $this->mapperVars['pk'] = new Cg_Php_Expression($this->pkStr);
        $this->mapperVars['recordClass'] = $this->modelClass;
        $this->mapperVars['tableName'] = $this->tableName;
        $this->mapperVars['id'] = $this->mapperClass;
        $this->mapperVars['columnNames'] = new Cg_Php_Expression($this->exportArray($this->model->tableObject->listColumns(), 0, false, true, true));
        foreach ($this->model->tableObject->listColumns() as $nm) {
            $col = $this->model->tableObject->getColumn($nm);
            $this->mapperVars['defaults'][$nm] = $col->default;
        }
        $this->mapperVars['defaults'] = new Cg_Php_Expression($this->exportArray($this->mapperVars['defaults'], 8, true, false, true));
        
        $this->relationPrototypes = array();
        
        foreach ($this->model->listAeModelRelations() as $r) {
            $prot = $this->model->getAeModelRelationPrototype($r);
            $key = isset($prot['srcVarName'])? $prot['srcVarName'] : count($this->relationPrototypes);
            if ($prop = $this->model->searchPropertyByRelation($r)) {
                $this->assocProperties[$prop->getClassMemberName()] = $prop;
            } else {
                var_dump("Prop by relation not found:", $r);
            }
            $this->relationPrototypes[$key] = $prot;
        }
        
        $this->uniqueIndexData = $this->model->tableObject->getUniqueIndexData();
        
    }

    /**
     * @param Cg_Property_Object $prop
     * @return Cg_Template_Assoc_Strategy
     */
    function getAssocStrategy($relationId, $prop) {
        if ($prop->isList() && $prop->isManyToMany()) $class = 'Cg_Template_Assoc_Strategy_ManyToMany';
        elseif ($prop->isList()) $class = 'Cg_Template_Assoc_Strategy_Many';
        else $class = 'Cg_Template_Assoc_Strategy_One';

        //$class = 'Cg_Template_Assoc_Strategy';
        $res = new $class (array('relationId' => $relationId, 'prop' => & $prop, 'model' => & $this->model, 'template' => & $this));
        return $res;
    }

    /**
     * @param Cg_Property_Object $prop
     */
    function _showModelMethodsForAssociation($relationId, $prop) {
        $strategy = $this->getAssocStrategy($relationId, $prop);
        $strategy->showGenModelMethods();
    }
    
    /**
     * Shows loadBy<IndexNameOrIndexFields> methods for every unique index
     */
    function _showMapperLoaderMethods() {
        foreach ($this->uniqueIndexData as $idxName => $idxFields) {
            if ($this->model->useIndexNamesInMapper) $funcSfx = $idxName; 
                else $funcSfx = str_replace(" ", "", ucwords(implode(" ", $idxFields)));
            $funcSfx = ucfirst($funcSfx);
            $params = '$'.implode(', $', $idxFields);
            $sqlCrit = array();
            foreach ($idxFields as $f) {
                $sqlCrit[] = "'.\$this->getDb()->n('$f').' = '.\$this->getDb()->q(\${$f}).'";
            }
            $sqlCrit = implode(" AND ", $sqlCrit);
?>

    /**
     * @return <?php $this->d($this->modelClass); ?> 
     */
    function loadBy<?php $this->d($funcSfx); ?> (<?php $this->d($params); ?>) {
        $recs = $this->loadRecordsByCriteria('<?php $this->d($sqlCrit); ?>');
        if (count($recs)) $res = $recs[0];
            else $res = null;
        return $res;
    }
<?php            
        } //foreach 
    }
    
    /**
     * @param Cg_Property_Object $prop
     */
    function _showMapperMethodsForAssociation($relationId, $prop) {
        $strategy = $this->getAssocStrategy($relationId, $prop);
        $strategy->showGenMapperMethods();
    }
    
    function _showModelStorageMethods() {
        $up = '';
        $down = '';
        $nn = '';
        foreach (array_keys($this->assocProperties) as $relId) { 
            $prop = $this->assocProperties[$relId];
            $strat = $this->getAssocStrategy($relId, $prop);
            ob_start(); $r = $strat->showStoreReferencedPart(); $part = ob_get_clean(); if ($r !== false) $up .= $part;
            ob_start(); $r = $strat->showStoreReferencingPart(); $part = ob_get_clean(); if ($r !== false) $down .= $part;
            ob_start(); $r = $strat->showStoreNNPart(); $part = ob_get_clean(); if ($r !== false) $nn .= $part;  
        }
        if (strlen($up)) {
?>

    function _storeReferencedRecords() {
        $res = parent::_storeReferencedRecords() !== false;
        $mapper = $this->getMapper();
<?php   echo $up; ?> 
        return $res;
    }
<?php           
        }
        if (strlen($down)) {
?>

    function _storeReferencingRecords() {
        $res = parent::_storeReferencingRecords() !== false;
        $mapper = $this->getMapper();
<?php   echo $down; ?>
        return $res; 
    }
<?php           
        }
        if (strlen($nn)) {
?>

    function _storeNNRecords() {
        $res = parent::_storeNNRecords() !== false;
        $mapper = $this->getMapper();
<?php   echo $nn; ?>
        return $res; 
    }
<?php           
        }
    }
    
    function showModelGenObject() {  

    // ------------------------------------------- modelGenObject -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?>


<?php if ($this->model->parentClassIsAbstract) echo "abstract "; ?>class <?php $this->d($this->genModelClass); ?> extends <?php $this->d($this->parentClass); ?> {
    
<?php foreach($this->vars as $var => $default) { ?>
    var $<?php $this->d($var); ?> = <?php $this->export($default); ?>;
<?php } ?>
    
    var $_mapperClass = <?php $this->str($this->mapperClass); ?>;
    
    /**
     * @var <?php echo $this->mapperClass; ?> 
     */
    protected $mapper = false;

    /**
     * @return <?php echo $this->domain->getAppClass(); ?> 
     */
    function getApplication() {
        return parent::getApplication();
    }
    
    /**
     * @return <?php echo $this->mapperClass; ?> 
     */
    function getMapper($mapperClass = false) {
        return parent::getMapper($mapperClass);
    }
    
    function listOwnProperties() {
<?php if ($this->parentClass !== 'Ac_Model_Object') { ?>
    return array_merge(parent::listOwnProperties(), <?php $this->exportArray($this->ownProperties, 0, false, true); ?>);
<?php } else { ?>        
        return <?php $this->exportArray($this->ownProperties, 0, false, true); ?>;
<?php }?>        
    }

    function listOwnLists() {
<?php if ($this->parentClass !== 'Ac_Model_Object') { ?>
    return array_merge(parent::listOwnLists(), <?php $this->exportArray($this->ownLists, 0, false, true); ?>);
<?php } else { ?>        
        return <?php $this->exportArray($this->ownLists, 0, false, true); ?>;
<?php }?>        
    }

    function listOwnAssociations() {
<?php if ($this->parentClass !== 'Ac_Model_Object') { ?>
    return array_merge(parent::listOwnLists(), <?php $this->exportArray($this->ownAssociations, 0, false, true); ?>);
<?php } else { ?>        
        return <?php $this->exportArray($this->ownAssociations, 0, false, true); ?>;
<?php }?>        
    }

    function getOwnPropertiesInfo() {
    	<?php if ($this->generator->php5) echo 'static $pi = false; if ($pi === false) '; ?>$pi = <?php $this->exportArray($this->ownPropInfo, 8, true); ?>;
<?php   if ($this->parentClass === 'Ac_Model_Object') { ?>    
        return $pi;
<?php   } else { ?>
        return Ac_Util::m($pi, parent::getOwnPropertiesInfo());
<?php   } ?>                
    }
<?php if ($this->model->hasUniformPropertiesInfo) { ?>

    function hasUniformPropertiesInfo() { return true; }
<?php } ?>
<?php if ($this->model->tracksChanges) { ?>

    function tracksChanges() { return true; }
<?php } ?>
<?php foreach (array_keys($this->assocProperties) as $relId) { $this->_showModelMethodsForAssociation($relId, $this->assocProperties[$relId]); } ?>  
<?php $this->_showModelStorageMethods(); ?>
    
}

<?php //$this->phpClose(); ?><?php        
    }
    
    function showModelObject() {  

    // ------------------------------------------- modelObject -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?>


class <?php $this->d($this->modelClass); ?> extends <?php $this->d($this->genModelClass); ?> {
<?php if ($this->model->generateMethodPlaceholders) { ?>
    
    /*
    function getOwnPropertiesInfo() {
        return Ac_Util::m(parent::getOwnPropertiesInfo(), array(
            '' => array(
                'caption' => '',
                'dataType' => '',
                'controlType' => '',
            ),
        ));
    }
    
    function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array(
            '', '',
        ));
    }
    
    function listOwnLists() {
        return array_merge(parent::listOwnLists(), array(
            '' => '', '' => '',
        ));
    }
    
    function listOwnAssociations() {
        return array_merge(parent::listOwnAssociations(), array(
            '' => '', '' => '',
        ));
    }
    
    */
<?php } ?>
}

<?php //$this->phpClose(); ?><?php
    }
    
    function showGenMapper() {

    // ------------------------------------------- genMapper -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?>


<?php if ($this->model->parentMapperIsAbstract) echo "abstract "; ?>class <?php $this->d($this->genMapperClass); ?> extends <?php $this->d($this->parentMapperClass); ?> {
<?php foreach ($this->mapperVars as $var => $default) { ?>

    var $<?php echo $var; ?> = <?php $this->export($default); ?>; 
<?php } ?> 
<?php if ($this->autoincFieldName) { ?>
    
    protected $autoincFieldName = <?php $this->str($this->autoincFieldName) ?>;
    
<?php } ?>
    function listSqlColumns() {
        return $this->columnNames;
    }
    
    /**
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    function factory ($className = false) {
        $res = parent::factory($className);
        return $res;
    }
    
    /**
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    function reference ($values = array()) {
        return parent::reference($values);
    }
    
    /**
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    function loadRecord ($id) {
        return parent::loadRecord($id);
    }
    
    /**
     * Returns first record in the resultset (returns NULL if there are no records)
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    function loadSingleRecord($where = '', $keysToList = false, $order = '', $joins = '', $limitOffset = false, $limitCount = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount);
    }

    <?php if ($this->model->titleProp) { ?>
    
    function getTitleFieldName() {
        return <?php $this->str($this->model->titleProp); ?>;   
    }
    <?php } ?>
    <?php if ($this->model->orderingProp) { $ord = $this->model->orderingProp; if ($this->model->orderGroupProp) $ord = $this->model->orderGroupProp.', '.$ord;  ?>
    
    function getDefaultOrdering() {
        return <?php $this->str($ord); ?>;
    }
    <?php } ?>
    <?php if ($this->relationPrototypes) { ?>
    
    protected function getRelationPrototypes() {
<?php   if (!in_array($this->genMapperClass, array('Ac_Model_Mapper', 'Ac_Model_CpkMapper'))) { ?>
        return Ac_Util::m(parent::getRelationPrototypes(), <?php $this->exportArray($this->relationPrototypes, 8); ?>);
<?php   } else { ?>
        return <?php $this->exportArray($this->relationPrototypes, 8); ?>;
<?php   } ?>        
    }
    <?php } ?>
    
    protected function doGetInfoParams() {
<?php   if (!in_array($this->genMapperClass, array('Ac_Model_Mapper', 'Ac_Model_CpkMapper'))) { ?>
        return Ac_Util::m( 
            <?php $this->exportArray($this->model->getMapperInfoParams(), 12); ?>,
            parent::doGetInfoParams()
        );
<?php   } else { ?>
        return Ac_Util::m(parent::doGetInfoParams(), 
            <?php $this->exportArray($this->model->getMapperInfoParams(), 12); ?>
        );
<?php   } ?>        
    }
    
    <?php if ($this->uniqueIndexData) { ?>
    
    protected function doGetUniqueIndexData() {
        return <?php $this->exportArray($this->uniqueIndexData, 8); ?>;
    }
    <?php } ?>
    <?php $this->_showMapperLoaderMethods(); ?>
    <?php foreach (array_keys($this->assocProperties) as $relId) { $this->_showMapperMethodsForAssociation($relId, $this->assocProperties[$relId]); } ?>
    
}

<?php //$this->phpClose(); ?><?php
    }
    
    function showMapper() {  

    // ------------------------------------------- mapper -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?>


class <?php $this->d($this->mapperClass); ?> extends <?php $this->d($this->genMapperClass); ?> {
<?php if ($this->model->generateMethodPlaceholders) { ?>
    
    /*
    
	protected function doGetInfoParams() {
        
		$res = Ac_Util::m(parent::doGetInfoParams(), array(
        	'singleCaption' => '',
        	'pluralCaption' => '',
		
        	'adminFeatures' => array(
        		'Ac_Admin_Feature_Default' => array(
		
         			'actionSettings' => array(
			            '' => array(
			                'id' => '',
			                'scope' => 'any',
			                'image' => 'stop_f2.png', 
			                'disabledImage' => 'stop.png',
			                'caption' => '',
			                'description' => '',
			                'managerProcessing' => 'procName',
			                'listOnly' => true,
			            ), 
			        ),
			        
			        'processingSettings' => array(
			        	'procName' => array(
			        		'class' => 'Proc_Class',
			        	),
			        ),
		
        			'columnSettings' => array(
		
                        'col1' => array(
                            'class' => '',
                            'order' => -10,
                            'title' => '',
                        ),
                        
        			),
        			
                    'formFieldDefaults' => array(
                    ),
                    
                    'displayOrderStart' => 0,
                    
                    'displayOrderStep' => 10,
                    
			        'formSettings' => array(
			        	'controls' => array(
                            '' => array(
                            ),
				       	),
			        ),
			        
			        'filterPrototypes' => array(
			        ),
			        
			        'orderPrototypes' => array(
			        ),
			        
			        'filterFormSettings' => array(
			        	'controls' => array(
				        	'substring' => array(
			        			'class' => 'Ac_Form_Control_Text',
			        			'caption' => 'Filter',
			        			'htmlAttribs' => array(	
			        				'onchange' => 'document.aForm.submit();',
			        				'size' => 20,
			        			),
								'description' => '',			        			
				        	),
				        ),
			        ),
                    
                    'sqlSelectSettings' => array(
                        'tables' => array(
                        ),
                    ),
        			
        		),
        	),
		));
		return $res;
	}    
    
    protected function getRelationPrototypes() {
        return Ac_Util::m(parent::getRelationPrototypes(), array(
            '' => array(
                'srcMapperClass' => <?php $this->str($this->mapperClass); ?>,
                'destMapperClass' => '',
                'fieldLinks' => array(),
                'srcIsUnique' => false,
                'destIsUnique' => false,
            ),
        ));
    }
    
    */
    
}
    
<?php } ?>  
<?php //$this->phpClose(); ?><?php
    }
    
}

