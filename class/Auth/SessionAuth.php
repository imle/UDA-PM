<?php
	namespace PM\Auth;

	use PM\PDO\Base;
	use PM\User\User;
	use PM\Utility;

	class SessionAuth extends AbstractAuth {
		private static $REQUEST_URI = "REQUEST_URI";
		private static $VALID_LOGIN = "VALID_LOGIN";
		private static $LAST_ACTION = "LAST_ACTION";
		private static $SESSION_EXP = "SESSION_EXP";

		private static $COOKIE_SESSION = "s";

		private static $ERROR_EMAIL_INVALID = 1;
		private static $ERROR_USER_DNE = 2;
		private static $ERROR_USER_NO_LONGER_ACTIVE = 3;
		private static $ERROR_USER_NOT_VERIFIED = 4;
		private static $ERROR_INVALID_CREDENTIALS = 5;

		public function __construct(Base $_pdo) {
			$this->startSession();

			parent::__construct($_pdo);
		}

		public function authenticate(string $email, string $password) {
			if (!$this->isLoggedIn()) {
				if (!is_null($this->_remember)) {
					if ($this->_remember->isExpired()) {
						$this->_remember->remove();
					}
					else {
						$this->_remember->update();
						$this->login($this->_remember->getUser());

						return;
					}
				}

				if ($email == "" || $password == "") {
					$this->setError(self::$ERROR_INVALID_CREDENTIALS);

					return;
				}

				if (!Utility::stringContains($email, ["@", "."])) {
					$this->setError(self::$ERROR_EMAIL_INVALID);

					return;
				}

				$_user = User::findByEmail($this->_pdo, $email);

				if (is_null($_user)) {
					$this->setError(self::$ERROR_USER_DNE);
				}
				else if (!$_user->isActive()) {
					$this->setError(self::$ERROR_USER_NO_LONGER_ACTIVE);
				}
				else if (!Utility::verifyPassword($password, $_user->getPasswordHash())) {
					$this->setError(self::$ERROR_INVALID_CREDENTIALS);
				}
				else {
					$this->login($_user);
				}
			}
		}

		public function validate($is_login_page = false) {
			if ($this->isLoggedIn()) {
				if ($this->isTimedOut()) {
					$this->_remember = Remember::read($this->_pdo);

					if (is_null($this->_remember)) {
						$this->logout();
					}
				}
				if ($is_login_page) {
					Utility::displayPage("/");
				}
			}
			else {
				if (!$is_login_page) {
					$_SESSION[self::$REQUEST_URI] = $_SERVER[self::$REQUEST_URI];
				}

				$this->_remember = Remember::read($this->_pdo);

				if (!is_null($this->_remember)) {
					if (!$this->_remember->isExpired()) {
						$this->_remember->update();
						$this->login($this->_remember->getUser());
						Utility::displayPage($_SESSION[self::$REQUEST_URI] ?: "/");
					}
					else {
						$this->_remember->remove();
					}
				}
				else if (!$is_login_page) {
					Utility::displayPage("/login/");
				}
			}
		}

		/**
		 * @return User
		 */
		public function getUser() {
			if ($this->isLoggedIn() && !$this->_user)
				$this->_user = User::findByEmail($this->_pdo, $_SESSION[self::$VALID_LOGIN]);

			return $this->_user;
		}

		public function logout() {
			setcookie(self::$COOKIE_SESSION, "", -1);

			$is_timeout = $this->isTimedOut();

			$this->_remember = Remember::read($this->_pdo);

			if ($this->_remember)
				$this->_remember->remove();

			unset($this->_remember);
			unset($this->_user);

			session_destroy();
			session_start();

			if ($is_timeout) {
				$_SESSION[self::$SESSION_EXP] = true;
			}

			Utility::displayPage("/login/");
		}

		private function login(User $_user) {
			$this->_user = $_user;

			$_SESSION[self::$VALID_LOGIN] = $_user->getEmail();
			$_SESSION[self::$LAST_ACTION] = time();
		}

		public function remember() {
			$remember = new Remember($this->_pdo);
			$remember->create($this->_user);
		}

		public function isLoggedIn() : bool {
			return isset($_SESSION[self::$VALID_LOGIN]);
		}

		public function getRequestURI() : string {
			return $_SESSION[self::$REQUEST_URI] ?? "/";
		}

		private function isTimedOut() : bool {
			if (time() > $_SESSION[self::$LAST_ACTION] + Utility::SECONDS_IN_HOUR)
				return true;

			$_SESSION[self::$LAST_ACTION] = time();
			return false;
		}

		private function startSession() {
			session_name(self::$COOKIE_SESSION);
			session_start();
		}

		public function wasTimeout() : bool {
			if (isset($_SESSION[self::$SESSION_EXP])) {
				unset($_SESSION[self::$SESSION_EXP]);
				return true;
			}

			return false;
		}

		private function setError(int $error_id) {
			$this->error_id = $error_id;
		}

		public function hasError() : bool {
			return !!$this->error_id;
		}

		public function getErrorMessage() : string {
			switch ($this->error_id) {
				case self::$ERROR_EMAIL_INVALID:
					return "Invalid email address.";
				case self::$ERROR_USER_DNE:
					return "Email address not registered.";
				case self::$ERROR_USER_NO_LONGER_ACTIVE:
					return "This user is no longer active.";
				case self::$ERROR_USER_NOT_VERIFIED:
					return "Account not verified.";
				case self::$ERROR_INVALID_CREDENTIALS:
					return "Invalid credentials.";
				default:
					return "";
			}
		}
	}