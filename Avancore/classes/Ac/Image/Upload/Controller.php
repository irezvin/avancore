<?php

class Ac_Image_Upload_Controller extends Ac_Upload_Controller {

    var $defaultUploadManagerClass = 'Ac_Image_Upload_Manager';

    var $canShowThumbs = true;
    
    var $_templateClass = 'Ac_Image_Upload_Controller_Template';    

    function _getPreparedUploadManagerOptions() {
        $u = parent::_getPreparedUploadManagerOptions();
        if ($this->canShowThumbs && !isset($u['thumbLink'])) {
            $u['thumbLink'] = $this->getPartialUrlWithOpenParam('uploadId', array('action' => 'thumb'));
        }
        return $u;
    }
    
    function executeThumb() {
        if ($this->canShowThumbs) {
            if ($u = & $this->getUpload()) $this->_templatePart = 'thumb';
                else $this->_templatePart = '404'; 
        } else $this->_templatePart = '404';
    }
    
}
