<?php
	header("Content-Type: application/json");
//	ini_set("html_errors", false);
	require_once(__DIR__ . "/../../class/autoload.php");

	$_pdo = new \PM\PDO\MySQL();
	$_auth = new \PM\Auth\SessionAuth($_pdo);
	//Do not validate auth here, use authenticated controller

	$_POST["REQUEST_NAME"] = strtoupper($_POST["REQUEST_NAME"]);

	$data = [
		"err" => true,
		"msg" => "Invalid action attempted. Action does not exist."
	];

	if ($_POST["REQUEST_NAME"] == "SERVICE_NAME") {

	}

	echo json_encode($data, JSON_PRETTY_PRINT);