<?php

class Ac_Model_Relation_Provider_Evaluator {
    
    /**
     * @var Ac_Application
     */
    protected $application = false;

    function setApplication(Ac_Application $application) {
        $this->application = $application;
    }

    /**
     * @return Ac_Application
     */
    function getApplication() {
        return $this->application;
    }    
    
    protected function evaluateMapperProvider(array $relationProps, $preferWhenSeveralFields = false) {
        $res = false;
        $mapper = false;
        if (isset($relationProps['destMapper']) && $relationProps['destMapper'])
            $mapper = $relationProps['destMapper'];
        elseif ($relationProps['destMapperClass']) {
            $mapper = $this->application? $this->application->getMapper($relationProps['destMapperClass']) 
                : Ac_Application::getDefaultInstance()->getMapper($relationProps['destMapperClass']);
        }
        if ($mapper) {
            
                
            // todo: fix this when mapper provider will support several fields
            
            $hasMultiField = (isset($relationProps['fieldLinks']) && is_array($relationProps['fieldLinks']) && count($relationProps['fieldLinks']) > 1)
                || (isset($relationProps['fieldLinks2']) && is_array($relationProps['fieldLinks2']) && count($relationProps['fieldLinks2']) > 1);

            $hasSqlWhere = isset($relationProps['destWhere']) && is_string($relationProps['destWhere']);
            $hasSqlOrdering = isset($relationProps['destOrdering']) && 
                is_string($relationProps['destOrdering']) && preg_match('/[ ,]/', $relationProps['destOrdering']);
            $hasExtraJoins = isset($relationProps['destExtraJoins']) && $relationProps['destExtraJoins'];

            if (!(($hasMultiField && !$preferWhenSeveralFields) || $hasSqlWhere || $hasSqlOrdering || $hasExtraJoins)) {
            
                // check if dest key is PK
                $destKeys = $relationProps['fieldLinks2']? $relationProps['fieldLinks2'] : $relationProps['fieldLinks'];
                $dk = array_values($destKeys);
                $idf = $mapper->getIdentifierPublicField();
                $isPk = count($dk) == 1 && $dk[0] == $idf;
                $res = array(
                    'mapper' => $mapper
                );
                if ($isPk) {
                    $res['class'] = 'Ac_Model_Relation_Provider_Mapper_Pk';
                } else {
                    $res['class'] = 'Ac_Model_Relation_Provider_Mapper_Omni';
                    $res['keys'] = $dk;
                }
                if (isset($relationProps['destWhere']) && $relationProps['destWhere']) 
                    $res['query'] = $relationProps['destWhere'];
                
                if (isset($relationProps['destOrdering']) && $relationProps['destOrdering']) 
                    $res['sort'] = $relationProps['destOrdering'];
            }
        }
        return $res;
    }
    
    function evaluateProvider(array $relationProps) {
        // todo: replace with something better when decision 
        // about provider class is made
        if (isset($relationProps['nonSql']) && $relationProps['nonSql']) {
            $res = $this->evaluateMapperProvider($relationProps, true);
            if (!$res) {
                throw new Exception ("Cannot evaluate Provider for non-sql storage (can't retrieve Mapper)");
            }
        } else {
            $omni = true;
            if (!(isset($relationProps['fieldLinks2']) && $relationProps['fieldLinks2'])) { // not of many-to-many type
                $res = $this->evaluateMapperProvider($relationProps);
                if ($res) $omni = false;
            }
            if ($omni) $res = Ac_Model_Relation_Provider_Sql_Omni::evaluatePrototype($relationProps);
        }
        if (isset($relationProps['destIsUnique'])) $res['unique'] = $relationProps['destIsUnique'];
        return $res;
    }
    
}