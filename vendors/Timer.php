<?php
App::uses('AppModel', 'Model');
class Timer {
    var $classname = "Timer";
    var $start     = 0;
    var $stop      = 0;
    var $interval  = 0;
    public function __construct() {
        $current_time = $this->getCurrentTime();
        $this->start = $current_time;
        echo 'Timer started at '.$current_time.'please wait..<br/>';
    }
    public function __destruct() {
        $this->stop = $this->getCurrentTime();
       // debug($this->stop);
        echo 'Timer started at '.$this->start.',finished at '.$this->stop.'takes '.($this->stop - $this->start).' seconds.';
    } 
    function getCurrentTime() {
        $mtime = microtime();
        $mtime = explode( " ", $mtime );
        debug($mtime);
        return $mtime[1] + $mtime[0];
    }
    function getStart() {
        return $this->start;
    } 
 
    function getStop() {
        return $this->stop;
    } 
    /**
   # Constructor
    function Timer( $start = true ) {
        if($start)
            $this->start();
    }

   # Start counting time
    function start() {
        $this->start = $this->_gettime();
    }

    # Stop counting time
    function stop() {
        $this->stop    = $this->_gettime();
        $this->elapsed = $this->_compute();
    }

   # Get Elapsed Time
    function elapsed() {
        if(!$elapsed )
            $this->stop();
        return $this->elapsed;
    }

    # Get Elapsed Time
    function reset() {
        $this->start   = 0;
        $this->stop    = 0;
        $this->elapsed = 0;
    }

    #### PRIVATE METHODS ####

    # Get Current Time
    function _gettime() {
        $mtime = microtime();
        $mtime = explode( " ", $mtime );
        return $mtime[1] + $mtime[0];
    }

    # Compute elapsed time
    function _compute() {
        return $this->stop - $this->start;
    }
    */
}

