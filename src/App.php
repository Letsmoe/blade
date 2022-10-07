<?php

include_once __DIR__ . "/Response.php";
include_once __DIR__ . "/Request.php";
include_once __DIR__ . "/UndefinedError.php";

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

class App {
	public string $route;
	public string $method;

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
					$result = call_user_func_array($route["callback"], [new Request([...$args]), new Response(), [...$args]]);
				} else if (is_callable($route["callback"]) || function_exists($route["callback"])) {
					$result = call_user_func($route["callback"], ...[new Request([...$args]), new Response(), [...$args]]);
				} else {
					throw new Error("Unknown function '{$route['callback']}' in route.");
				}

				if ($result instanceof Response) {
					$result->sendHeader();
					$result->sendBody();
					exit();
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

	public const BEARER_VALIDATION = -1;
	public const BASIC_VALIDATION = -2;

	/**
	 * Sends a request to the user's browser to store a new cookie.
	 * @return App The current App instance.
	 */
	public function setCookie(string $name, string $value, int $expires = 86400, string $path = "", string $domain = "", bool $secure = false, bool $httpOnly = false) {
		setcookie($name, $value, time() + $expires, $path, $domain, $secure);
		return $this;
	}

	/**
	 * Retrieves a cookie from the $_COOKIE superglobal variable.
	 * @param string $name The name of the cookie to retrieve.
	 * @return string The cookie value associated with that name.
	 */
	public function getCookie(string $name) {
		return $_COOKIE[$name];
	}

	public function redirect(string $route, string $redirectRoute, int $redirectCode = 301, array $methods = ["GET", "POST", "PUT", "DELETE", "HEAD"]) {
		$this->addRoute($route, function($request, $response, $args) use ($redirectCode, $redirectRoute) {
			// When there were regex matches in the route, we need to pass them to the redirect route in case we want to keep them.
			$filledRedirectRoute = vsprintf($redirectRoute, $args);
			header("Location: $filledRedirectRoute", true, $redirectCode);
			exit();
		}, $methods);
	}


	public function authorize(int $method = self::BEARER_VALIDATION, string | Closure $callback) {
		$response = new Response();
		if ($method == self::BEARER_VALIDATION) {
			$token = getBearerToken();

			if ($result = $callback($token)) {
				return $result;
			} else {
				$response->plain("Bearer Authentication failed, token is invalid.");
				$response->sendHeader();
				$response->sendBody();
				die();
			}
		} else if ($method == self::BASIC_VALIDATION) {
			["username" => $username, "password" => $password] = getAuthInfo();

			if ($result = $callback($username, $password)) {
				return $result;
			} else {
				$response->plain("Basic Authentication failed, username or password is invalid.");
				$response->sendHeader();
				$response->sendBody();
				die();
			}
		} else {
			throw new Error("Unknown authentication method");
		}
	}
}