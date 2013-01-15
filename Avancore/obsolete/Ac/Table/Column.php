<?php

/**
 * Base class for rendering table columns in the record's admin listing. It's instances must be used in conjunction with 
 * Ac_Table class. 
 * 
 * By default this column renders same-named property of the record
 *  
 * @package Avancore Lite
 * @copyright Copyright &copy; 2007, Ilya Rezvin, Avansite (I.Rezvin@avansite.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

class Ac_Table_Column {
    
    /**
     * Link to parent table Ac_Table object that this column belongs to
     * @var Ac_Table
     * @access private
     */
    var $_table = false;
   
    /**
     * @var string Name of current column
     * @access private
     */
    var $_name = false;
    
    /**
     * @var array Settings of the column (meaning of keys mostly depend in descendants)
     * 
     * These keys can be used (see corresponding accessor functions):
     * 'cellAttribs', 'headerAttribs', 'fieldName'
     */
    var $settings = false;
    
    /**
     * @var string Default text to put into the legend
     */
    var $defaultHint = false;
    
    /**
     * @var string Text to put into the empty cells
     */
    var $nullText = "&nbsp;";
    
    /**
     * @var Ac_Model_Property
     */
    var $_staticMeta = false;
    
    /**
     * Whether get{$fieldName} funtion call is preferred over Ac_Model_Data::getFoo()
     * @var bool|string
     */
    var $useGetter = false;
    
    var $takeField = false;
    
    var $useAeDataFacilities = true;
    
    var $staticAttribs = true;

    var $fieldName = false;
    /*
    var $fieldIsMethod = false;
    
    var $returnsHtml = false; 
    */
    var $methodParams = array();
    
    var $hidden = false;
    
    var $disabled = false;
    
    var $_cellAttribs = false;
    
    var $order = false;
    
    var $isAutoOrder = false;
    
    var $decorator = false;
    
    /**
     * @param object table Parent Ac_Table instance
     * @param string name of the column
     * @param array settings
     */
    function Ac_Table_Column(& $table, $name, $settings = array()) {
        $this->_table = & $table;
        $this->_name = $name;
        $this->settings = $settings;
        
        foreach (array_diff(array_keys(get_object_vars($this)), array('settings', 'defaultHint', 'nullText')) as $propName) {
            if (($propName{0} != '_')  && isset($settings[$propName])) $this->$propName = & $settings[$propName];
        }

        if (!$this->fieldName) $this->fieldName = $this->_name;
        if (!$this->disabled) $this->getStaticPropertyInfo();
        $this->doOnCreate();
    }

    function doOnCreate() {
    }
    
    function getHeaderRowCount() {
        return 1;
    }
    
    function getHeaderRowspan($rowCount, $rowNo = 1) {
        if ($this->getHeaderRowCount() == 1) 
            $res = $rowCount;
        elseif ($this->getHeaderRowCount() < $rowCount && $this->getHeaderRowCount() == $rowCount - 1) 
            $res = $rowCount - $this->getHeaderRowCount();
        else $res = 1;
        
        return $res;
    }
    
    /**
     * @return array Array representation of the cell TD tag's attributes (for Ac_Util::mkAttribs function)  
     */
    function getCellAttribs() {
        if (isset($this->settings['cellAttribs'])) $res = $this->settings['cellAttribs'];
            else $res = array('align' => 'left');
        return $res;
    }
    
    function updateAttribs() {
        $this->_cellAttribs = Ac_Util::mkAttribs($this->getCellAttribs()); 
    }
    
    /**
     * @return array Array representation of the header TH tag's attributes (for Ac_Util::mkAttribs function)  
     */
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('align' => 'left');
            
        $res['rowspan'] = $this->getHeaderRowspan($rowCount, $rowNo);        
       
        return $res;
    }
    
    function getRecordProperty(& $record, $fieldName) {
        static $getters = array();
        $rc = get_class($record);
        if (!isset($getters[$rc]) || !isset($getters[$rc][$fieldName])) $getters[$rc][$fieldName] = $this->determineGetter($record, $fieldName);
        $g = $getters[$rc][$fieldName];
        $res = $this->$g[0] ($record, $fieldName, $g[1]);
        
        if ($this->decorator) $res = Ac_Decorator::decorate($this->decorator, $res, $this->decorator);
        
        return $res;
    }

    function determineGetter(& $record, $fn) {
        if (is_a($record, 'Ac_Model_Data') && $this->useAeDataFacilities && $this->_staticMeta) return array('getWithGetField', null);
        elseif (method_exists($record, $getterName = 'get'.ucfirst($fn))) {
            if ($fn == $this->fieldName && is_array($this->methodParams) && count($this->methodParams))
            return array('getWithGetterParams', array($getterName, $this->methodParams));
            else return array('getWithGetter', $getterName);
        }
        elseif (method_exists($record, 'getProperty')) return array('getWithGetProperty', null);
        elseif (isset($record->$fn)) return array('getWithObjectVar', null);
        elseif (isset($record->_otherValues[$fn])) return array('getWithOtherValues', null);
        else return array('getWithNull', null);
    }

    function getWithGetField(& $record, $fn, $p) {
        return $record->getField($fn);
    }

    function getWithGetter(& $record, $fn, $p) {
        return $record->$p();
    }

    function getWithGetterParams(& $record, $fn, $p) {
        return call_user_func_array(array(& $record, $p[0]), $p[1]);
    }

    function getWithGetProperty(& $record, $fn, $p) {
        return $record->getProperty($fn);
    }

    function getWithObjectVar(& $record, $fn, $p) {
        return $record->$fn;
    }

    function getWithOtherValues(& $record, $fn, $p) {
        return $record->_otherValues[$fn];
    }

    function getWithNull(& $record, $fn, $p) {
        return null;
    }

    /**
     * @return Ac_Model_Property
     * @param Ac_Model_Data $record
     */
    function getPropertyInfo(& $record, $fieldName = false, $isStatic = false) {
        if (is_a($record, 'Ac_Model_Data') && $this->useAeDataFacilities) {
            if ($fieldName === false) $fieldName = $this->fieldName;
            if ($record->hasProperty($fieldName))
                $res = $record->getPropertyInfo($fieldName, true);
            else 
              $res = null;
        } else $res = null;
        return $res;
    }
    
    /**
     * @return Ac_Model_Property
     */
    function getStaticPropertyInfo($fieldName = false) {
        if ($this->_staticMeta === false) {
            if (!$this->useAeDataFacilities) $res = null; else {
                $record = & $this->_table->getRecordPrototype(); 
                $res = $this->getPropertyInfo($record, $fieldName, true);        
            }
            $this->_staticMeta = $res;
        }
        return $this->_staticMeta;
    }
    
    /**
     * @return string Cell contents
     * @param object record Database record
     * @param int rowNo number of current row in the table
     */
    function getData(& $record, $rowNo, $fieldName) {
        $res = $this->getRecordProperty($record, $fieldName);
        return $res;
    }
    
    /**
     * @return string cell title
     */
    function getTitle() {
        if (isset($this->settings['title'])) $res = $this->settings['title'];
        elseif ($pi = & $this->getStaticPropertyInfo() && $pi->caption) {
            $res = $pi->caption;
        } else $res = $this->_name;
        return $res;
    }    
    
    /**
     * Renders (echo's) column header
     */
    function showHeader($rowCount, $rowNo = 1) {
        if ($this->staticAttribs) $this->updateAttribs();
        if (!$this->hidden) echo "<th title='{$this->_name}' ".Ac_Util::mkAttribs($this->getHeaderAttribs($rowCount, $rowNo)).">".$this->getTitle()."</th>";
    }
    
    /**
     * Renders (echo's) column cell
     */
    function showCell(& $record, $rowNo) {
        if (!$this->staticAttribs) $this->updateAttribs();
        if (is_null($data = $this->getData($record, $rowNo, $this->fieldName))) $data = $this->nullText;
        if (!$this->hidden) echo '<td ', $this->_cellAttribs, '>', $data, '</td>';
    }
    
    /**
     * @return string cell's hint (legend) content
     */
    function getHint() {
        if (isset($this->settings['hint'])) $res = $this->settings['hint'];
            else $res = $this->defaultHint;
        return $res;
    }
    
    /**
     * Renders (echo's) column legend (may be not supported in the template yet)
     */
    function showHint() {
        if (!$this->hidden) echo $this->getHint();
    }
    
}

?>