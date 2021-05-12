<?php

namespace BaseMicroservice;

use BaseMicroservice\Application\Context;
use BaseMicroservice\Application\HttpMethod;
use BaseMicroservice\Exception\NotSupportedException;
use Slim\App;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Application
{
	private Config $config;
	private App $app;

	public function __construct(Config $config)
	{
		$this->config = $config;

		$this->app = AppFactory::create();
		$this->app->addRoutingMiddleware();
		$this->app->addBodyParsingMiddleware();
		$errorMiddleware = $this->app->addErrorMiddleware(true, true, true);
		$errorHandler = $errorMiddleware->getDefaultErrorHandler();
		$errorHandler->forceContentType('application/json');
		$errorHandler->registerErrorRenderer('application/json', \BaseMicroservice\Application\ErrorHandler::class);
		$this->addHealthCheckRoute();
	}

	public function addRoute(
		string $method,
		string $pattern,
		callable $callback
	): void
	{
		if (
			!in_array(
				$method,
				[
					HttpMethod::ANY,
					HttpMethod::GET,
					HttpMethod::POST,
					HttpMethod::PUT,
					HttpMethod::PATCH,
					HttpMethod::DELETE,
				],
				true
			)
		)
		{
			throw new NotSupportedException('Wrong method');
		}
		$this->app->{$method}(
			$pattern,
			function (Request $request, Response $response, $args) use ($callback) {
				$context = new Context(
					$this,
					$request,
					$response,
					$args
				);
				$result = $callback($context);
				$response->getBody()->write(json_encode($result));

				return $response
					->withHeader('Content-Type', 'application/json');
			}
		);
	}

	public function addHealthCheckRoute(): void
	{
		$this->addRoute(
			HttpMethod::GET,
			'/health',
			static function (Context $context): array
			{
				return [
					'status' => 'OK'
				];
			}
		);
	}

	public function run(): void
	{
		$this->app->run();
	}

	public function getConfig(): Config
	{
		return $this->config;
	}
}