<?php

class Ac_Finder_Criterion extends Ac_Prototyped {

    /**
     * @var Ac_Finder
     */
    protected $finder = false;
    
    protected $name = false;
    
    protected $enabled = false;
    
    protected $value = false;
    
    protected $enableOnSetValue = true;
    
    protected $selectPrototypeExtra = array();
    
    protected $selectPartPrototype = array();
    
    protected $selectPartKey = false;

    protected $reportToFinderWhenChanged = false;
    
    protected $dontReport = false;

    /**
     * IDs of groups of criterions that can't be enabled along with this criterion
     * (except ones listed in $groupOnIds)
     * @var array
     */
    protected $groupIds = array();

    /**
     * IDs of groups of criterions that explicitly CAN be enabled along with
     * this criterion (exception from $groupIds)
     * @var array
     */
    protected $groupOnIds = array();
    
    function setEnabled($enabled) {
        $enabled = (bool) $enabled;
        if ($enabled !== ($oldEnabled = $this->enabled)) {
            $this->enabled = $enabled;
            if ($this->reportToFinderWhenChanged && !$this->dontReport && $this->finder)
                $this->finder->notifyCriterionChanged($this, $this->value, $oldEnabled);
        }
    }
        
    function getEnabled() {
        return $this->enabled;
    }

    function setValue($value = null) {
        
        $oldValue = $this->value;
        $oldEnabled = $this->enabled;
        
        $this->value = $value;
        
        if ($this->enableOnSetValue) {
            $this->dontReport = true;
            $this->setEnabled(!is_null($value));
            $this->dontReport = false;
        }
        
        if ($this->reportToFinderWhenChanged && $this->finder)
            $this->finder->notifyCriterionChanged($this, $oldValue, $oldEnabled);
            
    }

    function getValue() {
        return $this->value;
    }       
     
    function setFinder(Ac_Finder $finder) {
        $this->finder = $finder;
    }

    /**
     * @return Ac_Finder
     */
    function getFinder() {
        return $this->finder;
    }

    function setName($name) {
        if ($this->name !== false) throw new Exception("\$name property can be set only once");
        $this->name = $name;
    }

    function getName() {
        return $this->name;
    }
    
    function cleanToDelete() {
        $this->finder = false;
    }

    function reset() {
        $this->setValue();
    }
    
    /**
     * Template method
     * @param array $prototype
     */
    function applyToSelectPrototype(array & $prototype) {
        if ($this->selectPrototypeExtra) Ac_Util::ms($prototype, $this->selectPrototypeExtra);
    }
    
    /**
     * Template method
     * @param Ac_Sql_Select $select
     */
    function applyToSelect(Ac_Sql_Select & $select) { 
        $selectPart = Ac_Sql_Part::factory($this->selectPartPrototype);
        $selectPart->bind($this->value);
        $selectPart->applyToSelect($select);
    }
    
    protected function setSelectPrototypeExtra($selectPrototypeExtra) {
        $this->selectPrototypeExtra = $selectPrototypeExtra;
    }

    protected function setSelectPartKey($selectPartKey) {
        $this->selectPartKey = $selectPartKey;
    }    
    
    function setSelectPartPrototype($selectPartPrototype) {
        $this->selectPartPrototype = $selectPartPrototype;
    }
    
    function getSelectPartPrototype() {
        return $this->selectPartPrototype;
    }
    
    protected function setReportToFinderWhenChanged($reportToFinderWhenChanged) {
        $this->reportToFinderWhenChanged = $reportToFinderWhenChanged;
    }

    function getReportToFinderWhenChanged() {
        return $this->reportToFinderWhenChanged;
    }

    function setGroupIds($groupIds) {
        $groupIds = Ac_Util::toArray($groupIds);
        if (array_diff($groupIds, $this->groupIds) || array_diff($this->groupIds, $groupIds)) {
            $oldGroupIds = $this->groupIds;
            $this->groupIds = $groupIds;
            $this->finder->notifyCriterionGroupIdsChanged($this, $groupIds, $oldGroupIds);
        }
    }

    function getGroupIds() {
        return $this->groupIds;
    }

    function setGroupOnIds($groupOnIds) {
        $this->groupOnIds = Ac_Util::toArray($groupOnIds);
    }

    function getGroupOnIds() {
        return $this->groupOnIds;
    }

    /**
     * Check whether this criterion can be enabled when other criterion with $otherGroupIds is enabled too
     * @see Ac_Finder_Criterion::groupIds
     * @see Ac_Finder_Criterion::otherGroupIds
     * 
     * @param mixed $otherGroupIds
     * @return bool
     */
    function canBeEnabled($otherGroupIds) {
        $otherGroupIds = Ac_Util::toArray($otherGroupIds);
        $res = !array_intersect($this->groupIds, $otherGroupIds)
               || array_intersect($this->groupOnIds, $otherGroupIds);
        return $res;
    }

}

?>