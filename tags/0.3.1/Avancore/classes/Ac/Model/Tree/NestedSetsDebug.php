<?php

class Ac_Tree_NestedSetsDebug {
    
    protected $buffer = false;
    
    protected $bufferStack = array();
    
    var $mapperClasses = array();
    
    protected $writer = false;

    function setWriter(Ac_Debug_Log_AbstractWriter $writer = null) {
        $this->writer = $writer;
    }

    /**
     * @return type 
     */
    function getWriter() {
        return Ac_Debug_Log_AbstractWriter;
    }
    
    function beginCollectStatements() {
        if ($this->buffer !== false) {
            array_push ($this->bufferStack, $this->buffer);
            $this->buffer = array();
        } else {
            $this->buffer = array();
        }
    }
    
    function getCollectedStatements() {
        return $this->buffer;
    }
    
    function endCollectStatements($flush = false) {
        $res = $this->buffer;
        
        if (count($this->bufferStack)) {
            $this->buffer = array_pop($this->bufferStack);
        }
        if ($res !== false && $flush) {
            if ($this->buffer) $this->buffer = array_merge($this->buffer, $res);
                else if ($this->writer) foreach ($res as $stmt) {
                    $this->writer->write(null, array($stmt));
                }
        }
        return $res;
    }
    
    function canDebug($item) {
        $res = false;
        if ($item instanceof Ac_Model_Tree_NestedSetsImpl) {
            if ($this->mapperClasses) {
                $c = $item->getContainer();
                foreach ($this->mapperClasses as $mc) {
                    if ($item->getMapper() instanceof $mc) {
                        $res = true;
                        break;
                    }
                }
            } else $res = true;
        }
        return $res;
    }
    
    function getProblems($impl) {
        $container = $impl->getContainer();
        $res = false;
        $mapper = $container->getMapper();
        $problems = $mapper->findProblems(false/*, $modelId*/);
        if ($problems['wrongParents'] || $problems['wrongDepth']) $res = $problems;
        return $res;
    }
    
    function log($_) {
        if ($this->writer) {
            $args = func_get_args();
            $this->writer->write(null, $args);
        }
    }
    
    function implCallback($stage, $impl, $res = null) {
        if ($this->canDebug($impl)) {
            if ($stage == Ac_Model_Tree_NestedSetsImpl::debugBeginSelfStore || $stage == Ac_Model_Tree_NestedSetsImpl::debugBeginSelfDelete) {
                $this->beginCollectStatements();
                $this->buffer['_oldProblems'] = $this->getProblems($impl);
                $db = $impl->getNestedSets()->getDb();
                //$db->query("START TRANSACTION");
            } elseif ($stage == Ac_Model_Tree_NestedSetsImpl::debugEndSelfStore || $stage == Ac_Model_Tree_NestedSetsImpl::debugEndSelfDelete) {
                $oldProb = $this->buffer['_oldProblems'];
                $newProb = $this->getProblems($impl);
                unset($this->buffer['_oldProblems']);
                if ($newProb && $newProb != $oldProb) {
                    $this->log("NS error was caused by transaction {$stage} in ", $impl->getContainer()->getDataFields(), Ac_Debug_Log::getInstance()->getTrace(false, array('Ac_Debug_Log', get_class($this), 'Ac_Callbacks')));
                    $this->log("Old problems", $oldProb, "new problems", $newProb);
                    $this->log("Statements", implode("; \n\n", $this->buffer));
                    $db = $impl->getNestedSets()->getDb();
                    //$db->query("ROLLBACK");
                    throw new Exception("Problem detected in nested sets implementation - see log for details");
                } else {
                    $db = $impl->getNestedSets()->getDb();
                    //$db->query("COMMIT");
                }
                $this->endCollectStatements(true);
            }
        }
    }
    
    function nsCallback($ns, $stmt, $stage) {
        if ($stage === Ac_Sql_NestedSets::debugAfterQuery && is_array($this->buffer)) {
            $this->buffer[] = ''.$stmt;
        }
    }
    
    function registerCallbacks() {
        Ac_Callbacks::getInstance()->addHandler(Ac_Model_Tree_NestedSetsImpl::debugCallback, array($this, 'implCallback'));
        Ac_Callbacks::getInstance()->addHandler(Ac_Sql_NestedSets::debugStmtCallback, array($this, 'nsCallback'));
    }
   
}