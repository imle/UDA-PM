<?php
	namespace PM\Auth;

	use PM\PDO\Base;
	use PM\User\User;
	use PM\Utility;

	abstract class AbstractAuth {
		protected $error_id;

		/* @var Base $_pdo */
		protected $_pdo;

		/* @var User $_user */
		protected $_user;

		/* @var Remember $_remember */
		protected $_remember;

		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;

			$this->setVars();
		}

		abstract public function authenticate(string $email, string $password);

		abstract public function validate();

		/**
		 * @return User
		 */
		abstract public function getUser();

		abstract public function logout();

		abstract public function remember();

		abstract public function isLoggedIn() : bool;

		abstract public function wasTimeout() : bool;

		abstract public function hasError() : bool;

		abstract public function getErrorMessage() : string;

		private function setVars() {
			date_default_timezone_set("America/Chicago");
			error_reporting(Utility::isDevServer() ? (E_ALL & ~E_NOTICE) : 0);
			ini_set("display_errors", Utility::isDevServer());
		}
	}