<?php

include_once "../vendor/autoload.php";

app()->redirect("/([A-z]+)/name", "/%s/18/info", 301);

app()->get("/(.*?)/([0-9]+)/info", function($request, $response, $args) {
	$name = $request->get("name");

	[$name, $age] = $args;

	app()->setCookie($name, $age, 86400);

	return $response->plain(json_encode(["name" => $name, "age" => $age, "url" => $request->getRoute()]));
});

app()->run();

?>