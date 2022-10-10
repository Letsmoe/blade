<?php

include_once __DIR__ . "/Redirect.php";

class Response {
	public $charset = self::UTF8;
	public $headers = [];
	public $responseCode = 200;
	public $data = null;

	public function withHeader(string $name, string $value) {
		$this->headers[$name] = $value;
		return $this;
	}

	/**
	 * Checks whether the response has a specific header among all enabled headers.
	 * @param string $name The name of the header to check for.
	 * @return bool True if the header is enabled, false otherwise.
	 */
	public function hasHeader(string $name): bool {
		return isset($this->headers[$name]);
	}

	public function json(object | array $data): Response {
		$this->withContentType("application/json");
		$this->withCharset(self::UTF8);
		$this->data = json_encode($data);
		return $this;
	}

	public function withStatus(int $code): Response {
		$this->responseCode = $code;
		return $this;
	}

	public function withRedirect(string $url, int $redirectType = 301): Redirect {
		return new Redirect($url, $redirectType);
	}

	public function plain(string $data): Response {
		$this->withContentType("text/plain");
		$this->withCharset(self::UTF8);
		$this->data = $data;
		return $this;
	}

	public function html(string $data): Response {
		$this->withContentType("text/html");
		$this->withCharset(self::UTF8);
		$this->data = $data;
		return $this;
	}

	/**
	 * Removes the specified header from the response.
	 * @param string $name The name of the header to remove.
	 * @return Response The Response instance.
	 */
	public function withoutHeader(string $name): Response {
		unset($this->headers[$name]);
		return $this;
	}

	public function withContentType(string $contentType): Response {
		$this->withHeader("content-type", $contentType);
		return $this;
	}

	public function withCharset(string $charset): Response {
		$this->charset = $charset;
		return $this;
	}

	/**
	 * Returns the list of currently active headers.
	 * @return (array | object) An object containing all active headers with their names as keys and their values as values.
	 */
	public function getHeaders(): array | object {
		return $this->headers;
	}

	/**
	 * Returns a specific header present in the list of active headers.
	 * @param string $name The name of the header to return.
	 * @return string The value of the specified header.
	 */
	public function getHeader(string $name) {
		return $this->headers[$name];
	}

	/**
	 * A method that returns the currently used response code used for the response.
	 * @return int The response code
	 */
	public function getStatusCode(): int {
		return $this->responseCode;
	}

	public function sendHeader() {
		http_response_code($this->responseCode);
		$headers = [];
		foreach($this->headers as $name => $value) {
			$headers[] = $name . ": " . $value;
		}
		$headerString = implode(";", $headers);
		header($headerString . "; charset=$this->charset");
	}

	public function sendBody() {
		echo $this->data;
	}

	const JSON = "application/json";
	const XML = "application/xml";
	const PLAIN = "text/plain";
	const UTF8 = "utf-8";

	public function error(string $message = "", int $response_code = 400): Response {
		$this->withCharset(self::UTF8);
		$this->withContentType(self::JSON);
		$this->withStatus($response_code);
		$this->data = json_encode([
			"status" => $response_code,
			"message" => $message,
		]);
		return $this;
	}

	public function success($data, int $response_code = 200): Response {
		$this->withContentType(self::JSON);
		$this->withCharset(self::UTF8);
		$this->withStatus($response_code);
		$this->data = json_encode([
			"status" => $response_code,
			"data" => $data,
		]);
		return $this;
	}
}

function response() {
	return new Response;
}