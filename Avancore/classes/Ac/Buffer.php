<?php

/**
 * A virtual buffer capable of echoing both strings and objects
 * @TODO fix passOutput and, then, handling of echo() within the callback
 */
class Ac_Buffer {
    
    protected static $curr = false;
    
    protected static $stack = array();    
    
    protected static $lock = false;
    
    protected static $protect = false;
    
    public static $ignoreNext = false;
    
    /**
     * Alias for Ac_Buffer::out
     * 
     * @param mixed $item
     * @param mixed $_  Any number of additional arguments
     */
    static function o($item, $_ = null) {
        $a = func_get_args();
        return call_user_func_array(array('Ac_Buffer', 'out'), array_slice($a, func_num_args()));
    }
    
    static function flush() {
        if (self::$curr && !self::$lock) ob_flush();
    }
    
    /**
     * Outputs one or more objects into the buffer.
     * If no capturing is done, outputs the items into the PHP buffer.
     * 
     * @param mixed $item
     * @param mixed $_  Any number of additional arguments
     */
    static function out($item, $_ = null) {
        if (self::$protect) {
            ob_end_clean();
            throw new Exception("Recursion detected: fix bugs in Ac_Buffer::out()");
        }
        $args = array();
        for($i = 0; $i < func_num_args(); $i++) {
            $arg = func_get_arg($i);
            if (is_object($arg) && $arg instanceof Ac_Value_BufferIgnore) echo $arg;
            else $args[] = $arg;
        }
        if (count($args)) {
            if (self::$curr) {
                if (!self::$lock) {
                    self::$protect = true;
                    ob_flush();
                }
                self::$lock = true;
                ob_clean();
                $n = func_num_args();
                for ($i = 0; $i < $n; $i++) {
                    call_user_func_array(self::$curr['callback'], array_merge(array(func_get_arg($i)), self::$curr['callbackArgs']));
                }
                ob_flush();
                self::$lock = false;
            } else {
                foreach ($args as $a) {
                    echo $a;
                }
            }
        }
    }
    
    static function outHandler($item) {
        if (is_string($item) && !strlen($item)) return;
        if (self::$ignoreNext) {
            self::$ignoreNext = false;
            return false;
        }
        if (self::$lock) return false;
        
        self::$lock = true;
        self::out($item);
        self::$lock = false;
        
    }
    
    /**
     * Starts the capturing into the virtual buffer. 'Virtual' means callback function is used instead of capturing the output.
     * Note that is captures the PHP output and redirects echo() calls to out() too.
     * 
     * @param callback      $callback           Function that will be called on each out() call
     * @param bool          $passOutput         Output everything that is echo'ed to the parent buffer
     * @param int|null      $chunkSize
     * @param bool          $implicitFlush
     * @param callback|null $endCallback
     * @param array         $callbackArgs
     * @param array         $endCallbackArgs 
     */
    static function begin($callback, $passOutput = false, $chunkSize = null, $implicitFlush = false, $endCallback = null, array $callbackArgs = array(), array $endCallbackArgs = array()) {
        $curr = compact('callback', 'passOutput', 'chunkSize', 'implicitFlush', 'endCallback', 'callbackArgs', 'endCallbackArgs');
        if (self::$curr) array_push(self::$stack, self::$curr);
        self::$curr = $curr;
        ob_start(array('Ac_Buffer', 'outHandler'), (int) $chunkSize);
        if ($implicitFlush) {
            ob_implicit_flush((bool) $implicitFlush);
        }
    }
    
    static function end() {
        if (!self::$curr) {
            throw new Ac_E_InvalidUsage("Cannot Ac_Buffer::end() when no buffering is active; check with getLevel() first");
        }
        $curr = self::$curr;
        ob_end_flush();
        if ($curr['endCallback']) {
            call_user_func_array(self::$curr['endCallback'], self::$curr['endCallbackArgs']);
        }
        if (count(self::$stack)) {
            self::$curr = array_pop(self::$stack);
        }
    }
    
    static function getLevel() {
        return self::$curr? 1 + count(self::$stack) : 0;
    }
    
}