<?php

namespace Pulp\Lr;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Psr\Http\Message\RequestInterface;
use Ratchet\Http\HttpServerInterface;

class StaticServer implements HttpServerInterface  {
	public $pathMap = [];

    /**
     * @param \Ratchet\ConnectionInterface          $conn
     * @param \Psr\Http\Message\RequestInterface    $request null is default because PHP won't let me overload; don't pass null!!!
     * @throws \UnexpectedValueException if a RequestInterface is not passed
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
		$path = $request->getUri()->getPath();

		if (!array_key_exists($path, $this->pathMap)) {
			$rootDir = dirname(dirname(dirname(dirname(__FILE__)))).'/dist';
			$path = '/livereload.js';
		} else {
			$rootDir = $this->pathMap[$path];
		}
		$headers = [];
		if (is_string($rootDir)) {
			$contents = file_get_contents($rootDir.$path);
		}
		if (is_object($rootDir)) {
			$contents = $rootDir($path, $headers);
		}
		$headers = $this->fillHeaders($headers, $path);

		$conn->send("HTTP/1.0 200 OK\n");
		$conn->send("Content-length: ".strlen($contents)."\n");
		foreach ($headers as $k=>$v) {
			$conn->send( sprintf("%s: %s\n", $k, $v) );
		}
		$conn->send("\n");
		$x = $conn->send($contents);
		$conn->close();
	}

	public function fillHeaders($headers, $path) {
		if (!array_key_exists('Content-type', $headers)) {
			switch (substr($path, strrpos($path, '.'))) {
				case '.html':
					$headers["Content-type"] = "text/html";
					break;
				default:
					$headers["Content-type"] = "application/javascript";
			}
		}
		return $headers;
	}

	public function mapPath($path, $cb) {
		$this->pathMap[$path] = $cb;
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

