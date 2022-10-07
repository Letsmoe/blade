<?php

class Database {
	public function __construct(string $host, string $username, string $password, string $database, int $port = 22)
	{
		$this->conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function execute(string $query, array $params = array()) {
		$stmt = $this->conn->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
		if ($stmt) {
			if ($stmt->execute($params)) {
				return $stmt;
			}
		} else {
			throw new Error("Error whilst preparing query.");
		}
	}

	public function one(string $query, array $params = array()) {
		$stmt = $this->execute($query, $params);
		return $stmt->fetch();
	}

	public function insert(string $query, array $params = array()) {
		$stmt = $this->execute($query, $params);
		return $stmt;
	}
}