<?php

namespace Letsmoe\Blade;
require "./vendor/autoload.php";
require "./src/Blade.php";

app()->get("/blade/([0-9]+)", function($id) {
	echo $id;
	echo app()->args();
});

app()->run();