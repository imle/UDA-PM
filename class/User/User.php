<?php
	namespace PM\User;

	use PM\File\File;
	use PM\PDO\Base;
	use PM\Token\AccountVerifyToken;
	use PM\Token\PasswordResetToken;
	use PM\Traits\JSON;

	class User {
		use JSON {
			toArray as toArrayTrait;
		}

		const TYPE_SUPERVISOR = 0;
		const TYPE_DEVELOPER = 1;
		const TYPE_DESIGNER = 2;
		const TYPE_VIEWER = 3;

		public static $TYPE_STRINGS = [
			"Supervisor",
			"Developer",
			"Designer",
			"Viewer"
		];

		private $id, $active, $name_first, $name_last, $email, $type, $password_hash, $token_account_verify,
			$token_password_reset, $image_file_id;

		/* @var Base $_pdo */
		private $_pdo;

		/* @var File $_image_file */
		private $_image_file;

		/* @var PasswordResetToken $_token_password_reset */
		private $_token_password_reset;

		/* @var AccountVerifyToken $_token_account_verify */
		private $_token_account_verify;

		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;
		}

		/* Create */

		public function create($email) {
			$this->_pdo->perform("INSERT INTO `user` (email) VALUES (:u)", [
				"u" => $email
			]);

			$this->id = $this->_pdo->lastInsertId();
		}

		/* Setters */

		/* Getters */

		public function getId() : int {
			return $this->id;
		}

		public function isActive() : bool {
			return $this->active;
		}

		public function getEmail() : string {
			return $this->email;
		}

		public function getType() : int {
			return $this->type;
		}

		public function getPasswordHash() : string {
			return $this->password_hash;
		}

		public function getNameFirst() : string {
			return $this->name_first;
		}

		public function getNameLast() : string {
			return $this->name_last;
		}

		public function getNameFull() : string {
			return $this->name_first . " " . $this->name_last;
		}

		/**
		 * @return AccountVerifyToken
		 */
		public function getTokenAccountVerify() {
			if (is_null($this->_token_account_verify))
				$this->_token_account_verify = AccountVerifyToken::findByValue($this->_pdo, $this->token_account_verify ?? "");

			return $this->_token_account_verify;
		}

		/**
		 * @return PasswordResetToken
		 */
		public function getTokenPasswordReset() {
			if (is_null($this->_token_password_reset))
				$this->_token_password_reset = PasswordResetToken::findByValue($this->_pdo, $this->token_password_reset ?? "");

			return $this->_token_password_reset;
		}

		/**
		 * @return File
		 */
		public function getProfileImage() {
			if (is_null($this->_image_file))
				$this->_image_file = File::find($this->_pdo, $this->image_file_id ?? 0);

			return $this->_image_file;
		}

		/* Static Find Functions */

		/**
		 * @param Base $_pdo
		 * @param int $id
		 * @return self
		 */
		public static function find(Base $_pdo, int $id) {
			$row = $_pdo->fetchOne("SELECT * FROM `user` WHERE id = :id", [
				"id" => $id
			]);

			return self::getInstance($_pdo, $row);
		}

		/**
		 * @param Base $_pdo
		 * @param string $email
		 * @return self
		 */
		public static function findByEmail(Base $_pdo, string $email) {
			$row = $_pdo->fetchOne("SELECT * FROM `user` WHERE email = :e", [
				"e" => $email
			]);

			return self::getInstance($_pdo, $row);
		}

		public static function getAll(Base $_pdo, int $offset = 0, int $limit = 30) {
			$query = "SELECT * FROM `user`";

			if ($limit != -1)
				$query .= " LIMIT :o, :l";

			return $_pdo->fetchAll($query, [
				"o" => $offset,
				"l" => $limit
			], function($row) use ($_pdo) {
			    return User::getInstance($_pdo, $row);
			});
		}

		public function toArray() : array {
			$arr = $this->toArrayTrait();

			unset($arr["password_hash"]);
			unset($arr["token_account_verify"]);
			unset($arr["token_password_reset"]);

			return $arr;
		}

		/* Parse */

		public static function getInstance(Base $_pdo, $data) {
			if (is_null($data) || !is_array($data) || !count($data))
				return null;

			$i = new self($_pdo);
			$i->parse($data);
			return $i;
		}

		private function parse(array $data) {
			foreach ($data as $key => $item)
				$this->{$key} = $item;
		}
	}