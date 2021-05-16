<?php

namespace BaseMicroservice;

class Result
{
	private array $data = [];
	private bool $hasError = false;
	private bool $hasInfrastructureError = false;
	private string $error = '';

	public function setData(array $data): void
	{
		$this->data = $data;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function setInfrastructureError(string $error): void
	{
		$this->setError($error);
		$this->hasInfrastructureError = true;
	}

	public function hasInfrastructureError(): bool
	{
		return $this->hasInfrastructureError;
	}

	public function setError(string $error): void
	{
		$this->error = $error;
		$this->hasError = true;
	}

	public function isSuccess(): bool
	{
		return !$this->hasError;
	}

	public function getError(): string
	{
		return $this->error;
	}
}
