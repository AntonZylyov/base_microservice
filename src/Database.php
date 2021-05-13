<?php

namespace BaseMicroservice;

class Database
{
	protected string $dsn = '';
	protected string $username = '';
	protected string $password = '';

	protected $connection;
	protected static $instance;

	public static function getInstance(): self
	{
		if (!self::$instance)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function init(Config $config): void
	{
		$this->setConnectionParams(
			$config->getDatabaseDsn(),
			$config->getDatabaseUsername(),
			$config->getDatabasePassword()
		);
	}

	public function setConnectionParams(string $dsn, string $username, string $password): void
	{
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
	}

	public function add(string $tableName, array $fields): ?int
	{
		$sql = "INSERT INTO " . $tableName .
			" (" . implode(', ', array_keys($fields)) . ") " . // @todo fix possible sql injection in field names
			"VALUES (" . implode(', ', array_fill(0, count($fields), '?')) . ")";

		$statement = $this->getConnection()->prepare($sql);
		$statement->execute(array_values($fields));

		return $this->getConnection()->lastInsertId();
	}

	public function update(string $tableName, int $id, array $fields): void
	{
		if (empty($fields))
		{
			return;
		}
		$sqlFields = [];
		$replacements = [];
		foreach ($fields as $fieldName => $fieldValue)
		{
			$sqlFields[] = "`$fieldName` = ?";
			$replacements[] = (string)$fieldValue;
		}
		$replacements[] = $id;
		$sql = "UPDATE " . $tableName . " SET " . implode(', ', $sqlFields) . " WHERE id = ?";
		$statement = $this->getConnection()->prepare($sql);
		$statement->execute($replacements);
	}

	public function delete(string $tableName, int $id): void
	{
		$sql = "DELETE FROM " . $tableName . " WHERE id = ?";
		$statement = $this->getConnection()->prepare($sql);
		$statement->execute([$id]);
	}

	public function selectOne(string $tableName, string $condition, array $placeholders, array $order = []): ?array
	{
		$result = $this->selectAll($tableName, $condition, $placeholders, $order);
		return $result ? $result[0] : null;
	}


	public function selectAll(string $tableName, string $condition, array $placeholders, array $order = [], int $limit = 0): array
	{
		$result = [];
		$sql = "SELECT * FROM " . $tableName . " WHERE " . $condition;
		if (!empty($order))
		{
			$sortField = key($order);
			$sortBy = strtoupper(current($order)) === 'DESC' ? 'DESC' : 'ASC';
			$sql .= " ORDER BY $sortField $sortBy"; // @todo fix sql injection via order
		}
		if ($limit > 0)
		{
			$sql .= " LIMIT $limit";  //@todo Offset
		}
		$statement = $this->getConnection()->prepare($sql);
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		$statement->execute($placeholders);
		while ($row = $statement->fetch())
		{
			$result[] = $row;
		}
		return $result;
	}

	public function beginTransaction(): void
	{
		$this->getConnection()->beginTransaction();
	}

	public function commit(): void
	{
		$this->getConnection()->commit();
	}

	public function rollback(): void
	{
		$this->getConnection()->rollBack();
	}

	public function query($sql)
	{
		$this->getConnection()->prepare($sql)->execute();
	}

	protected function getConnection(): \PDO
	{
		if (!$this->connection)
		{
			$this->connection = new \PDO($this->dsn, $this->username, $this->password);
			$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		return $this->connection;
	}
}
