<?php

namespace Pulp\Lr;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Psr\Http\Message\RequestInterface;
use Ratchet\Http\HttpServerInterface;

class StaticServer implements HttpServerInterface  {
    /**
     * @param \Ratchet\ConnectionInterface          $conn
     * @param \Psr\Http\Message\RequestInterface    $request null is default because PHP won't let me overload; don't pass null!!!
     * @throws \UnexpectedValueException if a RequestInterface is not passed
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
		$contents = file_get_contents(dirname(dirname(dirname(dirname(__FILE__)))).'/dist/livereload.js');
		$conn->send("HTTP/1.0 200 OK\n");
		$conn->send("Content-length: ".strlen($contents)."\n");
		$conn->send("Content-type: application/javascript\n\n");
		$conn->send($contents);
		$conn->close();
	}
	public function onMessage(ConnectionInterface $conn, $msg) { }
    /**
	 * This is called before or after a socket is closed (depends on how it's closed).
	 * SendMessage to $conn will not result in an error if it has already been closed.
     */
    public function onClose(ConnectionInterface $conn) { }
    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     */
    public function onError(ConnectionInterface $conn, \Exception $e) { }
}

