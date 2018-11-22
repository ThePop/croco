<?php

require_once 'Messenger.php';
require_once 'RoomHandler.php';
require_once 'Timer.php';

class Room
{
    private $id;
    private $host;
    private $state;  // 0-waitingForPlayers/pause 1-gameStart 2-gameEnd
    private $userLists;  // list of client ids
    private $word;  // list of client ids
    private $timer;

    private $handler;
    private $messenger;

    function __construct($_title, $_user, $_serv)
    {
        $this->state = 0;
        $this->id = $_title;
        $this->host = $_user;

        // set timer to 120 seconds
        $timeSec = 120;
        $this->timer = new Timer($timeSec);
        $this->timer->set($_serv, $this->userLists, time());
        $this->messenger = new Messenger();
        $this->handler = new RoomHandler();
    }

    function get($_key)
    {
        return isset($this->{$_key}) ? $this->{$_key} : null;
    }

    // player connected
    function enterUser($_user, $_serv)
    {
        $this->userLists[] = $_user;

        //game already started
        if ($this->state){
            //reset timer on the same time
            $this->timer->update($_serv, $this->userLists);

            //send client role
            $data = array('state'=>$this->state, 'role'=>'client');
            $this->messenger->sendToUser($_serv, $_user, 'gameState', $data);

            //request canvas from host
            $data = array('toUser'=>$_user);
            $this->messenger->sendToUser($_serv, $this->host, 'sendCanvas', $data);

        } else if (!$this->state) { //game on pause

            //enough players to start a game
            if (count($this->userLists) >= 2) {
                $this->handler->game_start($_serv, $this->word, $this->state, $this->userLists, $this->host);
                $this->timer->set($_serv, $this->userLists, time());
                $this->timer->start();
                echo "Game ".$this->id." started".PHP_EOL;
            }
        }
    }

    // player disconnected
    function leaveUser($_serv, $_user)
    {
        if ($this->state){
            //reset timer on the same time
            $this->timer->update($_serv, $this->userLists);
        }
        // change the host
        if($_user == $this->host && !empty($this->userLists))
        {
            $this->handler->change_host($this->host, $this->userLists);
            $this->handler->game_start($_serv, $this->word, $this->state, $this->userLists, $this->host);
            $this->timer->set($_serv, $this->userLists, time());
            $this->timer->start();
        }

        // del user from list
        $tmp = array_flip($this->userLists);
        if(!isset($tmp[$_user]))
        {
            throw new Exception('User not found');
        }
        unset($tmp[$_user]);
        $this->userLists = array_values(array_flip($tmp));

        // not enough players to continue game
        if (count($this->userLists) < 2) {
            $this->handler->game_pause($_serv, $this->state, $this->userLists, $this->host);
            $this->timer->stop();
        }
    }

    // time is over
    function timeOver($_serv) {
        $this->timer->stop();
        $data = array('text'=>"Время вышло. Загаданное слово '".$this->word."'.", 'name'=>'Уведомление');
        $this->messenger->sendToUsers($_serv, $this->userLists, 'message', $data);

        $this->handler->change_host($this->host, $this->userLists);
        $this->handler->game_start($_serv, $this->word, $this->state, $this->userLists, $this->host);
        $this->timer->set($_serv, $this->userLists, time());
        $this->timer->start();
    }

    // serrender event
    function surrender($_serv, $userName) {
        $this->timer->stop();
        $data = array('text'=>"Игрок ".$userName." сдался. Загаданное слово '".$this->word."'.", 'name'=>'Уведомление');
        $this->messenger->sendToUsers($_serv, $this->userLists, 'message', $data);

        $this->handler->change_host($this->host, $this->userLists);
        $this->handler->game_start($_serv, $this->word, $this->state, $this->userLists, $this->host);
        $this->timer->set($_serv, $this->userLists, time());
        $this->timer->start();
    }

    // check if word from chat is correct
    function checkGuess($_serv, $_word, $_username) {
        if ($_word == $this->word && $this->state==1) {
            $this->timer->stop();
            $data = array('text'=>"Игрок ".$_username." угадал слово '".$this->word."'.", 'name'=>'Уведомление');
            $this->messenger->sendToUsers($_serv, $this->userLists, 'message', $data);

            $this->handler->change_host($this->host, $this->userLists);
            $this->handler->game_start($_serv, $this->word, $this->state, $this->userLists, $this->host);
            $this->timer->set($_serv, $this->userLists, time());
            $this->timer->start();
        }
    }
}

