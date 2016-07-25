<?php

class Ac_Etl_Operation_Query extends Ac_Etl_Operation {

    protected $sql = false;
    
    /**
     * @var array
     */
    protected $params = array();
    
    function doProcess() {
        $sql = Ac_Util::toArray($this->sql);
        $res = true;
        foreach ($sql as $k => $q) {
            $idPath = $this->getIdPath();
            $tags = "-- tags: operations/{$idPath}/modify/all\n";
            $q = $tags.$q;
            $sp = new Ac_Sql_Statement($q);
            $sp->setParams(array_merge(array(
                'importDb' => $this->import->getImporterDbName(),
                'targetDb' => $this->import->getTargetDbName(),
                'importId' => $this->import->getImportId(),
            ), $this->params));
            $res = $res && (bool) $this->getDb()->query($sp);
            if ($res) {
                $this->addAffected("query.".$k);
                $this->addAffected("query");
            }
            if (!$res) break;
        }
        return $res;
    }

    function setSql($sql) {
        $this->sql = $sql;
    }

    function getSql() {
        return $this->sql;
    }    

    function setParams(array $params) {
        $this->params = $params;
    }

    /**
     * @return array
     */
    function getParams() {
        return $this->params;
    }    
    
}