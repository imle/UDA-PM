<?php
	require_once __DIR__ . "/../../../class/autoload.php";

	$_pdo = new \PM\PDO\MySQL();
	$_auth = new \PM\Auth\SessionAuth($_pdo);
	$router = new AltoRouter([], "/service/email");


	$router->map("GET", "/[i:uid]/[*:template]/[*:view_key]/", function($uid, $template, $view_key) use ($_pdo) {
		$_user = \PM\User\User::find($_pdo, $uid);

		$mailer = new \PM\Contact\Mailer($_pdo);
		echo $mailer->getEmailHtml($template, $_user, $view_key);
	});

	$router->map("GET", "/plain/[i:uid]/[*:template]/[*:view_key]/", function($uid, $template, $view_key) use ($_pdo) {
		header("Content-Type: text/plain");

		$_user = \PM\User\User::find($_pdo, $uid);

		$mailer = new \PM\Contact\Mailer($_pdo);
		echo $mailer->getEmailHtml($template, $_user, $view_key, true);
	});


	if (\PM\Utility::isDevServer()) {
		$router->map("GET", "/", function() use ($_pdo) {
			$logs = $_pdo->fetchAll("SELECT * FROM email_log");

			foreach ($logs as $log) {
				echo "<a href=\"/service/email/". $log["user_id"] . "/"  . $log["template"] . "/"
					. $log["view_key"] . "/\">" . $log["user_id"] . " " . $log["template"] . "</a><br>";
			}
		});
	}












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

		echo json_encode([
			"err" => true,
			"msg" => "Invalid Request URI"
		], JSON_PRETTY_PRINT);
	}