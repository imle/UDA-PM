<!doctype html>
<html lang="en">
	<head>
		<title> <?= $page_title ?> | PM</title>

		<!-- Meta Tags -->
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="description" content="A unified way to manage all things greek." />
		<meta name="keywords" content="fraternity, sorority, greek, manage, management" />
		<meta name="viewport" content="width=device-width">

		<!-- Fonts -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:700,400,300" type="text/css">

		<!-- Base Style Sheets -->
		<link rel="stylesheet" href="/assets/css/libraries/bootstrap/bootstrap.min.css" type="text/css">
		<link rel="stylesheet" href="/assets/css/libraries/fontawesome/font-awesome.min.css" type="text/css">
		<link rel="stylesheet" href="/assets/css/libraries/selectize/selectize.css" type="text/css">
		<link rel="stylesheet" href="/assets/css/main.css" type="text/css">

		<!-- Base Javascript -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script src="/assets/js/libraries/underscore/underscore.min.js"></script>
		<script src="/assets/js/libraries/bootstrap/bootstrap.min.js"></script>
		<script src="/assets/js/libraries/dropzone/dropzone.js"></script>
		<script src="/assets/js/libraries/selectize/selectize.js"></script>
		<script src="/assets/js/libraries/moment/moment.min.js"></script>
		<script src="/assets/js/main.js"></script>
		<?php /** @var \PM\User\User[] $users */ ?>
		<?php if (is_array($users)) { ?>
			<script type="text/javascript">
				PM.data.users = <?= json_encode(array_map(function(\PM\User\User $user) {
					return $user->toArray();
				}, $users)) ?>;

				PM.data.users = PM.data.users.map(function(user) {
					return new PM.User(user);
				});
			</script>
		<?php } ?>
	</head>
	<body>
		<div id="container">
			<header class="navbar navbar-inverse navbar-fixed-top">
				<div class="container">
<!--					<img id="logo" src="/assets/images/logo.png" alt="logo">-->
				</div>
			</header>
			<?php include_once(__DIR__ . "/lsb.php"); ?>
			<div id="content">