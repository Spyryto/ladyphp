<?php

require_once(__DIR__ . '/LadyLoader.php');

spl_autoload_register(function ($class) {
	if (file_exists($file = sprintf('%s/%s.php', __DIR__, $class))) {
		require($file);
	}
});
