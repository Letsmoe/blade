<?php

class AuthorizationProvider {
	private $type = App::BASIC_VALIDATION;

	public function __construct(int $authorizationType)
	{
		$this->type = $authorizationType;
	}

	private function getAuthorizationHeader(){
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} else if (isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
			$headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);
		} else if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			//print_r($requestHeaders);
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	private function getBearerToken() {
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	} 

	private function getBasicAuthInfo() {
		$header = $this->getAuthorizationHeader();
		$auth_array = explode(" ", $header);
		$username_password = explode(":", base64_decode($auth_array[1]));
		$username = $username_password[0];
		$password = $username_password[1];

		return [$username, $password];
	}

	public function authorizeRequest(string | callable $callback): mixed {
		if ($this->type === App::BASIC_VALIDATION) {
			[$username, $password] = $this->getBasicAuthInfo();
			// Check whether username as well as password could be found in the authorization header.
			if (!$username || !$password) {
				return false;
			}
			return $callback($username, $password);
		} else if ($this->type === App::BEARER_VALIDATION) {
			$token = $this->getBearerToken();
			// Check whether a token could be found in the authorization header.
			if (!$token) {
				return false;
			}
			return $callback($token);
		}
	}
}