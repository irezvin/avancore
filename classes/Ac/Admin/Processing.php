<?php

class Ac_Admin_Processing extends Ac_Controller {
    
    const DEFAULT_RECORDS_NONE = 0;

    const DEFAULT_RECORDS_ALL = 1;
    
    const DEFAULT_RECORDS_BY_FILTERS = 2;
    
    /**
     * How $this->manager should deal with $this->response
     * Default behaviour: if there is response content, choose between RESPONSE_WRAP (if noWrap == false) or RESPONSE_REPLACE, otherwise RESPONSE_IGNORE
     */
    const RESPONSE_AUTO = 0;
    
    /**
     * How $this->manager should deal with $this->response: ignore it and redirect to list (default action)
     */
    const RESPONSE_IGNORE = 1;
    
    /**
     * How $this->manager should deal with $this->response: show it in a conventional wrapper
     */
    const RESPONSE_WRAP = 2;
    
    /**
     * How $this->manager should deal with $this->response: show it instead of own response
     */
    const RESPONSE_REPLACE = 3;
    
    var $id = null;
    
    var $managerResponseMode = self::RESPONSE_AUTO;
    
    var $title = 'Processing';

    var $header = '{title} started';
    
    var $defaultToAllRecords = Ac_Admin_Processing::DEFAULT_RECORDS_NONE;
    
    /**
     * @var bool
     * Whether manager should reference to this processing during the next request
     */
    var $stayOn = false;
    
    /**
     * @var bool
     * Return to record details if processing was called from record details
     */
    var $returnToDetails = true;
    
    /**
     * @var Ac_Admin_ReportEntry
     */
    var $_report = false;
    
    /**
     * @var array|false Records
     */
    var $_records = false;
    
    /**
     * @var Ac_Legacy_Collection
     */
    var $_recordsCollection = false;
    
    /**
     * @var array|false
     */
    var $_recordIdentifiers = false;
    
    /**
     * @var string|false
     */
    var $_mapperClass = false;
    
    var $_noRecords = false;
    
    var $_extRecords = false;
    
    var $_defaultMethodName = 'process';

    /**
     * @var Ac_Model_Mapper
     */
    var $_mapper = false;
    
    /**
     * @var Ac_Admin_Datalink
     */
    var $_datalink = false;
    
    /**
     * @var Ac_Admin_Manager
     */
    protected $manager = false;
    
    function setId($id) {
        // because setId() is already present in Ac_Controller which we inherit
        parent::setId($id);
        $this->id = $id;
    }
    
    function setManager(Ac_Admin_Manager $manager) {
        $this->manager = $manager;
        if ($manager->getApp()) $this->app = $manager->getApp();
    }
    
    function setMapperClass($mapperClass) {
        $this->setNoRecords();
        $this->_noRecords = $this->_extRecords = false;
        $this->_mapperClass = $mapperClass;
    }
    
    function setDatalink($datalink) {
        $this->_datalink = false;
        if ($datalink) {
            if (!is_a($datalink, 'Ac_Admin_Datalink')) 
                trigger_error("\$datalink must be instance of Ac_Admin_Datalink", E_USER_ERROR);
            else {
                $this->_datalink = $datalink;
                $this->_datalink->setProcessing($this);
            }
        }
    }
    
    // ----------------------------- request handling ----------------------------
    
    function executeProcess() {
        if ($this->_doBeforeProcess() !== false) {
            $coll = $this->_doGetRecordsCollection();
            while ($rec = $coll->getNext()) {
                if ($this->canProcessRecord($rec))
                    if ($this->_doProcessRecord($rec) === false) break;
                $rec->cleanupMembers();
                unset($rec);
            }
            $this->_doAfterProcess();
        }
    }
    
    // ----------------------------- template methods ----------------------------
    
    /**
     * @access protected
     */
    function _doBeforeProcess() {
        
    }
    
    /**
     * @access protected
     */
    function _doAfterProcess() {
        
    }
    
    /**
     * @access protected
     * @param Ac_Model_Object $record
     */
    function _doProcessRecord($record) {
        
    }
    
    // ------------------------------ record access ------------------------------

    function setNoRecords() {
        $this->_records = $this->_recordsCollection = $this->_recordIdentifiers = $this->_mapperClass = false;
        $this->_noRecords = true;
        $this->_extRecords = true;
    }
    
    function setRecordKeys($recordKeys = array(), $mapperClass = false) {
        $this->setNoRecords();
        $this->_noRecords = false;
        if ($mapperClass !== false) $this->_mapperClass = $mapperClass;
        $this->_recordIdentifiers = $recordKeys;
    }
    
    function setRecords($records = array()) {
        $this->setNoRecords();
        $this->_noRecords = false;
        $this->_records = $records;
    }
    
    function setRecordsCollection($recordsCollection) {
        $this->setNoRecords();
        $this->_noRecords = false;
        $this->_recordsCollection = $recordsCollection;
    }
    
    function canProcessRecord($record) {
        if ($this->_datalink) $res = $this->_datalink->canProcessRecord($record);
            else $res = true;
        return $res;
    }
    
    
    /**
     * Returns record source that will actually be used to access records 
     * @return Ac_Model_Collection_Abstract
     */
    function _doGetRecordsCollection() {
        if ($this->_recordsCollection === false) {
            if (!$this->_extRecords) $this->_recordIdentifiers = $this->_getIdentifiersFromRequest();
            if ($this->_noRecords || ($this->defaultToAllRecords == self::DEFAULT_RECORDS_NONE && is_array($this->_recordIdentifiers) && !count($this->_recordIdentifiers))) $this->_records = array();
            if (!$this->_recordsCollection) {
                if (is_array($this->_records)) {
                    $this->_recordsCollection = new Ac_Model_Collection_Array(array('items' => $this->_records));
                } else {
                    if ($this->defaultToAllRecords == self::DEFAULT_RECORDS_BY_FILTERS) {
                        $this->_recordsCollection = clone $this->manager->getRecordsCollection ($this->manager->mapperClass);
                    } else {
                        $this->_recordsCollection = $this->manager->createBareCollection();
                    }
                    // Filter to selected records, if applicable
                    if (is_array($this->_recordIdentifiers) && $this->_recordIdentifiers) {
                        $this->_recordsCollection->setKeys($this->_recordIdentifiers, $this->_mapperClass);
                        $this->_applyDatalinkToCollection();
                    }
                }
            }
        }
        return $this->_recordsCollection;
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function _getMapper() {
        if ($this->_mapper === false) {
            if (!strlen($this->_mapperClass)) 
                trigger_error ('Mapper class not provided - call setMapperClass() or provide \'mapperClass\' entry in the config array first', E_USER_ERROR);
            $this->_mapper = Ac_Model_Mapper::getMapper($this->_mapperClass);
        }
        return $this->_mapper;
    }
    
    /**
     * Deprecated in favor of _getIdentifiersFromRequest
     * @deprecated
     */
    function _getRecordKeysFromRequest() {
        return $this->_getIdentifiersFromRequest();
    }
    
    function _getIdentifiersFromRequest() {
        $res = array();
        if (isset($this->_rqWithState['keys']) && is_array($this->_rqWithState['keys'])) {
            $mapper = $this->_getMapper();
            $res = array_unique($this->_rqWithState['keys']);
        }
        return $res;
    }
    
    /**
     * @access private
     */
    function _applyDatalinkToCollection() {
        if ($this->_datalink && $this->_recordsCollection) {
            if (strlen($where = $this->_datalink->getSqlCriteria())) $this->_recordsCollection->addWhere($where);
            if ($j = $this->_datalink->getSqlExtraJoins()) $this->_recordsCollection->addJoin($j);
        }
    }
    
    // -------------------------------- reporting --------------------------------
    
    function hasMessages($type = false) {
        return $this->_report && $this->_report->hasChildEntries($type, true);
    }

    /**
     * @param Ac_Admin_ReportEntry $reportEntry
     */
    function setReport ($reportEntry) {
        if ($reportEntry && !is_a($reportEntry, 'Ac_Admin_ReportEntry'))
            trigger_error ("\$reportEntry should be instance of Ac_Admin_ReportEntry");
        
        $this->_report = $reportEntry;
    }
    
    /**
     * @return Ac_Admin_ReportEntry
     */
    function getReport() {
        return $this->_report;    
    }
    
    function addToReport ($reportEntry) {
        if (!$this->_report) $this->_report = $this->_createReportHeader();
        $this->_report->addChildEntry($reportEntry); 
    }
    
    /**
     * @param Ac_Model_Data $record
     * @param string $description
     * @param string $type message|warning|error Message type
     */
    function reportRecord($record, $description, $type = 'message', $dateTime = false, $isAvailable = true) {
        if (is_a($record, 'Ac_Model_Object') && ($m = $record->getMapper()) && ($tf = $m->getTitleFieldName())) $title = $record->getField($tf);
        elseif ($record->hasProperty('title') && ($p = $record->getPropertyInfo('title')) && !$p->assocClass && !$p->plural) 
            $title = $record->getField('title');
        else $title = false;
        if (is_a($record, 'Ac_Model_Object')) $id = $record->getPrimaryKey();
            else $id = false; 
        $e = new Ac_Admin_ReportEntry($description, $type, $dateTime, $id, $title, $isAvailable && $id !== false);
        $this->addToReport($e);
    }
    
    /**
     * If record has errors, add report entry with record's errors description
     * @param bool $forceCheck 
     * @param Ac_Model_Data $record
     */
    function reportRecordErrors($record, $forceCheck = false) {
        if ($forceCheck || $record->isChecked()) {
            if ($errs = $record->getErrors()) {
                $this->reportRecord($record, nl2br(htmlspecialchars(Ac_Util::implode_r("\n", $errs))), 'error');
            }
        }
    }
    
    /**
     * @return Ac_Admin_ReportEntry
     * @access protected
     */
    function _createReportHeader() {
        $header = str_replace('{title}', $this->title, $this->header);
        $res = new Ac_Admin_ReportEntry($header, 'message', time());
        return $res;
    }
    
    
}

