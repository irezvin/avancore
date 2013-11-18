<?php

class Ac_Result_Stage_Deferreds extends Ac_Result_Stage_Morph {

    // It is ALWAYS false
    protected $renderDeferreds = false;
    
    protected $deferreds = array();
    
    protected $results = array();
    
    protected $evaluators = array();
    
    /**
     * @var bool
     */
    protected $renderStrings = true;

    /**
     * @param bool $renderStrings
     */
    function setRenderStrings($renderStrings) {
        $this->renderStrings = $renderStrings;
    }

    /**
     * @return bool
     */
    function getRenderStrings() {
        return $this->renderStrings;
    }
    
    /**
     * @param bool $isBeforeStore
     */
    function setIsBeforeStore($isBeforeStore) {
        $this->isBeforeStore = (bool) $isBeforeStore;
    }
    
    protected function endItem($item) {
        parent::endItem($item);
        $this->processItem($item, $this->getCurrentResult(), $this->getCurrentProperty(), $this->getCurrentOffset());
    }
    
    protected function processItem($item, Ac_Result $result, $property, $offset) {
        $shouldCollect = false;
        if ($item instanceof Ac_I_Deferred_ResultAware) {
            $shouldCollect = $item->shouldEvaluate($this);
        } elseif ($item instanceof Ac_I_Deferred) {
            $shouldCollect = true;
        } elseif ($item instanceof Ac_I_StringObject_WithRender) {
            if ($this->renderStrings) {
                $result->replaceObjectInContent($item, $item->getRenderedString());
            }
        }
        if ($shouldCollect) {
            $this->collectDeferred($item, $property, $offset);
        }
    }
    
    protected function collectDeferred(Ac_I_Deferred $item, $property, $offset) {
        $result = $this->getCurrentResult();
        $this->results[$resHash = spl_object_hash($this->getCurrentResult())]['result'] = $this->getCurrentResult();
        $defId = spl_object_hash($item);
        $evProto = $item->getEvaluatorPrototype();
        $evHash = Ac_Evaluator::getArrayHash(Ac_Util::toArray($evProto));
        $evArg = $item instanceof Ac_I_Deferred_Substitute? $item->getEvaluatorArg() : $item;
        $this->results[$resHash = spl_object_hash($this->getCurrentResult())]['items'][$defId] = array(
            'p' => $property,
            'o' => $offset,
        );
        $this->deferreds[$defId] = array('r' => $result, 'd' => $item, 'p' => $property, 'o' => $offset);
        $this->evaluators[$evHash]['prototype'] = $evProto;
        $this->evaluators[$evHash]['deferreds'][$defId] = $evArg;
    }
    
    function invoke() {
        if ($this->isComplete) throw new Ac_E_InvalidUsage("renderDeferreds() already called; check with getIsComplete() first");
        $this->resetTraversal();
        $this->traverse();
        /**
         * $this->results: [ 
         *      $resultSplHash => [
         *          'result' => $objResult, 
         *          'items' => [
         *              $defSplHash => ['p' => $propertyId, 'o' => $offset] 
         *          ]
         *      ]
         * ] 
         * $this->evaluators: [
         *      $evProtoHashOrEvObjSplHash => [
         *          'prototype' => $evProtoOrEvObject,
         *          'deferreds' => [ $defSplHash => $evArg ]
         *      ]
         * ]
         * $this->deferreds: [ $defSplHash => [ 'r' => $resultObj, 'd' => $deferredObj, 'p' => $propertyId, 'o' => $offset ] ]
         */

        $allDeferreds = $this->deferreds;
        $preg = Ac_StringObject::getPregExpr();
            $evInstances = array();
        
        do {
        
            // TODO: add some infinite-loop protection
            
            $results = $this->results; $this->results = array();
            $deferreds = $this->deferreds; $this->deferreds = array();
            $evaluators = $this->evaluators; $this->evaluators = array();
      
            $shouldRepeat = false;
            
            foreach ($evaluators as $hash => $evData) {
                if (!isset($evInstances[$hash])) {
                    $ev = Ac_Prototyped::factory($evData['prototype'], 'Ac_I_Deferred_Evaluator');
                    $evInstances[$hash] = $ev;
                } else {
                    $ev = $evInstances[$hash];
                }
                $def = $evData['deferreds'];
                $evRes = $ev->evaluateDeferreds($def);

                $sameKeys = (count($def) == count($evRes)) && !array_diff_key($def, $evRes);
                if (!$sameKeys) throw new Ac_E_InvalidImplementation('Ac_I_Deferred_Evaluator::evaluateDeferreds(array $deferreds) should return array with same keys as in the argument');
                
                foreach ($evRes as $defId => $v) {
                    $v = ''.$v;
                    $shouldRepeat = $shouldRepeat || preg_match("/$preg/", $v);
                    if ($deferreds[$defId]['p'] != 'content') 
                        throw new Ac_E_InvalidUsage('Replacing deferreds in non-content property is not implemented yet');
                    $deferreds[$defId]['r']->replaceObjectInContent($deferreds[$defId]['d'], $v);
                }
            }
            
            if ($shouldRepeat) { 
                // unfortunately every non-retraversing method will be inaccurate
                // TODO: re-traverse only places of interest
                $this->resetTraversal();
                $this->traverse();
            }
            
        } while ($this->deferreds);
        
    }
    
    function setRenderDeferreds($renderDeferreds) {
        if ($renderDeferreds) trigger_error("\$setRenderDeferreds(TRUE) does not have effect in Ac_Result_Stage_Deferreds", E_USER_NOTICE);
    }
    
    protected function renderIfNecessary($item) {
        return false;
    }
    
    
}