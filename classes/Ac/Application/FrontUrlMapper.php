<?php

/**
 * Special version of UrlMapper that's intended to work with Ac_Application_FrontController to extract 
 * controller ID from path segment into $controllerParam and pass remaining part of pathInfo into __pathInfo__ param.
 * 
 * The special case here is that "defaultController" doesn't require id, and when it is used, first segment of path
 * is part of pathInfo for default controller.
 * 
 * To detect whether controllerId is provided or not, we need to restrict "controllerParam" to possible values of
 * controllerIds, and that list of controllerIds is returned by Ac_Application::listComponents('Ac_I_Controller').
 * 
 * FrontUrlMapper is able to pull $controllerParam, $defaultController, $adminController from 
 * front controller, and $extraControllerIds from Ac_Application instance when $autoConfigure is TRUE, 
 * on first parsing of URL.
 */
class Ac_Application_FrontUrlMapper extends Ac_UrlMapper_UrlMapper implements Ac_I_ApplicationComponent {
    
    use Ac_Compat_Overloader;
    
    protected static $_compat_application = 'app';
    protected static $_compat_setApplication = 'setApp';
    protected static $_compat_getApplication = 'getApp';
    
    /**
     * @var Ac_Application
     */
    protected $app = false;
    
    /**
     * @var string
     */
    protected $controllerParam = false;

    /**
     * @var string
     */
    protected $defaultController = false;

    /**
     * @var string
     */
    protected $adminController = false;

    /**
     * @var bool
     */
    protected $allowAdmin = false;
    
    /**
     * @var bool
     */
    protected $autoConfigure = true;
    
    /**
     * @var array
     */
    protected $extraControllerIds = false;
    
    protected $didAutoConfigure = false;
    
    protected $customPatterns = array();
    
    protected $childMappers = array();
    
    /**
     * @var bool
     */
    protected $useDefaultMapper = true;
    

    /**
     * @var array
     */
    protected $urlMappers = array();
    
    /**
     * ID of pattern that has controllerId provided (is re-built automatically)
     */
    const PATTERN_ID_CONTROLLER = 'rule.controller';
    
    /**
     * ID of rule that hasn't controllerId provided
     */
    const PATTERN_ID_DEFAULT = 'rule.default';
    
    function __construct(array $prototype = array()) {
        parent::__construct($prototype);
    }
    
    function setApp(Ac_Application $app) {
        $this->app = $app;
        $this->didAutoConfigure = false;
    }

    /**
     * @return Ac_Application
     */
    function getApp() {
        return $this->app;
    }
    
    function setPatterns(array $patterns, $replace = true) {
        if (!$this->customPatterns) $this->customPatterns = array();
        if ($replace) $this->customPatterns = array_keys($patterns);
            else $this->customPatterns = array_unique(array_merge($this->customPatterns, array_keys($patterns)));
        parent::setPatterns($patterns, $replace);
    }

    /**
     * @param bool $autoConfigure
     */
    function setAutoConfigure($autoConfigure) {
        $this->autoConfigure = (bool) $autoConfigure;
    }

    /**
     * @return bool
     */
    function getAutoConfigure() {
        return $this->autoConfigure;
    }    

    /**
     * @param string $controllerParam
     */
    function setControllerParam($controllerParam) {
        $this->controllerParam = $controllerParam;
    }

    /**
     * @return string
     */
    function getControllerParam() {
        return $this->controllerParam;
    }

    /**
     * @param string $defaultController
     */
    function setDefaultController($defaultController) {
        $this->defaultController = $defaultController;
    }

    /**
     * @return string
     */
    function getDefaultController() {
        if ($this->defaultController === false) {
            if ($this->autoConfigure) $this->applyAutoConfig();
        }
        return $this->defaultController;
    }
    
    function setExtraControllerIds(array $extraControllerIds) {
        $this->extraControllerIds = $extraControllerIds;
    }

    /**
     * @return array
     */
    function getExtraControllerIds() {
        if ($this->extraControllerIds === false) {
            if ($this->autoConfigure) $this->applyAutoConfig();
            else return array();
        }
        return $this->extraControllerIds;
    }    

    /**
     * @param string $adminController
     */
    function setAdminController($adminController) {
        $this->adminController = $adminController;
    }

    /**
     * @return string
     */
    function getAdminController() {
        if ($this->adminController === false) {
            if ($this->autoConfigure) $this->applyAutoConfig();
        }
        return $this->adminController;
    }

    /**
     * @param bool $allowAdmin
     */
    function setAllowAdmin($allowAdmin) {
        $this->allowAdmin = $allowAdmin;
    }

    /**
     * @return bool
     */
    function getAllowAdmin() {
        return $this->allowAdmin;
    }
    
    function applyAutoConfig($overwriteCustomPatterns = false) {
        if ($this->didAutoConfigure) {
            // check if no new controller appeared
            if (implode(",", $this->extraControllerIds) === implode(",", $this->app->listComponents('Ac_I_Controller'))) 
                return;
        }
        $this->didAutoConfigure = true;
        if (!$this->app) {
            throw new Ac_E_InvalidUsage("Cannot ".__METHOD__."() without setApp() first");
        }
        $frontController = $this->app->getFrontController();
        $this->controllerParam = $frontController->getControllerParam();
        $this->defaultController = $frontController->getDefaultController();
        $this->adminController = $frontController->getAdminController();
        $this->extraControllerIds = $this->app->listComponents('Ac_I_Controller');
        if (!$this->allowAdmin && $this->adminController) {
            $this->extraControllerIds = array_diff($this->extraControllerIds, [$this->adminController]);
        }
        if (strlen($this->defaultController)) {
            $this->extraControllerIds = array_diff($this->extraControllerIds, [$this->defaultController]);
        }
        $this->modifyPatterns($overwriteCustomPatterns);
    }
    
    protected function modifyPatterns($overwriteCustomPatterns = false) {
        
        $patterns = array();
        
        if (strlen($this->defaultController)) {
            $patterns[self::PATTERN_ID_DEFAULT] = array('const' => array('controller' => null), 'definition' => "{?'__pathInfo__'/.*}");
        }
        if ($this->extraControllerIds) {
            $rx = implode("|", array_map('preg_quote', $this->extraControllerIds, array_fill(0, count($this->extraControllerIds), '~')));
            $patterns[self::PATTERN_ID_CONTROLLER] = array('definition' => "/{?'controller'{$rx}}{?'__pathInfo__'/.*}");
        }
        
        if (!$overwriteCustomPatterns) {
            $patterns = array_diff_key($patterns, array_fill_keys($this->customPatterns, true));
        }
        
        $this->setPatterns($patterns, false);
        
    }
    
    function getChildMapper($controllerId) {
        if (isset($this->childMappers[$controllerId])) return $this->childMappers[$controllerId];
        $this->childMappers[$controllerId] = null;
        if (!strlen($controllerId)) return;
        if (!($controllerId === $this->defaultController || in_array($controllerId, $this->getExtraControllerIds()))) return;
        $class = $this->app->getComponentClass($controllerId);
        if (!$class) return;
        $cm = null;
        if (array_key_exists($controllerId, $this->urlMappers)) {
            $cm = $this->urlMappers[$controllerId];
        } elseif (method_exists($class, 'getUrlPatterns')) {
            $patterns = call_user_func(array($class, 'getUrlPatterns'));
            if (!$patterns) $cm = null;
            elseif (isset($patterns['class'])) {
                $cm = $patterns;
            } else {
                $cm = array('patterns' => $patterns);
            }
        } else {
            $controller = $this->app->getComponent($controllerId, 'Ac_I_Controller');
            $cm = $controller->createUrlMapper();
        }
        if ($cm === null && $this->useDefaultMapper) {
            $cm = new Ac_UrlMapper_StaticSignatures();
        }
        if ($cm) {
            if (!is_object($cm)) {
                $cm = Ac_Prototyped::factory($cm, 'Ac_UrlMapper_UrlMapper');
            }
            if ($cm instanceof Ac_UrlMapper_StaticSignatures && !$cm->getControllerClass()) {
                $cm->setControllerClass($class);
            }
            $this->childMappers[$controllerId] = $cm;
        }
        
        return $this->childMappers[$controllerId];
    }
    
    function moveParamsToString(array & $params) {
        
        if ($this->autoConfigure && !$this->didAutoConfigure) $this->applyAutoConfig();
        
        // we can't search for controller
        if (!strlen($this->controllerParam) && !strlen($this->defaultController)) return null;

        $controllerId = null;
        
        if (strlen($this->controllerParam) && isset($params[$this->controllerParam])) {
            $controllerId = $params[$this->controllerParam];
        }
        
        if ($this->defaultController !== null && strlen($this->defaultController) && !($controllerId !== null && strlen($controllerId))) $controllerId = $this->defaultController;
        
        if (!strlen($controllerId)) return parent::moveParamsToString($params);
        
        $childMapper = $this->getChildMapper($controllerId);
        
        if (!$childMapper) return parent::moveParamsToString($params);
        
        $childPathInfo = $childMapper->moveParamsToString($params);
        
        if (is_null($childPathInfo)) return; // cannot move
        
        $params['__pathInfo__'] = $childPathInfo;
        $res = parent::moveParamsToString($params);
        unset($params['__pathInfo__']);
        return $res;
    }
    
    function stringToParams($string) {
        if ($this->autoConfigure) $this->applyAutoConfig();
        
        $params = parent::stringToParams($string);
        if ($params === null) return;
        
        if (!isset($params['__pathInfo__'])) return $params;
        
        $pathInfo = $params['__pathInfo__'];
        
        $controllerId = null;
        
        if (strlen($this->controllerParam) && isset($params[$this->controllerParam])) {
            $controllerId = $params[$this->controllerParam];
        }
        
        if (!empty($this->defaultController)  && empty($controllerId)) $controllerId = $this->defaultController;
        
        if (empty($controllerId)) return $params;
        
        $childMapper = $this->getChildMapper($controllerId);
        
        if (!$childMapper) return $params;
        
        $childParams = $childMapper->stringToParams($pathInfo);
        
        if (is_null($childParams)) return null;
        
        unset($params['__pathInfo__']);

        Ac_Util::ms($params, $childParams);
        return $params;
    }
    
    /**
     * @param bool $useDefaultMapper
     */
    function setUseDefaultMapper($useDefaultMapper) {
        $this->useDefaultMapper = (bool) $useDefaultMapper;
    }

    /**
     * @return bool
     */
    function getUseDefaultMapper() {
        return $this->useDefaultMapper;
    }    
    
    /**
     * Sets specific UrlMappers or UrlMapper prototypes for controllers. 
     * They will be used instead of controller-provided ones
     * 
     * @param array $urlMappers
     * @param bool $replace
     */
    function setUrlMappers(array $urlMappers, $replace = true) {
        if (!$replace) $this->urlMappers = array_merge($this->urlMappers, $urlMappers);
        $this->urlMappers = $urlMappers;
        $this->childMappers = array_diff_key($this->childMappers, $this->urlMappers);
    }

    /**
     * @return array
     */
    function getUrlMappers() {
        return $this->urlMappers;
    }    
}
