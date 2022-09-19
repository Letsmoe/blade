<?php
function getAuthorizationHeader(){
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

/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function getAuthInfo() {
	$header = getAuthorizationHeader();
	$auth_array = explode(" ", $header);
	$username_password = explode(":", base64_decode($auth_array[1]));
	$username = $username_password[0];
	$password = $username_password[1];

	return ["username" => $username, "password" => $password];
}

class UndefinedError extends Error {
	public function __construct(string $message, int $code = 0, Throwable $previous = null) {
		parent::__construct("Array key '$message' not defined.", $code, $previous);
	}

	public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class App {
	public $route;
	public $method;

	private array $routes = [];
	private array $regexes = [];

	public function __construct(bool $useDefaultHtaccess = true) {
		if (!$useDefaultHtaccess) {
			// The user is not using the default .htaccess file, this means that some variables might not be set correctly.
			// The user MUST manually override following variables in order for this app to function.
			// string $this->route Defines the route the user used to access the current document. (Default: $_SERVER["ORIG_PATH_INFO"])
			// string $this->method Defines the request method that was used, should work even when not changed manually. (Default: $_SERVER["REQUEST_METHOD"])


		} else {
			if (isset($_SERVER["ORIG_PATH_INFO"])) {
				$this->route = $_SERVER["ORIG_PATH_INFO"];
			} else {
				throw new UndefinedError("ORIG_PATH_INFO");
			}
			if (isset($_SERVER["REQUEST_METHOD"])) {
				$this->method = $_SERVER["REQUEST_METHOD"];
			} else {
				throw new UndefinedError("REQUEST_METHOD");
			}
		}
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

	private function addRoute(string $route, string | array | Closure $callback, array $methods = ["GET", "POST"]) {
		$this->routes[] = array("httpMethods" => $methods, "route" => $route, "callback" => $callback);
		$this->regexes[] = "#^{$route}\$#";
	}

	public function get(string $route, string | array | Closure $callback) {
		$this->addRoute($route, $callback, ["GET"]);
	}

	public function post(string $route, string | array | Closure $callback) {
		$this->addRoute($route, $callback, ["POST"]);
	}

	public function put(string $route, string | array | Closure $callback) {
		$this->addRoute($route, $callback, ["PUT"]);
	}

	public function delete(string $route, string | array | Closure $callback) {
		$this->addRoute($route, $callback, ["DELETE"]);
	}

	public function run(bool $exit = true) {
		$i = 0;
		foreach ($this->regexes as $regex) {
			$args = [];
			if (preg_match($regex, $this->route, $args)) {
				// $args now contains the full input as first offset. Let's remove that.
				array_splice($args, 0, 1);
				$route = $this->routes[$i];

				if (!in_array($this->method, $route["httpMethods"])) {
					continue;
				}

				if (is_array($route["callback"]) && method_exists($route["callback"][0], $route["callback"][1])) {
					return call_user_func_array($route["callback"], [...$args]);
				} else if (is_callable($route["callback"]) || function_exists($route["callback"])) {
					return call_user_func($route["callback"], ...[...$args]);
				} else {
					throw new Error("Unknown function '{$route['callback']}' in route.");
				}
			}

			$i++;
		}

		if ($exit) {
			header("HTTP/1.0 404 Not Found");
			die();
		} else {
			return false;
		}
	}

	/**
	 * Outputs the given value as json encoded string.
	 */
	public function json(mixed $value) {
		$this->setContentType("application/json");
		if (is_string($value)) {
			echo $value;
		} else {
			echo json_encode($value);
		}
	}

	private function setContentType(string $contentType) {
		header("Content-Type: $contentType");
	}

	/**
	 * Outputs the given value as plain text.
	 */
	public function plain(string $value) {
		$this->setContentType("text/plain");
		echo $value;
	}

	public const BEARER_VALIDATION = -1;
	public const BASIC_VALIDATION = -2;

	public function authorize(int $method = self::BEARER_VALIDATION, string | Closure $callback) {
		if ($method == self::BEARER_VALIDATION) {
			$token = getBearerToken();

			if ($result = $callback($token)) {
				return $result;
			} else {
				$this->plain("Bearer Authentication failed, token is invalid.");
				die();
			}
		} else if ($method == self::BASIC_VALIDATION) {
			["username" => $username, "password" => $password] = getAuthInfo();

			if ($result = $callback($username, $password)) {
				return $result;
			} else {
				$this->plain("Basic Authentication failed, username or password is invalid.");
				die();
			}
		} else {
			throw new Error("Unknown authentication method");
		}
	}
}
