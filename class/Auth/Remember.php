<?php
	namespace PM\Auth;

	use DateInterval;
	use DateTime;
	use PM\PDO\Base;
	use PM\Traits\JSON;
	use PM\User\User;
	use PM\Utility;

	class Remember {
		use JSON;

		const EXPIRE_TIME = "30 days";

		private static $COOKIE_REMEMBER = "r";
		private static $COOKIE_SEPARATOR = ";";

		private $id, $user_id, $token, $date_expires, $user_agent;

		/* @var Base $_pdo */
		private $_pdo;

		/* @var User $_user */
		private $_user;

		public function __construct(Base $_pdo, array $data = array()) {
			$this->_pdo = $_pdo;

			if (!empty($data))
				$this->parse($data);
		}

		/* Create */

		public function create(User $_user) {
			$this->_user = $_user;
			$di = DateInterval::createFromDateString(self::EXPIRE_TIME);

			$query = "INSERT INTO remember (user_id, token, date_expires, user_agent) VALUES (:u, :t, :d, :a)";

			$this->_pdo->perform($query, [
				"u" => $this->user_id = $_user->getId(),
				"t" => $this->token = Utility::getRandomString(30),
				"d" => $this->date_expires = Utility::getExpiryAsMySQLDateTime($di),
				"a" => $this->user_agent = $_SERVER["HTTP_USER_AGENT"]
			]);

			$cookie_val = $_user->getEmail() . self::$COOKIE_SEPARATOR . $this->token;
			setcookie(self::$COOKIE_REMEMBER, $cookie_val, time() + Utility::SECONDS_IN_DAY * 30, "/");

			$this->id = $this->_pdo->lastInsertId();
		}

		/* Setters */

		public function update() {
			$di = DateInterval::createFromDateString(self::EXPIRE_TIME);

			$this->_pdo->perform("UPDATE remember SET token = :t, date_expires = :d WHERE id = :id", [
				"t" => $this->token = Utility::getRandomString(30),
				"d" => $this->date_expires = Utility::getExpiryAsMySQLDateTime($di),
				"id" => $this->id
			]);

			$cookie_val = $this->getUser()->getEmail() . self::$COOKIE_SEPARATOR . $this->token;
			setcookie(self::$COOKIE_REMEMBER, $cookie_val, time() + Utility::SECONDS_IN_DAY * 30, "/");
		}

		/* Getters */

		public function getId() : int {
			return $this->id;
		}

		public function getUserId() : int {
			return $this->user_id;
		}

		public function getUser() : User {
			if (!$this->_user)
				$this->_user = User::find($this->_pdo, $this->user_id);

			return $this->_user;
		}

		public function getToken() : string {
			return $this->token;
		}

		public function getDateExpires() : DateTime {
			return Utility::getDateTimeFromMySQLDateTime($this->date_expires);
		}

		public function isExpired() : bool {
			return $this->getDateExpires() <= new DateTime();
		}

		public function getUserAgent() : string {
			return $this->user_agent;
		}

		/* Static Find Functions */

		/**
		 * @param Base $_pdo
		 * @param int $id
		 * @return Remember
		 */
		public static function find(Base $_pdo, int $id) {
			$row = $_pdo->fetchOne("SELECT * FROM remember WHERE id = :id", [
				"id" => $id
			]);

			return $row ? new self($_pdo, $row) : null;
		}

		/**
		 * @param Base $_pdo
		 * @param string $token
		 * @return Remember
		 */
		public static function findByToken(Base $_pdo, string $token) {
			$row = $_pdo->fetchOne("SELECT * FROM remember WHERE token = :t", [
				"t" => $token
			]);

			return $row ? new self($_pdo, $row) : null;
		}

		/* Crossover */

		/**
		 * @param Base $_pdo
		 * @return Remember
		 */
		public static function read(Base $_pdo) {
			if (!isset($_COOKIE[self::$COOKIE_REMEMBER]))
				return null;

			list($email, $token) = explode(self::$COOKIE_SEPARATOR, urldecode($_COOKIE[self::$COOKIE_REMEMBER]), 2);

			$rem = self::findByToken($_pdo, $token);

			if (is_null($rem) || $rem->getUser()->getEmail() != $email)
				return null;

			return $rem;
		}

		public function remove() {
			setcookie(self::$COOKIE_REMEMBER, null, -1);

			$this->_pdo->perform("DELETE FROM remember WHERE id = :id", [
				"id" => $this->id
			]);
		}

		/* Parse */

		private function parse(array $data) {
			foreach ($data as $key => $item)
				$this->{$key} = $item;
		}
	}