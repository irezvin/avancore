<?php

class Ac_Cg_Template_ModelAndMapper extends Ac_Cg_Template {
    
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
    var $createAccessors = false;
    var $accessors = array();
    var $nullableSqlColumns = array();
    var $associationPrototypes = array();
    var $hasUniformPropertiesInfo = false;
    var $tracksChanges = false;
    var $modelCoreMixables = array();
    var $mapperCoreMixables = array();
    var $internalDefaults = false;
    
    var $ignoreVars = array();
    var $ignoreMethods = array();
    var $inheritedIndice = array();
    
    function _generateFilesList() {
        return array(
            'modelObject' => array(
                'relPath' => Ac_Cg_Util::className2fileName($this->modelClass), 
                'isEditable' => true, 
                'templatePart' => 'modelObject',
            ),
            'genModelObject' => array(
                'relPath' => 'gen/'.Ac_Cg_Util::className2fileName($this->genModelClass), 
                'isEditable' => false, 
                'templatePart' => 'modelGenObject',
            ),
            'mapper' => array(
                'relPath' => Ac_Cg_Util::className2fileName($this->mapperClass), 
                'isEditable' => true, 
                'templatePart' => 'mapper',
            ),
            'genMapper' => array(
                'relPath' => 'gen/'.Ac_Cg_Util::className2fileName($this->genMapperClass), 
                'isEditable' => false, 
                'templatePart' => 'genMapper',
            ), 
        );
    }
    
    function doInit() {
        $this->modelClass = $this->model->className;
        $this->genModelClass = $this->model->getGenModelClass();
        $this->parentClass = $this->model->parentClassName;
        
        $this->mapperClass = $this->model->getMapperClass();
        $this->genMapperClass = $this->model->getGenMapperClass();
        $this->parentMapperClass = $this->model->parentMapperClassName;
        $this->createAccessors = $this->model->createAccessors;
        
        foreach ($this->model->listProperties() as $name) {
            $prop = $this->model->getProperty($name);
            if (!$prop->isEnabled()) continue;
            if ($this->createAccessors && $prop instanceof Ac_Cg_Property_Simple && !$prop->inherited) {
                $this->accessors[$prop->getClassMemberName()] = $prop->getClassMemberName();
            }
        }
        
        $this->vars = $this->getModelClassMembers($this->model);
        
        $pps = $this->model->getAeDataPropLists();
        $parentModel = $this->model->getParentModel();
        if ($parentModel) {
            $parentProps = $parentModel->getAeDataPropLists();
            foreach (array_keys($pps) as $i) {
                $pps[$i] = Ac_Cg_Util::arrayDiffWithKeys($pps[$i], $parentProps[$i]);
            }
        }
        
        
        $this->ownProperties = $pps['ownProperties'];
        $this->ownAssociations = $pps['ownAssociations'];
        $this->ownLists = $pps['ownLists'];
        $this->ownPropInfo = $pps['ownPropertiesInfo'];  
        
        $this->tableName = $this->model->tableObject->name;
        $this->associationPrototypes = $this->model->getAssociationPrototypes();
        $this->hasUniformPropertiesInfo = $this->model->hasUniformPropertiesInfo;
        $this->tracksChanges = $this->model->tracksChanges;
        $this->modelCoreMixables = $this->model->modelCoreMixables;
        $this->mapperCoreMixables = $this->model->mapperCoreMixables;
        $this->autoincFieldName = $this->calcAi($this->model);
        $this->internalDefaults = $this->model->getInternalDefaults();
        
        $pk = $this->model->tableObject->listPkFields();
        if (!count($pk)) $this->pkStr = 'null';
        elseif (count($pk) == 1) {
            $this->pkStr = $this->str($pk[0], true);
        } else {
            $this->pkStr = $this->exportArray($pk, 0, false, true, true);
        }
        
        $this->mapperVars['pk'] = new Ac_Cg_Php_Expression($this->pkStr);
        $this->mapperVars['recordClass'] = $this->model->getMapperRecordClass();
        $this->mapperVars['tableName'] = $this->tableName;
        $this->mapperVars['id'] = $this->mapperClass;
        $this->mapperVars['columnNames'] = new Ac_Cg_Php_Expression($this->exportArray(array_keys($this->getDefaults($this->model)), 0, false, true, true));
        
        $this->ownProperties = array_diff($this->ownProperties, $this->model->listUsedColumns());
        if ($this->model->nullableSqlColumns) 
            $this->mapperVars['nullableSqlColumns'] = new Ac_Cg_Php_Expression($this->exportArray($this->model->nullableSqlColumns, 0, false, true, true));
        $this->mapperVars['defaults'] = $this->getDefaults($this->model);
        $this->mapperVars['defaults'] = new Ac_Cg_Php_Expression($this->exportArray($this->mapperVars['defaults'], 8, true, false, true));
        
        $this->relationPrototypes = $this->model->getRelationPrototypes();
        $this->assocProperties = $this->model->getAssocProperties();
        
        $this->uniqueIndexData = $this->model->tableObject->getUniqueIndexData();
        
        $this->calcInheritance();
    }
    
    function getModelClassMembers(Ac_Cg_Model $model) {
        $res = array('_hasDefaults' => true);
        foreach ($model->listProperties() as $name) {
            $prop = $model->getProperty($name);
            if (!$prop->isEnabled()) continue;
            $res = array_merge($res, $prop->getAllClassMembers());
        }
        return $res;
    }
    
    function calcAi(Ac_Cg_Model $model) {
        
        $res = false;
        foreach ($model->listUsedColumns() as $cn) {
            $col = $model->tableObject->getColumn($cn);
            if ($col->autoInc) {
                $res = $cn;
                break;
            }
        }
        return $res;
        
    }
    
    function getDefaults(Ac_Cg_Model $model) {
        $res = array();
        $tc = $model->tableObject->listColumns();
        foreach (array_intersect($model->listUsedColumns(), $tc) as $nm) {
            $col = $model->tableObject->getColumn($nm);
            $default = $col->default; 
            if (strpos(strtolower($col->type), 'text') !== false && is_null($col->default)) $default = '';
            $res[$nm] = $default;
        }
        return $res;
    }
    
    protected function calcInheritance() {
        $parentModel = $this->model->getParentModel();
        if (!$parentModel) return;

        $vars = $this->getModelClassMembers($parentModel);
        $this->vars = Ac_Cg_Util::arrayDiffWithKeys($this->vars, $vars);
        
        if ($parentModel->tableObject->listPkFields() == $this->model->tableObject->listPkFields()) {
            unset($this->mapperVars['pk']);
        }
        
        if ($parentModel->tableObject->name == $this->model->tableObject->name) {
            unset($this->mapperVars['tableName']);
        }
        
        if ($parentModel->listUsedColumns() == $this->model->listUsedColumns()) {
            unset($this->mapperVars['columnNames']);
        }
        
        if ($this->getDefaults($this->model) == $this->getDefaults($parentModel)) {
            unset($this->mapperVars['defaults']);
        }
        
        if ($this->model->nullableSqlColumns == $parentModel->nullableSqlColumns) {
            unset($this->mapperVars['nullableSqlColumns']);
        }
        
        if ($this->calcAi($parentModel) == $this->autoincFieldName) {
            $this->ignoreMethods['autoincFieldName'] = true;
        }
        
        $this->ignoreMethods['askRelationsForDefaults'] = true;
        
        $this->associationPrototypes = Ac_Cg_Util::arrayDiffWithKeys($this->associationPrototypes, $parentModel->getAssociationPrototypes());
        
        $this->relationPrototypes = Ac_Cg_Util::arrayDiffWithKeys($this->relationPrototypes, $parentModel->getRelationPrototypes());

        $this->internalDefaults = Ac_Cg_Util::arrayDiffWithKeys($this->internalDefaults, $parentModel->getInternalDefaults());
        
        $this->mapperCoreMixables = Ac_Cg_Util::arrayDiffWithKeys($this->mapperCoreMixables, $parentModel->mapperCoreMixables);
        
        $this->modelCoreMixables = Ac_Cg_Util::arrayDiffWithKeys($this->modelCoreMixables, $parentModel->modelCoreMixables);
        
        $parentIndexData = $parentModel->tableObject->getUniqueIndexData();
        
        foreach ($this->uniqueIndexData as $idxName => $idxFields) {
            if (isset($parentIndexData[$idxName]) && $parentIndexData[$idxName] == $idxFields) {
                $this->inheritedIndice[] = $idxName;
            }
        }
        
        if ($parentModel->hasUniformPropertiesInfo == $this->model->hasUniformPropertiesInfo) {
            $this->ignoreMethods['hasUniformPropertiesInfo'] = true;
        }
        
        if ($parentModel->tracksChanges == $this->model->tracksChanges) {
            $this->ignoreMethods['tracksChanges'] = true;
        }
        
    }

    /**
     * @param Ac_Cg_Property_Object $prop
     * @return Ac_Cg_Template_Assoc_Strategy
     */
    function getAssocStrategy($relationId, $prop) {
        $res = $prop->getAssocStrategy();
        $res->template = $this;
        return $res;
    }
    
    function _showModelAccessors() {
        foreach ($this->accessors as $propName => $varName) {
            $ucProp = ucfirst($propName);
?>

    function set<?php echo $ucProp; ?>($<?php echo $propName; ?>) {
        if ($<?php echo $propName; ?> !== ($old<?php echo $ucProp; ?> = $this-><?php echo $varName; ?>)) {
            $this-><?php echo $varName; ?> = $<?php echo $propName; ?>;
            $this->notifyFieldChanged(<?php $this->export($propName); ?>);
        }
    }

    function get<?php echo $ucProp; ?>() {
        return $this-><?php echo $varName; ?>;
    }
        
<?php
        }
    }

    /**
     * @param Ac_Cg_Property_Object $prop
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
            $inherited = in_array($idxName, $this->inheritedIndice);
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
<?php   if ($inherited) { ?>
        $res = parent::loadBy<?php $this->d($funcSfx); ?>(<?php $this->d($params); ?>);
<?php   } else { ?>
        $recs = $this->loadRecordsByCriteria('<?php $this->d($sqlCrit); ?>');
        if (count($recs)) $res = $recs[0];
            else $res = null;
<?php   } ?>
        return $res;
    }
<?php            
        } //foreach 
    }
    
    /**
     * @param Ac_Cg_Property_Object $prop
     */
    function _showMapperMethodsForAssociation($relationId, $prop) {
        $strategy = $this->getAssocStrategy($relationId, $prop);
        $strategy->showGenMapperMethods();
    }
    
    function showModelGenObject() {  

        $fieldVisibility = $this->createAccessors? 'protected' : 'public';
        
    // ------------------------------------------- modelGenObject -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?>


<?php if ($this->model->parentClassIsAbstract) echo "abstract "; ?>class <?php $this->d($this->genModelClass); ?> extends <?php $this->d($this->parentClass); ?> {

<?php foreach($this->vars as $var => $default) { ?>
    <?php echo $fieldVisibility; ?> $<?php $this->d($var); ?> = <?php $this->export($default); ?>;
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
<?php if ($this->modelCoreMixables) { ?> 

    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), <?php $this->exportArray($this->modelCoreMixables, 8, true); ?>);
    }
<?php } ?>
<?php   if ($this->ownProperties) { ?>    
    protected function listOwnProperties() {
        return array_unique(array_merge(parent::listOwnProperties(), <?php $this->exportArray($this->ownProperties, 0, false, true); ?>));
    }
<?php } ?>    
<?php if ($this->ownLists) { ?> 
    protected function listOwnLists() {
<?php if ($this->parentClass !== $this->model->getDefaultParentClassName()) { ?>
    return array_merge(parent::listOwnLists(), <?php $this->exportArray($this->ownLists, 0, false, true); ?>);
<?php } else { ?>        
        return <?php $this->exportArray($this->ownLists, 0, false, true); ?>;
<?php }?>
    }

<?php } ?>    
<?php if ($this->ownAssociations) { ?> 
    protected function listOwnAssociations() {
<?php if ($this->parentClass !== $this->model->getDefaultParentClassName()) { ?>
        return array_merge(parent::listOwnLists(), <?php $this->exportArray($this->ownAssociations, 0, false, true); ?>);
<?php } else { ?>
        return <?php $this->exportArray($this->ownAssociations, 0, false, true); ?>;
<?php }?>
    }

<?php } ?>
<?php if ($this->ownPropInfo) { ?>
    protected function getOwnPropertiesInfo() {
    	static $pi = false; 
        if ($pi === false) $pi = <?php $this->exportArray($this->ownPropInfo, 8, true); ?>;
<?php   if ($this->parentClass === $this->model->getDefaultParentClassName()) { ?>    
        return $pi;
<?php   } else { ?>
        return Ac_Util::m($pi, parent::getOwnPropertiesInfo());
<?php   } ?>                
    }
<?php } ?>    
<?php if ($this->hasUniformPropertiesInfo && !isset($this->ignoreMethods['hasUniformPropertiesInfo'])) { ?>

    function hasUniformPropertiesInfo() { return true; }
<?php } ?>
<?php if ($this->tracksChanges && !isset($this->ignoreMethods['tracksChanges'])) { ?>

    function tracksChanges() { return true; }
<?php } ?>
<?php if ($this->createAccessors) $this->_showModelAccessors(); ?>
<?php foreach (array_keys($this->assocProperties) as $relId) { $this->_showModelMethodsForAssociation($relId, $this->assocProperties[$relId]); } ?>  
    
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
    protected function getOwnPropertiesInfo() {
        return Ac_Util::m(parent::getOwnPropertiesInfo(), array(
            '' => array(
                'caption' => '',
                'dataType' => '',
                'controlType' => '',
            ),
        ));
    }
    
    protected function listOwnProperties() {
        return array_merge(parent::listOwnProperties(), array(
            '', '',
        ));
    }
    
    protected function listOwnLists() {
        return array_merge(parent::listOwnLists(), array(
            '' => '', '' => '',
        ));
    }
    
    protected function listOwnAssociations() {
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
<?php if ($this->autoincFieldName && !isset($this->ignoreMethods['autoincFieldName'])) { ?>
   
    protected $autoincFieldName = <?php $this->str($this->autoincFieldName) ?>;
<?php } ?>
<?php if (!isset($this->ignoreMethods['askRelationsForDefaults'])) { ?>
    protected $askRelationsForDefaults = false;
<?php   } ?> 
<?php if ($this->mapperCoreMixables) { ?>
    protected function doGetCoreMixables() { 
        return Ac_Util::m(parent::doGetCoreMixables(), <?php $this->exportArray($this->mapperCoreMixables, 8, true); ?>);
    }
    
<?php } ?> 
<?php if (!$this->model->getParentModel()) { ?>
    function listSqlColumns() {
        return $this->columnNames;
    }
<?php } ?> 
<?php if ($this->internalDefaults) { ?>
    function doGetInternalDefaults() {
<?php   if (!in_array($this->genMapperClass, array($this->model->getDefaultParentMapperClassName(), 'Ac_Model_CpkMapper'))) { ?>
        return Ac_Util::m(parent::doGetInternalDefaults(), <?php $this->exportArray($this->internalDefaults, 8); ?>);
<?php   } else { ?>
        return <?php $this->exportArray($this->internalDefaults, 8); ?>;
<?php   } ?>
    }
    
<?php } ?>
    /**
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    static function factory ($className = false,
        $unused1 = null, array $unused2 = array(), $unused3 = false, $unused4 = null) {
        trigger_error("Ac_Model_Mapper::factory() is deprecated and will be removed in the future; use ".
            "Ac_Model_Mapper::createRecord() instead", E_USER_DEPRECATED);
        $res = Ac_Model_Mapper::getMapper(<?php $this->str($this->mapperClass); ?>)->createRecord($className);
        return $res;
    }
    
    /**
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    function createRecord ($className = false) {
        $res = parent::createRecord($className);
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
    function loadFirstRecord($where = '', $order = '', $joins = '', $limitOffset = false, $tableAlias = false) {
        return parent::loadFirstRecord($where, $order, $joins, $limitOffset, $tableAlias);
    }
    
    /**
     * Returns single record in the resultset if it contains only one record
     * (returns NULL if there are no records or there is more than one record)
     * @return <?php $this->d($this->modelClass); ?> 
     */ 
    function loadSingleRecord($where = '', $order = '', $joins = '', $limitOffset = false, $limitCount = false, $tableAlias = false) {
        return parent::loadSingleRecord($where, $order, $joins, $limitOffset, $limitCount, $tableAlias);
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
    
    protected function doGetRelationPrototypes() {
<?php   if (!in_array($this->genMapperClass, array($this->model->getDefaultParentMapperClassName(), 'Ac_Model_CpkMapper'))) { ?>
        return Ac_Util::m(parent::doGetRelationPrototypes(), <?php $this->exportArray($this->relationPrototypes, 8); ?>);
<?php   } else { ?>
        return <?php $this->exportArray($this->relationPrototypes, 8); ?>;
<?php   } ?>        
    }
<?php } ?>
<?php if ($this->associationPrototypes) { ?>
    
    protected function doGetAssociationPrototypes() {
<?php   if (!in_array($this->genMapperClass, array($this->model->getDefaultParentMapperClassName(), 'Ac_Model_CpkMapper'))) { ?>
        return Ac_Util::m(parent::doGetAssociationPrototypes(), <?php $this->exportArray($this->associationPrototypes, 8); ?>);
<?php   } else { ?>
        return <?php $this->exportArray($this->associationPrototypes, 8); ?>;
<?php   } ?>        
    }
<?php } ?>
    
    protected function doGetInfoParams() {
<?php   if (!in_array($this->genMapperClass, array($this->model->getDefaultParentMapperClassName(), 'Ac_Model_CpkMapper'))) { ?>
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
    
<?php if ($this->uniqueIndexData && array_diff(array_keys($this->uniqueIndexData), $this->inheritedIndice)) { ?>
    
    protected function doGetUniqueIndexData() {
    return <?php $this->exportArray(array_diff_key($this->uniqueIndexData, array_flip($this->inheritedIndice)), 8); ?>;
    }
<?php } ?>
<?php   $this->_showMapperLoaderMethods(); ?>
    
<?php   foreach (array_keys($this->assocProperties) as $relId) { $this->_showMapperMethodsForAssociation($relId, $this->assocProperties[$relId]); } ?>
    
}

<?php //$this->phpClose(); ?><?php
    }
    
    function showMapper() {  

    // ------------------------------------------- mapper -------------------------------------------    
        
?>
<?php $this->phpOpen(); ?>


class <?php $this->d($this->mapperClass); ?> extends <?php $this->d($this->genMapperClass); ?> {
<?php if ($this->model->generateMethodPlaceholders) { ?>

//	protected function doGetInfoParams() {
//        
//		$res = Ac_Util::m(parent::doGetInfoParams(), array(
//        	'singleCaption' => '',
//        	'pluralCaption' => '',
//		
//        	'adminFeatures' => array(
//        		'Ac_Admin_Feature_Default' => array(
//		
//         			'actionSettings' => array(
//			            '' => array(
//			                'id' => '',
//			                'scope' => 'any',
//			                'image' => 'stop_f2.png', 
//			                'disabledImage' => 'stop.png',
//			                'caption' => '',
//			                'description' => '',
//			                'managerProcessing' => 'procName',
//			                'listOnly' => true,
//			            ), 
//			        ),
//			        
//			        'processingSettings' => array(
//			        	'procName' => array(
//			        		'class' => 'Proc_Class',
//			        	),
//			        ),
//		
//        			'columnSettings' => array(
//		
//                        'col1' => array(
//                            'class' => '',
//                            'order' => -10,
//                            'title' => '',
//                        ),
//                        
//        			),
//        			
//                    'formFieldDefaults' => array(
//                    ),
//                    
//                    'displayOrderStart' => 0,
//                    
//                    'displayOrderStep' => 10,
//                    
//			        'formSettings' => array(
//			        	'controls' => array(
//                            '' => array(
//                            ),
//				       	),
//			        ),
//			        
//			        'filterPrototypes' => array(
//			        ),
//			        
//			        'orderPrototypes' => array(
//			        ),
//			        
//			        'filterFormSettings' => array(
//			        	'controls' => array(
//				        	'substring' => array(
//			        			'class' => 'Ac_Form_Control_Text',
//			        			'caption' => 'Filter',
//			        			'htmlAttribs' => array(	
//			        				'onchange' => 'document.aForm.submit();',
//			        				'size' => 20,
//			        			),
//								'description' => '',			        			
//				        	),
//				        ),
//			        ),
//                    
//                    'sqlSelectSettings' => array(
//                        'tables' => array(
//                        ),
//                    ),
//        			
//        		),
//        	),
//		));
//		return $res;
//	}    
//    
//    protected function getRelationPrototypes() {
//        return Ac_Util::m(parent::getRelationPrototypes(), array(
//            '' => array(
//                'srcMapperClass' => <?php $this->str($this->mapperClass); ?>,
//                'destMapperClass' => '',
//                'fieldLinks' => array(),
//                'srcIsUnique' => false,
//                'destIsUnique' => false,
//            ),
//        ));
//    }
    
}
    
<?php } ?>  
<?php //$this->phpClose(); ?><?php
    }
    
}

