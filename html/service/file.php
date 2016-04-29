<?php
	use PM\Config;
	use PM\File\Attachment;
	use PM\File\File;
	use PM\Utility;

	require_once __DIR__ . "/../../class/autoload.php";

	$_pdo = new \PM\PDO\MySQL();
	$_auth = new \PM\Auth\SessionAuth($_pdo);
	$router = new AltoRouter([], "/service/file");

	ini_set("post_max_size", Config::getMaxFileSize());
	ini_set("upload_max_filesize", Config::getMaxFileSize());

	$router->map("POST", "/", function() use ($_pdo, $_auth) {
		header("Content-Type: application/json");

		$data = [];

		if (!count($_FILES)) {
			$data = Utility::errorJson("There was an error with the file upload.");
		}

		else if ($_FILES["file"]["error"]) {
			switch ($_FILES["file"]["error"]) {
				case 1:
				case 2:
					$data = Utility::errorJson("Your file is too large.");
					break;
				case 3:
				case 4:
				case 6:
				case 8:
					$data = Utility::errorJson("There was a problem uploading your image.");
					break;
				case 7:
					$data = Utility::errorJson("There was a problem saving your image.");
					break;
			}
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
				if (Config::getEnvironment() == Config::ENV_PROD) {
					$data = Utility::errorJson("There was an error uploading your file.");
				}
				else {
					$data = Utility::errorJson($er->getMessage());
				}
			}
		}

		if ($data["err"] == true) {
			header("HTTP/1.0 400 Bad Request");
		}

		echo json_encode($data, JSON_PRETTY_PRINT);
	});

	$router->map("GET", "/[i:id]/", function($id) use ($_pdo, $_auth) {
		$attachment = Attachment::find($_pdo, $id);

		if (is_null($attachment)) {
			header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
			return;
		}

		$file = $attachment->getFile();

		if (is_null($file)) {
			header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
			return;
		}

		header("Content-Description: File Transfer");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename='" . $attachment->getFullName() . "'");
		header("Expires: 0");
		header("Cache-Control: must-revalidate");
		header("Pragma: public");
		header("Content-Length: " . $file->getSize());

		$fs = Config::getFileSystem();

		$fh = fopen("php://output", "w");
		fwrite($fh, stream_get_contents($file->read($fs)));
		fclose($fh);
	});













	$match = $router->match();

	if ($match && !is_callable($match["target"])) {
		throw new TypeError("Target is not callable.");
	}
	else if ($match && is_callable($match["target"])) {
		$arr = call_user_func_array($match["target"], $match["params"]);
	}
	else {
		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
	}