<?php

interface Ae_I_Param_Source_Composite extends Ae_I_Param_Source {

    function listSources($mode = false, $path = false);

    function getSource($index);

    function addSource(Ae_I_Param_Source $source, $path, $mode = Ae_I_Param_Source::modeOverride);

    

}