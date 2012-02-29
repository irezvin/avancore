<?php

class Ae_Form_Control_Template_Placeholders extends Ae_Form_Control_Template_Basic {
    
    var $strTemplate = '{(etc)}';
    
    var $controlWrapper = '{control}';
    
    function showComposite(Ae_Form_Control_Composite $control) {
        $chunks = $this->getChunks($this->strTemplate);
        echo $this->processChunks($control, $chunks);
    }
    
    function getChunks($template) {
        $res = preg_split('#(\{[^\}]+\})#', $template, -1, PREG_SPLIT_DELIM_CAPTURE);
        return $res;
    }
    
    function processChunks(Ae_Form_Control_Composite $control, $chunks) {
        $etcChunkKey = false;
        $controlsById = array();
        foreach ($control->getOrderedDisplayChildren() as $child) {
            $controlsById[$child->name] = $child;
        } 
        $res = array();
        foreach ($chunks as $i => $chunk) {
            $val = $chunk;
            if (preg_match('#^\{([^\}]+)\}$#', trim($chunk), $matches)) {
                if (preg_match('#^\(([^\)]+)\)$#', trim($matches[1]), $m2)) {
                    $m2s = preg_split('#\s+#', $m2[1]);
                    if (count($m2s)) {
                        switch(trim($m2s[0])) {
                            case 'etc':
                                $etcChunkKey = $i;
                                break;
                        }
                    }
                } elseif (!strncmp($mc = trim($matches[1]), 'get:', 4)) {
                	$propId = substr($matches[1], 4);
                    if (($m = $control->getModel())) {
                        $val = Ae_Autoparams::getObjectProperty($m, trim($propId));
                    } else {
                        $val = '';
                    }
                } elseif (!strncmp($mc = trim($matches[1]), 'own:', 4)) {
                	$propId = substr($matches[1], 4);
                    $val = Ae_Autoparams::getObjectProperty($control, trim($propId));
                } elseif (substr(trim($matches[1]), 0, 1) == '#') {
                    $path = substr(trim($matches[1]), 1);
                    if (($c = $control->getControlByPath($path)) && ($c->getDisplayParent() === $control)) {
                        $val = $this->renderChildContainer($c, $controlsById);
                    }
                } elseif (!strncmp($mc = trim($matches[1]), 'lng:', 4)) {
                	$lngId = substr($matches[1], 4);
                	$val = Pmt_Lang_Resource::getInstance()->getString($lngId);
                } elseif (!strncmp($mc = trim($matches[1]), 'span:', 5)) {
                	$id = trim(substr($matches[1], 5));
                    if (isset($controlsById[$id])) $val = $this->renderChildContainer($controlsById[$id], $controlsById, 'spanWrapper');
                } else {
                    $id = trim($matches[1]);
                    if (isset($controlsById[$id])) $val = $this->renderChildContainer($controlsById[$id], $controlsById);
                }
            }
            $res[$i] = $val;
        }
        if ($etcChunkKey === false) $etcChunkKey = count($chunks);
        $res[$etcChunkKey] = $this->renderEtc($controlsById);
        $res = implode('', $res);
        return $res;
    }
    
    function renderChildContainer(Ae_Form_Control $child, & $controlsById, $part = 'divWrapper') {
        $cid = $child->name;
        $control = $child->fetchPresentation();
        if ($child->showWrapper) $control = $this->fetch($part, array($child, $control));
        if (isset($controlsById[$cid])) unset($controlsById[$cid]);
        $res = strtr($this->controlWrapper, array(
            '{control}' => $control,
        ));
        return $res;
    }
    
    function renderEtc($controls) {
        $x = $controls;
        $r = array();
        foreach ($x as $control) {
            $r[] = $this->renderChildContainer($control, $controls);
        }
        $res = implode('', $r);
        return $res; 
    }
    
}