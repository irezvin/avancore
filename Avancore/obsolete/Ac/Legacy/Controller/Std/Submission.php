<?php

class Ac_Legacy_Controller_Std_Submission extends Ac_Legacy_Controller {
    
    /**
     * @var Ac_Legacy_Controller_Std_Submission_Template
     */
    var $_template = false;
    
    var $_templateClass = 'Ac_Legacy_Controller_Std_Submission_Template';
    
    /**
     * Class of model object that implements submission data or FALSE to use $mapperClass property instead
     * @var bool|string
     */
    var $modelClass = false;
    
    /**
     * Class of model mapper of objects that are being submitted
     * @var bool|string
     */
    var $mapperClass = false;
    
    /**
     * Default values that will be merged with user-submitted data before bind()
     * @var array
     */
    var $modelDefaults = array();
    
    var $modelExtraVars = array();
    
    var $_errorMethodName = 'executeInvalidRequest';
    
    /**
     * @var Ac_Model_Object
     * @access protected
     */
    var $_model = false;
    
    /**
     * Submission form
     * @var Ac_Form
     */
    var $_form = false;
    
    /**
     * @var Ac_Legacy_Controller_Filter
     * @access protected
     */
    var $_filter = false;
    
    /**
     * Meta-property name that allows to find properties of model objects that are submitted via form
     * @var string
     */
    var $publicPropertyFlag = false;
    
    var $title = false;
    
    var $submissionCompleteTitle = false;
    
    var $submissionCompleteMessage = false;
    
    var $submitButtonCaption = 'Отправить форму';
    
    var $deleteObjectOnUnsuccessfulSend = false;
    
    var $reportUserAboutUnsuccessfulSend = false;
    
    var $reportAdminAboutUnsuccessfulSend = true;
    
    function doInitProperties($options = array()) {
        $this->_filter = new Ac_Legacy_Controller_Filter;
    }

    /**
     * @return Ac_Model_Object
     */
    function getModelObject() {
        if ($this->_model === false) {
            $this->getForm();         
        }
        return $this->_model;
    }
    
    function getPersistentParameters() {
        return array();
    }
    
    /**
     * @return Ac_Form
     */
    function getForm() {
        if ($this->_form === false) {
            $this->_form = null;
            if ($this->_model === false) {
                if ($this->mapperClass) {
                    $mapper = & Ac_Model_Mapper::getMapper($this->mapperClass);
                    $this->_model = & $mapper->factory();
                } else {
                    assert(strlen($this->modelClass));
                    $this->_model = new $this->modelClass;
                }
            }
            $formPrototype = $this->doGetFormPrototype();
            $ppf = $this->publicPropertyFlag;
            $conv = new Ac_Form_Converter();
            foreach ($this->_model->listProperties() as $propName) {
                $prop = & $this->_model->getPropertyInfo($propName);
                if (!$ppf || isset($prop->$ppf) && $prop->$ppf) {
                    $fieldSettings= $conv->getControlSettings($prop);
                    $formPrototype['controls'][$propName] = $fieldSettings;
                }
            }
            $this->doOnFormPrototype($prototype);
            $feContext = & $this->_context->cloneObject();
            $pp = $this->getPersistentParameters();
            $path = Ac_Util::pathToArray($feContext->mapParam(array()));
            $d = array();
            Ac_Util::setArrayByPath($d, $path, $pp);
            Ac_Util::ms($feContext->_baseUrl->query, $pp);
            $formContext = & $feContext->spawn('form');
            $this->_form = new Ac_Form($formContext, $formPrototype, 'form');
            $this->_form->setModel($this->_model);
        }
        return $this->_form;
    }
    
    function doGetFormPrototype() {
        $formPrototype = array(
            'name' => 'form',
            'templateClass' => 'Ac_Form_Control_Template_Basic',
            'templatePart' => 'table',
            'submissionControl' => 'submitButton',
            'controls' => array(
                'submitButton' => array(
                    'class' => 'Ac_Form_Control_Button',
                    'caption' => $this->submitButtonCaption,
                    'htmlAttribs' => array('class' => 'submitButton'),
                    'displayOrder' => 250,
                ),
            ),
        );
        return $formPrototype;
    }
    
    function doOnFormPrototype(& $prototype) {
    }

    /**
     * Template method that is executed after model is updated with user-provided data
     */
    function doOnBindModel() {
    }
    
    /**
     * Template method that is called after model was successfully checked
     */
    function doOnValidModel() {
    }
    
    function isAdmin() {
        $res = false;
        $disp = & Ac_Dispatcher::getInstance();
        if (isset($GLOBALS['my']) && is_a($GLOBALS['my'], 'mosUser') && $GLOBALS['my']->usertype == 'Super Administrator') $res = true;
        elseif ($disp->config->debug) $res = true;
        return $res;
    }

    function executeDefault() {
        $this->getTemplate();
        $this->_template->frontend = & $this;
        
        $form = & $this->getForm();
        $model = & $this->getModelObject();
        $this->_template->model = & $model;
        
        foreach (array_keys($this->modelExtraVars) as $k) $model->$k = & $this->modelExtraVars[$k];
        $ok = false;
        if ($form->isSubmitted()) {
            $data = $form->getValue();
            $model->bind(Ac_Util::m($this->modelDefaults, $data));
            $this->doOnBindModel();
            if ($model->check()) {
                $this->doOnValidModel();
                if (method_exists($model, 'store'))
                if (!$model->store()) {
                    $this->_template->errors['store'] = '�� ������� ��������� ������';
                    if ($this->isAdmin()) $this->_template->errors['storeDetails'] = $model->getError();
                } else {
                    $ok = $this->notifyRecipients();
                    if (!$ok) {
                        if ($this->deleteObjectOnUnsuccessfulSend && method_exists($model, 'delete')) $model->delete();
                        if ($this->reportAdminAboutUnsuccessfulSend && $this->isAdmin()) $this->_template->errors['send'] = $this->_errors;
                        elseif ($this->reportUserAboutUnsuccessfulSend) $this->_template->errors['send'] = '�� ������� ��������� ����������� � ����� ��������';
                        elseif (!$this->deleteObjectOnUnsuccessfulSend) {
                            $ok = true;
                        } else {
                            $this->_template->errors['send'] = '�������� ������ � ���� ��������� ����� ��������; ���������� ��������������� ������ ������ �����.';
                        }
                    }
                }
            }
        }
        if ($ok)
            $this->_response->content = $this->_template->fetch('done');
        else
            $this->_response->content = $this->_template->fetch('form'); 
            
    }
    
    function executeInvalidRequest() {
        $this->_response->content = $this->_template->fetch('invalidRequest');
    }
    
    /**
     * Template method to return sendout info.
     * @return array(prototypes for Ac_Legacy_Controller_Std_Submission_Sendout)
     */
    function doGetSendouts() {
        return array();
    }

    /**
     * Template function to customize mail object before it will be sent. 
     * If this function returns false, mail won't be sent.
     * 
     * @access protected
     * @param Ac_Mail $mail
     * @param Ac_Legacy_Controller_Std_Submission_Sendout $sendout
     * @return bool
     */
    function doBeforeSendMail(& $mail, & $sendout) {
    }

    /**
     * Template function to handle case when email was set with an error.
     * Should return false to stop whole sendout process.
     * 
     * @access protected
     * @param Ac_Mail $mail
     * @param Ac_Legacy_Controller_Std_Submission_Sendout $sendout
     * @param string $sendoutKey Key of sendout object in the settings array
     * @return bool
     */
    function doHandleSendError(& $mail, & $sendout, $sendoutKey = false) {
        $this->_errors['sendout_'.$sendoutKey] = $mail->getError();
    }
    
    function notifyRecipients() {
        foreach ($this->doGetSendouts() as $sendoutPrototype) {
            $sendout = new Ac_Legacy_Controller_Std_Submission_Sendout($this, $this->getModelObject(), $this->getTemplate(), $sendoutPrototype);
            $mail = & $sendout->getMail();
            if ($mail) {
                if ($this->doBeforeSendMail($mail, $sendout) !== false) {
                    if (!$mail->send()) {
                        if ($this->doHandleSendError($mail, $sendout) === false) break;
                    }
                }
            }
        }
        return !$this->_errors;
    }
    
    function doBeforeExecute() {
        $this->getTemplate();
        $this->_template->submission = & $this; 
    }
    
    
}
    


?>