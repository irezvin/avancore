<?php

/**
 * CSV reader that supports multiple "Sections"
 * Each section can have its own header ($sections must be two-dimensional array if \$useHeader === false)
 *
 * Section header is a row which has only one, first, non-empty (non-whitespace) cell (other cells
 * may be missing, have no characters or have only whitespace characters) which matches $this->sectionHeaderRegex.
 * 
 * $this->sectionHeaderRegex must have 1st capturing subpattern that will contain name of section.
 * 
 * By default section header must have [sectionName] format.
 * 
 * If $this->requireEmptyRowBetweenSections is TRUE, every non-first section must be preceeded with one or more
 * 'empty' rows (having no cells or having any number of empty or whitespace-only cells).
 * 
 * If no section header will be found at beginning of CSV file, $defaultSectionId will be assumed.
 * 
 * getResult() always returns associative array (sectionId => array of rows).
 * 
 * Ae_Util_Csv_Sections DOES NOT check any validity of data in CSV document, presence of mandatory section header
 * or only defined sections and/or columns - it's up to application to handle that.
 * 
 */
class Ac_Util_Csv_Sections extends Ac_Util_Csv {
    
    var $sectionHeaderRegex = "/^\s*\[([^\]]+)\]\s*$/";
    
    var $requireEmptyRowBetweenSections = true;
    
    var $defaultSectionId = 0;
    
    var $sectionHeaders = array();
    
    protected $currentSectionId = false;
    
    protected $numEmptyRows = 0;
    
    protected $tmpUseHeader = false;
    
    function hasPublicVars() {
        return true;
    }
    
    function pushLine($line) {
        $this->tmpUseHeader = $this->useHeader;
        if ($this->useHeader && $this->currentSectionId === false) $this->useHeader = false;
        $res = parent::pushLine($line);
        if (is_array($res)) { // Line is pushed
            // pop it back
            array_pop($this->result);
            list ($sectionId, $isEmpty) = $this->analyzeResultRow($res);
            // It is section header
            if (strlen($sectionId) && ($this->currentSectionId === false || !$this->requireEmptyRowBetweenSections || $this->numEmptyRows > 0)) {
                $this->beginSection($sectionId);
                $this->numEmptyRows = 0;
                $res = false;
            } 
            // It is empty row between sections
            elseif ($isEmpty && $this->requireEmptyRowBetweenSections && $this->currentSectionId !== false) { 
                $this->numEmptyRows++;
                $res = false;
            // It is data row
            } else {
                $this->numEmptyRows = 0;
                if ($this->currentSectionId === false) $this->beginSection($this->defaultSectionId);
                if (!isset($this->result[$this->currentSectionId])) {
                    $this->result[$this->currentSectionId] = array();
                }
                $this->result[$this->currentSectionId][] = $res;
                $tmp = array($this->currentSectionId => $res);
                $res = $tmp;
            }
        }
        $this->useHeader = $this->tmpUseHeader;
        return $res;
    }
    
    /**
     * @param array $row 
     * @return array($sectionId, $isEmpty)
     */
    protected function analyzeResultRow(array $row) {
        $n = count($row);
        $sectionId = false;
        $isEmpty = false;
        if ($n) {
            $first = trim(array_shift($row));
            if (strlen($first)) {
                if (preg_match($this->sectionHeaderRegex, $first, $matches)) {
                    $sectionId = $matches[1];
                } else {
                    array_unshift($row, $first); // this will force while() loop to stop 
                }
            }
            while (count($row) && !strlen(trim(array_shift($row)))) {};
            if (!count($row)) $isEmpty = !strlen($sectionId); // no section id & no non-empty cells
        } else {
            $isEmpty = true;
        }
        return array($sectionId, $isEmpty);
            
    }
    
    protected function beginSection($sectionId) {
        $this->header = false;
        if (!$this->useHeader && isset($this->sectionHeaders[$sectionId]) && is_array($this->sectionHeaders[$sectionId])) {
            $this->header = $this->sectionHeaders[$sectionId];
        }
        $this->currentSectionId = $sectionId;
        $this->numEmptyRows = 0;
        $this->useHeader = $this->tmpUseHeader;
    }
    
    function reset() {
        parent::reset();
        $this->currentSectionId = false;
        $this->numEmptyRows = 0;
        $this->useHeader = $this->tmpUseHeader;
        $this->tmpUseHeader = false;
    }
    
}