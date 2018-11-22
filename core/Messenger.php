<?php

class Messenger
{
    // send message to all users in $_userList
    function sendToUsers($_serv, $_userList, $_type, $_data) {
        $data = $this->toJson($_type, $_data);
        foreach($_userList as $user) {
            $_serv->push($user, $data);
        }
    }

    // send message to $_user
    function sendToUser($_serv, $_user, $_type, $_data) {
        $data = $this->toJson($_type, $_data);
        $_serv->push($_user, $data);
    }

    // send gamestate and roles
    function sendRoles($_serv, $_userList, $_host, $_gameState) {
        foreach ($_userList as $user) {
            $role = ($user==$_host) ? 'host' : 'client';
            $data = array('state'=>$_gameState, 'role'=>$role);
            $_serv->push($user, $this->toJson('gameState',$data));
        }
    }

    // convert data to json
    private function toJson($_type, $_data) {
        return json_encode($newData = [
            'type'=>$_type,
            'data'=>$_data,
        ]);
    }
}