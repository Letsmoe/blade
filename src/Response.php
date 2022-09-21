<?php

class Response {
	public static $charset = "utf-8";
	public static $contentType = "application/json";

	public static function json(object $data): void {
		self::setContentType("application/json");
		self::setCharset("utf-8");
		self::sendHeader();
		if (is_string($data)) {
			echo $data;
		} else {
			echo json_encode($data);
		}
	}

	public static function text(string $data): void {
		self::setContentType("text/plain");
		self::setCharset("utf-8");
		self::sendHeader();
		echo $data;
	}

	public static function setContentType(string $contentType): void {
		self::$contentType = $contentType;
	}

	public static function setCharset(string $charset): void {
		self::$charset = $charset;
	}

	public static function sendHeader() {
		$contentType = self::$contentType;
		$charset = self::$charset;
		header("Content-Type: $contentType; charset=$charset");
	}

	public static function error(string $message = "", int $response_code = 400) {
		header("Content-Type: application/json; charset=utf-8");
		http_response_code($response_code);
		echo json_encode([
			"status" => $response_code,
			"message" => $message,
		]);
	}
}

function response() {
	return new Response;
}