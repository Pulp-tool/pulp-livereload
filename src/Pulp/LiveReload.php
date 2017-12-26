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

	public function __construct($opts=array()) {

		$port = 35729;
		if (array_key_exists('port', $opts)) {
			$port = (int)$opts['port'];
		}

		$this->address = 'tcp://0.0.0.0:'.$port;
	}

	public function listen($loop) {

		$routes = new RouteCollection;
		$socket = new Reactor($this->address, $loop);
		$this->lr     = new LiveReloadWsServer();

		$server = new \Ratchet\Server\IoServer(
			new \Ratchet\Http\HttpServer(
				new \Ratchet\Http\Router(
					new \Symfony\Component\Routing\Matcher\UrlMatcher($routes, new RequestContext()))
			),
			$socket,
			$loop
		);

		$routes->add('websocket', new Route('/livereload',    ['_controller' => new WsServer($this->lr)]));
		$routes->add('status',    new Route('/livereload.js', ['_controller' => new StaticServer()]));

		/*
		$loop->addPeriodicTimer(5.05, function() use($lr) {
			$lr->sendReload('/templates/sandler/dist/css/site.css');
		});
		 */
	}

	/**
	 * mark any files received as changed
	 */
	public function end($data=null) {
		$this->fileChanged($data);
	}

	public function fileChanged($file) {
		if (is_string($file)) {
			$file = new \SplFileInfo(getcwd().'/'.$file);
		}
		$fname = str_replace(getcwd(), '', $file->getPathName());
		$this->lr->sendReload($fname);
	}
}
