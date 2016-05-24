<?php
	use PM\Config;
	use PM\File\Attachment;
	use PM\Project\Comment;
	use PM\Project\Project;
	use PM\User\User;
	use PM\Utility;

	require_once __DIR__ . "/../class/autoload.php";
	$_pdo = new \PM\PDO\MySQL();
	$_auth = new \PM\Auth\SessionAuth($_pdo);


	$router = new AltoRouter();

	$router->map("GET", "/", function() use ($_pdo, $_auth) {
		$_auth->validate();
		require __DIR__ . "/../view/home.php";
	}, "Home");

	$router->map("GET", "/login/", function() use ($_pdo, $_auth) {
		$_auth->validate(true);

		require __DIR__ . "/../view/login.php";
	}, "Login");

	$router->map("GET", "/logout/", function() use ($_pdo, $_auth) {
		$_auth->logout();
	}, "Logout");



	
	$router->map("GET", "/account/", function() use ($_pdo, $_auth) {
		$_auth->validate();

		require __DIR__ . "/../view/account.php";
	}, "Account");




	$router->map("GET", "/projects/", function() use ($_pdo, $_auth) {
		$_auth->validate();

		$search = Utility::cleanString($_GET["search"]);
		$filter = Utility::cleanInt($_GET["filter"]);

		if ($filter == Project::RELATION_ASSIGNED) {
			$projects = Project::findAllUserAssigned($_pdo, $_auth->getUser(), 0, 30, $search);
		}
		else if ($filter == Project::RELATION_LEAD) {
			$projects = Project::findAllUserLead($_pdo, $_auth->getUser(), 0, 30, $search);
		}
		else if ($filter == Project::RELATION_CREATED) {
			$projects = Project::findAllUserCreated($_pdo, $_auth->getUser(), 0, 30, $search);
		}
		else {
			if ($_auth->getUser()->getType() == User::TYPE_SUPERVISOR) {
				$projects = Project::findAll($_pdo, 0, 30, $search);
			}
			else {
				$projects = Project::findAllUserRelated($_pdo, $_auth->getUser(), 0, 30, $search);
			}
		}


		require __DIR__ . "/../view/projects.php";
	}, "Projects");

	$router->map("GET", "/projects/[i:id]/", function($id) use ($_pdo, $_auth) {
		$_auth->validate();

		$project = Project::find($_pdo, $id);

		if ($project) {
			$comments = Comment::findAllForProject($_pdo, $project);
			$attachments = Attachment::findAllForProject($_pdo, $project);
		}

		require __DIR__ . "/../view/project.php";
	}, "Project");









	if (Utility::isDevServer()) {
		$router->map("GET", "/test/", function() use ($_pdo, $_auth) {
			require __DIR__ . "/../view/test.php";
		}, "Test");

//		$router->map("GET", "/test/worker/email/", function() use ($_pdo, $_auth) {
//			$worker = new \PM\Worker\EmailWorker($_pdo);
//			$worker->startWorker();
//		}, "Email Worker");
//
//		$router->map("GET", "/test/worker/file/", function() use ($_pdo, $_auth) {
//			$worker = new \PM\Worker\FileWorker($_pdo);
//			$worker->startWorker();
//		}, "File Worker");
	}












	$match = $router->match();

	if ($match && !is_callable($match["target"])) {
		throw new TypeError("Target is not callable.");
	}
	else if ($match && is_callable($match["target"])) {
		$page_title = $match["name"];

		$users = [];

		if ($_auth->isLoggedIn()) {
			$users = User::getAll($_pdo, 0, -1);
		}

		require __DIR__ . "/../view/part/top.php";

		call_user_func_array($match["target"], $match["params"]);

		require __DIR__ . "/../view/part/bottom.php";
	}
	else {
		display404();
	}

	function display404() {
		$page_title = "404";

		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

		require __DIR__ . "/../view/part/top.php";

		require __DIR__ . "/../view/code/404.php";

		require __DIR__ . "/../view/part/bottom.php";
	}