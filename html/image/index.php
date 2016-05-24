<?php
	require_once __DIR__ . "/../../class/autoload.php";
	error_reporting(0);

	$_pdo = new \PM\PDO\MySQL();

	$_auth = new \PM\Auth\SessionAuth($_pdo);

	$router = new AltoRouter([], "/image");

	$_fs = \PM\Config::getFileSystem();



	$router->map("GET", "/profile/[small|medium|large:size]/[i:id]/", function($size, $id) use ($_pdo, $_fs, $_auth) {
		$user = \PM\User\User::find($_pdo, $id);

		if (is_null($user)) {
			header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
			return;
		}

		$img = $user->getProfileImage();

		if (is_null($img) || !$img->isImage()) {
			header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
			return;
		}

		$file_name = strtolower($user->getNameFirst() . "-" . $user->getNameLast() . "." . $img->getExtension());

		header("Content-Disposition: inline; filename=\"" . $file_name . "\"");

		$_im = \PM\Image\GDImageManipulator::read($img, $_fs);

		if ($size == "medium") {
			$_im->resize("50%", "50%");
		}
		else if ($size == "small") {
			$_im->resize("25%", "25%");
		}

		$_im->output($img->getExtension());
	});













	$match = $router->match();

	if ($match && !is_callable($match["target"])) {
		throw new TypeError("Target is not callable.");
	}
	else if ($match && is_callable($match["target"])) {
		$page_title = $match["name"];

		call_user_func_array($match["target"], $match["params"]);
	}
	else {
		$page_title = "404";

		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
	}