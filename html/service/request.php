<?php
	header("Content-Type: application/json");
//	ini_set("html_errors", false);
	require_once(__DIR__ . "/../../class/autoload.php");

	$_pdo = new \PM\PDO\MySQL();
	$_auth = new \PM\Auth\SessionAuth($_pdo);
	//Do not validate auth here, use authenticated controller

	$data = [];

	if ($_POST["REQUEST_NAME"] == "") {

	}

	else if (\PM\Utility::stringStartsWith($_POST["REQUEST_NAME"], "ADMIN_")) {
		$_POST["REQUEST_NAME"] = str_replace("ADMIN_", "", $_POST["REQUEST_NAME"]);

	}

	else {
		$data = [
			"err" => true,
			"msg" => "Action not set."
		];
	}

	echo json_encode($data, JSON_PRETTY_PRINT);