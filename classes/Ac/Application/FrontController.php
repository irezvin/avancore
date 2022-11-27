<?php

class Ac_Application_FrontController extends Ac_Application_Component {
    
    /**
     * @var Ac_Controller_Context
     */
    protected $context = null;
    
    protected $controllerParam = 'controller';

    protected $defaultController = 'frontend';

    protected $adminController = 'admin';
    
    /**
     * @var Ac_Controller_Output
     */
    protected $output = null;

    /**
     * @var array
     */
    protected $outputPrototype = null;
    
    /**
     * @var bool
     */
    protected $useUrlMapper = true;

    /**
     * @return Ac_Controller_Context
     */
    function getContext($asIs = false) {
        if ($this->context === null && !$asIs) {
            $this->setContext($this->getApplication()->getAdapter()->createDefaultContext());
        }
        return $this->context;
    }    

    function setOutput(Ac_Controller_Output $output = null) {
        $this->output = $output;
        $this->outputPrototype = null;
    }

    /**
     * @return Ac_Controller_Output
     */
    function getOutput($asIs = false) {
        if ($this->output === null && !$asIs) {
            if ($this->outputPrototype) {
                $this->output = Ac_Prototyped::factory($this->outputPrototype, 'Ac_Controller_Output');
            } else {
                $this->output = $this->application->getAdapter()->createDefaultOutput();
            }
        }
        return $this->output;
    }

    function setOutputPrototype(array $outputPrototype = null) {
        $this->outputPrototype = $outputPrototype;
        $this->output = null;
    }

    /**
     * @return array
     */
    function getOutputPrototype() {
        return $this->outputPrototype;
    }

    function setControllerParam($controllerParam) {
        $this->controllerParam = $controllerParam;
    }

    function getControllerParam() {
        return $this->controllerParam;
    }

    function setDefaultController($defaultController) {
        $this->defaultController = $defaultController;
    }

    function getDefaultController() {
        return $this->defaultController;
    }

    function setAdminController($adminController) {
        $this->adminController = $adminController;
    }

    function getAdminController() {
        return $this->adminController;
    }
    
    /**
     * @param bool $useUrlMapper
     */
    function setUseUrlMapper($useUrlMapper) {
        $this->useUrlMapper = $useUrlMapper;
    }

    /**
     * @return bool
     */
    function getUseUrlMapper() {
        return $this->useUrlMapper;
    }    
    
    function setContext(Ac_Controller_Context $context) {
        $this->context = $context;
    }
    
    protected $urlMapper = false;
    
    /**
     * @return Ac_UrlMapper_UrlMapper
     */
    function getUrlMapper() {
        if ($this->urlMapper === false) {
            $this->urlMapper = $this->application->getUrlMapper();
            return $this->urlMapper;
        }
        return $this->urlMapper;
    }
    
    function setUrlMapper($urlMapper) {
        if (!$urlMapper && $urlMapper !== null) $urlMapper = false;
        if ($urlMapper) $urlMapper = Ac_Prototyped::factory($urlMapper, 'Ac_UrlMapper_UrlMapper');
        $this->urlMapper = $urlMapper;
    }
    
    
    function handleRequest(Ac_Controller_Context $context = null, $admin = false) {
        
        if ($context) $this->setContext($context);
        else $this->setContext($this->getApplication()->getAdapter()->createDefaultContext());
        
        $context = $this->context;
        
        $urlMapper = null;
        if ($this->useUrlMapper) $urlMapper = $this->application->getUrlMapper();
        if ($urlMapper) {
            $this->context->getBaseUrl()->setUrlMapper($urlMapper);
            // TODO
            if (strlen($pathInfo = $context->pathInfo)) {
                $params = $urlMapper->stringToParams($pathInfo);
                if (is_null($params)) {
                    throw new Ac_E_ControllerException("Unparsable path: '{$pathInfo}'");
                }
                $this->context->updateData($params);
                $this->context->getBaseUrl()->pathInfo = '';
            }
        }
        
        if ($admin) {
            $controllerId = $this->adminController;
        } else {
            if (strlen($this->controllerParam)) {
                $controllerId = $this->context->useParam($this->controllerParam);
            }

            if (strlen($this->defaultController)) {
                if (empty($controllerId)) $controllerId = $this->defaultController;
            }
        }
        
        if (!$controllerId) {
            throw new Ac_E_ControllerException("Cannot determine controller id", 400);
        }
        
        if ($controllerId === $this->adminController && !$admin) {
            throw new Ac_E_ControllerException("Access denied", 403);
        }
        
        $controller = $this->application->getComponent($controllerId, null, true);
        
        if (!$controller || !($controller instanceof Ac_Cr_Controller || $controller instanceof Ac_Controller)) {
            throw new Ac_E_ControllerException("Controller '{$controllerId}' not found", 404);
        }
        
        
        $this->processRequest($controller);
        
    }
    
    protected function processRequest (Ac_Controller $controller) {
        
        $ctx = clone $this->getContext();
        
        $controller->setContext($ctx);
        
        $response = $controller->getResponse();
        
        $output = $this->getOutput();
        
        $output->outputResponse($response);
        
    }

}