<?php

require_once 'core/User.php';
require_once 'core/Room.php';
require_once 'core/Messenger.php';
require_once 'core/RedisDB.php';

class Game
{
    private $redis;
    private $messenger;

    private $user;
    private $room;
    private $userName;
    private $roomName;

    function __construct()
    {
        $this->redis = new RedisDB();
        $this->messenger = new Messenger();
    }

    //user connected
    function connect(swoole_server $_serv, $_client)
    {
        echo ("Client ".$_client." connected");
    }

    // draw event
    function draw($_serv, $_client, $_data)
    {
        $this->getAttributes($_client);

        // check if its host
        if ($_client == $this->room->get('host')) {
            $userList = array_diff($this->room->get('userLists'), array($this->room->get('host')));
            $this->messenger->sendToUsers($_serv, $userList, 'draw', $_data);
        }
    }

    // send canvas to new connected player
    function sendCanvas($_serv, $_client, $_data) {
        $data = array('canvas'=>$_data['canvas'], 'flagDraw'=>$_data['flagDraw']);
        echo "TEST:".$_data['flagDraw'];
        $this->messenger->sendToUser($_serv, $_data['id'], 'drawCanvas', $data);
    }

    // clear canvas event
    function clearCanvas($_serv, $_client, $_data) {

        $this->getAttributes($_client);

        $userList = array_diff($this->room->get('userLists'), array($this->room->get('host')));
        $this->messenger->sendToUsers($_serv, $userList, 'clearCanvas', $_data);
    }


    // surrender event
    function surrender($_serv, $_client) {
        $this->getAttributes($_client);

        $this->room->surrender($_serv, $this->userName);
        $this->redis->set('room', $this->roomName, $this->room);
    }

    // time over event
    function timeOver($_serv, $_client) {
        $this->getAttributes($_client);

        $this->room->timeOver($_serv, $this->userName);
        $this->redis->set('room', $this->roomName, $this->room);
    }

    // message in chat event
    function message($_serv, $_client, $_data) {
        $this->getAttributes($_client);

        $data = array('text'=>$_data['text'], 'name'=> $this->userName);
        $this->messenger->sendToUsers($_serv, $this->room->get('userLists'), 'message', $data);
        $this->room->checkGuess($_serv, $_data['text'], $this->userName);

        $this->redis->set('room', $this->roomName, $this->room);
    }

    // player connected
    function joinRoom($_serv, $_client, $_data) {
        $user = new User($_client);

        $roomName = $_data['room'];
        $username = $_data['username'];

        $user->enterRoom($roomName);
        $user->setUsername($username);
        $this->redis->set('user', $_client, $user);

        // create or update room
        $room = $this->redis->get('room', $roomName);
        if (!$room) {
            $room = new Room($roomName, $_client, $_serv);
            echo "Created room: ".$roomName." by ".$_client.PHP_EOL;
        }
        $room->enterUser($_client, $_serv);

        $data = array('text'=>'Игрок '.$user->get('userName').' зашел в комнату.', 'name'=>'Уведомление');
        $this->messenger->sendToUsers($_serv, $room->get('userLists'), 'message', $data);

        $this->redis->set('room', $roomName, $room);
    }

    // player disconnected
    function disconnect($_serv, $_client) {
        $user = $this->redis->get('user', $_client);

        // if user exists
        if ($user) {
            $username = $user->get('userName');
            $roomName = $user->get('roomName');
            $room = $this->redis->get('room', $roomName);

            // if user was in room
            if ($room) {

                $room->leaveUser($_serv, $_client);
                $this->redis->del('user', $_client);
                unset($user);

//            // no users left in this room
                if (count($room->get('userLists'))==0) {
                    $this->redis->del('room', $roomName);
                    unset($room);
                    echo("Room ".$roomName." is deleted.".PHP_EOL);
                    return;
                }
//          }

                $data = array('text'=>'Игрок '.$username.' вышел из комнаты.', 'name'=>'Уведомление');
                $this->messenger->sendToUsers($_serv, $room->get('userLists'), 'message', $data);

                $this->redis->set('room', $roomName, $room);
            }
        }
    }


    private function getAttributes($_client) {
        $this->user = $this->redis->get('user', $_client);
        $this->userName = $this->user->get('userName');
        $this->roomName = $this->user->get('roomName');
        $this->room = $this->redis->get('room', $this->roomName);
    }
}


