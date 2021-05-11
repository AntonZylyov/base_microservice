<?php

namespace BaseMicroservice;

use BaseMicroservice\Exception\EntityException;

abstract class Entity
{
	protected array $fields = [];
	protected string $idempotenceKey = '';
	protected Database $db;

	public static function getById(int $id): ?self
	{
		$fields = Database::getInstance()->selectOne(static::getTableName(), "id = ?", [$id]);
		if ($fields)
		{
			return static::createFromArray($fields);
		}
		return null;
	}

	public function __construct()
	{
		$this->db = \BaseMicroservice\Database::getInstance();
	}

	public function isIdempotenceSupported(): bool
	{
		return true;
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

	abstract protected static function getTableName(): string;
	abstract public static function createFromArray(array $fields): self;

	protected function getFieldValue(string $fieldId)
	{
		return $this->fields[$fieldId] ?? null;
	}

	protected function setFieldValue(string $fieldId, $fieldValue): void
	{
		$this->fields[$fieldId]  = $fieldValue;
	}

	public function toArray(): array
	{
		return $this->fields;
	}

	protected function getFieldsForSave(): array
	{
		return $this->fields;
	}

	public function save(): void
	{
		$fields = $this->getFieldsForSave();
		if ($this->isIdempotenceSupported())
		{
			$fields['idempotenceKey'] = $this->getIdempotenceKey();
		}

		$id = $fields['id'];
		unset($fields['id']);
		if ($id > 0)
		{
			$this->db->update(static::getTableName(), $id, $fields);
		}
		else
		{
			$id = $this->db->add(static::getTableName(), $fields);
			if (!$id)
			{
				throw new EntityException('Entity was not created');
			}
			$this->fields['id'] = $id;
		}
	}

	public function delete(): void
	{
		$id = (int)$this->fields['id'];
		if ($id > 0)
		{
			$this->db->delete(static::getTableName(), $id);
		}
	}
}
