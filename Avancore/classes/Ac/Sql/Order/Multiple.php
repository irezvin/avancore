<?php

class Ac_Sql_Order_Multiple extends Ac_Sql_Order {

    var $_orders = array();
    
    function listOrders() {
        return array_keys($this->_orders);
    }
    
    /**
     * @param string $id
     * @return Ac_Sql_Order
     */
    function getOrder($id) {
        if (!isset($this->_orders[$id])) trigger_error("No such order: '$id'", E_USER_ERROR);
        if (is_array($this->_orders[$id])) {
            $options = $this->_orders[$id];
            $options['id'] = $id;
            $options['parentPart'] = $this;
            $options['db'] = $this->_db;
            $this->_orders[$id] = $this->factory($options, 'Ac_Sql_Order');
        }
        $res = $this->_orders[$id];
        return $res;
    }
    
    /**
     * @param Ac_Sql_Db $db
     */
    function setDb($db) {
        parent::setDb($db);
        foreach (array_keys($this->_orders) as $o) if (is_object($this->_orders[$o])) $this->_orders[$o]->setDb($db);
    }
    
    /**
     * @param array|Ac_Sql_Order $order
     * @param string $id
     */
    function addOrder($order, $id = false) {
        assert(
                is_array($order) && (strlen($id) || isset($order['id']) && strlen($order['id'])) 
            ||  is_a($order, 'Ac_Sql_Order') && (strlen($id) || strlen($order->id))
        );
        $aId = is_array($order)? (isset($order['id'])? $order['id'] : false) : $order->id;
        if (!strlen($aId)) $aId = $id;
        if (isset($this->_orders[$aId])) trigger_error("Order with id '{$id}' is already in the collection", E_USER_ERROR);
        $this->_orders[$aId] = $order;
        if (is_object($order)) {
            $order->setDb($this->_db);
        }
        return $aId;
    }
    
    function _colCriteria() {
        $res = 0;
        $ord = array();
        foreach ($this->listOrders() as $i) {
            $o = $this->getOrder($i);
            $ord = array_merge($ord, $o->getAppliedOrderBy());
        }
        if (count($ord) == 1) {
            $ord = array_slice($ord, 0, 1);
            $res = current($ord);
        } elseif (count($ord) > 1) {
            $res = $this->_db->indent("\n".implode(", ", $ord));
        } else {
            $res = false;
        }
        return $res;
    }
    
    // ---------------------------------- template methods ------------------------------

    function _doOnInitialize($options) {
        if (isset($options['orders'])) {
            assert(is_array($options['orders']));
            foreach(array_keys($options['orders']) as $k) $this->addOrder($options['orders'][$k], is_numeric($k)? false : $k);  
        }
    }
    
    function _doBeforeExpandPaths(& $input) {
        $r = array();
        $c = false;
        foreach (array_keys($input) as $k) {
            if (is_numeric($k) && strlen($input[$k])) {
                $r[$input[$k]] = true;
                $c = true;
            } else {
                $r[$k] = $input[$k];
            }
        }
        if ($c) $input = $r;
    }
    
    /**
     * @access protected
     */
    function _doBind($input) {
        if ($input === true) {
            $in = array();
            foreach ($this->listOrders() as $id) $in[$id] = true;
            $input = $in; 
        }
        if (is_array($input)) {
            $appliedOrders = array();
            foreach ($this->listOrders() as $id) {
                if (isset($input[$id])) {
                    $o = $this->getOrder($id);
                    $o->bind($input[$id]);
                    $appliedOrders[] = $id;
                }
            }
            if (!count($appliedOrders)) $this->applied = false;
        } else {
            $this->applied = false;
        }
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedOrderBy () {
        if (count($this->_orders)) {
            $res = array($this->_colCriteria());
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedAliases() {
        $res = $this->aliases;
        foreach ($this->listOrders() as $i) {
            $o = $this->getOrder($i);
            $res = array_merge($res, $o->getAppliedAliases());
        }
        $res = array_unique($res);
        return $res;
    }
    
}
?>