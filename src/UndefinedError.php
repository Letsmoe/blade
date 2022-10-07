<?php

class UndefinedError extends Error {
	public function __construct(string $message, int $code = 0, Throwable $previous = null) {
		parent::__construct("Array key '$message' not defined.", $code, $previous);
	}

	public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}