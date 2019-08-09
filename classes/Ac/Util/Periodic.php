<?php

class Ac_Util_Periodic extends Ac_Prototyped {
    
    /**
     * identifier of Periodic instance (required)
     * @var string
     */
    protected $id = false;

    /**
     * Lock object to acquire during execution
     * @var Ac_Util_Lock
     */
    protected $lock = false;

    /**
     * object that contains execution flags
     * @var Ac_Flags
     */
    protected $flags = false;

    /**
     * interval between execution
     * @var int
     */
    protected $intervalSeconds = false;

    /**
     * run() immediately when an object is created
     * @var bool
     */
    protected $runOnCreate = false;

    /**
     * callback to call when it's time to execute
     */
    protected $callback = false;

    function __construct(array $prototype = array()) {
        parent::__construct($prototype);
        if ($this->runOnCreate) $this->run();
    }
    
    /**
     * Sets identifier of Periodic instance (required)
     * @param string $id
     */
    function setId($id) {
        $this->id = $id;
    }

    /**
     * Returns identifier of Periodic instance (required)
     * @return string
     */
    function getId() {
        return $this->id;
    }

    /**
     * Sets Lock object to acquire during execution
     * @param array|Ac_Util_Lock $lock
     */
    function setLock($lock) {
        if (is_array($lock) || (is_object($lock) && $lock instanceof Ac_Util_Lock)) {
            $this->lock = $lock;
        } else {
            throw Ac_E_InvalidCall::wrongType('lock', $lock, array('object', 'array'));
        }
    }

    /**
     * Returns Lock object to acquire during execution
     * @return Ac_Util_Lock
     */
    function getLock() {
        if (is_array($this->lock))
            $this->lock = Ac_Prototyped::factory($this->lock, 'Ac_Util_Lock');
        return $this->lock;
    }

    /**
     * Sets object that contains execution flags
     * @param array|false|Ac_Flags $flags
     */
    function setFlags($flags) {
        if ($flags === false || is_array($flags) || (is_object($flags) && $flags instanceof Ac_Flags)) {
            $this->flags = $flags;
        } else {
            throw Ac_E_InvalidCall::wrongType('flags', $flags, array('object', 'array', 'bool'));
        }
        $this->flags = $flags;
    }

    /**
     * Returns object that contains execution flags
     * @param bool $require Whether it is absolutely necessary to create Flags instance
     * @return Ac_Flags
     */
    function getFlags($require = false) {
        if ($this->flags === false) {
            if ($require) {
                $def = Ac_Application::getDefaultInstance();
                if ($def) {
                    $this->flags = Ac_Application::getDefaultInstance()->getFlags();
                } else {
                    $this->flags = new Ac_Flags();
                }
            }
        } elseif (is_array($this->flags)) {
            $this->flags = Ac_Prototyped::factory($this->flags, 'Ac_Flags');
        }
        return $this->flags;
    }

    /**
     * Sets interval between execution
     * @param int $intervalSeconds
     */
    function setIntervalSeconds($intervalSeconds) {
        if (is_numeric($intervalSeconds) && $intervalSeconds > 0) {
            $this->intervalSeconds = (int) $intervalSeconds;
        } else {
            throw new Ac_E_InvalidCall("\$intervalSeconds must be a number greater than 0");
        }
    }

    /**
     * Returns interval between execution
     * @return int
     */
    function getIntervalSeconds() {
        return $this->intervalSeconds;
    }

    /**
     * Sets run run() immediately when an object is created
     * @param bool $runOnCreate
     */
    function setRunOnCreate($runOnCreate) {
        $this->runOnCreate = (bool) $runOnCreate;
    }

    /**
     * Returns run run() immediately when an object is created
     * @return bool
     */
    function getRunOnCreate() {
        return $this->runOnCreate;
    }

    /**
     * Sets callback to call when it's time to execute
     * @param callable $callback
     */
    function setCallback($callback) {
        $this->callback = $callback;
    }

    /**
     * Returns callback to call when it's time to execute
     * @return callable
     */
    function getCallback() {
        return $this->callback;
    }    
    
    function getFlagName() {
        if (!strlen($this->id)) throw new Ac_E_InvalidUsage("setId() before getFlagName()");
        $res = __CLASS__.'_'.$this->id;
        return $res;
    }
    
    function run() {
        $res = false;
        $flagName = $this->getFlagName();
        $flags = $this->getFlags(true);
        $time = $flags->getMtime($flagName);
        $shouldExec = $time === false || (time() - $time) >= $this->intervalSeconds;
        if ($shouldExec) {
            $canProceed = true;
            $lock = $this->getLock();
            if ($lock) {
                if (!strlen($lock->getFileName())) $lock->setFileName($this->getFlagName().'_lock');
                $canProceed = $lock->acquire();
            }
            if ($canProceed) {
                $data = uniqid();
                $flags->touch($flagName, $data);
                $flags->getMtime($flagName, $contents);
                if ($contents !== $data) {
                    $canProceed = false;
                    if ($lock) $lock->release();
                }
            }
            if ($canProceed) {
                $res = true;
                if ($this->callback) {
                    try {
                        call_user_func($this->callback);
                    } catch (Exception $e) {
                        if ($lock) $lock->release();
                        throw $e;
                    }
                }
            }
        }
        return $res;
    }
    
    /**
     * Should be called when there is no callback to release the lock
     */
    function done() {
        if (($lock = $this->getLock())) {
            $lock->release();
        }
    }
    
}