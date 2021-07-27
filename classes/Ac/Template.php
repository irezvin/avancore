<?php

class Ac_Template {

    /**
     * @var Ac_Controller
     */
    var $controller = false;
    
    /**
     * @var Ac_Controller_Context
     */
    var $context = false;
    
    /**
     * Ac_Template_Helper instances
     *
     * @var unknown_type
     */
    var $_templateHelpers = array();
    
    /**
     * Names of template variables that won't be set with setVars()
     * @var array
     * @access protected
     */
    var $_privateVars = array();
    
    /**
     * @var Ac_Application
     */
    protected $application = false;
    
    /**
     * @param array $vars Initial values for template variables 
     */
    function __construct($vars = array()) {
        $this->setVars($vars);
    }
    
    /**
     * @param string $className Name of template class
     * @param array $vars Initial values for template variables
     * @return Ac_Template
     */
    function factory($className, $vars = array()) {
        $res = new $className($vars);
        return $res;
    }
    
    /**
     * Returns current template variables
     *
     * @return array
     */
    function getVars() {
        $res = array();
        foreach (array_keys($vars = get_object_vars($this)) as $k) if ($k[0] != '_') $res[$k] = $vars[$k];
        return $res;
    }
    
    /**
     * Assigns template variables
     * 
     * @param array $vars Values of tempalte variables to assign 
     */
    function setVars($vars) {
        $v = array();
        foreach (array_diff(array_keys($vars), $this->_privateVars) as $k) {
            $v[$k] = $vars[$k];
        }
        Ac_Util::bindAutoparams($this, $vars);
    }
    
    /**
     * Creates and shows other template instance
     * 
     * @param string $className Name of template class
     * @param array $vars Initial values for template variables
     * @param string $partName Name of template part to show
     * @param array $extraParams Parameters to pass to template function
     */
    function showTemplate($className, $vars = array(), $partName = 'default', $extraParams = array()) {
        $tpl = Ac_Template::factory($className, $vars);
        return $tpl->show($partName, $extraParams);
    }
    
    /**
     * Creates other template instance and returns its rendered part
     * 
     * @param string $className Name of template class
     * @param array $vars Initial values for template variables
     * @param string $partName Name of template part to show
     * @param array $extraParams Parameters to pass to template function
     * 
     * @return string rendered template part 
     */
    function fetchTemplate($className, $vars = array(), $partName = 'default', $extraParams = array()) {
        $tpl = Ac_Template::factory($className, $vars);
        return $tpl->fetch($partName, $extraParams);
    }
        
    /**
     * Shows default template part
     */
    function showDefault() {
    }
    
    /**
     * Displays template part
     * 
     * @param string $partName Name of template part to show
     * @param array $extraParams Parameters to pass to template function
     */
    function show($partName = 'default', $extraParams = array()) {
        if (method_exists($this, $mtdName = 'show'.$partName)) {
            return call_user_func_array(array(& $this, $mtdName), $extraParams);
        } else {
            trigger_error('Template part \''.$partName.'\' not exists', E_USER_ERROR);
        }
    }
    
    /**
     * returns template part instead of showing it 
     * 
     * @param string $partName Name of template part to show
     * @return string rendered template part
     */
    function fetch($partName = 'default', $extraParams = array()) {
        ob_start();
        $this->show($partName, $extraParams);
        return ob_get_clean();
    }
    
    /**
     * Outputs variable
     * @param mixed $var 
     */
    function d($var) {
        echo $var;
    }
    
    /**
     * Outputs object
     * @param object $obj Object that supports toString() or show() methods
     */
    function o($obj) {
        if (method_exists($obj, 'toString')) echo $obj->toString();
        elseif (method_exists($obj, 'show')) $obj->show();
    }
    
    /**
     * Outputs dump of any variable
     * @param mixed $anything 
     */
    function p($anything) {
        print_r($anything);
    }
    
    /**
     * Otputs dump of any variable
     * @param mixed $anything 
     */
    function dump($anything) {
        return $this->p($anything);
    }
    
    /**
     * Outputs variable
     * @param mixed $var 
     */
    function display($var) {
        return $this->d($var);
    }
    
    /**
     * Outputs object
     * @param object $obj Object that supports toString() or show() methods
     */
    function displayObject($obj) {
        return $this->d($obj);
    }
    
    /**
     * @return Ac_Tempalte_Helper
     */
    function getTemplateHelper ($class) {
        if (!isset($this->_templateHelpers[$class])) {
            $this->_templateHelpers[$class] = new $class ($this);
        }
        return $this->_templateHelpers[$class];
    }
    
    function l($langString, $defaultOrArgs = false, $return = false) {
        $res = new Ac_Lang_String($langString, $defaultOrArgs);
        if ($return) return $res;
            else echo $res;
    }
    
    function langString($langString, $defaultOrArgs = false, $return = false) {
        return $this->l($langString, $defaultOrArgs, $return);
    }

    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        if ($this->application === false) {
            if ($this->controller) return $this->controller->getApplication ();
            else return Ac_Application::getDefaultInstance ();
        }
        return $this->application;
    }    

}

