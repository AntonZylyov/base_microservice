<?php

namespace BaseMicroservice\Application;

use BaseMicroservice\Application;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class Context
{
	private Application $application;
	private ServerRequestInterface $request;
	private ResponseInterface $response;
	private array $routeArgs = [];

	public function __construct(
		Application $application,
		ServerRequestInterface $request,
		ResponseInterface $response,
		array $routeArgs = []
	)
	{
		$this->application = $application;
		$this->request = $request;
		$this->response = $response;
		$this->routeArgs = $routeArgs;
	}

	public function getApplication(): Application
	{
		return $this->application;
	}

	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}

	public function getResponse(): ResponseInterface
	{
		return $this->response;
	}

	public function addResponseHeader($name, $value)
	{
		$this->response = $this->response->withHeader($name, $value);
	}

	public function getRouteArgs()
	{
		return $this->routeArgs;
	}
}
