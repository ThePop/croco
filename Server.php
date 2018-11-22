<?php
class Server
{
    private $server;
    private $service;
    function __construct()
    {
        $conf = parse_ini_file('config.ini', true);

        $this->server = new swoole_websocket_server($conf['server']['host'], $conf['server']['port'], SWOOLE_BASE, SWOOLE_SOCK_TCP | SWOOLE_SSL);

        //use this for non SSL server (ws:// doesn't work on some browsers)
        //$this->server = new swoole_websocket_server($conf['server']['host'], $conf['server']['port'], SWOOLE_BASE, SWOOLE_SOCK_TCP);

        // path to certificates for SSL mode
        $pathCert = 'cert.pem';
        $pathKey ='key.pem';
        $this->server->set([
            'ssl_cert_file' => $pathCert,
            'ssl_key_file' => $pathKey,
            'log_level' => 2,
        ]);

        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('open', [$this, 'onOpen']);
        $this->server->on('message', [$this, 'onMessage']);
        $this->server->on('close', [$this, 'onClose']);
        $this->server->start();
    }


    //event handlers

    function onStart($server)
    {
        require_once 'Game.php';
        $this->service = new Game();
        echo('Server started'.PHP_EOL);
    }
    function onOpen($server, $req)
    {
        echo("get connection from id:{$req->fd}, ip:{$req->server['remote_addr']}".PHP_EOL);
        $this->service->connect($server, $req->fd);
    }
    function onMessage($server, $frame)
    {
        echo("recv data[{$frame->data}] from client[{$frame->fd}]".PHP_EOL);
        $data = json_decode($frame->data, 1);
        $params = isset($data['data']) ? [$server, $frame->fd, $data['data']] : [$server, $frame->fd];
        call_user_func_array([$this->service, $data['type']], $params);
    }
    function onClose($server, $fd)
    {
        echo("client[$fd] close connection".PHP_EOL);
        $this->service->disconnect($server, $fd);
    }
}

new Server();
