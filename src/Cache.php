<?php

function path_join(string ...$paths) {
	return join("/", array_filter(array_map(function ($path) {
		return trim($path, "/");
	}, $paths)));
}


class Cache {
	const APC = 1;
	public $dir = "./";
	public $ttl = 0;
	private $current_page = "";
	public function __construct(string $page) {
		$this->current_page = hash("md5", $page);
	}

	public function doCache() {
		ob_start();
		register_shutdown_function(function() {
			$buffer = ob_get_contents();
			file_put_contents(path_join($this->dir, $this->current_page), $buffer);
			ob_flush();
			ob_end_clean();
		});
	}

	public function output() {
		$path = path_join($this->dir, $this->current_page);
		if (file_exists($path)) {
			echo file_get_contents($path);
		} else {
			throw new Error("Could not read cache. File seems to be empty.");
		}
	}

	public function isCached() {
		$path = path_join($this->dir, $this->current_page);
		$exists = file_exists($path);
		$expired = $exists && ((time() - $this->ttl) > filemtime($path));

		if ($expired) {
			unlink($path);
			return false;
		}

		return $exists && !$expired;
	}
}

