<?php

namespace Pulp\Lr;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class LiveReloadWsServer implements MessageComponentInterface {
    protected $clients;
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

	public function sendReload($filePath) {
		echo "Sending reload to all clients...\n";
		foreach ($this->clients as $_client) {
			$_client->send(
				json_encode( ['command'=>'reload',
				'path'=>$filePath,
				'liveCSS'=>true
				])
			);
		//	$this->clients->detach($_client);
		}
	}

    public function onMessage(ConnectionInterface $from, $msg) {
		var_dump($msg);
		$msg = json_decode($msg, TRUE);
		if ($msg['command'] == 'hello') {
			$from->send(
				json_encode( ['command'=>'hello',
				'protocols'=>['http://livereload.com/protocols/official-7','http://livereload.com/protocols/official-6',]
				] )
			);
		} else {
			/*
			$from->send(
				json_encode(['command'=>'alert',
				'message'=>'Hey'
				])
			);
			 */
		}
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        trigger_error("An error has occurred: {$e->getMessage()}\n", E_USER_WARNING);
        $conn->close();
    }
}
