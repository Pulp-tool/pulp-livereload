<?php

namespace Pulp;
use Pulp\Lr\LiveReloadWsServer;
use Pulp\Lr\StaticServer;

use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\ServerProtocol;
use Ratchet\Http\HttpServerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;


class LiveReload extends DataPipe {
	public $address;
	public $lr;
	public $routes;
	public $staticServer;

	public function __construct($opts=array()) {

		$port = 35729;
		if (array_key_exists('port', $opts)) {
			$port = (int)$opts['port'];
		}

		$this->address = 'tcp://0.0.0.0:'.$port;
	}

	public function listen($loop) {

		$this->routes = new RouteCollection;
		$socket       = new Reactor($this->address, $loop);
		$this->lr     = new LiveReloadWsServer();

		$server = new \Ratchet\Server\IoServer(
			new \Ratchet\Http\HttpServer(
				new \Ratchet\Http\Router(
					new \Symfony\Component\Routing\Matcher\UrlMatcher($this->routes, new RequestContext()))
			),
			$socket,
			$loop
		);

		$this->staticServer = new StaticServer();
		$this->routes->add('websocket', new Route('/livereload',    ['_controller' => new WsServer($this->lr)]));
		$this->routes->add('static',    new Route('/{file}', ['_controller' => $this->staticServer]));

	}

	public function addStaticRoute($path, $cb) {
		$this->staticServer->mapPath($path, $cb);
	}

	/**
	 * mark any files received as changed
	 */
	public function write($data) {
		if ($data === NULL) { return; }
		$this->fileChanged($data);
		$this->emit('log', ['Sending reload because file changed: '.$data->__toString()]);
	}

	public function fileChanged($file) {
		if (is_string($file)) {
			$file = new \SplFileInfo(getcwd().'/'.$file);
		}
		$fname = str_replace(getcwd(), '', $file->getPathName());
		$this->lr->sendReload($fname);
	}
}
