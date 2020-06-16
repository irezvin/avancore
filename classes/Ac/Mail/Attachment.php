<?php

class Ac_Mail_Attachment extends Ac_Prototyped implements Ac_I_Mail_Attachment {
    
    protected $attachmentContent = false;

    protected $attachmentContentDisposition = 'attachment';

    protected $attachmentContentType = false;

    protected $attachmentFilename = false;

    function setAttachmentContent($attachmentContent) {
        $this->attachmentContent = $attachmentContent;
    }

    function getAttachmentContent() {
        return $this->attachmentContent;
    }

    function setAttachmentContentDisposition($attachmentContentDisposition) {
        $this->attachmentContentDisposition = $attachmentContentDisposition;
    }

    function getAttachmentContentDisposition() {
        return $this->attachmentContentDisposition;
    }

    function setAttachmentContentType($attachmentContentType) {
        $this->attachmentContentType = $attachmentContentType;
    }

    function getAttachmentContentType() {
        return $this->attachmentContentType;
    }

    function setAttachmentFilename($attachmentFilename) {
        $this->attachmentFilename = $attachmentFilename;
    }

    function getAttachmentFilename() {
        return $this->attachmentFilename;
    }
    
}