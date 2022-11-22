# Blade Response
The Response object is a simple wrapper for interacting with the output of your Application, you can set **headers** or modify the **response content** easily.

<hr>

## Usage
The response object is passed to your active route via a function parameter.

```php
app()->get("/index/", function(Request $request, Response $response) {
	$data = $request->data();
	$args = request()->args();

	// Return the response object to send output to your user.
	return $response->json($data);
})
```

There are two ways to access the `Response` object. You may either call the `response()` function which will return the global `response` instance, or you can use the `$response` variable which is passed to your current route as a parameter.

#### Please Note

When using the response object in a global scope, the output will **not** be sent to the user automatically!

If you want to send output to a user, you will need to call these commands on the response object:

```php
response()->sendHeader();
response()->sendBody();
```

The output of a route will **not** be used automatically either! If you want to output content from a route you need to **return the response object** like in the example above.