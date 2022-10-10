<?php

class Database {
	private $host = null;
	private $username = null;
	private $password = null;
	private $database = null;

	public function __construct(string $host = null, string $username = null, string $password = null, string $database = null)
	{
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->connect();
	}

	public function setHost(string $host): bool {
		$this->host = $host;
		return $this->connect();
	}

	public function setUsername(string $username): bool {
		$this->username = $username;
		return $this->connect();
	}

	public function setPassword(string $password): bool {
		$this->password = $password;
		return $this->connect();
	}

	public function setDatabase(string $database): bool {
		$this->database = $database;
		return $this->connect();
	}

	public function close(): bool {
		return $this->conn->close();
	}

	public function reconnect(): bool {
		$this->conn->close();
		return $this->connect();
	}

	/**
	 * Instantiates a new database connection once all required parameters have been set.
	 */
	private function connect(): bool {
		if ($this->host && $this->username && $this->password && $this->database) {
			$this->conn = new PDO("mysql:host=$this->host;dbname=$this->database", $this->username, $this->password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return true;
		}

		return false;
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

	public function one(string $query, array $params = array(), $mode = PDO::FETCH_ASSOC) {
		$stmt = $this->execute($query, $params);
		return $stmt->fetch($mode);
	}

	public function all(string $query, array $params = array(), $mode = PDO::FETCH_ASSOC) {
		$stmt = $this->execute($query, $params);
		return $stmt->fetchAll($mode);
	}

	public function insert(string $query, array $params = array()) {
		$stmt = $this->execute($query, $params);
		return $stmt;
	}
}