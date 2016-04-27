<?php

	phpinfo();

	die;

	$_auth->authenticate("simle@uda1.com", "3rag0N10");

	var_dump($_auth->getErrorMessage());

	die;

	for ($i = 1; $i < 100; $i++) {
		$project = \PM\Project\Project::find($_pdo, $i);

		do {
			$user = \PM\User\User::find($_pdo, \PM\Utility::getRandomNumber(1, 100));
		} while ($user->getType() != \PM\User\User::TYPE_DEVELOPER);

		$project->setProjectLead($user);

//		$rand = \PM\Utility::getRandomNumber(1, 6);
//
//		for ($j = 0; $j < $rand; $j++) {
//			$user = \PM\User\User::find($_pdo, \PM\Utility::getRandomNumber(1, 100));
//
//			$project->assignUser($user);
//		}
	}