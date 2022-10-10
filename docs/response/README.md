# The Response Object
The Blade Framework implements it's own representation of the [PSR-7 Response Object](https://www.php-fig.org/psr/psr-7/) to guarantee compatibility and execution speed.
Once the callback to a route has been called, a [Request Object](../request/README.md) and a Response Object will be passed as parameters.
A Response Object is used to handle all answers to a given request depending on the resource that has been requested.
In contrast to the official PSR-7 specification, the Response Object introduced by Blade is created only once. Every setting that is being changed will be affected globally. The Response Object is not supposed to be cloned since it is only valid for a specific request.

```php
app()->get("/api/v1/resource/", function(Request $request, Response $response) {
	return $response->json([
		"status" => "success",
		"data" => [
			"name" => "..."
		]
	])->withStatus(200);
});

app()->run();
```