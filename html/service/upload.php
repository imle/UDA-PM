<?php
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

//	else if (\PM\Utility::stringStartsWith($_REQUEST["REQUEST_NAME"], "ADMIN_")) {
//		$_REQUEST["REQUEST_NAME"] = str_replace("ADMIN_", "", $_REQUEST["REQUEST_NAME"]);
//
//		if (!$_auth->getUser()->getPosition() || !$_auth->getUser()->getPosition()->isOfficer()) {
//			$data = [
//				"err" => true,
//				"msg" => "You do not have permission to do that."
//			];
//		}
//
//		else if ($_REQUEST["DATA_TYPE"] == "UPLOAD_EVENT_ATTACHMENT") {
//			$event_id = Utility::cleanInt($_POST["event_id"], 1);
//
//			if (!$event_id) {
//				$data = [
//					"err" => true,
//					"msg" => "Invalid event ID."
//				];
//
//				goto end;
//			}
//
//			$_event = \PM\Calendar\Event::find($_pdo, $event_id);
//
//			if (is_null($_event) || $_event->getCreator()->getChapterId() != $_auth->getUser()->getChapterId()) {
//				$data = [
//					"err" => true,
//					"msg" => "Invalid event ID."
//				];
//
//				goto end;
//			}
//
//			try {
//				$_fs = \PM\Config::getFileSystem();
//
//				$_uploader = new \PM\FileOld\Builder\EventFileBuilder($_pdo, $_fs, $_event);
//				$_file = $_uploader->create($_auth->getUser(), $_FILES["event_attachment"]);
//
//				$data = [
//					"err" => false,
//					"msg" => "",
//					"file" => $_file->toArray()
//				];
//
//				//TODO: Decide if an email should be sent here
//			} catch (\PM\Exception\UploadException $er) {
//				$data = array(
//					"err" => true,
//					"msg" => $er->getMessage()
//				);
//			}
//		}
//	}

	else {
		$data = array(
			"err" => true,
			"msg" => "Action not set."
		);
	}


	end: echo json_encode($data, JSON_PRETTY_PRINT);