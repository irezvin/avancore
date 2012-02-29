<?php

class Ae_Upload_Controller extends Ae_Legacy_Controller {

    var $uploadCharset = false;
    
    var $canDownload = true;
    
    var $canView = true;
    
    var $autoClose = true;
    
    var $showUploadItem = false;
    
    var $oldUploadId = false;
    
    var $newUploadId = false;
    
    var $paramName = false;
    
    var $readOnly = false;
    
    var $fileChangeFn = false;
    
    var $_defaultMethodName = 'upload';
    
    var $_retFieldName = false;
    
    var $_retFormName = false;
    
    var $_retLabelId = false;
    
    var $_fileChangeFn = false;
    
    var $_templateClass = 'Ae_Upload_Controller_Template';

    var $_autoTplVars = array('retFieldName', 'retFormName', 'url', 'upload', 'newUpload', 'retLabelId', 'error', 'readOnly', 'paramName', 'newUploadId', 'uploadCharset', 'fileChangeFn');
    
    var $_uploadId = false;
    
    var $_error = false;
    
    var $_autoStateVars = array('retFieldName', 'retFormName', 'uploadId', 'retLabelId', 'fileChangeFn'/*, 'error'*/);
    
    var $uploadErrorMessage = 'Error in upload processing: %s';
    
    var $uploadStoreErrorMessage = 'Error occured while storing an upload';
    
    /**
     * @var Ae_Upload_Manager
     */
    var $_uploadManager = false;
    
    var $_uploadManagerOptions = array();
    
    var $defaultUploadManagerClass = 'Ae_Upload_Manager';
    
    /**
     * @var Ae_Upload_File
     */
    var $_upload = false;
    
    /**
     * @var Ae_Upload_File
     */
    var $_newUpload = false;
    
    var $_fileData = false;
    
    function getError() {
        if ($this->_error === false) {
            $this->_error = $this->_context->getData('error', false);
        }
        return $this->_error;
    }
    
    function getRetFieldName() {
        if ($this->_retFieldName === false) {
            $this->_retFieldName = $this->_context->getData('retFieldName', false);
        }
        return $this->_retFieldName;
    }
    
    function isOk() {
        $res = $this->_context->getData('ok', false) !== false;
        return $res;
    }
    
    function isCancel() {
        return $this->_context->getData('cancel', false) !== false;
    }
    
    function isUpload() {
        return $this->_context->getData('upload', false) !== false;
    }
    
    /**
     * @return Ae_Upload_File
     */
    function getUpload() {
        if ($this->_upload === false) {
            if (($uid = $this->getUploadId()) !== false) {
                $m = & $this->getUploadManager();
                $this->_upload = & $m->getUpload($uid, true);
            }
        }
        return $this->_upload;
    }
    
    /**
     * @return Ae_Upload_File
     */
    function getNewUpload() {
        if ($this->_newUpload === false) {
            if (($uid = $this->newUploadId) !== false) {
                $m = & $this->getUploadManager();
                $this->_newUpload = & $m->getUpload($uid);
            }
        }
        return $this->_newUpload;
    }
    
    
    function getRetFormName() {
        if ($this->_retFormName === false) {
            $this->_retFormName = $this->_context->getData('retFormName');
        }
        return $this->_retFormName;
    }
    
    function getUploadId() {
        if ($this->_uploadId === false) {
            $this->_uploadId = $this->_context->getData('uploadId', $this->oldUploadId);
        }
        return $this->_uploadId;
    }
    
    function getRetLabelId() {
        if ($this->_retLabelId === false) {
            $this->_retLabelId = $this->_context->getData('retLabelId', false);
        }
        return $this->_retLabelId;
    }
    
    function getFileChangeFn() {
        if ($this->_fileChangeFn === false) {
            $this->_fileChangeFn = $this->_context->getData('fileChangeFn', $this->fileChangeFn);
        }
        return $this->_fileChangeFn;
    }
    
    function getMethodParamValue() {
        if ($this->showUploadItem) $res = 'showUploadItem';
        elseif ($this->isOk()) $res = 'ok';
        elseif ($this->isCancel()) $res = 'cancel';
        elseif ($this->isUpload()) $res = 'upload';
        else $res = parent::getMethodParamValue();
        return $res;
    }
    
    function executeDownload() {
        if ($this->canDownload) {
            if ($u = & $this->getUpload()) $this->_templatePart = 'download';
                else $this->_templatePart = '404'; 
        } else $this->_templatePart = '404';
    }
    
    function executeView() {
        if ($this->canView) {
            if ($u = & $this->getUpload()) $this->_templatePart = 'view';
                else $this->_templatePart = '404'; 
        } else $this->_templatePart = '404';
    }
    
    function executeOk() {
        if ($u = & $this->getUpload()) {
            //if ($n = $u->getName()) $this->app->purgeUploads($n);
            $this->_templatePart = 'retVal';
        } else $this->executeUpload();
    }
    
    function executeCancel() {
        if ($u = & $this->getUpload()) {
            $m = & $this->getUploadManager();            
            if ($id = $u->getId()) $m->purgeUploads($id);
        }
        $this->_templatePart = 'close';
    }
    
    function getFileData() {
        $paramPath = $this->_context->mapParam('file', true);
        $f = Ae_Util::getUploadedFilesByHierarchy();
        $fileData = Ae_Util::getArrayByPath($f, $paramPath, false);
        return $fileData;
    }
    
    function executeUpload() {
        $f = false;
        $m = & $this->getUploadManager();
        $paramPath = $this->_context->mapParam('file', true);
        $fileData = false;
        $this->_templatePart = 'default';
        $fileData = $this->getFileData();
        if ($fileData && (!isset($fileData['error']) || !$fileData['error']) ) {
            if ($u = & $this->getUpload()) {
                $m->purgeUploads($u->getId());
                $this->_upload = $this->_uploadId = false;
            }
            $options = array('userData' => $fileData);
            $this->upload = & $m->factory($options);
            if ($this->upload->getError()) {
                $this->_error = sprintf($this->uploadErrorMessage, $this->upload->getFilename());
            } else {
                if ($m->tempStoreUpload($this->upload)) {
                    $this->_uploadId = $this->upload->getId();
                    $this->_error = false;
                } else {
                    $m->purgeUploads($this->upload->getId());
                    $this->_error = $this->uploadStoreErrorMessage;
                    $this->_upload = false;
                    $this->_uploadId = false;
                }
            }

            $params = array();
            if ($this->_error === false && $this->autoClose) {
                $params['ok'] = true;
                $this->_response->redirectUrl = $this->getUrl($params);
            }
        }
    }
    
    function executeShowUploadItem() {
        $this->_templatePart = 'uploadItem';
    }
    
    function getUploadWindowUrl() {
        return $this->_getPartialUrlWithOpenParam('uploadId', array('action' => 'upload'));
    }
    
    function getPartialUrlWithOpenParam($paramName, $attribs = array()) {
        $url = & $this->getUrl($attribs);
        $iip = $this->_context->mapParam($paramName);
        if ($url->query) {
            Ae_Util::unsetArrayByPath($url->query, Ae_Util::pathToArray($iip));
            $iip = '&'.$iip.'='; 
        } else {
            $iip = '?'.$iip.'=';
        }
        $res = $url->toString().$iip;
        return $res;
    }

    function _getPreparedUploadManagerOptions() {
        $u = $this->_uploadManagerOptions;
        if ($this->canDownload && !isset($u['downloadLink'])) {
            $u['downloadLink'] = $this->getPartialUrlWithOpenParam('uploadId', array('action' => 'download'));
        }
        if ($this->canView && !isset($u['viewLink'])) {
            $u['viewLink'] = $this->getPartialUrlWithOpenParam('uploadId', array('action' => 'view'));
        }
        return $u;
    }
    
    /**
     * @return Ae_Upload_Manager
     */
    function getUploadManager() {
        if ($this->_uploadManager === false) {
            $this->_uploadManager = & Ae_Util::factoryWithOptions($this->_getPreparedUploadManagerOptions(), $this->defaultUploadManagerClass, 'class', false);
        }
        return $this->_uploadManager;
    }
    
    function setUploadManager(& $uploadManager) {
        $this->_uploadManager = & $uploadManager;
    }
    
    function getUploadManagerOptions() {
        return $this->_uploadManagerOptions;
    }
    
    function setUploadManagerOptions($options) {
        $this->_uploadManagerOptions = $options;
        $this->_uploadOptions = false;
    }
    
    function reset() {
        $f = false;
        $this->_uploadId = false;
        $this->_upload = false;
        $this->_newUpload = false;
        $this->_bound = false;
        $this->_response = false;
        $this->_template = false;
    }
    
    function getMaxUploadSize() {
        return ini_get('upload_max_filesize');
    }
    
}

?>