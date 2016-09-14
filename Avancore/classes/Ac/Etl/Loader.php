<?php

/**
 * Loads parsed data into the temporary tables 
 */

class Ac_Etl_Loader extends Ac_Prototyped {
    
    /**
     * @var Ac_Etl_Import
     */
    protected $import = false;
    
    protected $id = false;
    
    protected $lineNo = -1;
    
    protected $began = false;
    
    protected $checkOnly = false;
 
    protected $columns = array();
    
    protected $columnLogs = array();
    
    protected $numReceivedLines = 0;
    
    protected $numReceivedCells = 0;
    
    protected $finishLog = null;
    
    /**
     * @var Ac_Param_Source_Array
     */
    protected $source = false;
    
    function hasPublicVars() {
        return true;
    }
    
    function setImport(Ac_Etl_Import $import) {
        $this->import = $import;
        if ($this->columns) foreach ($this->columns as $col) $col->setImport($import);
    }
    
    /**
     * @return Ac_Etl_Import
     */
    function getImport() {
        return $this->import;
    }
    
    function getLineNo() {
        return $this->lineNo;
    }
    
    protected function ensureImport() {
        if (!$this->import) throw new Exception("setImport() first");
    }
    
    function pushLine(array $line, $lineNo = null) {
        $this->ensureImport();
        if (is_numeric($lineNo) && (int) $lineNo >= 0) $this->lineNo = (int) $lineNo;
            else $this->lineNo++;
        $this->analyze($line);
    }
    
    function pushLines(array $lines, $lineNo = null, $beginAndEnd = false) {
        if ($beginAndEnd && !$this->began) $this->begin();
        $this->ensureImport();
        if (count($lines)) {
            if (is_numeric($lineNo) && (int) $lineNo >= 0) $this->lineNo = (int) $lineNo - 1;
            foreach ($lines as $line) {
                $this->lineNo++;
                $this->analyze($line);
            }
        }
        if ($beginAndEnd) $this->end();
    }
    
    protected function analyze(array $line) {
        if (!$this->source) $this->source = new Ac_Param_Source_Array();
        $this->source->setData($line);
        $destRecords = array();
        foreach ($this->getColumns() as $i => $column) {
            $this->columnLogs[$i]->beginProfiling();
            $errors = array();
            /* @var $column Ac_Etl_Column */
            if ($column->apply($this->source, $destRecords, $errors)) {
            } else {
                if (!$errors) $errors = 'Unspecified error';
                $this->import->logMessage ($errors, Ac_Etl_I_Logger::logTypeError, $this->lineNo, $column->getId());
            }
            $this->numReceivedCells++;
            $this->columnLogs[$i]->endProfiling();
        }
        $this->numReceivedLines++;        
        $this->putDestRecordsIntoTables($destRecords);
    }
    
    protected function putDestRecordsIntoTables($destRecords) {
        $byTables = array();
        foreach ($destRecords as $table => $records) {
            foreach ($records as $i => $cv) {
                foreach ($cv as $col => $val) {
                    $byTables[$table][$i][$col] = $val;
                }
            }
        }
        foreach ($byTables as $table => $records) {
            $this->import->getTable($table)->appendRecords($records, $this->lineNo);
        }
    }

    function setCheckOnly($checkOnly) {
        $this->checkOnly = (bool) $checkOnly;
    }

    function getCheckOnly() {
        return $this->checkOnly;
    }    
    
    function begin($checkOnly = null) {
        if ($checkOnly !== null) $this->checkOnly = $checkOnly;
        if ($this->began) throw new Exception("begin() was already called without end()");
        $this->import->logItem(new Ac_Etl_Log_Item("Loader '{$this->id}' started", 'debug', array('chrono')));
        $this->finishLog = new Ac_Etl_Log_Item("Loader '{$this->id}' finished", 'debug', array('chrono'), array(), true);
        $this->ensureImport();
        $this->numReceivedLines = 0;
        $this->numReceivedCells = 0;
        foreach (array_keys($this->columns) as $i) {
            $this->columnLogs[$i] = new Ac_Etl_Log_Item("Column {$i} processing", 'profile', array("load/columns/{$this->id}"));
        }
        foreach ($this->import->getTables() as $table) {
            /** @var $table Ac_Etl_Table */
            $table->beginLoadData($this->checkOnly);
        }
        $this->began = true;
    }

    function end() {
        $this->ensureImport();
        $this->began = false;
        if ($logger = $this->import->getLogger()) {
            foreach ($this->columnLogs as $item) {
                if ($this->numReceivedCells) $item->message .= '; Tavg='.(round($item->spentTime/$this->numReceivedCells, 4)).'; Mavg='.round($item->spentMemory/$this->numReceivedCells, 4);
                $logger->acceptItem($item);
            }
        }
        foreach ($this->import->getTables() as $table) {
            /** @var $table Ac_Etl_Table */
            $table->endLoadData();
        }
        $this->import->logItem($this->finishLog);
    }
    
    function getBegan() {
        return $this->began;
    }
    
    function setColumns (array $columns) {
        $this->columns = array();
        $this->addColumns($columns);
    }
    
    function addColumns (array $columns) {
        $defs = array();
        if ($this->import) $defs["import"] = $this->import;
        $this->columns = array_merge($this->columns, Ac_Prototyped::factoryCollection($columns, 'Ac_Etl_Column', $defs, 'id', true, true));
    }
    
    /**
     * @return Ac_Etl_Column
     */
    function getColumn($id, $throw = false) {
        $res = null;
        if (isset($this->columns[$id])) $res = $this->columns[$id];
        elseif ($throw) throw new Exception("No such column: {$id}");
        return $res;
    }
    
    function getColumns() {
        return $this->columns;
    }
    
    function setId($id) {
        if ($this->id !== false && $this->id !== $id) throw new Exception("can setId() only once");
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }    
    
    function getNumReceivedLines() {
        return $this->numReceivedLines;
    }
    
    function getNumReceivedCells() {
        return $this->numReceivedCells;
    }
    
}