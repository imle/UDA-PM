<?php
	require_once __DIR__ . "/../../class/autoload.php";
	header("Content-Type: application/json");

	$_pdo = new \PM\PDO\MySQL();
	$_auth = new \PM\Auth\SessionAuth($_pdo);
	$router = new AltoRouter([], "/service");


	$router->map("POST", "/login/", function() use ($_pdo, $_auth) {
		$_auth->authenticate($_POST["email"] ?: "", $_POST["password"] ?: "");

		$user = null;

		if (!$_auth->hasError() && $_auth->getUser()) {
			$user = $_auth->getUser();

			if (\PM\Utility::cleanBoolean($_POST["remember"])) {
				$_auth->remember();
			}

			$user = $user ? $user->toArray() : $user;
		}

		return [
			"err" => $_auth->hasError(),
			"msg" => $_auth->getErrorMessage(),
			"request" => $_auth->getRequestURI(),
			"user" => $user
		];
	});













	$match = $router->match();

	if ($match && !is_callable($match["target"])) {
		throw new TypeError("Target is not callable.");
	}
	else if ($match && is_callable($match["target"])) {
		$page_title = $match["name"];

		$arr = call_user_func_array($match["target"], $match["params"]);

		echo json_encode($arr, JSON_PRETTY_PRINT);


	}
	else {
		$page_title = "404";

		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

		echo json_encode([
			"err" => true,
			"msg" => "Invalid Request URI"
		], JSON_PRETTY_PRINT);
	}