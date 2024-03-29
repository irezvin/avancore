<?php

/**
 * This feature works with every record to populate manager with basic info 
 */
class Ac_Admin_Feature_Default extends Ac_Admin_Feature {
    
    var $order = 0;
    
    var $preloadRelations = false;
    
    var $_columnSettings = false;
    
    var $columnSettings = array();

    /**
     * @var callback to function (array & $columnConfig, $propName, Ac_Model_Property $property)
     */
    var $columnGeneratorCallback = false;
    
    var $formSettings = array();

    /**
     * @var callback to function (array & $fieldConfig, $propName, Ac_Model_Property $property, array & $formSettings)
     */
    var $formFieldGeneratorCallback = false;
    
    var $formFieldDefaults = array();
    
    var $actionSettings = array();
    
    var $filterFormSettings = array();
    
    var $filterPrototypes = array();
    
    var $orderPrototypes = array();
    
    var $sqlSelectSettings = array();
    
    var $searchSettings = array();
    
    var $processingSettings = array();

    var $addErrorList = true;
    
    var $errorListPrototype = array();
    
    var $displayOrderStart = 0;
    
    var $displayOrderStep = 10;
    
    var $subManagersExtra = array();
    
    function doCanBeApplied() {
        return true;
    }
    
    function getPreloadRelations() {
        if ($this->preloadRelations === false) {
            $this->getColumnSettings();
        }
        return Ac_Util::array_unique($this->preloadRelations);
    }
    
    function getColumnSettings() {
        if ($this->_columnSettings === false) {
            if ($this->preloadRelations === false) $this->preloadRelations = array();
            $prot = $this->manager->getRecordPrototype();
            $map = $this->manager->getMapper();
            $tf = $map->getTitleFieldName();
            $res = array();
            $res['_recordBinder_'] = array(
                'class' => 'Ac_Admin_Column_RecordBinder',
                'manager' => & $this->manager,
            );
            $res['_rowNumber'] = array(
                'class' => 'Ac_Table_Column_Number',
                'cellAttribs' => array ('class' => 'ctr'),
            );
            $res['_checked'] = array(
                'class' => 'Ac_Admin_Column_Checked',
                'manager' => & $this->manager,
                'headerAttribs' => array ('class' => 'w20'),
                'cellAttribs' => array ('class' => 'w20 ctr'),
            );
            foreach ($prot->listFields() as $f) {
                $pi = $prot->getPropertyInfo($f, true);
                $s = false;
                if (isset($pi->assocPropertyName) && strlen($opn = $pi->assocPropertyName)) {
                    $op = $prot->getPropertyInfo($opn, true);
                    if (isset($op->mapperClass) && strlen($mc = $op->mapperClass)) {
                        $mpr = Ac_Model_Mapper::getMapper($mc);
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
                        $s['class'] = 'Ac_Admin_Column_DetailsLink';
                        $s['manager'] = $this->manager;
                    }
                }
                if (isset($pi->showInTable)) $showInTable = $pi->showInTable;
                    else $showInTable = !($pi->controlType == 'textArea');
                if (!$showInTable) $s['disabled'] = true;
                if (isset($pi->columnPrototype) && is_array($pi->columnPrototype)) {
                    Ac_Util::ms($s, $pi->columnPrototype);
                }
                if ($this->columnGeneratorCallback)  {
                    call_user_func_array($this->columnGeneratorCallback, array(& $s, $f, $pi));
                }
                $res[$f] = $s;
            }
            $this->_columnSettings = $res;
            if ($this->columnSettings) Ac_Util::ms($this->_columnSettings, $this->columnSettings);
        }
        return $this->_columnSettings;
    }
    
    function getActions() {
    
        return Ac_Util::m(array(
            'new' => array(
                'id' => 'new',
                'scope' => 'any',
                'caption' => AC_ADMIN_CREATE_NEW_RECORD_CAPT, 
                'description' => AC_ADMIN_CREATE_NEW_RECORD_DESCR, 
                'managerAction' => 'new',
                'listOnly' => true,
            ), 
            'edit' => array(
                'id' => 'edit',
                'scope' => 'one',
                'caption' => AC_ADMIN_EDIT_RECORD_CAPT,
                'description' => AC_ADMIN_EDIT_RECORD_DESCR,
                'managerAction' => 'details',
                'listOnly' => true,
            ), 
            'delete' => array(
                'id' => 'delete',
                'scope' => 'some',
                'caption' => AC_ADMIN_DELETE_RECORD_CAPT,
                'description' => AC_ADMIN_DELETE_RECORD_DESCR,
                'confirmationText' => AC_ADMIN_DELETE_RECORD_CONFIRM,
                'managerProcessing' => 'delete',
                'listOnly' => true,
            ), 
            'apply' => array(
                'id' => 'apply',
                'scope' => 'any',
                'caption' => AC_ADMIN_APPLY_CAPT,
                'description' => AC_ADMIN_APPLY_DESCR,
                'managerAction' => 'apply',
                'formOnly' => true,
            ), 
            'save' => array(
                'id' => 'save',
                'scope' => 'any',
                'caption' => AC_ADMIN_SAVE_CAPT,
                'description' => AC_ADMIN_SAVE_DESCR,
                'managerAction' => 'save',
                'formOnly' => true,
            ), 
            'saveAndAdd' => array(
                'id' => 'saveAndAdd',
                'scope' => 'any',
                'caption' => AC_ADMIN_SAVE_ADD_CAPT,
                'description' => AC_ADMIN_SAVE_ADD_DESCR,
                'managerAction' => 'saveAndAdd',
                'formOnly' => true,
            ), 
            'cancel' => array(
                'id' => 'cancel',
                'scope' => 'any',
                'caption' => AC_ADMIN_CANCEL_CAPT,
                'description' => AC_ADMIN_CANCEL_DESCR,
                'managerAction' => 'cancel',
                'formOnly' => true,
            ), 
        ), $this->actionSettings);
    }
    
    
    function applyToFormSettings(& $formSettings) {
        $rec = $this->manager->getRecord();
        $mpr = $this->manager->getMapper();
        $gen = $mpr->listGeneratedFields();
        $conv = new Ac_Form_Converter();
        $do = $this->displayOrderStart;
        if ($this->addErrorList) {
            $formSettings['controls']['errorList'] = array('class' => 'Ac_Form_Control_ErrorList', 'caption' => '');
            if ($this->errorListPrototype && is_array($this->errorListPrototype)) 
                Ac_Util::ms($formSettings['controls']['errorList'], $this->errorListPrototype);
        }
        foreach ($rec->listFields() as $p) {
            $prop = $rec->getPropertyInfo($p, true);
            if (isset($prop->showInForm) && !$prop->showInForm) continue;
            $conf = Ac_Util::m($this->formFieldDefaults, $conv->getControlSettings($prop, $rec));
            if ($do !== false) {
                if (!isset($conf['displayOrder'])) 
                    $conf['displayOrder'] = $do;
                $do += $this->displayOrderStep;
            }
            if (in_array($prop->propName, $gen)) {
                $conf['readOnly'] = true;
                $conf['emptyCaption'] = AC_ID_EMPTY_CAPTION;
            }
            if ($this->formFieldGeneratorCallback) call_user_func_array($this->formFieldGeneratorCallback, array(& $conf, $p, $prop, & $formSettings));
            if (is_array($conf))
                $formSettings['controls'][$prop->propName] = $conf;
        }
        if ($this->formSettings) Ac_Util::ms($formSettings, $this->formSettings);
    }
    
    function applyToFilterFormSettings(& $filterFormSettings) {
        if ($this->filterFormSettings) Ac_Util::ms($filterFormSettings, $this->filterFormSettings);
    }
    
    function getProcessings() {
        $res = Ac_Util::m(
            array(
	            'delete' => array(
	                'class' => 'Ac_Admin_Processing_Delete',
	            ),
	        ),
	        $this->processingSettings
        );
        return $res;
    }
    
    
    function getSubManagersConfig() {
        $res = array();
        $rec = $this->manager->getRecord();
        $mpr = $this->manager->getMapper();
        if ($mpr) {
            $listProps = array_keys($rec->listLists());
            $assocProps = array_keys($rec->listAssociations());
            $aLists = array_intersect($listProps, $assocProps);
            foreach ($aLists as $propName) {
                $p = $rec->getPropertyInfo($propName, true);
                if (isset($p->mapperClass) && strlen($mc = $p->mapperClass) && isset($p->relationId) && strlen($rid = $p->relationId)) {
                    $rel = $mpr->getRelation($rid);
                    if (!$rel->midTableName) {
                        $subMapper = Ac_Model_Mapper::getMapper($mc);
                        $i = $subMapper->getInfo();
                        $asm = $i->allowSubManagers;
                        $res[$propName] = array('mapperClass' => $mc, 'allowSubManagers' => $asm, '_relId' => $p->relationId);
                    }
                }
            }
        }
        Ac_Util::ms($res, $this->subManagersExtra);
        
        foreach ($res as $k => $v) if (!is_array($v) && !$v) 
            unset($res[$v]);

        // Re-order sub-managers beginning from subManagersExtra, in the same order
        $tmp = $res;
        $res = array();
        foreach (array_unique(array_merge(array_keys($this->subManagersExtra), array_keys($tmp))) as $k) {
            $res[$k] = $tmp[$k];
        }
        
        return $res;
    }
    
    
    /**
     * @param string $id
     * @param Ac_Admin_Manager $subManager 
     * @param array $smConfig Configuration that was used to create subManager
     */
    function onSubManagerCreated($id, $subManager, $smConfig = array()) {
        if (isset($smConfig['_relId'])) {
            $dl = new Ac_Admin_Datalink_Subrecord();
            $subManager->setDatalink($dl);
            $dl->setRelationId($this->manager->mapperClass, $smConfig['_relId']);
            $rec = $this->manager->getRecord();
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
    
    function getSearchSettings() {
        return $this->searchSettings;
    }
    
}
