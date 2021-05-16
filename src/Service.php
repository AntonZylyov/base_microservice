<?php
namespace BaseMicroservice;

use BaseMicroservice\Application\HttpMethod;
use GuzzleHttp\Exception\GuzzleException;

abstract class Service
{
	protected Config $config;
	protected int $retryDelay = 200000; // 0.2 sec
	protected int $retryCount = 5;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function request(string $endpoint, array $params = [], $httpMethod = HttpMethod::POST): Result
	{
		// считаем что микросервисы как настоящие:
		//  - могут умирать и потом оживать
		//  - поддерживают идемпотентность через X-Request-Id, а значит их можно долбить несколько раз
		// поэтому делаем несколько попыток

		$requestOptions = [
			'headers' => [
				'Content-Type' => 'application/json',
				'X-Request-Id' => bin2hex(random_bytes(16)),
			],
			'connect_timeout' => 1,
		];
		if (!empty($params))
		{
			$requestOptions['body'] = json_encode($params, JSON_THROW_ON_ERROR);
		}

		$tryCount = 0;
		do
		{
			$tryCount++;
			$result = $this->doRequest($endpoint, $requestOptions, $httpMethod);
			if ($result->isSuccess())
			{
				break;
			}
			usleep($this->retryDelay);
		}
		while ($tryCount <= $this->retryCount);

		return $result;
	}
	private function doRequest(string $endpoint, array $requestOptions = [], $httpMethod = HttpMethod::POST): Result
	{
		$result = new Result();
		$client = new \GuzzleHttp\Client();
		try
		{
			$response = $client->request(
				$httpMethod,
				$this->getHost() . $endpoint,
				$requestOptions
			);
			$decoded = json_decode($response->getBody()->getContents(), true, 128, JSON_THROW_ON_ERROR);
			if (!is_array($decoded))
			{
				$result->setError('Wrong response');
			}
			else
			{
				if (isset($decoded['error']))
				{
					$result->setError($decoded['error']);
				}
				else
				{
					$result->setData($decoded);
				}
			}
		}
		catch (GuzzleException $e)
		{
			$result->setError($e->getMessage());
		}

		return $result;
	}

	abstract protected function getHost(): string;
}
