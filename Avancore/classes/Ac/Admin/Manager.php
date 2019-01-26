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
     * @var Ac_Sql_Select
     */
    protected $sqlSelect = false;
    
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
    
    /**
     * @var string
     */
    var $_recordClass = false;
    
    /**
     * @var Ac_Model_Collection_Mapper
     */
    protected $collection = false;
    
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
    
    var $_recordIdentifiers = false;
    
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
        
        if (($u = $this->getReturnUrl()) && !isset($this->_response->hasToRedirect)) {
            $this->_context->setData('returnUrl', null);
            $this->_context->setData('returnUrl64', null);
            $this->_returnUrl = null;
            $this->_response->hasToRedirect = $this->preserveFragment($u);
            $redir = false;
        }

        if ($redir) {
            $this->_recordIdentifiers = array();
            $this->_record = null;
            $u = $this->getManagerUrl('list');
            $this->_response->hasToRedirect = $this->preserveFragment($u->toString());
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
                $u = $this->getManagerUrl('list', array('keys' => null))->toString();
            }
            $this->_response->hasToRedirect = $this->preserveFragment($u);
            
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
            $this->_response->redirectUrl = $this->preserveFragment(''.$u);
        } else {
            $u = $this->getManagerUrl('list');
            $this->_response->hasToRedirect = $this->preserveFragment(''.$u);
        }
    }
    
    protected function preserveFragment($u) {
        if ($f = $this->_context->getData('_fragment')) {
            $u .= '#'.$f;
        }
        return $u;
     }
    
    function executeGoBack() {
    }
    
    function executeGoForward() {
    }
    
    function execFallback($methodParamValue = null) {
    }
    
    // ------------------------------------------- form related methods -----------------------------------------------
    
    function isNewRecord() {
        return !$this->onlyRecord && ($this->getRecordIdentifier() === false) && !$this->_recordStored && (isset($this->_rqData['new']) && $this->_rqData['new'] || $this->_isNewRecord);
    }
    
    protected function callFeatures($method, $_ = null) {
        $args = func_get_args();
        array_shift($args);
        return $this->callFeaturesA($method, $args);
    }
    
    protected function callFeaturesA($method, array $args = array()) {
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
            } elseif (($id = $this->getRecordIdentifier()) !== false) {
                if ($this->onlyRecord) $this->_record = $this->onlyRecord;
                else {
                    $m = $this->getMapper();
                    $this->_record = $m->loadRecord($id);
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
    function getRecordIdentifier() {
        $ids = $this->getRecordIdentifiers();
        if (count($ids)) $res = $ids[0];
            else $res = false;
        if ($this->_isNewRecord && !$this->_recordStored) $res = false;
        return $res;
    }
    
    /**
     * Returns all primary keys provided
     * @return array
     */
    function getRecordIdentifiers() {
        if ($this->_recordIdentifiers === false) {
            $res = array();
            if (isset($this->_rqData['keys']) && is_array($this->_rqData['keys'])) {
                $res = array_unique($this->_rqData['keys']);
                        }
            $this->_recordIdentifiers = $res;
                    }
        return $this->_recordIdentifiers;
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
        $id = $this->getIdentifierOf($record);
        $ctx->setData(array('keys' => array($id), 'action' => 'details'));
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
            $d['keys'][] = $this->getIdentifierOf($rec);
        }
        */
        $skipKeys = (($this->_stayOnProcessing && $action === false) || $action === 'list');
        $s = $this->getStateData(false, $skipKeys);
        $d = Ac_Util::m($s, $d, true);
        if (strlen($action)) $d[$this->_methodParamName] = $action;
        $c->setData($d);
        $u = $c->getUrl();
        return $u;
    }
    
    function getStateData($withFilterForm = null, $skipKeys = false) {
        $res = array();
        if ($this->_stayOnProcessing) {
            $res['action'] = 'processing';
            $res['processing'] = $this->_stayOnProcessing;
        } else {
            $res['action'] = $this->isForm()? 'details' : 'list';
        }
        if ($this->isNewRecord()) $res['new'] = 1;
        elseif (($res['action'] !== 'form') && $keys = $this->getRecordIdentifiers()) {
            $res['keys'] = $keys; 
        }
        elseif ($rec = $this->getRecord()) {
            $res['keys'][] = $this->getIdentifierOf($rec);
        }
        if ($this->_isForm && isset($this->_rqData['form']) && !$this->_recordStored) {
            $res['form'] = $this->_rqData['form'];
        }
        if ($res['action'] === 'list' || $skipKeys) {
            unset($res['keys']);
        }
        if (($u = $this->getReturnUrl()) !== null) {
            $res['returnUrl64'] = base64_encode($u);
        }
        
        if ($this->_context->getData('_fragment')) {
            $res['_fragment'] = $this->_context->getData('_fragment');
        }
        
        if ($withFilterForm === null) {
            $withFilterForm = $res['action'] !== 'list';
        }
        
        if ($withFilterForm) {
            if (isset($this->_rqData['filterForm'])) {
                $ff = $this->_rqData['filterForm'];
                if (is_array($ff)) {
                    foreach ($ff as $k => $v) {
                        if (is_array($v)? !count($v) : !strlen($v)) unset($ff[$k]);
                    }
                }
                $res['filterForm'] = $ff;
            }
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
    
    function getManagerParamsParamName() {
        return $this->_context->mapParam('');
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
            $this->_table = new Ac_Table_Sequential($this->_getColumnSettings(), $this->getRecordsCollection(), $this->getPagination(), array(), $this->_getRecordClass());
            $this->_table->_manager = $this;
            $p = $this->getPagination();
            $c = $this->getRecordsCollection();
            $c->close();
            $c->setOffset($p->getOffset());
            $c->setLimit($p->getNumberOfRecordsPerPage());
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
            if ($coll = $this->getRecordsCollection()) {
                if (!$this->dontCount)
                    $this->_pagination->totalRecords = $coll->getCount();
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
    function getIdentifierOf($record) {
        if ($this->onlyRecord) $res = '1';
            else $res = $this->getMapper()->getIdentifier($record);
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
            if ($u->fragment) $mu->fragment = $u->fragment;
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
            if ($keys = $this->getRecordIdentifiers()) {
                $d['keys'] = $keys;
            }
        }
        $ctx->setData($d);
        $ctx->setBaseUrl($url);
        return $ctx;
    }

    function getPreloadRelations() {
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
     * @return Ac_Sql_Select
     * @access protected
     */
    function getSqlSelect() {
        if ($this->sqlSelect === false) {
            $proto = array(
                'parts' => array(),
            );
            $usesSqlSelect = false;
            foreach ($this->listFeatures() as $i) {
                $feat = $this->getFeature($i);
                if ($feat->usesSqlSelect()) {
                    $usesSqlSelect = true;
                    Ac_Util::ms($proto, $feat->getSqlSelectSettings());
                    Ac_Util::ms($proto['parts'], $feat->getFilterPrototypes());
                    Ac_Util::ms($proto['parts'], $feat->getOrderPrototypes());
                }
            }
            if (!$proto['parts']) unset($proto['parts']);
            if ($usesSqlSelect) {
                $this->sqlSelect = $this->getMapper()->createSqlSelect($proto);
                $this->callFeatures('onCreateSqlSelect', $this->sqlSelect);
            } else {
                $this->sqlSelect = null;
            }
        }
        return $this->sqlSelect;
    }
    
    /**
     * @deprecated - use getSqlSelect()
     * @return Ac_Sql_Select
     */
    function _getSqlSelect() {
        return $this->getSqlSelect();
    }
    
    function _preloadRelations() {
        if ($pr = $this->getPreloadRelations()) {
            $myRecs = $this->collection->fetchGroup();
            $this->getMapper()->preloadRelations($myRecs, $pr);
        }
    }
    
    /**
     * @deprecated - use getCollection()
     * @return Ac_Model_Collection_Mapper
     */
    function getRecordsCollection() {
        return $this->getCollection();
    }
        
    /**
     * @return Ac_Model_Collection_Mapper
     */
    function getCollection() {
        if ($this->collection === false) {
            if ($this->onlyRecord) {
                $proto = array(
                    'class' => 'Ac_Model_Collection_Array',
                    'items' => array($this->onlyRecord),
                );
            } else {
                $this->collection = $this->createBareCollection();
                foreach ($this->listFeatures() as $i) $this->getFeature($i)->onCollectionCreated ($this->collection);
                list($query, $sort) = $this->getQueryAndSort($this->collection);
                if ($query) $this->collection->setQuery(Ac_Util::m($this->collection->getQuery(), $query));
                if ($sort === null) {
                    $sort = $this->getMapper()->getDefaultSort();
                    if ($sort) $this->collection->setSort($sort);
                }
                else if ($sort !== null) $this->collection->setSort($sort);
            }
        }
        return $this->collection;
    }

    /**
     * Creates the collection without applied filters
     * @return Ac_Model_Collection_Mapper
     */
    function createBareCollection() {
        $proto = array(
            'class' => 'Ac_Model_Collection_Mapper',
            'application' => $this->getApplication(),
            'mapper' => $this->getMapper(),
            'query' => array(),
            'autoOpen' => true,
        );

        $searchPrototype = array();

        foreach ($this->listFeatures() as $i) {
            Ac_Util::ms($searchPrototype, $this->getFeature($i)->getSearchSettings());
        }

        if ($searchPrototype) {
            $proto['searchPrototype'] = $searchPrototype;
        }
        if ($s = $this->getSqlSelect()) {
            $proto['class'] = 'Ac_Model_Collection_SqlMapper';
            $proto['sqlSelect'] = $s;
        }

        if ($this->_datalink && $qp = $this->_datalink->getQueryPart()) {
            $proto['query'] = $qp;
        }
                
        $this->callFeaturesA('onBeforeCreateCollection', array(& $proto));
        $res = Ac_Prototyped::factory($proto, 'Ac_Model_Collection_Abstract');
        return $res;
        
    }
    
    protected function filterNonEmpty($v) {
        if (is_array($v)) return (bool) array_filter($v, array($this, 'filterNonEmpty'));
        return $v !== null && $v !== false && $v !== '';
    }
    
    protected function getQueryAndSort(Ac_Model_Collection_Mapper $collection) {
        $f = $this->getFilterForm();
        $query = array();
        $sort = false;
        $val = array();
        $sort = null;
        if ($f) {
            $val = $f->getValue();
            $val = array_filter($val, array($this, 'filterNonEmpty'));
            // TODO: improve this ugly kludge with criteria enumeration
            $searchCrit = $collection->listPossibleCriteria();
            $sortCrit = $collection->listPossibleSortCriteria();
            $query = array_intersect_key($val, array_flip($searchCrit));
            if (isset($val['sort']) && in_array($val['sort'], $sortCrit) && !isset($query['sort'])) {
                $sort = $val['sort'];
            }
        } else {
            $searchCrit = array();
            $sortCrit = array();
        }
        $this->callFeaturesA('onGetQueryAndSort', array(& $query, & $sort, $val, $searchCrit, $sortCrit));
        return array($query, $sort);
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
    
    function setConfigService(Ac_Admin_ManagerConfigService $configService) {
        $this->configService = $configService;
    }

    /**
     * @return Ac_Admin_ManagerConfigService
     */
    function getConfigService() {
        return $this->application->getComponent(Ac_Application::CORE_COMPONENT_MANAGER_CONFIG_SERVICE);
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

