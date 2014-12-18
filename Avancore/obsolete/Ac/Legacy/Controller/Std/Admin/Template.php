<?php

class Ac_Legacy_Controller_Std_Admin_Template extends Ac_Legacy_Template_Html {
	
	/**
	 * @var Ac_Admin_Manager
	 */
	var $manager = false;
	
	/**
	 * @var Ac_Legacy_Controller_Response_Http
	 */
	var $managerResponse = false;
    
    /**
     * @var Ac_Legacy_Controller_Std_Admin
     */
    var $controller = false;
	
	
	function showStart() {
	    ob_start();
?>

		<div id='cpanel'>
		    <h1>Controller start page</h1>
		</div>
<?php 	    
        $this->_showWrapper(ob_get_clean());
	}
	
	function _showWrapper($content) {
        $this->htmlResponse->addCssLib('{AC}/managers.css', true, true);
        if (is_array($this->controller->extraAssets))
            $this->addAssetLibs($this->controller->extraAssets);
?>

		<div class='main'>
			<?php echo $content; ?>
		</div>
<?php 	    
	}
	
	
	function showManager() {
        $bu = $this->controller->getUrl();
        $ctx = $this->manager->getContext();
        $allState[$this->manager->getInstanceId()] = $this->manager->getStateData();
        if ($this->managerResponse && isset($this->managerResponse->hasToRedirect) && ($redir = $this->managerResponse->hasToRedirect)) {
            $bu = $ctx->getUrl();
            $u = new Ac_Url($redir);
            $bu->query = Ac_Util::m($allState, $u->query, true);
            //Ac_Debug::ddd(strlen(''.$bu), ''.$bu);
            $this->htmlResponse->redirectUrl = $bu;
        } else {
            $bu = $this->controller->getUrl(array($this->controller->_methodParamName => 'manager', 'mapper' => $this->context->getData('mapper')));
            $formAttribs = array('action' => $bu->toString(false), 'method' => 'post', 'name' => 'aForm', 'id' => 'aForm',  'enctype' => 'multipart/form-data');
            $this->htmlResponse->mergeWithResponse($this->managerResponse);
            ob_start(); 
?>
    		<div class='avanadmin'>
    			<form <?php echo Ac_Util::mkAttribs($formAttribs); ?>>
    				<?php echo $bu->getHidden(); ?>
    				<?php echo $this->managerResponse->content; ?>    				
    			</form>
    		</div>
<?php
            $this->_showWrapper(ob_get_clean());
        }
	}

}
