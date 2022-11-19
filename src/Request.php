<?php

class Request {
	private $args = [];
	public function __construct(array $args = []) {
		$this->args = $args;
	}

	public function data() {
		if ($_SERVER["CONTENT_TYPE"] == "application/json") {
			return json_decode(file_get_contents("php://input"), true);
		} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
			return $_GET;
		} else {
			return file_get_contents("php://input");
		}
	}

	public function server(string | bool $property = false): mixed {
		if ($property) {
			return $_SERVER[$property];
		}
		return $_SERVER;
	}

	/**
	 * Returns the current request uri.
	 * @return string The request path.
	 */
	public function getRoute() {
		return $_SERVER["ORIG_PATH_INFO"];
	}

	/**
	 * Returns the currently used request method.
	 * @return string Request method.
	 */
	public function getRequestMethod() {
		return $_SERVER["REQUEST_METHOD"];
	}

	public function get(string $name) {
		$data = $this->data();
		if (array_key_exists($name, $data)) {
			return $data[$name];
		} else {
			return null;
		}
	}

	public function args() {
		return $this->args;
	}
}