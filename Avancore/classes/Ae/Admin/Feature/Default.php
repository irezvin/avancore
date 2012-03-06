<?php

/**
 * This feature works with every record to populate manager with basic info 
 */
class Ae_Admin_Feature_Default extends Ae_Admin_Feature {
    
    var $priority = 0;
    
    var $_preloadRelations = false;
    
    var $_columnSettings = false;
    
    var $columnSettings = array();
    
    var $formSettings = array();
    
    var $formFieldDefaults = array();
    
    var $actionSettings = array();
    
    var $filterFormSettings = array();
    
    var $filterPrototypes = array();
    
    var $orderPrototypes = array();
    
    var $sqlSelectSettings = array();
    
    var $processingSettings = array();
    
    var $displayOrderStart = false;
    
    var $displayOrderStep = 0;
    
    function doCanBeApplied() {
        return true;
    }
    
    function getPreloadRelations() {
        if ($this->_preloadRelations === false) $this->getColumnSettings();
        return $this->_preloadRelations;
    }
    
    function getColumnSettings() {
        if ($this->_columnSettings === false) {
            $this->_preloadRelations = array();
            $prot = & $this->manager->getRecordPrototype();
            $map = & $this->manager->getMapper();
            $tf = $map->getTitleFieldName();
            $res = array();
            $res['_recordBinder_'] = array(
                'class' => 'Ae_Admin_Column_RecordBinder',
                'manager' => & $this->manager,
            );
            $res['_rowNumber'] = array(
                'class' => 'Ae_Table_Column_Number',
                'cellAttribs' => array ('class' => 'ctr'),
            );
            $res['_checked'] = array(
                'class' => 'Ae_Admin_Column_Checked',
                'manager' => & $this->manager,
                'headerAttribs' => array ('class' => 'w20'),
                'cellAttribs' => array ('class' => 'w20 ctr'),
            );
            foreach ($prot->listOwnFields() as $f) {
                $pi = & $prot->getPropertyInfo($f, true);
                $s = false;
                if (isset($pi->objectPropertyName) && strlen($opn = $pi->objectPropertyName)) {
                    $op = & $prot->getPropertyInfo($opn, true);
                    if (isset($op->mapperClass) && strlen($mc = $op->mapperClass)) {
                        $mpr = & Ae_Model_Mapper::getMapper($mc);
                        if ($tfn = $mpr->getTitleFieldName()) {
                            $s = array('fieldName' => "{$opn}[{$tfn}]");
                            if (strlen($op->caption)) $s['title'] = $op->caption;
                        }
                    }
                    if (isset($op->relationId) && strlen($rId = $op->relationId)) {
                        $this->_preloadRelations[$rId] = array('default_'.$f); 
                    }
                }
                if ($s === false) {
                    $s = array('fieldName' => $f);
                    if ($f == $tf) {
                        $s['class'] = 'Ae_Admin_Column_DetailsLink';
                        $s['manager'] = $this->manager;
                    }
                }
                if (isset($pi->showInTable)) $showInTable = $pi->showInTable;
                    else $showInTable = !($pi->controlType == 'textArea');
                if (!$showInTable) $s['disabled'] = true;
                $res[$f] = $s;
            }
            $this->_columnSettings = $res;
            if ($this->columnSettings) Ae_Util::ms($this->_columnSettings, $this->columnSettings);
        }
        return $this->_columnSettings;
    }
    
    function getActions() {
        return Ae_Util::m(array(
            'new' => array(
                'id' => 'new',
                'scope' => 'any',
                'image' => 'new_f2.png', 
                'disabledImage' => 'new.png',
                'caption' => AE_ADMIN_CREATE_NEW_RECORD_CAPT, 
                'description' => AE_ADMIN_CREATE_NEW_RECORD_DESCR, 
                'managerAction' => 'new',
                'listOnly' => true,
            ), 
            'edit' => array(
                'id' => 'edit',
                'scope' => 'one',
                'image' => 'edit_f2.png', 
                'disabledImage' => 'edit.png',
                'caption' => AE_ADMIN_EDIT_RECORD_CAPT,
                'description' => AE_ADMIN_EDIT_RECORD_DESCR,
                'managerAction' => 'details',
                'listOnly' => true,
            ), 
            'delete' => array(
                'id' => 'delete',
                'scope' => 'some',
                'image' => 'delete_f2.png', 
                'disabledImage' => 'delete.png',
                'caption' => AE_ADMIN_DELETE_RECORD_CAPT,
                'description' => AE_ADMIN_DELETE_RECORD_DESCR,
                'confirmationText' => AE_ADMIN_DELETE_RECORD_CONFIRM,
                'managerProcessing' => 'delete',
                'listOnly' => true,
            ), 
            'apply' => array(
                'id' => 'apply',
                'scope' => 'any',
                'image' => 'apply_f2.png', 
                'disabledImage' => 'apply.png',
                'caption' => AE_ADMIN_APPLY_CAPT,
                'description' => AE_ADMIN_APPLY_DESCR,
                'managerAction' => 'apply',
                'formOnly' => true,
            ), 
            'save' => array(
                'id' => 'save',
                'scope' => 'any',
                'image' => 'save_f2.png', 
                'disabledImage' => 'save.png',
                'caption' => AE_ADMIN_SAVE_CAPT,
                'description' => AE_ADMIN_SAVE_DESCR,
                'managerAction' => 'save',
                'formOnly' => true,
            ), 
            'saveAndAdd' => array(
                'id' => 'saveAndAdd',
                'scope' => 'any',
                'image' => 'save_f2.png', 
                'disabledImage' => 'save.png',
                'caption' => AE_ADMIN_SAVE_ADD_CAPT,
                'description' => AE_ADMIN_SAVE_ADD_DESCR,
                'managerAction' => 'saveAndAdd',
                'formOnly' => true,
            ), 
            'cancel' => array(
                'id' => 'cancel',
                'scope' => 'any',
                'image' => 'cancel_f2.png', 
                'disabledImage' => 'cancel.png',
                'caption' => AE_ADMIN_CANCEL_CAPT,
                'description' => AE_ADMIN_CANCEL_DESCR,
                'managerAction' => 'cancel',
                'formOnly' => true,
            ), 
        ), $this->actionSettings);
    }
    
    
    function applyToFormSettings(& $formSettings) {
        $rec = & $this->manager->getRecord();
        $mpr = & $this->manager->getMapper();
        if ($mpr) $aif = $mpr->getAutoincFieldName();
            else $aif = false;
        $conv = new Ae_Form_Converter();
        $do = $this->displayOrderStart;
        foreach ($rec->listOwnFields() as $p) {
            $prop = & $rec->getPropertyInfo($p, true);
            if (isset($prop->showInForm) && !$prop->showInForm) continue;
            $conf = Ae_Util::m($this->formFieldDefaults, $conv->getControlSettings($prop));
            if ($do !== false) {
                $conf['displayOrder'] = $do;
                $do += $this->displayOrderStep;
            }
            if ($prop->propName === $aif) {
                $conf['readOnly'] = true;
                $conf['emptyCaption'] = AE_ID_EMPTY_CAPTION;
            }
                            
            $formSettings['controls'][$prop->propName] = $conf;
        }
        if ($this->formSettings) Ae_Util::ms($formSettings, $this->formSettings);
    }
    
    function applyToFilterFormSettings(& $filterFormSettings) {
        if ($this->filterFormSettings) Ae_Util::ms($filterFormSettings, $this->filterFormSettings);
    }
    
    function getProcessings() {
        $res = Ae_Util::m(
            array(
	            'delete' => array(
	                'class' => 'Ae_Admin_Processing_Delete',
	            ),
	        ),
	        $this->processingSettings
        );
        return $res;
    }
    
    
    function getSubManagersConfig() {
        $res = array();
        $rec = & $this->manager->getRecord();
        $mpr = & $this->manager->getMapper();
        if ($mpr) {
            $listProps = array_keys($rec->listOwnLists());
            $assocProps = array_keys($rec->listOwnAssociations());
            $aLists = array_intersect($listProps, $assocProps);
            foreach ($aLists as $propName) {
                $p = & $rec->getPropertyInfo($propName, true);
                if (isset($p->mapperClass) && strlen($mc = $p->mapperClass) && isset($p->relationId) && strlen($rid = $p->relationId)) {
                    $rel = & $mpr->getRelation($rid);
                    if (!$rel->midTableName) {
                        $subMapper = & Ae_Model_Mapper::getMapper($mc);
                        $i = & $subMapper->getInfo();
                        $asm = $i->allowSubManagers;
                        $res[$propName] = array('mapperClass' => $mc, 'allowSubManagers' => $asm, '_relId' => $p->relationId);
                    }      
                }
            }
        }
        return $res;
    }
    
    
    /**
     * @param string $id
     * @param Ae_Admin_Manager $subManager 
     * @param array $smConfig Configuration that was used to create subManager
     */
    function onSubManagerCreated($id, & $subManager, $smConfig = array()) {
        if (isset($smConfig['_relId'])) {
            $dl = new Ae_Admin_Datalink_Subrecord();
            $subManager->setDatalink($dl);
            $dl->setRelationId($this->manager->mapperClass, $smConfig['_relId']);
            $rec = & $this->manager->getRecord();
            $dl->setParentRecord($rec);
        }
    }
    
    function getOrderPrototypes() {
        return $this->orderPrototypes;
    }
    
    function getFilterPrototypes() {
        return $this->filterPrototypes;
    }
    
    function getSqlSelectSettings() {
        return $this->sqlSelectSettings;
    }
    
}
?>