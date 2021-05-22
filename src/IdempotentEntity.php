<?php

namespace BaseMicroservice;

abstract class IdempotentEntity extends Entity
{
	protected string $idempotenceKey = '';

	public static function getByIdempotenceKey(string $idempotenceKey): ?self
	{
		$fields = Database::getInstance()->selectOne(
			static::getTableName(),
			'idempotenceKey = ? AND created > ?',
			[
				$idempotenceKey,
				date('Y-m-d H:i:s', time() - 60) // создано меньше минуты назад
			]);
		if ($fields)
		{
			return static::createFromArray($fields);
		}
		return null;
	}

	public function setIdempotenceKey(string $key): self
	{
		$this->idempotenceKey = $key;
		return $this;
	}

	public function getIdempotenceKey(): string
	{
		return $this->idempotenceKey;
	}

	protected function getFieldsForSave(): array
	{
		$fields = parent::getFieldsForSave();
		if (!isset($fields['id']))
		{
			$fields['idempotenceKey'] = $this->getIdempotenceKey();
		}

		return $fields;
	}
}
