<?php

/**
 * Implements editing parameter values with mosParameters object and .xml file 
 */
class Ac_Form_Control_Parameters extends Ac_Form_Control {
    
    /**
     * @var mosParameters
     */
    protected $jForm = false;

    var $xmlFilePath = false;
    
    /**
     * Exact XML string ($xmlFilePath won't be used)
     * @var string
     */
    var $xml = false;
    
    var $returnsArray = '?';
    
    var $templateClass = 'Ac_Form_Control_Template_Parameters';
    
    var $templatePart = 'parameters';
    
    var $appRootDir = false;
    
    /**
     * XPath to find form definition in the form XML
     * @var string
     */
    var $xpath = '//fields';
    
    var $formName = 'form';
    
    var $fieldsGroup = 'params';
    
    /**
     * @var string|array Format of fieldsets' labels language strings
     */
    var $fieldsetLabelFormat = 'COM_PLUGINS_%s_FIELDSET_LABEL';
        
    /**
     * @var string|array Language packages to load
     */
    var $langPackages = array('com_plugins');
    
    /**
     * @var string|false keys where errors from the JForm will be passed in the value
     */
    var $passErrors = 'errors';
    
    function getXmlFilePath() {
        if ($this->xmlFilePath === false) {
            $p = $this->getModelProperty();
            if ($p && isset($p->xmlFilePath) && strlen($p->xmlFilePath)) {
                $res = $p->xmlFilePath;
            } else {
                $res = false;
            }
        } else {
            $res = $this->xmlFilePath;
        }
        return $res;
    } 
    
    function getReturnsArray() {
        if ($this->returnsArray === '?') {
            if (($p = $this->getModelProperty()) && ($p->plural || $p->arrayValue)) $res = true;
            else $res = false;
        } else {
            $res = $this->returnsArray;
        }
        return $res;
    }
    
    /**
     * @return JForm
     */
    function getJForm() {
        if ($this->jForm === false) {
            jimport('joomla.form.form');
            $this->jForm = new JForm($this->formName, array('control' => $this->getContext()->mapParam('value')));
            if (!strlen($this->xml)) {
                $dir = $this->getAppRootDir();
                $xfp = $this->getXmlFilePath();
                if (strlen($xfp) && !in_array($xfp[0], array('/', '\\'))) $xfp = $dir.'/'.$xfp;
                $this->jForm->loadFile($xfp, true, $this->xpath);
            } else {
                $this->jForm->load($this->xml, true, $this->xpath);
            }
            if ($v = $this->getValue()) {
                if (!is_array($v)) $v = $this->stringToArray($v);
                if (strlen($this->fieldsGroup)) {
                    $v = array($this->fieldsGroup => $v);
                }
                $this->jForm->bind($v);
            }
        }
        return $this->jForm;
    }
    
    function getStringParams() {
        $v = $this->getValue();
        if (is_array($v)) {
            $res = $this->arrayToString($v);
        } else $res = $v;
        return $res;
    }

    protected  function arrayToString(array $v) {
        $res = json_encode($v);
        return $res;
    }
    
    protected function stringToArray($string) {
        $res = json_decode($string, true);
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetValue() {
        if (!($this->readOnly === true)) {
            if ($this->isSubmitted() && !isset($this->_rqData['value'])) $res = array();
            elseif (isset($this->_rqData['value']) && is_array($this->_rqData['value'])) {
                $jf = $this->getJForm();
                $jf->bind($this->_rqData['value']);
                $res = $jf->filter($this->_rqData['value']);
                if (is_string($this->fieldsGroup) && is_array($res)) {
                    if (array_keys($res) == array($this->fieldsGroup)) {
                        $res = $res[$this->fieldsGroup];
                    }
                }
                if (!$jf->validate($this->_rqData['value'])) {
                    if (strlen($this->passErrors)) {
                        $err = Ac_Util::flattenArray($jf->getErrors());
                        foreach ($err as $k => $v) {
                            if ($v instanceof Exception) $err[$k] = $v->getMessage();
                        }
                        Ac_Util::setArrayByPath($res, Ac_Util::pathToArray($this->passErrors), $err);
                    }
                    else {
                        $this->_errors['validate'] = $jf->getErrors();
                        $res = $this->getDefault();
                    }
                }
                if (is_array($res) && !$this->getReturnsArray()) {
                    $res = $this->arrayToString($res);
                }
            } else {
                $res = $this->getDefault();
            }
        } else {
            $res = $this->getDefault();
        }
        return $res;
    }
    
    function getAppRootDir() {
        if ($this->appRootDir === false) {
            $app = $this->getApplication();
            if ($app) $res = $app->getAppRootDir();
        } else {
            $res = $this->appRootDir;
        }
        return $res;
    }
        
}

