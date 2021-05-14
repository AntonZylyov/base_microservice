<?php

namespace BaseMicroservice;

class Config
{
	protected array $config = [];

	public static function createFromEnv(): self
	{
		$instance = new self();
		$instance->initFromEnv();

		return $instance;
	}

	public function __construct(array $config = [])
	{
		$this->config = $config;
	}

	public function initFromEnv(): void
	{
		$this->config = getenv();
	}

	public function getDatabaseDsn(): string
	{
		$host = $this->getValue('DATABASE_HOST');
		$port = $this->getValue('DATABASE_PORT');
		$mysqlHost = $host . ($port ? ':' . $port : '');
		$mysqlDbName = $this->getValue('DATABASE_NAME');

		return "mysql:host=$mysqlHost;dbname=$mysqlDbName";
	}

	public function getDatabaseUsername(): string
	{
		return $this->getValue('DATABASE_USERNAME');
	}

	public function getDatabasePassword(): string
	{
		return $this->getValue('DATABASE_PASSWORD');
	}

	protected function getValue(string $key): string
	{
		return (string)($this->config[$key] ?? '');
	}
}
