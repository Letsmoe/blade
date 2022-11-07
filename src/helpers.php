<?php

include_once __DIR__ . "/App.php";
include_once __DIR__ . "/Cache.php";
include_once __DIR__ . "/Database.php";
include_once __DIR__ . "/Session.php";

global $__DB__;
global $__APP__;
global $__SESSION__;
global $__CACHE__;
$__SESSION__ = new Session();
$__CACHE__ = new Cache($_SERVER["ORIG_PATH_INFO"]);
$__APP__ = new App();
$__DB__ = new Database();

/**
 * A helper function that takes the global $__APP__ instance and returns it.
 * This function is very useful since it is exposed in all global and local scopes.
 * This way, callback functions don't have to "use" the an $app variable.
 * `app()` can always be called inside a function.
 * 
 * @return App An instance of the `App` class.
 */
function app(): App {
	return $GLOBALS["__APP__"];
}

/**
 * A helper function that takes the global $__CACHE__ instance and returns it.
 * 
 * @return Cache An instance of the `Cache` class.
 */
function cache(): Cache {
	return $GLOBALS["__CACHE__"];
}

/**
 * A helper function that takes the global $__DB__ instance and returns it.
 * 
 * @return Database An instance of the `Database` class.
 */
function db(): Database {
	return $GLOBALS["__DB__"];
}

/**
 * A helper function that takes the global $__SESSION__ instance and returns it.
 * 
 * @return Session An instance of the `Session` class.
 */
function session(): Session {
	return $GLOBALS["__SESSION__"];
}