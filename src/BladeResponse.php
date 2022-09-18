<?php

function response(): ResponseInterface {
	return new ResponseInterface();
}


class ResponseInterface {
	private $charset = "utf-8";
	private $contentType = "application/json";

	public function json(object $data): void {
		$this->setContentType("application/json");
		$this->setCharset("utf-8");
		$this->sendHeader();
		if (is_string($data)) {
			echo $data;
		} else {
			echo json_encode($data);
		}
	}

	public function text(string $data): void {
		$this->setContentType("text/plain");
		$this->setCharset("utf-8");
		$this->sendHeader();
		echo $data;
	}

	private function setContentType(string $contentType): void {
		$this->contentType = $contentType;
	}

	private function setCharset(string $charset): void {
		$this->charset = $charset;
	}

	private function sendHeader() {
		header("Content-Type: $this->contentType; charset=$this->charset");
	}
}