<?php

class Ae_Upload_Controller_Template extends Ae_Template_Html {
    
    var $langFileToUpload = 'File to upload';
    var $langFilename = 'Filename';
    var $langMimeType = 'Mime type';
    var $langFilesize = 'File size';
    var $langUploadAnotherFile = 'Upload another file';
    var $langUseThisFile = 'Use this file';
    var $langReplaceFile = 'Replace file';
    var $langUploadFile = 'Upload file';
    var $langCancel = 'Cancel';
    var $langUploadFailed = 'File upload failed';
    var $langNoUpload = 'No file uploaded';
    var $langUploadNewFile = 'Upload new file';
    var $langDownloadFile = 'Download file';
    var $langMaxUploadSize = 'File size should not exceed %s';
    
    var $readOnly = false;
    var $retFieldName = false;
    var $retFormName = false;
    var $retLabelId = false;
    var $error = false;
    var $paramName = false;
    var $newUploadId = false;
    var $uploadWindowUrl = false;
    
    var $uploadCharset = false;
    
    var $fileChangeFn = false;
    
    /**
     * @var Ae_Upload_Controller
     */
    var $controller = false;
    
    /**
     * @var Ae_Url
     */
    var $url = false;
    
    /**
     * @var Ae_Upload_File
     */
    var $upload = false;
    
    /**
     * @var Ae_Upload_File
     */
    var $newUpload = false;
    
    protected function rShowUpload() {
        if ($this->upload) {
?>
        <div>
            <b><?php echo($this->langFileToUpload); ?></b>
            <br />
            <?php echo $this->langFilename.': '.$this->upload->getFilename(); ?><br />
            <?php echo $this->langMimeType.': '.$this->upload->getMimeType(); ?><br />
            <?php echo $this->langFilesize.': '.$this->upload->getContentSize(); ?><br />
        </div>
<?php       
        }
        
}
    
    function showClose() {
?>
        <script type='text/javascript'>
            window.close();
        </script>
<?php
    }
    
    function showRetVal() {
        $this->htmlResponse->noWrap = true;
        if ($this->upload) {
?>
        <script type='text/javascript'>
            var field, lbl;
<?php       if (strlen($this->retFieldName)) { ?>
            if ((field = window.opener.document.getElementById(<?php $this->echoJson($this->retFieldName); ?>))) {
                field.value = <?php $this->echoJson($this->upload->getId()); ?>;
            }
<?php       if ($this->fileChangeFn) { ?>
                window.fileChangeFn = <?php echo $this->fileChangeFn; ?>;
                window.fileChangeFn(<?php $this->echoJson($this->upload->getId()); ?>);
<?php       } ?>             
<?php       } ?>
<?php       if (strlen($this->retLabelId)) { ?>
            if ((lbl = window.opener.document.getElementById(<?php $this->echoJson($this->retLabelId); ?>))) {
                lbl.innerHTML = <?php $this->echoJson($this->upload->getDescr()); ?>;
            } 
<?php       } ?>
        </script>
<?php   $this->showClose(); ?>

<?php
        }
    }
    
    function showDefault() {
        $this->htmlResponse->noWrap = true;
?>
        <div>
<?php       if ($this->error) { ?>
            <p style='color: red; text-align: center'><?php echo htmlspecialchars($this->error); ?></p>
<?php       } ?>
            <?php $this->openFormElement(array(), array('enctype' => 'multipart/form-data', 'method' => 'post'), false); ?>
<?php           if ($this->upload) { ?>
<?php           $this->rShowUpload(); ?>
                <input type='submit' name='<?php $this->mapParam('ok'); ?>' value=<?php echo($this->langUseThisFile ); ?> />
                <p><?php echo($this->langUploadAnotherFile); ?></p> 
<?php           } ?>
                <input type='file' name='<?php $this->mapParam('file') ?>' />
                <input type='submit' name='<?php $this->mapParam('upload'); ?>' value='<?php if ($this->upload) { echo($this->langReplaceFile);  } else { echo($this->langUploadFile);  } ?>' />
                <br />
<?php           if (strlen($s = $this->controller->getMaxUploadSize())) { ?>
                <p><?php echo(sprintf($this->langMaxUploadSize, $s)); ?></p>
<?php           } ?>                
                <br />
                <input type='submit'  name='<?php $this->mapParam('cancel'); ?>' value='<?php echo($this->langCancel); ?>' />
            </form>
        </div>
<?php
    }
    
    function showUploadItem() {
        
            $readOnly = $this->readOnly;
            $upload = & $this->upload;
            $newUpload = & $this->newUpload;
            $paramName = $this->paramName;
            $valueId = $paramName.'_value';
            $titleId = $paramName.'_title';
            $value = $this->newUploadId;
            
            $this->addJsLib('uploadFiles.js');
         
?>
<?php       if ($upload) { ?>
                <?php echo $upload->getDescr(); ?>
<?php           if ($dl = $upload->getDownloadUrl()) { ?>
                <br /><a href='<?php echo htmlspecialchars($dl); ?>'><?php echo($this->langDownloadFile); ?></a>
<?php           } ?>
<?php       } else {  
                echo($this->langNoUpload);  
            } 
?>
<?php       if (!$readOnly) { ?>
                <input type='hidden' name='<?php echo htmlspecialchars($paramName); 
                    ?>' id='<?php echo htmlspecialchars($valueId); 
                    ?>' value='<?php echo htmlspecialchars($value); 
                    ?>' />
                <a href='#' onclick='showSelectFile("<?php 
                    $this->d($this->controller->getPartialUrlWithOpenParam('uploadId', array('retFieldName' => $valueId, 'retLabelId' => $titleId, 'fileChangeFn' => $this->fileChangeFn))); 
                ?>", document.getElementById("<?php echo htmlspecialchars($valueId); ?>").value<?php if ($this->fileChangeFn !== false) { echo ', '.$this->fileChangeFn; } ?>); return false;'> <?php echo($this->langUploadNewFile); ?></a>
                <br /><span id='<?php echo htmlspecialchars($titleId); ?>'><?php if ($newUpload) echo $newUpload->getDescr(); ?></span>
<?php       } ?>            
<?php
    }
    
    function _getContentHeaderSuffix() {
        $res = 'filename="'.$this->upload->getFilename().'"';
        return $res;
    }
    
    function _getContentType() {
        $res = $this->upload->getMimeType();
        if (strlen($this->uploadCharset)) $res .= '; charset='.$this->uploadCharset;
        return $res;
    }
        
    
    function showDownload() {
        //$this->upload->stream(true, false);
        $this->htmlResponse->extraHeaders[] = 'Content-Type: '.$this->_getContentType();
        $this->htmlResponse->extraHeaders[] = 'Content-Disposition: attachment; '.$this->_getContentHeaderSuffix();
        $this->htmlResponse->noHtml = true;
        $this->htmlResponse->noWrap = true;
        echo $this->upload->getContent();
    }
    
    function showView() {
        //$this->upload->stream(true, false);
        $this->htmlResponse->extraHeaders[] = 'Content-Type: '.$this->_getContentType();
        $this->htmlResponse->extraHeaders[] = 'Content-Disposition: inline; '.$this->_getContentHeaderSuffix();
        $this->htmlResponse->noHtml = true;
        $this->htmlResponse->noWrap = true;
        echo $this->upload->getContent();
    }
    
    function show404() {
        $this->htmlResponse->extraHeaders[] = 'HTTP/1.0 404 Not Found';
        //$this->htmlResponse->extraHeaders[] = array('HTTP/1.0�404�Not�Found', 404);
        //$this->htmlResponse->extraHeaders[] = 'Status: 404 Not Found';
        //$this->htmlResponse->noWrap = true;
        $this->htmlResponse->content = '<h1>404: Not Found</h1>';
    }
        
}

?>