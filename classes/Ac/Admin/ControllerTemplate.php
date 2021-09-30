<?php

class Ac_Admin_ControllerTemplate extends Ac_Template_Html {
    
    /**
     * @var Ac_Form
     */
    var $loginForm = null;
    
    /**
     * @var Ac_Admin_Manager
     */
    var $manager = null;
    
    var $managerResponse = null;
    
    var $appCaption = null;
    
    var $appCaptionSuffix = ' Admin';
    
    function showDashboard() {
        ob_start();
        echo "<h1>Dashboard</h1>";
        $this->showWrapper(ob_get_clean());
    }
    
    function showMenu() {
        $menu = $this->controller->getMenu();
?>
        <header>
            <div class="logo"><h1><?php echo $this->appCaption; ?></h1></div>
            <div class="user">
                <p><?php echo "Hello, User # ".$this->controller->getApplication()->getUser()->getId(); ?></p>
                <div class="logout"><a class="logout" href="<?php echo $this->controller->getUrl(['action' => 'logout']); ?>">Logout</a></div>
            </div>
            <nav>
<?php           $this->showMenuItems($menu); ?>            
            </nav>
        </header>
<?php
    }
    
    function showMenuItems(array $menu) {
?>        
                <ul>
                    <?php foreach ($menu as $item) { ?>
                    <li class="menuItem menu-<?php echo $item->id; if ($item->isActive()) echo " active"; if ($item->hasActive()) echo " expanded"; if ($item->menuEntries) echo " hasChildren"; ?>">
                        <a href="<?php echo $this->controller->getUrl($item->getQuery()); ?>">
                            <?php $this->d($item->caption); ?>
                        </a>
<?php                   if ($item->menuEntries) $this->showMenuItems($item->menuEntries); ?>                        
                    </li>
                    <?php } ?>
                </ul>
<?php
    }
    
    function showWrapper($content) {
        $this->addPageTitle($this->appCaption.$this->appCaptionSuffix);
        $this->htmlResponse->addCssLib('{AC}/managers.css', true, true);
        $this->htmlResponse->htmlAttribs['class'] = 'avanadmin';
        if ($this->controller->extraAssets) {
            $this->addAssetLibs($this->controller->extraAssets);
        }
        $this->showMenu();
?>
        <main>
			<?php echo $content; ?>
        </main>
<?php 	    
    }
    
    function showLogin() {
        $this->addPageTitle('Login');
        $this->loginForm->_response = $this->htmlResponse;
        echo $this->loginForm->fetchPresentation();
    }
    
    function showLoggedIn() {
?>
        <p>You are already logged in.</p>
        <p><a href="<?php $this->d($this->controller->getUrl(['action' => 'logout'])); ?>">Logout</a></p>
<?php
    }
    
    function showManager() {
        $this->addPageTitle($this->manager->isForm()? $this->manager->getFormTitle() : $this->manager->getPluralCaption());
        $bu = $this->controller->getUrl();
        $ctx = $this->manager->getContext();
        $allState[$this->manager->getInstanceId()] = $this->manager->getStateData();
        if ($this->managerResponse && strlen($this->managerResponse->redirectUrl)) {
            $this->htmlResponse->redirectUrl = $this->managerResponse->redirectUrl;
            $this->htmlResponse->redirectType = $this->managerResponse->redirectType;
            return;
        }
        if ($this->managerResponse && isset($this->managerResponse->hasToRedirect) && ($redir = $this->managerResponse->hasToRedirect)) {
            $bu = $ctx->getUrl();
            $u = new Ac_Url($redir);
            $bu->query = $u->query;
            $bu->fragment = $u->fragment;
            // this creates weird issues i.e. when we try to save the record
            // $bu->query = Ac_Util::m($allState, $u->query, true);
            $this->htmlResponse->redirectUrl = $bu;
        } else {
            $bu = $this->controller->getUrl(array($this->controller->_methodParamName => 'manager', 'mapper' => $this->context->getData('mapper')));
            $formAttribs = array('action' => $bu->toString(false), 'method' => 'post', 'name' => 'aForm', 'id' => 'aForm',  /*'enctype' => 'multipart/form-data'*/);
            $this->htmlResponse->mergeWithResponse($this->managerResponse);
            ob_start(); 
?>
            <form <?php echo Ac_Util::mkAttribs($formAttribs); ?>>
                <?php echo $bu->getHidden(); ?>
                <?php echo $this->managerResponse->content; ?>    				
            </form>
<?php
            $this->showWrapper(ob_get_clean());
        }
	}    
    
}

