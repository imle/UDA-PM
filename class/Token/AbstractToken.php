<?php
	namespace PM\Token;

	use DateInterval;
	use DateTime;
	use PM\PDO\Base;
	use PM\Utility;

	class AbstractToken {
		private $id, $value, $expire_time;

		/* @var Base $_pdo */
		private $_pdo;

		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;
		}

		/* Create */

		public function create(DateInterval $di) {
			$this->_pdo->perform("INSERT INTO token (`value`, expire_time) VALUES (:v, :e)", [
				"v" => $this->value = Utility::getRandomString(32),
				"e" => $this->expire_time = Utility::getExpiryAsMySQLDateTime($di)
			]);

			$this->id = $this->_pdo->lastInsertId();
		}

		/* Setters */

		/* Getters */

		public function getId() : int {
			return $this->id;
		}

		public function getValue() : string {
			return $this->value;
		}

		public function getExpireTime() : DateTime {
			return Utility::getDateTimeFromMySQLDateTime($this->expire_time);
		}

		public function isExpired() : bool {
			return $this->getExpireTime() < new DateTime();
		}

		/* Static Find Functions */

		public static function find(Base $_pdo, int $id) {
			$row = $_pdo->fetchOne("SELECT * FROM token WHERE id = :id", [
				"id" => $id
			]);

			return self::getInstance($_pdo, $row);
		}

		public static function findByValue(Base $_pdo, string $value) {
			$row = $_pdo->fetchOne("SELECT * FROM token WHERE `value` = :v", [
				"v" => $value
			]);

			return self::getInstance($_pdo, $row);
		}

		/* Parse */

		private static function getInstance(Base $_pdo, array $data) {
			if (is_null($data) || !count($data))
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