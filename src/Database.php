<?php

class Database {
	private $host = null;
	private $username = null;
	private $password = null;
	private $database = null;
	public $conn = null;

	public function __construct(string $host = null, string $username = null, string $password = null, string $database = null)
	{
		$this->set($host, $username, $password, $database);
		$this->connect();
	}

	public function __get(string $name): mixed {
		if (property_exists($this, $name)) {
			return $this[$name];
		} else {
			throw new Error("Error when trying to access invalid property '$name' of " . get_class($this));
		}
	}

	public function set(string $host, string $username, string $password, string $database): void {
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
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
		$this->close();
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
		if (!$stmt) {
			return false;
		}

		if (!$stmt->execute($params)) {
			return false;
		}

		return $stmt;
	}

	public function one(string $query, array $params = array(), $mode = PDO::FETCH_ASSOC) {
		$stmt = $this->execute($query, $params);
		if (!$stmt) {
			return false;
		}
		return $stmt->fetch($mode);
	}

	public function all(string $query, array $params = array(), $mode = PDO::FETCH_ASSOC) {
		$stmt = $this->execute($query, $params);
		if (!$stmt) {
			return false;
		}
		return $stmt->fetchAll($mode);
	}

	public function insert(string $query, array $params = array()) {
		$stmt = $this->execute($query, $params);
		if (!$stmt) {
			return false;
		}
		return $stmt;
	}

	public function update(string $query, array $params = array()) {
		$stmt = $this->execute($query, $params);
		if (!$stmt) {
			return false;
		}
		return $stmt;
	}
}