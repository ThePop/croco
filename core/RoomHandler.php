<?php

require_once "Messenger.php";

class roomHandler
{
    private $messenger;

    function __construct() {
        $this->messenger = new Messenger();
    }

    // starts the game
    function game_start($_serv, &$_word, &$_state, $_userLists, $_host)
    {
        $_state = 1;
        $_word = $this->chooseWord();

        //send that game started
        $this->messenger->sendRoles($_serv, $_userLists, $_host, $_state);
        $data = array('word'=>$_word);
        $this->messenger->sendToUser($_serv, $_host, 'wordToDraw', $data);
    }

    // set room to pause/waiting for players state
    function game_pause($_serv, &$_state,  $_userLists, $_host)
    {
        $_state = 0;
        $this->messenger->sendRoles($_serv,$_userLists,$_host,$_state);
    }

    // end of round state for future updates
    function game_end()
    {
        $this->state = 2;
    }

    // switch host to next player in userLists array
    function change_host(&$_host, $_userLists) {
        $currHost = $_host;
        $currIndex = array_search($currHost,$_userLists);
        $nextIndex = ($currIndex+1) % count($_userLists);
        $_host = $_userLists[$nextIndex];
    }

    // picks random word from file
    private function chooseWord() {
        $wordsPath = $_SERVER['DOCUMENT_ROOT']."words.txt";
        $f_contents = file($wordsPath);
        $line = $f_contents[array_rand($f_contents)];
        $data = str_replace("\r\n", "",$line);
        return $data;
    }
}