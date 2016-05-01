<?php
	use PM\File\Attachment;
	use PM\File\File;
	use PM\Project\Comment;
	use PM\Project\Project;
	use PM\User\User;
	use PM\Utility;

	require_once __DIR__ . "/../../class/autoload.php";
	header("Content-Type: application/json");

	const SELECT_LIMIT = 50;

	$_pdo = new \PM\PDO\MySQL();
	$_auth = new PM\Auth\SessionAuth($_pdo);

	$router = new AltoRouter([], "/api/v1");

	if (!Utility::isDevServer()) {
		$router->map("GET", "[*]", function() use ($_pdo) {
			return Utility::errorJson("API is still under development.");
		});
	}
	else {
		$router->map("GET", "/projects/", function() use ($_pdo, $_auth) {
			$offset = Utility::cleanInt($_GET["offset"], 0, 0);

			$projects = Project::findAllUserAssigned($_pdo, $_auth->getUser(), $offset, SELECT_LIMIT + 1);

			$has_more = count($projects) == SELECT_LIMIT + 1;

			$projects = array_slice($projects, 0, SELECT_LIMIT);

			return [
				"err" => false,
				"msg" => "",
				"pagination" => [
					"has_more" => $has_more,
					"count" => count($projects),
					"offset" => $offset
				],
				"projects" => array_map(function(Project $project) {
					return $project->toArray();
				}, $projects)
			];
		});

		$router->map("GET", "/projects/[i:id]/", function($id) use ($_pdo) {
			$project = Project::find($_pdo, $id);

			return [
				"err" => !$project,
				"msg" => !$project ? "No project found with given id" : "",
				"project" => $project->toArray()
			];
		});

		$router->map("POST", "/projects/", function() use ($_pdo, $_auth) {
			$project_lead_id = Utility::cleanInt($_POST["project_lead_id"]);
			$name = Utility::cleanString($_POST["name"]);
			$notes = Utility::cleanString($_POST["notes"]);
			$type = Utility::cleanInt($_POST["type"]);
			$status = Utility::cleanInt($_POST["status"]);
			$assigned_ids = $_POST["assigned_ids"] ?? [];
			
			if (!is_array($assigned_ids)) {
				return Utility::errorJson("Error processing assigned users.");
			}

			if (!Project::isValidStatus($status)) {
				return Utility::errorJson("Invalid status selected.");
			}

			if (!Project::isValidType($type)) {
				return Utility::errorJson("Invalid type selected.");
			}

			$lead = null;

			if ($project_lead_id != 0) {
				$lead = User::find($_pdo, $project_lead_id);

				if (is_null($lead)) {
					return Utility::errorJson("Invalid project lead.");
				}

				if ($lead->getType() != User::TYPE_DEVELOPER) {
					return Utility::errorJson("Invalid project lead.");
				}
			}

			$users = [];
			foreach ($assigned_ids as $assigned_id) {
				$assigned_id = Utility::cleanInt($assigned_id);
				$tmp = User::find($_pdo, $assigned_id);

				if (is_null($tmp)) {
					return Utility::errorJson("Error processing assigned users.");
				}

				$users[] = $tmp;
			}

			$project = new Project($_pdo);
			$project->create($_auth->getUser(), $name, $notes, $status, $type, $lead);

			foreach ($users as $user) {
				$project->assignUser($user);
			}

			return [
				"err" => !$project,
				"msg" => !$project ? "No project found with given id" : "",
				"project" => $project->toArray(),
			];
		});

		$router->map("PUT", "/projects/[i:id]/", function($id) use ($_pdo) {
			return Utility::errorJson("Path not defined.");
		}); // !TODO:

		$router->map("DELETE", "/projects/[i:id]/", function($id) use ($_pdo, $_auth) {
			$project = Project::find($_pdo, $id);

			if ($_auth->getUser()->getType() != User::TYPE_SUPERVISOR) {
				if ($project->getUserCreatedId() != $_auth->getUser()->getId()) {
					return Utility::errorJson("You do not have permission to do that.");
				}
			}

			if ($project) {
				$project->delete($_auth->getUser());
			}

			return [
				"err" => !$project,
				"msg" => !$project ? "No project found with given id" : ""
			];
		});




		$router->map("GET", "/projects/[i:id]/users/", function($id) use ($_pdo) {
			$type = Utility::cleanInt($_GET["type"], -1, 0);

			$project = Project::find($_pdo, $id);

			if (!$project) {
				return Utility::errorJson("No project found with given id");
			}

			return [
				"err" => false,
				"msg" => "",
				"lead" => $project->getProjectLead()->toArray(),
				"users" => array_map(function(User $user) {
					return $user->toArray();
				}, $project->getAllUsersAssigned($type))
			];
		});





		$router->map("GET", "/projects/[i:pid]/attachments/", function($pid) use ($_pdo, $_auth) {
			$project = Project::find($_pdo, $pid);

			$offset = Utility::cleanInt($_GET["offset"], 0, 0);

			$attachments = Attachment::findAllForProject($_pdo, $project, $offset, SELECT_LIMIT + 1);

			$has_more = count($attachments) == SELECT_LIMIT + 1;

			$attachments = array_slice($attachments, 0, SELECT_LIMIT);

			return [
				"err" => false,
				"msg" => "",
				"pagination" => [
					"has_more" => $has_more,
					"count" => count($attachments),
					"offset" => $offset
				],
				"attachments" => array_map(function(Attachment $file) {
					return $file->toArray();
				}, $attachments)
			];
		});

		$router->map("GET", "/projects/[i:pid]/attachments/[i:id]/", function($pid, $id) use ($_pdo) {
			$project = Project::find($_pdo, $pid);

			if (is_null($project)) {
				return Utility::errorJson("Invalid project data provided.");
			}

			$attachment = Attachment::findProject($_pdo, $project, $id);

			return [
				"err" => !$attachment,
				"msg" => !$attachment ? "No attachment found with given id" : "",
				"attachment" => $attachment->toArray()
			];
		});

		$router->map("POST", "/projects/[i:pid]/attachments/", function($pid) use ($_pdo, $_auth) {
			$file_id = Utility::cleanInt($_POST["file_id"]);

			$file = File::find($_pdo, $file_id);

			if (is_null($file)) {
				return Utility::errorJson("Invalid file data provided.");
			}

			$project = Project::find($_pdo, $pid);

			if (is_null($project)) {
				return Utility::errorJson("Invalid project data provided.");
			}

			$attachment = Attachment::create($_pdo, $file, $_auth->getUser(), $project);

			if (!is_null($attachment)) {
				$project->setLmod($_auth->getUser());
			}

			return [
				"err" => !$attachment,
				"msg" => !$attachment ? "There was an error creating your attachment." : "",
				"attachment" => $attachment->toArray()
			];
		});

		$router->map("PUT", "/projects/[i:pid]/attachments/[i:id]/", function($pid, $id) use ($_pdo) {
			return Utility::errorJson("Path not defined.");
		}); // !TODO:

		$router->map("DELETE", "/projects/[i:pid]/attachments/[i:id]/", function($pid, $id) use ($_pdo, $_auth) {
			$project = Project::find($_pdo, $pid);

			if (is_null($project)) {
				return Utility::errorJson("Invalid project data provided.");
			}

			$attachment = Attachment::findProject($_pdo, $project, $id);

			if (is_null($attachment)) {
				return Utility::errorJson("Invalid attachment data provided.");
			}

			$attachment->remove(\PM\Config::getFileSystem());

			$project->setLmod($_auth->getUser());

			return [
				"err" => false,
				"msg" => ""
			];
		});





		$router->map("GET", "/projects/[i:pid]/comments/", function($pid) use ($_pdo, $_auth) {
			$project = Project::find($_pdo, $pid);

			$offset = Utility::cleanInt($_GET["offset"], 0, 0);

			$comments = Comment::findAllForProject($_pdo, $project, $offset, SELECT_LIMIT + 1);

			$has_more = count($comments) == SELECT_LIMIT + 1;

			$comments = array_slice($comments, 0, SELECT_LIMIT);

			return [
				"err" => false,
				"msg" => "",
				"pagination" => [
					"has_more" => $has_more,
					"count" => count($comments),
					"offset" => $offset
				],
				"comments" => array_map(function(Comment $file) {
					return $file->toArray();
				}, $comments)
			];
		});

		$router->map("GET", "/projects/[i:pid]/comments/[i:id]/", function($pid, $id) use ($_pdo) {
			$project = Project::find($_pdo, $pid);

			if (is_null($project)) {
				return Utility::errorJson("Invalid project data provided.");
			}

			$comment = Comment::findProject($_pdo, $project, $id);

			return [
				"err" => !$comment,
				"msg" => !$comment ? "No comment found with given id" : "",
				"comment" => $comment->toArray()
			];
		});

		$router->map("POST", "/projects/[i:pid]/comments/", function($pid) use ($_pdo, $_auth) {
			$text = Utility::cleanString($_POST["text"]);

			$project = Project::find($_pdo, $pid);

			if (is_null($project)) {
				return Utility::errorJson("Invalid project data provided.");
			}

			$comment = Comment::create($_pdo, $project, $_auth->getUser(), $text);

			if (!is_null($comment)) {
				$project->setLmod($_auth->getUser());
			}

			return [
				"err" => !$comment,
				"msg" => !$comment ? "There was an error creating your comment." : "",
				"comment" => $comment->toArray()
			];
		});

		$router->map("PUT", "/projects/[i:pid]/comments/[i:id]/", function($pid, $id) use ($_pdo) {
			return Utility::errorJson("Path not yet defined.");
		}); // TODO:

		$router->map("DELETE", "/projects/[i:pid]/comments/[i:id]/", function($pid, $id) use ($_pdo) {
			return Utility::errorJson("Path not yet defined.");

//			$project = Project::find($_pdo, $pid);
//
//			if (is_null($project)) {
//				return Utility::errorJson("Invalid project data provided.");
//			}
//
//			$comment = Comment::findProject($_pdo, $project, $id);
//
//			if (is_null($comment)) {
//				return Utility::errorJson("Invalid comment data provided.");
//			}
//
//			$comment->remove(\PM\Config::getFileSystem());
//
//			return [
//				"err" => false,
//				"msg" => ""
//			];
		});






		$router->map("GET", "/users/", function() use ($_pdo) {
			$offset = Utility::cleanInt($_GET["offset"], 0, 0);

			$users = User::getAll($_pdo, $offset, SELECT_LIMIT + 1);

			$has_more = count($users) == SELECT_LIMIT + 1;

			$users = array_slice($users, 0, SELECT_LIMIT);

			return [
				"err" => false,
				"msg" => "",
				"pagination" => [
					"has_more" => $has_more,
					"count" => count($users),
					"offset" => $offset
				],
				"users" => array_map(function(User $user) {
					return $user->toArray();
				}, $users)
			];
		});

		$router->map("GET", "/users/[i:id]/", function($id) use ($_pdo) {
			$user = User::find($_pdo, $id);

			return [
				"err" => !$user,
				"msg" => !$user ? "No user found with given id" : "",
				"user" => $user->toArray()
			];
		});

		$router->map("POST", "/users/", function() use ($_pdo) {
			return Utility::errorJson("Path not defined.");
		}); // !TODO:

		$router->map("PUT", "/users/[i:id]/", function($id) use ($_pdo) {
			return Utility::errorJson("Path not defined.");
		}); // !TODO:

		$router->map("DELETE", "/users/[i:id]/", function($id) use ($_pdo) {
			return Utility::errorJson("Path not defined.");
		}); // !TODO:




		$router->map("GET", "/users/[i:id]/projects/[assigned|lead|created:type]?/", function($id, $type) use ($_pdo) {
			$offset = Utility::cleanInt($_GET["offset"], 0, 0);

			$user = User::find($_pdo, $id);

			if (!$user) {
				return Utility::errorJson("No user found with given id");
			}

			if ($type == "assigned") {
				$projects = Project::findAllUserAssigned($_pdo, $user, $offset, SELECT_LIMIT + 1);
			}
			else if ($type == "lead") {
				$projects = Project::findAllUserLead($_pdo, $user, $offset, SELECT_LIMIT + 1);
			}
			else if ($type == "created") {
				$projects = Project::findAllUserCreated($_pdo, $user, $offset, SELECT_LIMIT + 1);
			}
			else {
				$projects = Project::findAllUserRelated($_pdo, $user, $offset, SELECT_LIMIT + 1);
			}

			$has_more = count($projects) == SELECT_LIMIT + 1;

			$projects = array_slice($projects, 0, SELECT_LIMIT);

			return [
				"err" => false,
				"msg" => "",
				"pagination" => [
					"has_more" => $has_more,
					"count" => count($projects),
					"offset" => $offset
				],
				"projects" => array_map(function(Project $project) {
					return $project->toArray();
				}, $projects)
			];
		});
	}













	$match = $router->match();

	$pretty = Utility::isDevServer() ? JSON_PRETTY_PRINT : 0;

	if ($match && !is_callable($match["target"])) {
		throw new TypeError("Target is not callable.");
	}
	else if ($match && is_callable($match["target"])) {
		$page_title = $match["name"];

		$arr = @call_user_func_array($match["target"], $match["params"]);

		echo json_encode($arr, $pretty);


	}
	else {
		$page_title = "404";

		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

		echo json_encode([
			"err" => true,
			"msg" => "Invalid Request URI",
			"uri" => $_SERVER["REQUEST_URI"]
		], $pretty | JSON_UNESCAPED_SLASHES);
	}