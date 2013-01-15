<?php

class Ac_E_Database extends Exception {
    
    protected $query = false;

    public function __construct($message, $code, Exception $previous = null) {
        
        if (is_array($message)) {
            $query = $this->query = $message[1];
            $message = $message[0];
            $message = $message . '\n\nQuery was: "'.$query.'"';
        }
        
        if (func_num_args() == 3) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
    
    function setQuery($query) {
        $this->query = $query;
    }
    
    function getQuery($query) {
        return $this->query;
    }
    
}