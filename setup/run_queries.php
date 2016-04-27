<?php
	require_once __DIR__ . "/../class/autoload.php";

	$_pdo = new \FMA\PM\MySQL();

	$in_string = file_get_contents(__DIR__ . "/../config/query_log.sql");

	preg_match_all('/-- START\s-- DATE:\s([^\s]*)\s([\S\s]*?)\s-- END/i', $in_string, $m, PREG_SET_ORDER);

	$matches = [];

	foreach ($m as $key => $match) {
		if (isset($matches[$match[1]])) {
			$matches[$match[1]] = $match[2] . "\n\n" . $matches[$match[1]];
		}
		else {
			$matches[$match[1]] = $match[2];
		}
	}

	unset($m, $match, $key);

	$matches = array_reverse($matches);

	foreach ($matches as $key => $value) {
		$matches[$key] = preg_split('/;(\s*)/', $value);
	}

	$last_update = \FMA\Utility::getDateTimeFromMySQLDate(\FMA\Config::getLastUpdate());

	foreach ($matches as $date => $queries) {
		$date = \FMA\Utility::getDateTimeFromMySQLDate($date);

		if ($date <= $last_update)
			continue;

		foreach ($queries as $query) {
			$_pdo->perform($query);
		}
	}

	end($matches);
	\FMA\Config::setLastUpdate(key($matches));