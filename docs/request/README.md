# Blade Request
The request object allows you to interact with the HTTP Request parameters as well as the custom [Request Arguments](./request-args.md) passed to your Application.

<hr>

## Usage
The request object is passed to your active route via a function parameter.

```php
app()->get("/index/", function(Request $request, Response $response) {
	// Here you can access the request object.
	$data = $request->data();
	$args = request()->args();

	return $response->json($data);
})
```

There are two ways to access the `Request` object. You may either call the `request()` function which will return the global `Request` instance, or you can use the `$request` variable which is passed to your current route as a parameter.

Using them interchangeably is not recommended because it will literally confuse everyone on your team...

But if you need to get the server parameters you can easily call the `request()` function in a global scope.