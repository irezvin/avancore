<?php

/**
 * @property Ac_Application $application
 * @method Ac_Application getApplication()
 * @method void setApplication(Ac_Application $application)
 */
class Ac_Template extends Ac_Prototyped {

    use Ac_Compat_Overloader;
    protected static $_compat_application = 'app';
    protected static $_compat_setApplication = 'setApp';
    protected static $_compat_getApplication = 'getApp';
    
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
     * @var Ac_Application
     */
    protected $app = false;

    protected $topLevelPart = null;
    
    function hasPublicVars() {
        return true;
    }    
    
    /**
     * Assigns template variables
     * 
     * @param array $vars Values of tempalte variables to assign 
     */
    function setVars($vars) {
        $this->initFromPrototype($vars, Ac_Prototyped::STRICT_PARAMS_WARNING);
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
            $partName[0] = strtolower($partName[0]);
            $this->topLevelPart = $partName;
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

    function setApp(Ac_Application $app = null) {
        $this->app = $app;
    }

    /**
     * @return Ac_Application
     */
    function getApp() {
        if ($this->app === false) {
            if ($this->controller) return $this->controller->getApp();
            else return Ac_Application::getDefaultInstance ();
        }
        return $this->app;
    }    

}

