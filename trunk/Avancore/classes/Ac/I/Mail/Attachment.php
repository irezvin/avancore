<?php

interface Ac_I_Mail_Attachment {
    
    function getAttachmentFilename();
    
    function getAttachmentContent();
    
    function getAttachmentContentDisposition();
    
    function getAttachmentContentType();
    
}