<?php

/**
 *
 */

/**
 * Ac_Cr_Result_Response combines data returned by action method into Response in most straight way.
 * 
 * There are three possible ways for an action method to return something.
 * 
 * - Provide prototype of Ac_Response or an instance of Ac_Response using $this->result['response']
 * - Return some result
 * - Write some output using echo
 * 
 * If action method uses two or all three ways to return data, resulting Response is created using several rules.
 * 
 * - If prototype or Ac_Response instace are provided, it is used as a base for a merging
 * - If action method returns some result, it is intelligently converted into Ac_Response using Ac_Cr_Result_Response_Decision
 * - Output is combined with 'content' registry item unless result is returned, otherwise is is treated as debug data
 *
 * This default behavior may be changed using $outputTarget or $returnTarget which are instances or prototypes of Ac_I_RegistryUpdater.
 */
class Ac_Cr_Result_Response extends Ac_Cr_Result {
    
    protected $currentResponse = array();
    
    protected $decision = 'Ac_Cr_Result_Response_Decision';

    protected $outputTarget = false;

    protected $returnTarget = false;
    
    protected $response = false;
    
    function setResponse($response) {
        return $this->setCurrentResponse($response);
    }
    
    function setCurrentResponse($response) {
        $this->currentResponse = $response;
        $this->response = false;
    }
    
    function getCurrentResponse() {
        return $this->currentResponse;
    }
    
    /**
     * @return Ac_Response
     */
    function getObjectResponse() {
        if (!is_object($this->currentResponse))
            $this->currentResponse = Ac_Prototyped::factory ($this->currentResponse, 'Ac_Response');
        return $this->currentResponse;
    }
    
    /**
     * @return Ac_Response
     */
    protected function instantiateResponse($arrResponse) {
        if (isset($arrResponse['class'])) {
            $class = $arrResponse['class'];
            unset($arrResponse['class']);
        } else {
            $class = 'Ac_Response';
        }
        $res = new $class;
        if (!$res instanceof Ac_Response) {
            throw new Exception("$class is not descendant of Ac_Response");
        }
        $res->setRegistry($arrResponse);
        return $res;
    }
    
    /**
     * @return Ac_Response
     */
    function getResponse() {
        if (!$this->response) {
            if (!is_null($this->methodReturnValue) && !$this->returnTarget) {
                $baseResponse = $this->getDecision()->getResponseFor($this->methodReturnValue);
                if (strlen($this->methodOutput)) {
                    if (!$this->outputTarget) $outputUpdater = new Ac_Response_Updater_Debug;
                        else $outputUpdater = $this->outputTarget;
                    $outputUpdater->update($baseResponse, $this->methodOutput);
                }
                if ($this->currentResponse) {
                    if (!is_object($this->currentResponse)) $or = $this->instantiateResponse($this->currentResponse);
                        else $or = $this->currentResponse;
                    $baseResponse->mergeRegistry($or);
                }
            } else {
                if ($this->currentResponse) {
                    if (!is_object($this->currentResponse)) {
                        $baseResponse = $this->instantiateResponse($this->currentResponse);
                    } else {
                        $baseResponse = clone $this->currentResponse;
                    }
                } else {
                    $baseResponse = new Ac_Response;
                }
                if (!is_null($this->methodReturnValue)) {
                    $target = $this->getReturnTarget();
                    $target->update($baseResponse, $this->methodReturnValue);
                }
                if (strlen($this->methodOutput)) {
                    $target = $this->getOutputTarget();
                    if (!$target) $target = new Ac_Response_Updater_Content;
                    $target->update($baseResponse, $this->methodOutput);
                }
            }
            $this->response = $baseResponse;
        }
        return $this->response;
    }
    
    function setDecision($decision) {
        $this->decision = $decision;
    }

    /**
     * @param bool $dontInstantiate
     * @return Ac_Cr_Result_Response_Decision
     */
    function getDecision($dontInstantiate = false) {
        if (!$dontInstantiate && !is_object($this->decision))
            $this->decision = Ac_Prototyped::factory($this->decision, 'Ac_I_ResultResponseDecision');
        return $this->decision;
    }

    function setOutputTarget($outputTarget) {
        if ($outputTarget && !is_object($outputTarget))
            $this->outputTarget = Ac_Prototyped::factory ($outputTarget, 'Ac_I_RegistryUpdater');
        $this->outputTarget = $outputTarget;
    }

    /**
     * @param bool $dontInstantiate
     * @return Ac_I_RegistryUpdater
     */
    function getOutputTarget() {
        return $this->outputTarget;
    }

    function setReturnTarget($returnTarget) {
        if ($returnTarget && !is_object($returnTarget))
            $this->returnTarget = Ac_Prototyped::factory ($returnTarget, 'Ac_I_RegistryUpdater');
        else $this->returnTarget = $returnTarget;
    }

    /**
     * @param bool $dontInstantiate
     * @return Ac_I_RegistryUpdater
     */
    function getReturnTarget() {
        return $this->returnTarget;
    }    
    
    function setMethodOutput($methodOutput) {
        parent::setMethodOutput($methodOutput);
        $this->response = false;
    }
    
    function setMethodReturnValue($methodReturnValue) {
        parent::setMethodReturnValue($methodReturnValue);
        $this->response = false;
    }
    
}