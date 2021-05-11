<?php

namespace BaseMicroservice;

class Result
{
	private array $data = [];
	private bool $hasError = false;
	private string $error = '';

	public function setData(array $data): void
	{
		$this->data = $data;
	}

	public function getData(): array
	{
		return $this->data;
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
