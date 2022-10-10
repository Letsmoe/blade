<?php

include_once __DIR__ . "/Response.php";
include_once __DIR__ . "/Request.php";
include_once __DIR__ . "/Authorization.php";
include_once __DIR__ . "/UndefinedError.php";

class App {
	public string $route;
	public string $method;

	private array $routes = [];

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

	private function addRoute(string $route, string | array | callable $callback, array $methods = ["GET", "POST"]) {
		$this->routes[] = array("httpMethods" => $methods, "route" => $route, "callback" => $callback, "regex" => "#^{$route}\$#");
	}

	public function get(string $route, string | array | callable $callback) {
		$this->addRoute($route, $callback, ["GET"]);
	}

	public function post(string $route, string | array | callable $callback) {
		$this->addRoute($route, $callback, ["POST"]);
	}

	public function put(string $route, string | array | callable $callback) {
		$this->addRoute($route, $callback, ["PUT"]);
	}

	public function delete(string $route, string | array | callable $callback) {
		$this->addRoute($route, $callback, ["DELETE"]);
	}

	public function run(bool $exit = true) {
		foreach ($this->routes as $route) {
			$args = [];
			if (!preg_match($route["regex"], $this->route, $args)) {
				continue;
			}

			// $args now contains the full input as first offset. Let's remove that.
			array_splice($args, 0, 1);

			if (!in_array($this->method, $route["httpMethods"])) {
				continue;
			}

			$callback = $route["callback"];
			$isMethod = is_array($callback) && method_exists($callback[0], $callback[1]);

			if ($isMethod || is_callable($callback) || function_exists($callback)) {
				$result = call_user_func_array($callback, [new Request([...$args]), new Response(), [...$args]]);
			} else {
				throw new Error("Unknown function '{$route['callback']}' in route.");
			}

			if ($result instanceof Response) {
				$result->sendHeader();
				$result->sendBody();
				exit;
			} else if ($result instanceof Redirect) {
				header("Location: " . $result->redirectUrl, true, $result->redirectType);
				exit;
			} else if ($result) {
				return $result;
			}
		}

		if ($exit) {
			header("HTTP/1.0 404 Not Found");
			die;
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
		$this->addRoute($route, function(Request $request, Response $response) use ($redirectCode, $redirectRoute) {
			// When there were regex matches in the route, we need to pass them to the redirect route in case we want to keep them.
			$args = $request->args();
			$filledRedirectRoute = vsprintf($redirectRoute, $args);
			return $response->withRedirect($filledRedirectRoute, $redirectCode);
		}, $methods);
	}


	public function authorize(int $method = self::BEARER_VALIDATION, string | callable $callback) {
		$provider = new AuthorizationProvider($method);
		$result = $provider->authorizeRequest($callback);

		$response = new Response();
		
		if ($result) {
			$response->plain("Fatal error in Authentication.");
			$response->sendHeader();
			$response->sendBody();
			exit;
		}
	}
}