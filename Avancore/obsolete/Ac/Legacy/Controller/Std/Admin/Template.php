<?php

class Ac_Legacy_Controller_Std_Admin_Template extends Ac_Template_Html {
	
	/**
	 * @var Ac_Admin_Manager
	 */
	var $manager = false;
	
	/**
	 * @var Ac_Legacy_Controller_Response_Http
	 */
	var $managerResponse = false;
	
	
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
        $this->htmlResponse->addCssLib('{ACCSS}/managers.css', true);
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
            $this->htmlResponse->redirectUrl = $bu;
        } else {
            $bu = $this->controller->getUrl(array('action' => 'manager', 'mapper' => $this->context->getData('mapper')));
            $formAttribs = array('action' => $bu->toString(false), 'method' => 'post', 'name' => 'aForm', 'id' => 'aForm',  'enctype' => 'multipart/form-data');
            $this->htmlResponse->mergeWithResponse($this->managerResponse);
            ob_start(); 
?>
    		<div class='avantravel'>
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
