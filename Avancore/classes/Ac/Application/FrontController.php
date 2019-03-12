<?php

class Ac_Application_FrontController extends Ac_Application_Component {
    
    /**
     * @var Ac_Cr_Context
     */
    protected $context = null;
    
    protected $controllerParam = 'controller';

    protected $defaultController = 'frontend';

    protected $adminController = 'admin';
    
    /**
     * @var Ac_Result_Writer
     */
    protected $writer = null;

    /**
     * @var array
     */
    protected $writerPrototype = null;

    /**
     * @var Ac_Result_Environment_Html
     */
    protected $environment = null;

    /**
     * @var array
     */
    protected $environmentPrototype = null;
    
    /**
     * @var Ac_Legacy_Output
     */
    protected $legacyOutput = null;

    /**
     * @var array
     */
    protected $legacyOutputPrototype = null;
    
    /**
     * @var bool
     */
    protected $useUrlMapper = true;

    /**
     * @return Ac_Cr_Context
     */
    function getContext($asIs = false) {
        if ($this->context === null && !$asIs) {
            $this->setContext($this->getApplication()->getAdapter()->createDefaultContext());
        }
        return $this->context;
    }    

    function setWriter(Ac_Result_Writer $writer = null) {
        $this->writer = $writer;
        $this->writerPrototype = null;
    }

    /**
     * @return Ac_Result_Writer
     */
    function getWriter($asIs = false) {
        if ($this->writer === null && !$asIs) {
            if ($this->writerPrototype !== null) {
                $this->writer = Ac_Prototyped::factory($this->writerPrototype, 'Ac_Result_Writer');
            } else {
                $this->writer = $this->application->getAdapter()->createDefaultResultWriter();
            }
        }
        return $this->writer;
    }

    function setWriterPrototype(array $writerPrototype = null) {
        $this->writerPrototype = $writerPrototype;
        $this->writer = null;
    }

    /**
     * @return array
     */
    function getWriterPrototype() {
        return $this->writerPrototype;
    }

    function setEnvironment(Ac_Result_Environment $environment = null) {
        $this->environment = $environment;
        $this->environmentPrototype = null;
    }

    /**
     * @return Ac_Result_Environment_Html
     */
    function getEnvironment($asIs = false) {
        if ($this->environment === false && !$asIs) {
            if ($this->environmentPrototype !== null) {
                $this->environment = Ac_Prototyped::factory($this->environmentPrototype, 'Ac_Result_Environment');
            } else {
                $this->environment = $this->application->getAdapter()->createDefaultResultEnvironment();
            }
        }
        return $this->environment;
    }

    function setEnvironmentPrototype(array $environmentPrototype) {
        $this->environmentPrototype = $environmentPrototype;
        $this->environment = null;
    }

    function setLegacyOutput(Ac_Legacy_Output $legacyOutput = null) {
        $this->legacyOutput = $legacyOutput;
        $this->legacyOutputPrototype = null;
    }

    /**
     * @return Ac_Legacy_Output
     */
    function getLegacyOutput($asIs = false) {
        if ($this->legacyOutput === null && !$asIs) {
            if ($this->legacyOutputPrototype) {
                $this->legacyOutput = Ac_Prototyped::factory($this->legacyOutputPrototype, 'Ac_Legacy_Output');
            } else {
                $this->legacyOutput = $this->application->getAdapter()->createDefaultLegacyOutput();
            }
        }
        return $this->legacyOutput;
    }

    function setLegacyOutputPrototype(array $legacyOutputPrototype = null) {
        $this->legacyOutputPrototype = $legacyOutputPrototype;
        $this->legacyOutput = null;
    }

    /**
     * @return array
     */
    function getLegacyOutputPrototype() {
        return $this->legacyOutputPrototype;
    }

    /**
     * @return array
     */
    function getEnvironmentPrototype() {
        return $this->environmentPrototype;
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
    
    function setContext(Ac_Cr_Context $context) {
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
    
    
    function handleRequest(Ac_Cr_Context $context = null, $admin = false) {
        
        if ($context) $this->setContext($context);
        else $this->setContext($this->getApplication()->getAdapter()->createDefaultContext());
        
        $context = $this->context;
        
        $urlMapper = null;
        if ($this->useUrlMapper) $urlMapper = $this->application->getUrlMapper();
        if ($urlMapper) {
            $this->context->getBaseUrl()->setUrlMapper($urlMapper);
            if (strlen($pathInfo = $context->getRequest()->server->pathInfo)) {
                $params = $urlMapper->stringToParams($pathInfo);
                if (is_null($params)) {
                    throw new Ac_E_ControllerException("Unparsable path: '{$pathInfo}'");
                }
                $this->context->getRequest()->updateValues('get', $params, true);
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
                if (!strlen($controllerId)) $controllerId = $this->defaultController;
            }
        }
        
        if (!$controllerId) {
            throw new Ac_E_ControllerException("Cannot determine controller id", 400);
        }
        
        $controller = $this->application->getComponent($controllerId, null, true);
        
        if (!$controller || !($controller instanceof Ac_Cr_Controller || $controller instanceof Ac_Legacy_Controller)) {
            throw new Ac_E_ControllerException("Controller '{$controllerId}' not found", 404);
        }
        
        
        if ($controller instanceof Ac_Legacy_Controller) $this->processLegacyRequest($controller);
        else $this->processRequest($controller);
        
    }
    
    protected function processLegacyRequest (Ac_Legacy_Controller $controller) {
        
        $ctx = $this->getContext();
        $lc = Ac_Legacy_Controller_Context_Http::createFromContext($ctx);
        
        $controller->setContext($lc);
        
        $response = $controller->getResponse();
        
        $output = $this->getLegacyOutput();
        
        $output->outputResponse($response);
        
    }

    protected function processRequest (Ac_Cr_Controller $controller) {
        
        $controller->setContext($this->getContext());
        
        $result = $controller->getResult();
        
        $writer = $this->getWriter();
        
        $writer->writeResult($result);
        
    }
   
}