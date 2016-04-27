<?php
	namespace PM;

	function load($namespace) {
		$split_path = explode('\\', $namespace);
		$path = '';
		$name = '';
		$first_word = true;
		for ($i = 0; $i < count($split_path); $i++) {
			if ($split_path[$i] && !$first_word) {
				if ($i == count($split_path) - 1)
					$name = $split_path[$i];
				else
					$path .= DIRECTORY_SEPARATOR . $split_path[$i];
			}
			if ($split_path[$i] && $first_word) {
				if ($split_path[$i] != __NAMESPACE__)
					break;
				$first_word = false;
			}
		}
		if (!$first_word) {
			$full_path = __DIR__ . $path . DIRECTORY_SEPARATOR . $name . '.php';

			/** @noinspection PhpIncludeInspection */
			return include_once($full_path);
		}

		return false;
	}

	function loadPath($abs_path) {
		/** @noinspection PhpIncludeInspection */
		return include_once($abs_path);
	}

	require_once(__DIR__ . "/../vendor/autoload.php");

	spl_autoload_register(__NAMESPACE__ . '\load');