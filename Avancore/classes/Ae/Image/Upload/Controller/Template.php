<?php

Ae_Dispatcher::loadClass('Ae_Upload_Controller_Template');

class Ae_Image_Upload_Controller_Template extends Ae_Upload_Controller_Template {

    var $langWidth = 'Width';

    var $langHeight = 'Height';
    
    /**
     * @var Ae_Image_Upload
     */
    var $upload = false;
    
    /**
     * @var Ae_Image_Upload
     */
    var $newUpload = false;
    
    protected function rShowUpload() {
        if ($this->upload) {
?>
        <div>
            <b><?php echo($this->langFileToUpload); ?></b>
            <br />
            <?php echo $this->upload->getImgTag(array(), true); ?>
            <br />
            <?php echo $this->langWidth.': '.$this->upload->getWidth(); ?><br />
            <?php echo $this->langHeight.': '.$this->upload->getHeight(); ?><br />
            <?php echo $this->langFilename.': '.$this->upload->getFilename(); ?><br />
            <?php echo $this->langMimeType.': '.$this->upload->getMimeType(); ?><br />
            <?php echo $this->langFilesize.': '.$this->upload->getContentSize(); ?><br />
        </div>
<?php       
        }
    }
    
    function showThumb() {
        $this->htmlResponse->extraHeaders[] = 'Content-Type: '.$this->upload->getMimeType();
        $this->htmlResponse->extraHeaders[] = 'Content-Disposition: inline; filename=thumb-'.$this->upload->getFilename();
        $this->htmlResponse->noHtml = true;
        $this->htmlResponse->noWrap = true;
        echo $this->upload->getThumbnail();
    }
    
        
}

?>