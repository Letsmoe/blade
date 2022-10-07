<?php

include_once "../vendor/autoload.php";

app()->redirect("/([A-z]+)/name", "/%s/18/info", 301);

app()->get("/(.*?)/([0-9]+)/info", function($request, $response, $args) {
	cache()->setCacheDir("./cache/");
	if (cache()->hasCache() || app()->getCookie("wasCached")) {
		app()->setCookie("wasCached", true);
		$data = cache()->dump();
		return $response->plain($data);
	} else {
		app()->setCookie("wasCached", false);
		cache()->store();
	}

	[$name, $age] = $args;

	app()->setCookie($name, $age, 86400);

	return $response->plain(json_encode(["name" => $name, "age" => $age, "url" => $request->getRoute()]));
});

app()->run();

?>