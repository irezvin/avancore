<?php

class Ac_Admin_Processing extends Ac_Legacy_Controller {

    var $title = 'Processing';

    var $header = '{title} started';
    
    /**
     * @var Ac_Admin_ReportEntry
     */
    var $_report = false;
    
    /**
     * @var array|false Records
     */
    var $_records = false;
    
    /**
     * @var Ac_Model_Collection
     */
    var $_recordsCollection = false;
    
    /**
     * @var array|false
     */
    var $_recordKeys = false;
    
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
    
    function doInitProperties($options = array()) {
        if (!strcasecmp(get_class($this), 'ae_admin_processing'))
            trigger_error ("Attempt to instantiate abstract class", E_USER_ERROR);
        
        if (isset($options['mapperClass'])) $this->setMapperClass($options['mapperClass']);
        if (isset($options['datalink'])) {
            trigger_error ("Cannot set datalink in the constructor - call setDatalink() after Ac_Admin_Processing instantiation", E_USER_ERROR);
        }
        
        if (isset($options['recordKeys'])) $this->setRecordKeys($options['recordKeys']);
        elseif (isset($options['records'])) $this->setRecords($options['records']);
        elseif (isset($options['recordsCollection'])) $this->setRecordsCollection($options['recordsCollection']);
        elseif (isset($options['noRecords']) && $options['noRecords']) $this->setNoRecords($options['noRecords']);        
    }
    
    function setMapperClass($mapperClass) {
        $this->setNoRecords();
        $this->_noRecords = $this->_extRecords = false;
        $this->_mapperClass = $mapperClass;
    }
    
    function setDatalink(& $datalink) {
        $this->_datalink = false;
        if ($datalink) {
            if (!is_a($datalink, 'Ac_Admin_Datalink')) 
                trigger_error("\$datalink must be instance of Ac_Admin_Datalink", E_USER_ERROR);
            else {
                $this->_datalink = & $datalink;
                $this->_datalink->setProcessing($this);
            }
        }
    }
    
    // ----------------------------- request handling ----------------------------
    
    function executeProcess() {
        if ($this->_doBeforeProcess() !== false) {
            $coll = & $this->_doGetRecordsCollection();
            while ($rec = & $coll->getNext()) {
                if ($this->canProcessRecord($rec))
                    if ($this->_doProcessRecord($rec) === false) break;
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
    function _doProcessRecord(& $record) {
        
    }
    
    // ------------------------------ record access ------------------------------

    function setNoRecords() {
        $this->_records = $this->_recordsCollection = $this->_recordKeys = $this->_mapperClass = false;
        $this->_noRecords = true;
        $this->_extRecords = true;
    }
    
    function setRecordKeys($recordKeys = array(), $mapperClass = false) {
        $this->setNoRecords();
        $this->_noRecords = false;
        if ($mapperClass !== false) $this->_mapperClass = $mapperClass;
        $this->_recordKeys = $recordKeys;
    }
    
    function setRecords($records = array()) {
        $this->setNoRecords();
        $this->_noRecords = false;
        $this->_records = $records;
    }
    
    function setRecordsCollection(& $recordsCollection) {
        $this->setNoRecords();
        $this->_noRecords = false;
        $this->_recordsCollection = & $recordsCollection;
    }
    
    function canProcessRecord(& $record) {
        if ($this->_datalink) $res = $this->_datalink->canProcessRecord($record);
            else $res = true;
        return $res;
    }
    
    
    /**
     * Returns record source that will actually be used to access records 
     * @return Ac_Model_Collection
     */
    function & _doGetRecordsCollection() {
        if ($this->_recordsCollection === false) {
            if (!$this->_extRecords) $this->_recordKeys = $this->_getRecordKeysFromRequest();
            if ($this->_noRecords || is_array($this->_recordKeys) && !count($this->_recordKeys)) $this->_records = array();
            if (!$this->_recordsCollection) {
                $this->_recordsCollection = new Ac_Model_Collection();
                // Most straightforward way
                if (is_array($this->_records)) {
                    $this->_recordsCollection->setRecords($this->_records);
                } elseif (is_array($this->_recordKeys)) {
                    $this->_getMapper();
                    $this->_recordsCollection->setKeys($this->_recordKeys, $this->_mapperClass);
                    $this->_applyDatalinkToCollection();
                } else {
                    $this->_getMapper();
                    $this->_recordsCollection->useMapper($this->_mapperClass);
                    $this->_applyDatalinkToCollection();
                }
            }
        }
        return $this->_recordsCollection;
    }
    
    /**
     * @return Ac_Model_Mapper
     */
    function & _getMapper() {
        if ($this->_mapper === false) {
            if (!strlen($this->_mapperClass)) 
                trigger_error ('Mapper class not provided - call setMapperClass() or provide \'mapperClass\' entry in the config array first', E_USER_ERROR);
            $this->_mapper = & Ac_Model_Mapper::getMapper($this->_mapperClass);
        }
        return $this->_mapper;
    }
    
    function _getRecordKeysFromRequest() {
        $res = array();
        if (isset($this->_rqWithState['keys']) && is_array($this->_rqWithState['keys'])) {
            $mapper = & $this->_getMapper();
            $pkl = count($mapper->listPkFields());
            foreach ($this->_rqWithState['keys'] as $key) {
                if (is_string($key)) {
                    if ($pkl === 1) $res[] = $key;
                    else {
                        $u = @unserialize($key);
                        if (($u !== false) && is_array($u) && (count($u) === $pkl)) $res[] = $u;
                    }
                }
            }
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
    function setReport (& $reportEntry) {
        if ($reportEntry && !is_a($reportEntry, 'Ac_Admin_ReportEntry'))
            trigger_error ("\$reportEntry should be instance of Ac_Admin_ReportEntry");
        
        $this->_report = & $reportEntry;
    }
    
    /**
     * @return Ac_Admin_ReportEntry
     */
    function getReport() {
        return $this->_report;    
    }
    
    function addToReport (& $reportEntry) {
        if (!$this->_report) $this->_report = & $this->_createReportHeader();
        $this->_report->addChildEntry($reportEntry); 
    }
    
    /**
     * @param Ac_Model_Data $record
     * @param string $description
     * @param string $type message|warning|error Message type
     */
    function reportRecord(& $record, $description, $type = 'message', $dateTime = false, $isAvailable = true) {
        if (is_a($record, 'Ac_Model_Object') && ($m = & $record->getMapper()) && ($tf = $m->getTitleFieldName())) $title = $record->getField($tf);
        elseif ($record->hasProperty('title') && ($p = & $record->getPropertyInfo('title')) && !$p->assocClass && !$p->plural) 
            $title = $record->getField('title');
        else $title = false;
        if (is_a($record, 'Ac_Model_Object')) $key = $record->getPrimaryKey();
            else $key = false; 
        $e = new Ac_Admin_ReportEntry($description, $type, $dateTime, $key, $title, $isAvailable && $key !== false);
        $this->addToReport($e);
    }
    
    /**
     * If record has errors, add report entry with record's errors description
     * @param bool $forceCheck 
     * @param Ac_Model_Data $record
     */
    function reportRecordErrors(& $record, $forceCheck = false) {
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
    function & _createReportHeader() {
        $header = str_replace('{title}', $this->title, $this->header);
        $res = new Ac_Admin_ReportEntry($header, 'message', time());
        return $res;
    }
    
    
}

?>