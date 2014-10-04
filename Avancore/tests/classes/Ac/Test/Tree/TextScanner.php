<?php

class Ac_Test_Tree_TextScanner implements Tr_I_ScannerImpl {
    
    var $defaultDataProperty = "title";
    
    /**
     * @return Tr_Node
     * $object a text or an array of lines:
     * 
     * node1
     *  node1.1 
     *  node1.2
     * {"title": "node2", "field2": "value2"} 
     * #lines beginning with '#' are ignored
     *  node2.1
     *   node2.1.1
     *   node2.1.2
     *  node2.2
     *  node2.3
     *  
     * # Node is considered a child if it has more indent spaces than parent
     * # First line is always considered root node and it's indent is ignored
     */
    function createRootNode($object) {
        if (!is_array($object)) { // $object should be array of lines 
            // split string into lines while removing empty lines
            $object = preg_split("/\s*[\n\r]+/", trim($object)); 
        }
        $object = preg_grep('/^\s*(#.*)?$/', $object, PREG_GREP_INVERT); // remove comments
        $object = array_values($object);
        $first = array_shift($object);
        return new Ac_Test_Tree_Node($object, $this->getExtra($first));
    }

    var $nsMap = array(
        'left' => 'leftCol',
        'right' => 'rightCol',
        'depth' => 'depth',
        'ordering' => 'ordering',
        'title' => 'title',
    );
    
    var $adjMap = array(
        'id' => 'id',
        'parentId' => 'parentId',
        'ordering' => 'ordering',
        'title' => 'title',
    );
    
    static function remap($src, $map) {
        foreach ($map as $k => $v) $res[$k] = $src[$v];
        return $res;
    }
    
    /**
     * $nsData must be ordered by 'left'!!!
     * @param array $nsData
     */
    function getTextFromNestedSets(array $nsData) {
        $res = array();
        foreach ($nsData as $row) {
            $data = self::remap ($row, $this->nsMap);
            $res[] = str_repeat(' ', $data['depth']).json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $res;
    }
    
    function getChildRows($adjData, $id) {
        $res = array();
        foreach ($adjData as $row) {
            if ($row['parentId'] === $id) $res[] = $row;
        }
        return $res;
    }
    
    function makeAdjText($adjData, $parentId, $depth = 0) {
        $res = array();
        foreach ($this->getChildRows($adjData, $parentId) as $data) {
            $res[] = str_repeat(' ', $depth + 1).json_encode($data, JSON_UNESCAPED_UNICODE);
            $res = array_merge($res, $this->makeAdjText($adjData, $data['id'], $depth + 1));
        }
        return $res;
    }
    
    function getTextFromAdjacency(Ac_Sql_Db $db, $tableName, $rootParentId = null, $depth = 0) {
        $res = array();
        $sql = Ac_Sql_Statement::create(
            'SELECT * FROM [[tableName]] ORDER BY [[ordering]]', 
            array_merge(
                $this->adjMap, 
                array(
                    'tableName' => $tableName,
                )
            )
        );
        $mapped = array();
        foreach ($db->fetchArray($sql) as $row) {
            $mapped[] = self::remap($row, $this->adjMap);
        }
        $res = $this->makeAdjText($mapped, $rootParentId);
        return $res;
    }
    
    function scanNode(Tr_Node $node) {
        $lines = $node->getObject();
        
        $indent = 0;
        $top = false;
        $body = array();
        while (!is_null($curr = array_shift($lines))) {
            $currIndent = $this->getIndent($curr);
            if ($top === false) {
                $top = $curr;
                $indent = $currIndent;
            } 
            elseif($currIndent > $indent) $body[] = $curr;
            else {
                $node->createNode($body, $this->getExtra($top));
                $body = array();
                $top = $curr;
                $indent = $currIndent;
            }
        }
        if ($top !== false)
            $node->createNode($body, $this->getExtra($top));
    }
    
    function getIndent($string) {
        $string = str_replace("\t", "    ", $string);
        return strlen($string) - strlen(ltrim($string));
    }
    
    function getExtra($string) {
        $indent = $this->getIndent($string);
        $string = trim($string);
        $jd = json_decode($string, true);
        if (json_last_error()) $jd = $string;
        if (is_array($jd)) $res = array('data' => $jd);
            else $res = array('data' => array($this->defaultDataProperty => $jd));
        return $res;
    }
    
}
