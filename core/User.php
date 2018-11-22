<?php

class User
{
    private $roomName;
    private $clientId;
    private $userName;

    function __construct($_id)
    {
        $this->clientId = $_id;
        $this->roomName = 0;
    }

    function get($_key)
    {
        return $this->{$_key};
    }

    function enterRoom($_roomName)
    {
        $this->roomName = $_roomName;
    }

    function setUsername($_username) {
      $this->userName = $_username;
    }
}

 ?>
