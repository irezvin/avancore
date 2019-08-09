<?php

class Ac_Cg_Template_Skel_Native extends Ac_Cg_Template_Skel {
    
    /**
     * @var Ac_Cg_Layout_Native
     */
    protected $layout = false;

    protected function getFileMap() {
        $res = Ac_Util::m(parent::getFileMap(), array(
            '{pathSql}' => array('content' => Ac_Cg_Generator::CONTENT_DIR),
            '{pathVendor}/.htaccess' => array('templatePart' => 'denyHtaccess'),
            '{pathWeb}/admin.php' => 'webAdminPhp',
            '{pathWeb}/index.php' => 'webIndexPhp',
            '{pathVar}/.htaccess' => array('templatePart' => 'denyHtaccess'),
        ));
        return $res;
    }
    
    function setLayout(Ac_Cg_Layout $layout) {
        if (!$layout instanceof Ac_Cg_Layout_Native)
            throw Ac_E_InvalidCall::wrongClass ('layout', $layout, 'Ac_Cg_Layout_Native');
        parent::setLayout($layout);
    }

    /**
     * @return Ac_Cg_Layout_Native
     */
    function getLayout() {
        return parent::getLayout();
    }    
    
}