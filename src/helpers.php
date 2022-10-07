<?php

include_once __DIR__ . "/App.php";

global $__APP__;
$__APP__ = new App();

/**
 * A helper function that takes the global $__APP__ instance and returns it.
 * This function is very useful since it is exposed in all global and local scopes.
 * This way, callback functions don't have to "use" the an $app variable.
 * `app()` can always be called inside a function.
 * 
 * @return App An instance of the `App` class.
 */
function app() {
	return $GLOBALS["__APP__"];
}