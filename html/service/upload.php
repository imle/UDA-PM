<?php
	use PM\Config;
	use PM\File\File;
	use PM\Utility;

	header("Content-Type: application/json");
	require_once __DIR__ . "/../../class/autoload.php";

	$_pdo = new \PM\PDO\MySQL();
	$_auth = new \PM\Auth\SessionAuth($_pdo);
	$_auth->validate();

	$data = [];

	if (!count($_FILES)) {
		$data = [
			"err" => true,
			"msg" => "There was an error with the file upload."
		];
	}

	else {
		$file_name = Utility::cleanString($_FILES["file"]["name"]);
		$file_tmp_name = Utility::cleanString($_FILES["file"]["tmp_name"]);

		try {
			$_fs = Config::getFileSystem();

			$_file = File::create($_pdo, $_fs, $_auth->getUser(), $file_name, $file_tmp_name);

			$data = [
				"err" => false,
				"msg" => "",
				"file" => $_file->toArray()
			];
		} catch (Exception $er) {
			$message = "There was an error uploading your file.";

			$data = array(
				"err" => true,
				"msg" => Config::getEnvironment() == Config::ENV_PROD ? $message : $er->getMessage()
			);
		}
	}

	if ($data["err"] == true) {
		header("HTTP/1.0 400 Bad Request");
	}

	end: echo json_encode($data, JSON_PRETTY_PRINT);