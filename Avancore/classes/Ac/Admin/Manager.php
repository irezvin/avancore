<?php

class Ac_Admin_Manager extends Ac_Legacy_Controller {

    var $singleCaption = false;
    
    var $pluralCaption = false;
    
    var $recordTitleField = false;
    
    var $showRecordTitle = true;
    
    var $allowSubManagers = true;
    
    var $prohibitSubsystems = false;
    
    var $separateToolbar = false;
    
    /**
     * If $separateToolbar is true, $toolbarContent will contain toolbar HTML after manager is shown. 
     * @var string
     */
    var $toolbarContent = false;
    
    var $toolbarHeader = false;
    
    var $_stayOnProcessing = false;
    
    /**
     * @var Ac_Sql_Filter
     */
    var $_sqlFilter = false;
    
    /**
     * @var Ac_Sql_Order
     */
    var $_sqlOrder = false;

    /**
     * @var Ac_Sql_Select
     */
    var $_sqlSelect = false;
    
    /**
     * Datalink that is injected when Manager is created...
     * @access protected 
     * @var Ac_Admin_Datalink
     */
    var $_datalink = false;
    
    /**
     * @var Ac_Admin_Template
     */
    var $_template = false;

    var $_templateClass = 'Ac_Admin_Template';

    /**
     * The only record that manager works with (if there should be more records, mapperClass should be provided)
     * @var Ac_Model_Data
     */
    var $onlyRecord = false;
    
    /**
     * @var Ac_Model_Data
     */
    var $_recordPrototype = false;
    
    var $mapperClass = false;

    /**
     * Extra settings of table features
     * @var array
     */
    var $featureSettings = array();
    
    var $debugSql = false;
    
    /**
     * @var string
     */
    var $_recordClass = false;
    
    /**
     * @var Ac_Model_Collection
     */
    var $_recordsCollection = false;
    
    /**
     * Prototypes of table columns
     * @var array
     */
    var $_columnSettings = false;
    
    /**
     * Features that can be applied to current manager
     * @var array of Ac_Manager_Feature
     */
    var $_featureObjects = false;
    
    /**
     * @var Ac_Table
     */
    var $_table = false;
    
    /**
     * Native manager pagination object
     * @var Ac_Admin_Pagination
     */
    var $_pagination = false;
    
    var $_managerFormName = false;
    
    var $_actions = false;
    
    var $_defaultMethodName = 'list';
    
    var $_primaryKeys = false;
    
    /**
     * @var Ac_Model_Object
     */
    var $_record = false;
    
    /**
     * @var Ac_Form
     */
    var $_form = false;
    
    /**
     * @var Ac_Form
     */
    var $_filterForm = false;
    
    var $_isForm = false;
    
    var $_isList = false;
    
    var $_isNewRecord = false;
    
    var $_recordStored = false;
    
    var $_preloadRelations = false;
    
    var $_processings = false;
    
    /**
     * @var Ac_Admin_ReportEntry
     */
    var $_report = false;
    
    var $_subManagers = false;
    
    var $_caching = false;
    
    var $_formResponse = false;
    
    var $_filterFormResponse = false;
    
    var $dontCount = false;
    
    var $lastRecordErrors = array();
    
    var $_returnUrl = false;
    
    /**
     * @var Ac_Admin_ManagerConfigService
     */
    protected $configService = false;
    
    function doInitProperties($options = array()) {
        Ac_Util::bindAutoparams($this, $options);
    }
    
    // ------------------------------------------- cache support methods ----------------------------------------------
    
    
    function _getCacheId($d = false, $rqd = false) {
        $m = $this->getMapper(); 
        if ($m) $mtime = $m->getMtime(); else $mtime = false;
        if ($rqd === false) $rqd = $this->_rqWithState;
        $dlId = $this->_datalink? $this->_datalink->getCacheId() : false;
        return md5(serialize(array($this->_instanceId, $rqd, $mtime, $d, $dlId)));
    }
    
    function _getCacheGroup() {
        return get_class($this).$this->_instanceId;
    }
    
    function _loadFromCache($d = false, $rqd = false) {
        $cache = $this->getApplication()->getCache();
        $cid = $this->_getCacheId($d, $rqd);
        return $cache->get($cid, $this->_getCacheGroup());
    }
    
    function _saveToCache($data, $d = false, $rqd = false) {
        $cid = $this->_getCacheId($d, $rqd);
        $cache = $this->getApplication()->getCache();
        return $cache->put($cid, $data, $this->_getCacheGroup());
    }
    
    function _getFormResponse() {
        if ($this->_formResponse === false) {
            $template = $this->getTemplate();
            $form = $this->getForm();
            if ($form) {
                $hr = new Ac_Legacy_Controller_Response_Html();
                $form->htmlResponse = $hr;
                $hr->content = $form->fetchPresentation();
                $this->_formResponse = $hr;
            } else {
                $hr = new Ac_Legacy_Controller_Response_Html();
                $this->_formResponse = $hr;
            }
        }
        return $this->_formResponse;
    }
    
    function _getFilterFormResponse() {
        if ($this->_filterFormResponse === false) {
            $template = $this->getTemplate();
            $form = $this->getFilterForm();
            $hr = new Ac_Legacy_Controller_Response_Html();
            $form->htmlResponse = $hr;
            $hr->content = $form->fetchPresentation();
            $this->_filterFormResponse = $hr;
        }
        return $this->_filterFormResponse;
    }
    
    // -------------------------------------- public methods of controller --------------------------------------------

    function isList() {
        return $this->_isList;
    }
    
    function isForm() {
        return $this->_isForm;
    }
    
    function executeList() {
        $this->_isList = true;
        $this->_stayOnProcessing = false;
        $template = $this->getTemplate();
        if ($this->_caching && $c = $this->_loadFromCache()) {
            $this->_response = unserialize($c);
        } else {
            $this->_response->content = $template->fetch('managerWrapper', array('managerList', true));
            if ($this->_caching) $this->_saveToCache(serialize($this->_response));
        }
    }
    
    function executeDetails($refresh = false) {
        $this->_isForm = true;
        $this->_stayOnProcessing = false;
        
        if ($refresh) {
            $form = $this->getForm();
            $record = $this->getRecord();
            $form->updateModel();
        }

        $template = $this->getTemplate();
        if (!$this->_processSubManagers()) return true;
        
        $rqd = isset($this->_rqWithState['form'])? $this->_rqWithState['form'] : false;
        if ($this->_caching && $c = $this->_loadFromCache('form', $rqd)) {
            $this->_formResponse = unserialize($c);
        } else {
            $this->_formResponse = $this->_getFormResponse();
            if ($this->_caching) $this->_saveToCache(serialize($this->_formResponse), 'form', $rqd);
        }
        
        if (!(isset($this->_response->hasToRedirect) && $this->_response->hasToRedirect)) 
            $this->_response->content = $template->fetch('managerWrapper', array('managerDetails', true));
    }
    
    function executeRefreshDetails() {
        return $this->executeDetails(true);
    }
    
    function executeProcessing() {
        $ok = false;
        
        $proc = null;
        $procResp = null;
        $respMode = null;
        $this->_stayOnProcessing = false;
        
        if (isset($this->_rqData['processing']) && is_string($this->_rqData['processing']) 
            && strlen($procName = $this->_rqData['processing'])) 
        {
            if (in_array($procName, $this->listProcessings())) {
                $proc = $this->getProcessing($procName);
                
                $procResp = $proc->getResponse();
                $respMode = $proc->managerResponseMode;

                if ($rep = $proc->getReport()) {
                    if (!$this->_report) {
                        $this->_report = new Ac_Admin_ReportEntry();
                    }
                    $this->_report->addChildEntry($rep);
                    $ok = true;
                }
                if ($proc->stayOn) {
                    $this->_stayOnProcessing = $this->_rqData['processing'];
                }
            }
        }
        
        $redir = true;
        
        if ($procResp) {
            if ($respMode == Ac_Admin_Processing::RESPONSE_AUTO) {
                if (strlen($procResp->content)) {
                    $respMode = $procResp->noWrap? Ac_Admin_Processing::RESPONSE_REPLACE : Ac_Admin_Processing::RESPONSE_WRAP;
                } else {
                    $respMode = Ac_Admin_Processing::RESPONSE_IGNORE;
                }
            }
            if ($respMode !== Ac_Admin_Processing::RESPONSE_IGNORE) {
                $redir = false;
                if ($respMode == Ac_Admin_Processing::RESPONSE_REPLACE) {
                    $this->_response = $procResp;
                } else {
                    $this->_templatePart = 'processing';
                    $this->_tplData['processing'] = $proc;
                    $this->_tplData['processingResponse'] = $procResp;
                }
            }
        }
        
        if ($redir && isset($this->_response->hasToRedirect)) $redir = false;

        if ($redir) {
            $this->_primaryKeys = array();
            $this->_record = null;
            $u = $this->getManagerUrl('list');
            $this->_response->hasToRedirect = $u->toString();
        }
        
            
    }
    
    function executeNew() {
        $this->_isForm = true;
        $this->_isNewRecord = true;
        $this->_stayOnProcessing = false;        
        
        $template = $this->getTemplate();
        $this->_response->content = $template->fetch('managerWrapper', array('managerDetails', true));
    }
    
    function executeSave($withAdd = false, $stayOnDetails = false) {
        $this->_isForm = true;
        $this->_stayOnProcessing = false;        
        
        $form = $this->getForm();
        $form->setSubmitted(true);
        $record = $this->getRecord();
        $form->updateModel();
        $this->callFeatures('onBind', $record);
        $this->lastRecordErrors = array();
        $subOk = $this->_processSubManagers(true);
        if ($subOk && $record->check() && $record->store()) {
            $this->_recordStored = true;
            $pk = $record->getPrimaryKey();
            $this->_record = null;
            $u = $this->getReturnUrl();
            if ($withAdd) {
                $params = array();
                if (strlen($u)) 
                    $params['returnUrl64'] = base64_encode($u);
                $u = $this->getManagerUrl('new', $params)->toString();
            } elseif (strlen($u)) {
                $this->_response->redirectUrl = $u;
                $u = false;
            } elseif ($stayOnDetails) {
                $u = $this->getManagerUrl('details', array('keys' => array($pk)))->toString();
            } else {
                $u = $this->getManagerUrl('list')->toString();
            }
            $this->_response->hasToRedirect = $u;
            
        } else {
        
            $this->lastRecordErrors = $this->_record->getErrors();
            $template = $this->getTemplate();
            $this->_response->content = $template->fetch('managerWrapper', array('managerDetails', true));
            
        }
    }
    
    function executeSaveAndAdd() {
        return $this->executeSave(true);
    }
    
    function executeApply() {
        return $this->executeSave(false, true);
    }
    
    function executeCancel() {
        $this->_record = null;
        $this->_stayOnProcessing = false;
        $u = $this->getReturnUrl();
        if (strlen($u)) {
            $this->_response->redirectUrl = ''.$u;
        } else {
            $u = $this->getManagerUrl('list');
            $this->_response->hasToRedirect = ''.$u;
        }
    }
    
    function executeGoBack() {
    }
    
    function executeGoForward() {
    }
    
    function execFallback($methodParamValue = null) {
    }
    
    // ------------------------------------------- form related methods -----------------------------------------------
    
    function isNewRecord() {
        return !$this->onlyRecord && ($this->getPrimaryKey() === false) && !$this->_recordStored && (isset($this->_rqData['new']) && $this->_rqData['new'] || $this->_isNewRecord);
    }
    
    protected function callFeatures($method, $_ = null) {
        $args = func_get_args();
        array_shift($args);
        foreach ($this->listFeatures() as $id) {
            $feat = $this->getFeature($id);
            if (method_exists($feat, $method)) call_user_func_array(array($feat, $method), $args);
        }
    }
    
    /**
     * Returns record that should be edited with the form
     * @return Ac_Model_Object
     */
    function getRecord() {
        if ($this->_record === false) {
            $this->_record = null;
            if ($this->isNewRecord()) {
                $m = $this->getMapper();
                $this->_record = $m->createRecord();
                $this->callFeatures('onCreate', $this->_record);
                if ($this->_datalink) $this->_datalink->setRecordDefaults($this->_record);
            } elseif (($pk = $this->getPrimaryKey()) !== false) {
                if ($this->onlyRecord) $this->_record = $this->onlyRecord;
                else {
                    $m = $this->getMapper();
                    $this->_record = $m->loadRecord($pk);
                    $this->callFeatures('onLoad', $this->_record);
                    if (!$this->_record) $this->_record = null; else {
                        if ($this->_datalink && !$this->_datalink->canProcessRecord($this->_record))
                            $this->_record = null;
                    }
                }
            }
        }
        return $this->_record;
    }
    
    // -------------------------------------- record processing related methods ------------------------------------------
    
    /**
     * Checks whether this record can be processed with the form or other processing settings.
     * TODO: connect method implementation to the user-supplied connector class that should be responsible for concrete Ac_Admin_Manager behavior
     * (perhaps Ac_Admin_Datalink) 
     *
     * @param Ac_Model_Object $record
     * @return bool
     */
    function canProcessRecord($record) {
        return true;        
    }
    
    /**
     * Returns first primary key provided or FALSE if no one is provided
     * @return mixed|bool
     */
    function getPrimaryKey() {
        $pks = $this->getPrimaryKeys();
        if (count($pks)) $res = $pks[0];
            else $res = false;
        if ($this->_isNewRecord && !$this->_recordStored) $res = false;
        return $res;
    }
    
    /**
     * Returns all primary keys provided
     * @return array
     */
    function getPrimaryKeys() {
        if ($this->_primaryKeys === false) {
            $res = array();
            if (isset($this->_rqData['keys']) && is_array($this->_rqData['keys'])) {
                $pkl = $this->getPrimaryKeyLength();
                foreach ($this->_rqData['keys'] as $key) {
                    if (is_string($key)) {
                        if ($pkl === 1) $res[$key] = $key;
                        else {
                            $u = @unserialize($key);
                            if (($u !== false) && is_array($u) && (count($u) === $pkl)) $res[$key] = $u;
                        }
                    }
                }
                $res = array_values($res);
            }
            $this->_primaryKeys = $res;
        } 
        return $this->_primaryKeys;
    }

    // -------------------------------------- request related methods -------------------------------------------------
    
    /**
     * @return Ac_Legacy_Controller_Context_Http
     */
    function getContext() {
        $res = parent::getContext();
        return $res;
    }
    
    
    /**
     * @param Ac_Model_Object & $record
     * @return Ac_Url
     */
    function getDetailsUrl($record) {
        $ctx = $this->_context->cloneObject();
        $key = $this->getStrPk($record);
        $ctx->setData(array('keys' => array($key), 'action' => 'details'));
        $res = $ctx->getUrl();
        return $res;
    }
    
    /**
     * @return Ac_Url
     */
    function getManagerUrl($action = false, $extraParams = array()) {
        $c = $this->_context->cloneObject();
        $d = $extraParams;
        /*
        if ($action === false) $action = $this->isForm()? 'details' : 'list';
        
        if ($this->isNewRecord()) $d['new'] = 1;
        elseif ($rec = $this->getRecord()) {
            $d['keys'][] = $this->getStrPk($rec);
        }
        */
        $s = $this->getStateData();
        $d = Ac_Util::m($s, $d, true);
        if (strlen($action)) $d[$this->_methodParamName] = $action;
        $c->setData($d);
        $u = $c->getUrl();
        return $u;
    }
    
    function getStateData() {
        $res = array();
        if ($this->_stayOnProcessing) {
            $res['action'] = 'processing';
            $res['processing'] = $this->_stayOnProcessing;
        } else {
            $res['action'] = $this->isForm()? 'details' : 'list';
        }
        if ($this->isNewRecord()) $res['new'] = 1;
        elseif (($res['action'] !== 'form') && $keys = $this->getPrimaryKeys()) {
            $res['keys'] = $keys; 
        }
        elseif ($rec = $this->getRecord()) {
            $res['keys'] = array($this->getStrPk($rec)); 
        }
        if ($this->_isForm && isset($this->_rqData['form']) && !$this->_recordStored) {
            $res['form'] = $this->_rqData['form'];
        }
        if ($res['action'] === 'list') {
            unset($res['keys']);
        }
        if (($u = $this->getReturnUrl()) !== null) {
            $res['returnUrl64'] = base64_encode($u);
        }
        
        if (isset($this->_rqData['filterForm'])) {
            $ff = $this->_rqData['filterForm'];
            if (is_array($ff)) {
                foreach ($ff as $k => $v) {
                    if (is_array($v)? !count($v) : !strlen($v)) unset($ff[$k]);
                }
            }
            $res['filterForm'] = $ff;
        }
        
        //if (isset($this->_rqData['pagination'])) $res['pagination'] = $this->_rqData['pagination'];
        //if (isset($this->_rqData['order'])) $res['order'] = $this->_rqData['order'];
        return $res;
    }
    
    // -------------------------------------- response generation methods ---------------------------------------------
    
    
    function doPopulateTemplate() {
        $this->_template->setManager($this);
    }
    
    function doPopulateResponse() {
        //$template = $this->getTemplate();
        //$this->_response->content = $template->fetch('manager');
    }
    
    function getProcessingParamName($mapped = false) {
        $res = 'processing';
        if ($mapped) $res = $this->_context->mapParam($res);
        return $res;
    }
    
    function getProcessingParamsParamName() {
        return $this->_context->mapParam('processingParams');
    }
    
    function getJsListControllerRef($onlyId = false) {
        $id = $this->_context->mapIdentifier($this->_instanceId.'_listController');
        if ($onlyId) return $id;
        $res = "window.AvanControllers.instances['".$id."']"; 
        return $res;
    }
    
    function getJsActionsControllerRef($onlyId = false) {
        $id = $this->_context->mapIdentifier($this->_instanceId.'_actionsController');
        if ($onlyId) return $id;
        $res = "window.AvanControllers.instances['".$id."']"; 
        return $res;
    }
    
    function getJsPaginationControllerRef($onlyId = false) {
        $id = $this->_context->mapIdentifier($this->_instanceId.'_paginationController');
        if ($onlyId) return $id;
        $res = "window.AvanControllers.instances['".$id."']"; 
        return $res;
    }
    
    function getJsFormControllerRef($onlyId = false) {
        $id = $this->_context->mapIdentifier($this->_instanceId.'_formController');
        if ($onlyId) return $id;
        $res = "window.AvanControllers.instances['".$id."']"; 
        return $res;
    }
    
    function getJsManagerControllerRef($onlyId = false) {
        $id = $this->_context->mapIdentifier($this->_instanceId.'_managerController');
        if ($onlyId) return $id;
        $res = "window.AvanControllers.instances['".$id."']"; 
        return $res;
    }
    
    function getManagerFormName() {
        if ($this->_context->isInForm) $res = $this->_context->isInForm;
            else $res = $this->_context->mapIdentifier('managerForm');
        return $res;
    }
    
    
    // -------------------------------------- associations related methods -------------------------------------------- 

    /**
     * @return Ac_Model_Datalink
     */
    function getDataLink() {
        return $this->_datalink;
    }
    
    /**
     * @param Ac_Model_Datalink $datalink
     */
    function setDatalink($datalink) {
        if (is_object($datalink)) {
            if (!is_a($datalink, 'Ac_Admin_Datalink')) 
                trigger_error ("\$datalink should be Ac_Admin_Datalink instance", E_USER_ERROR);
            $datalink->setManager($this);
        }
        $this->_datalink = $datalink;
    }
    
    /**
     * @return Ac_Form
     */
    function getForm() {
        if ($this->_form === false) {
            if ($record = $this->getRecord()) {
                $formConfig = $this->_computeFormConfig();
                if (isset($formConfig['class']) && strlen($formConfig['class'])) $class = $formConfig['class'];
                    else $class = 'Ac_Form';
                $formContext = $this->_createFormContext();
                $this->_form = new $class ($formContext, $formConfig, 'form');
                $this->_form->htmlResponse = $this->_response;
                if (!is_a($this->_form, 'Ac_Form')) trigger_error ("'{$class}' is not descendant of Ac_Form", E_USER_ERROR);
                foreach ($this->listFeatures() as $f) {
                    $feat = $this->getFeature($f);
                    $feat->applyToForm($this->_form);
                }
                if ($this->_datalink) $this->_datalink->onManagerFormCreated($this->_form);
            } else {
                $this->_form = null;
            }
        }
        return $this->_form;
    }
    
    /**
     * @return Ac_Form
     */
    function getFilterForm() {
        if ($this->_filterForm === false) {
            $formConfig = $this->_computeFilterFormConfig();
            if (isset($formConfig['controls']) && is_array($formConfig['controls']) && count($formConfig['controls'])) {
                if (isset($formConfig['class']) && strlen($formConfig['class'])) $class = $formConfig['class'];
                    else $class = 'Ac_Form';
                $formContext = $this->_createFilterFormContext();
                $this->_filterForm = new $class ($formContext, $formConfig, 'filterForm');
                $this->_filterForm->htmlResponse = $this->_response;
                if (!is_a($this->_filterForm, 'Ac_Form')) trigger_error ("'{$class}' is not descendant of Ac_Form", E_USER_ERROR);
                foreach ($this->listFeatures() as $f) {
                    $feat = $this->getFeature($f);
                    $feat->applyToFilterForm($this->_filterForm);
                }
            } else {
                $this->_filterForm = null;
            }
        }
        return $this->_filterForm;
    }
    
    /**
     * @return Ac_Admin_Template
     */
    function getTemplate() {
        $res = parent::getTemplate();
        return $res;
    }
    
    function listAllFeatureClasses() {
        $res = array_unique(array_merge(array('Ac_Admin_Feature_Default'), array_keys($this->featureSettings)));
        return $res;
    }
    
/**
     * @return Ac_Model_Mapper
     */
    function getMapper() {
        $res = Ac_Model_Mapper::getMapper($this->mapperClass);
        return $res;
    }
    
    /**
     * Retrieves prototype record
     * @return Ac_Model_Data
     */
    function getRecordPrototype() {
        if ($this->_recordPrototype === false) {
            if (!$this->mapperClass && !$this->onlyRecord) 
                trigger_error('Either $mapperClass or $onlyRecord properties must be set', E_USER_ERROR);
            if ($this->onlyRecord) { 
                if (is_a($this->onlyRecord, 'Ac_Model_Data')) $this->_recordPrototype = $this->onlyRecord;
                else trigger_error("$onlyRecord must be descendant of Ac_Model_Data", E_USER_ERROR);
            } else {
                $mapper = $this->getMapper();
                $this->_recordPrototype = $mapper->getPrototype();
            }
        } 
        return $this->_recordPrototype;
    }
    
    /**
     * @return Ac_Table 
     */
    function getTable() {
        if ($this->_table === false) {
            if ($this->_collectionCanBeUsed()) {
                $this->_table = new Ac_Table_Sequential($this->_getColumnSettings(), $this->_getRecordsCollection(), $this->getPagination(), array(), $this->_getRecordClass());
                //var_dump($this->_getRecordsCollection()->getStatementTail(), $this->_getRecordsCollection()->_extraColumns);
            } else {
                $records = array($this->onlyRecord);
                $this->_table = new Ac_Table ($this->_getColumnSettings(), $records, $this->_getCompatPagenav(), array(), $this->_getRecordClass());
            }
            $this->_table->_manager = $this;
            $p = $this->getPagination();
            $c = $this->_getRecordsCollection();
            $c->setLimits($p->getOffset(), $p->getNumberOfRecordsPerPage());
            $this->_preloadRelations();            
        }
        return $this->_table;
    }
    
    protected function cmpFeatures(Ac_Admin_Feature $feat1, Ac_Admin_Feature $feat2) {
        return $feat1->order - $feat2->order;
    }
    
    function listFeatures() {
        if ($this->_featureObjects === false) {
            $this->_featureObjects = array();
            if ($this->mapperClass) {
                $mapper = $this->getMapper();
                $info = $mapper->getInfo();
                if (is_array($info->adminFeatures)) {
                    $this->featureSettings = Ac_Util::m($info->adminFeatures, $this->featureSettings);
                }
            }
            foreach ($this->listAllFeatureClasses() as $fc) {
                if (isset($this->featureSettings[$fc]) && is_array($this->featureSettings[$fc])) $featureSettings = $this->featureSettings[$fc];
                    else $featureSettings = array(); 
                $class = $fc;
                if (isset($featureSettings['class']) && strlen($featureSettings['class'])) {
                    $class = $featureSettings['class'];
                }
                $feature = new $class ($this, $featureSettings);
                if ($feature->canBeApplied()) $this->_featureObjects[$fc] = $feature;
            }
            uasort($this->_featureObjects, array($this, 'cmpFeatures'));
        }
        return array_keys($this->_featureObjects);
    }
    
    /**
     * @return Ac_Admin_Feature
     */
    function getFeature($f) {
        if (!in_array($f, $this->listFeatures())) trigger_error("No such feature: '{$f}'", E_USER_ERROR);
        $res = $this->_featureObjects[$f];
        return $res;
    }
    
/**
     * Returns list of the available actions
     *
     * @param string $mode 'list'|'form'|'both'
     */
    function listActions (/* $mode = 'both' */) {
        if ($this->_actions === false) {
            $this->_actions = array();
            $actionPrototypes = false;
            foreach ($this->listFeatures() as $f)  {
                $feat = $this->getFeature($f);
                $a = $feat->getActions();
                foreach (array_keys($a) as $ak) {
                    if (is_a($a[$ak], 'Ac_Admin_Action'))  {
                        $action = $a[$ak];
                        if (!strlen($action->id)) $action->id = ''.$ak;
                        $this->_actions[$action->id] = $a[$ak];
                    } elseif (is_array($a[$ak])) {
                        if (!isset($actionPrototypes[$ak])) {
                            $actionPrototypes[$ak] = $a[$ak];
                        } else {
                            Ac_Util::ms($actionPrototypes[$ak], $a[$ak]);
                        }
                    } elseif ($a[$ak] === false || is_null($a[$ak])) {
                        unset($actionPrototypes[$ak]);
                    }
                }
            }
            if (!is_array($actionPrototypes)) $actionPrototypes = array();
            foreach ($actionPrototypes as $ak => $p) {
                $action = Ac_Admin_Action::factory($p);
                if ($action->formOnly && !$this->isForm()) continue;
                if ($action->listOnly && !$this->isList()) continue;
                if (!strlen($action->id)) $action->id = ''.$ak;
                $this->_actions[$action->id] = $action; 
            }
            foreach ($this->_actions as $a) {
                $a->setManager($this);
            }
        }
        return array_keys($this->_actions);
    }
    
    function getAction ($id) {
        if (!in_array($id, $this->listActions())) trigger_error ("No such action: '{$id}'", E_USER_ERROR);
        $res = $this->_actions[$id];
        return $res;
    }
    
    /**
     * @return Ac_Admin_Pagination
     */
    function getPagination() {
        if ($this->_pagination === false) {
            $context = $this->getContext();
            $pgContext = $context->spawn('pagination');
            $this->_pagination = new Ac_Admin_Pagination($pgContext, array(), $this->_instanceId.'_pagination');
            if ($coll = $this->_getRecordsCollection()) {
                if (!$this->dontCount)
                    $this->_pagination->totalRecords = $coll->countRecords();
            } else {
                $this->_pagination->totalRecords = 1;
            }
        }
        return $this->_pagination;
    }

    
    function listProcessings() {
        if ($this->_processings === false) {
            $this->_processings = array();
            foreach ($this->listFeatures() as $f) {
                $feat = $this->getFeature($f);
                foreach (array_keys($fps = $feat->getProcessings()) as $i) {
                    if (!isset($this->_processings[$i]))
                        $this->_processings[$i] = $fps[$i];
                    else Ac_Util::ms($this->_processings[$i], $fps[$i]);
                }
            }
        }
        return array_keys($this->_processings);
    }
    
    
    function listSubManagers() {
        if ($this->_subManagers === false) {
            $this->_subManagers = array();
            if ($this->allowSubManagers && $this->getRecord() && $this->getRecord()->isPersistent()) {
                foreach ($this->listFeatures() as $f) {

                    $feat = $this->getFeature($f);
                    $smc = $feat->getSubManagersConfig();
                    if (is_array($this->allowSubManagers)) {
                        foreach (array_keys($smc) as $k) {
                            if (isset($smc[$k]['mapperClass']) && !in_array($smc[$k]['mapperClass'], $this->allowSubManagers))
                                unset($smc[$k]);
                        }
                    }
                    if (is_array($this->prohibitSubsystems)) foreach ($this->prohibitSubsystems as $subsystemPrefix) {
                        foreach (array_keys($smc) as $k) {
                            if (isset($smc[$k]['mapperClass']) && !strncmp($smc[$k]['mapperClass'], $subsystemPrefix, strlen($subsystemPrefix) ))
                                unset($smc[$k]);
                            else {
                                if (!isset($smc[$k]['prohibitSubsystems']) || !is_array($smc[$k]['prohibitSubsystems']))
                                    $smc[$k]['prohibitSubsystems'] = array();
                                Ac_Util::ms($smc[$k]['prohibitSubsystems'], $this->prohibitSubsystems);
                            }
                        }
                    }
                    Ac_Util::ms($this->_subManagers, $smc);   
                }
            }
            $allSubManagers = $this->_subManagers;
            $this->_subManagers = array();
            foreach (array_keys($allSubManagers) as $i => $sm) {
                if (is_array($allSubManagers[$sm]) || is_object($allSubManagers[$sm]))
                    $this->_subManagers[$i] = $allSubManagers[$sm];
            }
        }
        return array_keys($this->_subManagers);
    }
    
    /**
     * @param string $id
     * @return Ac_Admin_Manager
     */
    function getSubManager($id) {
        if (!in_array($id, $this->listSubManagers())) 
            trigger_error ("No such sub manager: {$id}", E_USER_ERROR);
        if (is_array($this->_subManagers[$id])) {
            $conf = $this->_subManagers[$id];
            $ctx = $this->_createSubManagerContext($id);
            if (isset($conf['mapperClass']) && strlen($conf['mapperClass'])) {
                $mapper = $this->application->getMapper($conf['mapperClass']);
                $conf = Ac_Util::m($mapper->getManagerConfig(), $conf);
            }
            if (isset($conf['class']) && strlen($conf['class'])) {
                $class = $conf['class'];
            } else $class = 'Ac_Admin_Manager';
            $sm = new $class ($ctx, $conf, $this->_instanceId.'_'.$id);
            $sm->setApplication($this->getApplication());
            $this->_subManagers[$id] = $sm;
            foreach ($this->listFeatures() as $f) {
                $feat = $this->getFeature($f);
                $feat->onSubManagerCreated($id, $this->_subManagers[$id], $conf);
            }
        }
        return $this->_subManagers[$id];
    }
    
    /**
     * @param string $p
     * @return Ac_Admin_Processing
     */
    function getProcessing($p) {
        if (!in_array($p, $this->listProcessings()))
            trigger_error ("No such processing: '{$p}'", E_USER_ERROR);
        if (is_array($this->_processings[$p])) {
            $prot = $this->_processings[$p];
            $prot['manager'] = $this;
            $prot['application'] = $this->application;
            if (isset($prot['class']) && strlen($prot['class'])) {
                $class = $prot['class'];
            } else {
                $class = 'Ac_Admin_Processing';
            }
            $ctx = $this->_createProcessingContext($p);
            $prc = new $class ($ctx, $prot, 'processing.'.$p);
            if ($this->onlyRecord) $prc->setRecords(array(& $this->onlyRecord));
                else $prc->setMapperClass($this->mapperClass);
            $this->_processings[$p] = $prc;
        }
        return $this->_processings[$p];
    }
    
    /**
     * Returns string representation of record's primary key
     *
     * @param Ac_Model_Record $record
     */
    function getStrPk($record) {
        if ($this->onlyRecord) $res = '1';
        else {
            if (is_array($pk = $record->getPrimaryKey())) $res = serialize($pk);
                else $res = $pk;
        }
        return $res;
    }
    
    function getPrimaryKeyLength() {
        if ($this->onlyRecord) $res = 1; 
        else {
            $mapper = $this->getMapper();
            $res = count($mapper->listPkFields());
        }
        return $res;
    }
    
    // --------------------------------------------------------- forms-related methods --------------------------------------------------
    
    function _computeFormConfig() {
        $rec = $this->getRecord();
        $res = array(
            'name' => 'form',
            'templateClass' => 'Ac_Form_Control_Template_Basic',
            'templatePart' => 'table',
            'performOwnSubmissionCheck' => true,
//            'childWrapperTemplateClass' => 'Ac_Form_Control_Template_Basic',
//            'childWrapperTemplatePart' => 'divWrapper',
            'controls' => array(),
            'model' => & $rec,
        );
        
        foreach ($this->listFeatures() as $n) {
            $f = $this->getFeature($n);
            $f->applyToFormSettings($res);
        }
        
        if($this->_datalink) $this->_datalink->onManagerFormPreset($res);
        
        return $res;
    }
    
    
    function _computeFilterFormConfig() {
        $res = array(
            'name' => 'filterForm',
            'templateClass' => 'Ac_Form_Control_Template_Basic',
            'templatePart' => 'simpleList',
            'performOwnSubmissionCheck' => true,
            'controls' => array(),
        );
        
        foreach ($this->listFeatures() as $n) {
            $f = $this->getFeature($n);
            $f->applyToFilterFormSettings($res);
        }
        
        return $res;
    }
    
    /**
     * @return Ac_Legacy_Controller_Context
     */
    function _createFormContext() {
        $res = $this->_context->spawn('form');
        if ($this->_context->isInForm) $res->isInForm = $this->_context->isInForm; 
            else $res->isInForm = $this->getManagerFormName();
        return $res;
    }
    
    /**
     * @return Ac_Legacy_Controller_Context
     */
    function _createFilterFormContext() {
        $res = $this->_context->spawn('filterForm');
        if ($this->_context->isInForm) $res->isInForm = $this->_context->isInForm; 
            else $res->isInForm = $this->getManagerFormName();
        return $res;
    }
    
    // ------------------------------------------------ methods to be called from the template -----------------------------------------
    
    function getSingleCaption() {
        if ($this->singleCaption === false) {
            if ($m = $this->getMapper()) {
                $i = $m->getInfo();
                $res = $i->singleCaption;
            }
        } else $res = $this->singleCaption;
        return $res;
    }
    
    function getPluralCaption() {
        if ($this->pluralCaption === false) {
            if ($m = $this->getMapper()) {
                $i = $m->getInfo();
                $res = $i->pluralCaption;
            }
        } else $res = $this->pluralCaption;
        return $res;
    }
    
    function getFormTitle() {
        if ($this->showRecordTitle) {
            $res = $this->getSingleCaption();
            if ($rec = $this->getRecord()) {
                $ttl = false;
                if ($this->recordTitleField) $ttl = $rec->getField($this->recordTitleField);
                else {
                    if ($m = $this->getMapper()) {
                        if ($tf = $m->getTitleFieldName()) {
                            $ttl = $rec->getField($tf);
                        }
                    }
                }
                if (strlen($ttl)) $res .= ': '.$ttl;
            } else {
            }
        } else $res = $this->getSingleCaption();
        return $res;
    }
    
    // ---------------------------------------------------- protected supplementary methods --------------------------------------------
    
    function _processSubManagers($forceSave = false) {
        $subRedirect = false;
        $allState = array();
        $res = true;
        foreach ($this->listSubManagers() as $i) {
            $s = $this->getSubManager($i);
            if ($forceSave && $s->getMethodParamValue() == 'details') {
                $methodName = 'executeApply';
            } else {
                $methodName = false;
            }
            $r = $s->getResponse($methodName);
            if ($forceSave && $methodName !== false) {
                if ($s->lastRecordErrors) {
                    $this->_tplData['activeSubManagerId'] = $i;
                    $res = false;
                } else {
                    $s->_response = false;
                    $r = $s->getResponse('executeDetails');
                }
            }
            $this->_response->mergeWithResponse($r);
            if (!$forceSave && isset($r->hasToRedirect) && $r->hasToRedirect) {
                $subRedirect = $r->hasToRedirect;
            }
            elseif ($r->noHtml || $r->noWrap) {
                $subRedirect = false;
                break;
            }
            $allState['sm_'.$i] = $s->getStateData();
        }
        if ($subRedirect) {
            $u = new Ac_Url($subRedirect);
            $mu = $this->getManagerUrl('details', $allState);
            $mu->query = Ac_Util::m($mu->query, $u->query, true);
            $this->_response->hasToRedirect = $mu->toString();
        }
        return $res;
    }
    
    /**
     * @param string $id Sub manager id
     * @return Ac_Legacy_Controller_Context_Http
     */
    function _createSubManagerContext($id) {
        $ctx = $this->_context->spawn('sm_'.$id);
        return $ctx;
    }
    
	/**
     * @return Ac_Legacy_Controller_Context_Http
     */
    function _createProcessingContext($processingId) {
        $ctx = $this->_context->spawn('processingParams');
        $url = $this->getManagerUrl('processing', array('processing' => $processingId));
        $d = $ctx->getData();
        if (!isset($d['keys'])) {
            if ($keys = $this->getPrimaryKeys()) {
                $d['keys'] = $keys;
            }
        }
        $ctx->setData($d);
        $ctx->setBaseUrl($url);
        return $ctx;
    }
    
    function _getPreloadRelations() {
        if ($this->_preloadRelations === false) {
            $this->_preloadRelations = array();
            foreach ($this->listFeatures() as $f) {
                $feat = $this->getFeature($f);
                $this->_preloadRelations = Ac_Util::array_unique(array_merge($this->_preloadRelations, $feat->getPreloadRelations()));
            }
        }
        return $this->_preloadRelations;
    }
    
    function _getColumnSettings() {
        if ($this->_columnSettings === false) {
            $this->_columnSettings = array();
            foreach ($this->listFeatures() as $f) {
                $feat = $this->getFeature($f);
                $cs = $feat->getColumnSettings();
                foreach ($cs as $c => $s) {  
                    // subsequent features CAN override columns of previous ones
                    if (!isset($this->_columnSettings[$c])) {
                        $this->_columnSettings[$c] = $s;
                        $this->_columnSettings[$c]['manager'] = $this;
                    }
                    else Ac_Util::ms($this->_columnSettings[$c], $s);
                }
            }
            if ($this->_datalink) $this->_datalink->onManagerColumnsPreset($this->_columnSettings);
        }
        return $this->_columnSettings;
    }

    /**
     * @return bool
     */
    function _collectionCanBeUsed() {
        return !$this->onlyRecord;
    }
    
    /**
     * @return Ac_Sql_Filter
     * @access protected
     */
    function _getSqlFilter() {
        if ($this->_sqlFilter === false) {
            $allFilters = array();
            foreach ($this->listFeatures() as $i) {
                $f = $this->getFeature($i);
                $allFilters = array_merge($allFilters, $f->getFilterPrototypes());
            }
            if (!count($allFilters)) $this->_sqlFilter = null;
            else {
                if (0 && (count($allFilters) == 1)) {
                    $filterSettings = Ac_Util::array_values(array_slice($allFilters, 0, 1));
                    $filterSettings = $allFilters[0];
                } else {
                    $filterSettings = array(
                        'class' => 'Ac_Sql_Filter_Multiple',
                        'filters' => $allFilters, 
                        'applied' => true,
                    );
                }
                $this->_sqlFilter = Ac_Sql_Part::factory($filterSettings, 'Ac_Sql_Filter');
            }
        }
        return $this->_sqlFilter;
    }
    
    /**
     * @return Ac_Sql_Order
     * @access protected
     */
    function _getSqlOrder() {
        if ($this->_sqlOrder === false) {
            $allOrders = array();
            foreach ($this->listFeatures() as $i) {
                $f = $this->getFeature($i);
                $allOrders = array_merge($allOrders, $f->getOrderPrototypes());
            }
            if (!count($allOrders)) $this->_sqlOrder = null;
            else {
                if (0 && (count($allOrders) == 1)) {
                    $orderSettings = Ac_Util::array_values(array_slice($allOrders, 0, 1));
                    $orderSettings = $orderSettings[0];
                } else {
                    $orderSettings = array(
                        'class' => 'Ac_Sql_Order_Multiple',
                        'orders' => $allOrders, 
                        'applied' => 1,
                    );
                }
                $this->_sqlOrder = Ac_Sql_Part::factory($orderSettings, 'Ac_Sql_Order');
            }
        }
        return $this->_sqlOrder;
    }
    
    /**
     * @return Ac_Sql_Select
     * @access protected
     */
    function _getSqlSelect() {
        if ($this->_sqlSelect === false) {
            $options = array();
            foreach ($this->listFeatures() as $i) {
                $feat = $this->getFeature($i);
                Ac_Util::ms($options, $feat->getSqlSelectSettings());
            }
            $f = $this->_getSqlFilter();
            $o = $this->_getSqlOrder();
            $ff = $this->getFilterForm();
            $sqlDb = $this->application->getDb();
            $options = Ac_Util::m($this->getMapper()->getSqlSelectPrototype('t'), $options);
            $this->_sqlSelect = new Ac_Sql_Select($sqlDb, $options);
            if ($ff) {
                $fVal = $ff->getValue();
            } else {
                $fVal = array();
            }
            if ($f) {
                $f->bind($fVal);
                $f->applyToSelect($this->_sqlSelect);
            }
            if ($o) {
                $o->bind($fVal);
                $o->applyToSelect($this->_sqlSelect);
            }
            // find missing filters and apply them to respective parts
            $filtersInManager = $f? $f->listFilters() : array();
            $filtersInForm = array_keys($fVal);
            $filtersInMapper = $this->_sqlSelect->listParts();
            $filtersToSet = array_diff(array_intersect($filtersInForm, $filtersInMapper), $filtersInManager);
            foreach ($filtersToSet as $partName) {
                $this->_sqlSelect->getPart($partName)->bind($fVal[$partName]);
            }
            $this->callFeatures('onCreateSqlSelect', $this->_sqlSelect);
        }
        return $this->_sqlSelect;
    }
    
    /**
     * @return Ac_Sql_Select
     */
    function getSqlSelect() {
        return $this->_getSqlSelect();
    }
    
    function _preloadRelations() {
        if ($pr = $this->_getPreloadRelations()) {
            $myRecs = array();
            while ($rec = $this->_recordsCollection->getNext()) {
                $myRecs[] = $rec;
            }
            $this->getMapper()->preloadRelations($myRecs, $pr);
        }
    }
    
    /**
     * @return Ac_Model_Collection
     */
    function _getRecordsCollection() {
        return $this->getRecordsCollection();
    }
    
    /**
     * @return Ac_Model_Collection
     */
    function getRecordsCollection() {
        if ($this->_recordsCollection === false) {
            if ($this->_collectionCanBeUsed()) {
                $this->_recordsCollection = new Ac_Model_Collection($this->mapperClass, false, $this->_getWhere(), $this->_getOrder(), $this->_getJoins(), $this->_getExtraColumns());
                    if (strlen($h = $this->_getHaving())) $this->_recordsCollection->setHaving ($h);
                $this->_recordsCollection->setDistinct();
                $this->_recordsCollection->setGroupBy($this->_getGroupBy());
                foreach ($this->listFeatures() as $i) $this->getFeature($i)->onCollectionCreated ($this->_recordsCollection);
            } else {
                $this->_recordsCollection = null;
            }
        }
        
        return $this->_recordsCollection;
    }
    
    function _getRecordClass() {
        if ($this->_recordClass === false) {
            if ($this->onlyRecord) {
                if (is_a($this->onlyRecord, 'Ac_Model_Data')) $this->_recordClass = get_class($this->onlyRecord);
                    else trigger_error ('$onlyRecord property must be set to a valid Ac_Model_Data instance', E_USER_ERROR);
            } else {
                 $mapper = $this->getMapper();
                 $this->_recordClass = $mapper->recordClass;
            }
        }
        return $this->_recordClass;
    }
    
    function _getWhere() {
        if ($this->_datalink && ($c = $this->_datalink->getSqlCriteria())) {
            $res = $c;
        } else {
            $res = false;
        }
        if (($s = $this->_getSqlSelect()) && (strlen($w = $s->getWhereClause(false)))) {
            $res = strlen($res)? "($res) AND ($w)" : $w;
        }
        return $res;
    }
    
    function _getOrder() {
        $s = $this->_getSqlSelect();
        if (($s = $this->_getSqlSelect()) && (strlen($w = $s->getOrderByClause(false)))) {
            $res = $w;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function _getHaving() {
        $s = $this->_getSqlSelect();
        if (($s = $this->_getSqlSelect()) && (strlen($w = $s->getHavingClause(false)))) {
            $res = $w;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function _getGroupBy() {
        if (($s = $this->_getSqlSelect()) && (strlen($w = $s->getGroupByClause(false)))) {
            $res = $w;
        } else {
            $res = false;
        }
        return $res;
    }
    
    function _getJoins() {
        if ($this->_datalink && $j = $this->_datalink->getSqlExtraJoins()) {
            $res = $j;
        } else {
            $res = false;
        }
        if (($sel = $this->_getSqlSelect()) && (strlen($s = $sel->getFromClause(false, array('t'))))) {
            if (strlen($res)) $res = $res . ' '. $s;
                else $res = $s;
        }
        return $res;
    }
    
    function _getExtraColumns() {
        $res = array();
        if (($s = $this->_getSqlSelect())) $res = array_merge($res, $s->getColumnsList(false, false, true));
        return $res;
    }
    
    function setConfigService(Ac_Admin_ManagerConfigService $configService) {
        $this->configService = $configService;
    }

    /**
     * @return Ac_Admin_ManagerConfigService
     */
    function getConfigService() {
        if ($this->configService === false) {
            if (!$this->application || (!$this->configService = $this->application->getService('managerConfigService', true))) {
                $this->configService = new Ac_Admin_ManagerConfigService($this->getApplication());
            }
        }
        return $this->configService;
    }    
    
    function getReturnUrl() {
        if ($this->_returnUrl === false) {
            $this->_returnUrl = $this->getContext()->getData('returnUrl', null);
            if (is_null($this->_returnUrl)) {
                $tmp = $this->getContext()->getData('returnUrl64', null);
                if (strlen($tmp) && strlen($tmp = base64_decode($tmp))) 
                    $this->_returnUrl = $tmp;
            }
        }
        return $this->_returnUrl;
    }
    
}

