<?php

// TODO: translate language strings
class Ac_Admin_Processing_SaveOrder extends Ac_Admin_Processing {
	
	var $fieldName = 'ordering';
	
    var $paramName = 'ordering';
	
	/**
	 * @var Ac_Admin_Manager
	 */
	var $manager = false;
	
	function getOrderingValue(Ac_Model_Data $record) {
		$res = $this->getContext()->getData(array($this->paramName, $this->manager->getIdentifierOf($record)), false);
        $meta = $record->getPropertyInfo($this->paramName);
        $allowFloat = $meta->dataType === 'float';
		if (is_numeric($res)) $res = $allowFloat? (float) $res : (int) $res;
			else $res = false;
		return $res;
	}
	
	function _doProcessRecord($record) {
		
        if ($this->manager->getMapper() instanceof Ac_I_BatchAwareMapper) $this->manager->getMapper()->beginBatchChange(array($this->fieldName));
        $record->load();
		$value = $record->getField($this->fieldName);
		$newValue = $this->getOrderingValue($record);
		if ($newValue !== false && $newValue != $value) {
		$record->setField($this->fieldName, $newValue);
			if ($record->store()) {
				$this->reportRecord($record, 'Запись перемещена', 'message', false, false);
			} else {
				$this->reportRecord($record, 'Не удалось переместить запись', 'error', false, false);
			}
		}
		
        if ($this->manager->getMapper() instanceof Ac_I_BatchAwareMapper) $this->manager->getMapper()->endBatchChange();
		
	}
	
}
