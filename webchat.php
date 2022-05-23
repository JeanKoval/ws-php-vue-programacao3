<?php

use Ds\Map;
use Ds\Set;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\WebSocket\WsServer;

require 'vendor/autoload.php';

$chatComponent = new class implements MessageComponentInterface {
    private $connections;

    public function __construct()
    {
        $this->connections = new Set();
    }

    public function onOpen(ConnectionInterface $conn) 
    {
        $this->connections[] = $conn;
        echo "Quantidade de conexões: " . $this->connections->count() . PHP_EOL;
    }

    public function onMessage(ConnectionInterface $from, $msg) 
    {
        if(empty($msg)) exit;

        $indexFrom = array_search($from, $this->connections->toArray());
        if($indexFrom % 2 == 0)
            $indexEnvio = $indexFrom+1;
        else
            $indexEnvio = $indexFrom-1;
        
        echo 'Evento on message!';

        if(array_key_exists($indexEnvio, $this->connections->toArray())){
            echo "Enviado para Conexão: ". $indexEnvio . PHP_EOL;
            $this->connections->get($indexEnvio)->send($msg);
        }else{
            echo "Sem Conexão p/ Envio!!!". PHP_EOL;
        }
    }

    public function onClose(ConnectionInterface $conn) 
    {
        echo "Encerrou a conexão" . PHP_EOL;
    }

    public function onError(ConnectionInterface $conn, \Exception $e) 
    {
    }
};

$server = IoServer::factory(
    new HttpServer(
        new WsServer($chatComponent)
    ), 
    7777
);

$server->run();