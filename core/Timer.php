<?php

require_once 'Messenger.php';

class Timer
{
    private $timeSec;  // time to draw
    private $timeStart;
    //private $timeCur; // current timer value
    private $messenger;
    private $id;
    private $state; // 1-on, 0-off

    function __construct($_timeSec) {
        $this->timeSec = $_timeSec;
        $this->timeCur = $_timeSec;
        $this->messenger = new Messenger();
        $this->state = 0;
        //$this->set($_serv, $this->timeSec, $_userLists);
    }


    function start() {
        $this->state = 1;
    }

    function stop() {
        $this->state = 0;
    }

    //set new timer
    function set($_serv, $_userLists, $_timestamp) {
        $this->timeStart = $_timestamp;
        $timeStart = $this->timeStart;
        if (isset($this->id)) {
            swoole_timer_clear($this->id);
        }
        //$this->start();
        $this->id = swoole_timer_tick(1000, function ($test) use ($_serv, $_userLists, $timeStart) {
            $this->tick($_serv, $_userLists, $timeStart);
        });
        //echo "timerIdSet:".$this->timer;
    }


    // update timer with the same time (for new players)
    function update($_serv, $_userLists) {
        if ($this->state) {
            $this->set($_serv, $_userLists, $this->timeStart);
            $this->start();
        }
        echo "update timeCur: ".$this->timeCur.PHP_EOL;
    }

    // ticks every 1 sec
    private function tick($_serv, $_userLists, $_timeStart) {
        if ($this->state) { // timer is on
            $sec = $_timeStart+$this->timeSec-time();

            $data = array('seconds'=>$sec);
            $this->messenger->sendToUsers($_serv, $_userLists, 'timer', $data);
        }
    }
}