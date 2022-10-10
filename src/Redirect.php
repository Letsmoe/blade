<?php


class Redirect {
	public $redirectUrl = null;
	public $redirectType = 301;

	public function __construct(string $route, int $type = 301) {
		$this->redirectUrl = $route;
		$this->redirectType = $type;
	}

	public function setRedirectUrl(string $redirectUrl) {
		$this->redirectUrl = $redirectUrl;
	}

	public function setRedirectType(int $type) {
		$this->redirectType = $type;
	}
}