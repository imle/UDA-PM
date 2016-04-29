<?php
	namespace PM\File;

	use DateTime;
	use Exception;
	use League\Flysystem\AdapterInterface;
	use League\Flysystem\FilesystemInterface;
	use PM\PDO\Base;
	use PM\Traits\JSON;
	use PM\User\User;
	use PM\Utility;

	class File {
		use JSON;

		private $id, $user_id, $original_name, $extension, $size, $md5, $mime_type, $date_added;

		/* @var Base $_pdo */
		private $_pdo;

		/* @var User $_user */
		private $_user;

		/* Constructor */

		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;
		}

		/* Create */

		public static function create(Base $_pdo, FilesystemInterface $_fs, User $_user,
		                              string $name, string $temp_location) : self {
			$extension = pathinfo($name, PATHINFO_EXTENSION);
			$name = pathinfo($name, PATHINFO_FILENAME);

			if (!strlen($extension))
				throw new Exception("Invalid file extension.");

			$mime_type = mime_content_type($temp_location);

			if (!$mime_type || !strlen($mime_type))
				throw new Exception("Invalid file extension.");

			$query = "INSERT INTO `file` (user_id, original_name, extension, size, md5, mime_type, date_added)
					  VALUES (:u, :n, :e, :s, :m, :t, :d)";

			$temp = new self($_pdo);
			$temp->_user = $_user;
			$temp->_pdo->perform($query, [
				"u" => $temp->user_id = $_user->getId(),
				"n" => $temp->original_name = $name,
				"e" => $temp->extension = $extension,
				"s" => $temp->size = filesize($temp_location),
				"m" => $temp->md5 = md5_file($temp_location),
				"t" => $temp->mime_type = $mime_type,
				"d" => $temp->date_added = Utility::getDateTimeForMySQLDateTime()
			]);

			$temp->id = $temp->_pdo->lastInsertId();

			$stream = fopen($temp_location, "r+");

			$temp->write($_fs, $stream);

			fclose($stream);

			return $temp;
		}

		/* Getters */

		public function getId() : int {
			return $this->id;
		}

		public function isImage() : bool {
			return Utility::stringStartsWith($this->mime_type, "image/");
		}

		public function getUserId() : int {
			return $this->user_id;
		}

		public function getUser() {
			if (!$this->_user)
				$this->_user = User::find($this->_pdo, $this->user_id);

			return $this->_user;
		}

		public function getOriginalName() : string {
			return $this->original_name;
		}

		public function getExtension() : string {
			return $this->extension;
		}

		public function getSize() : int {
			return $this->size;
		}

		public function getMd5() : string {
			return $this->md5;
		}

		public function getMimeType() : string {
			return $this->mime_type;
		}

		public function getDateAdded() : DateTime {
			return Utility::getDateTimeFromMySQLDateTime($this->date_added);
		}

		/* Path Stuff */

		private function getPath() : string {
			return DIRECTORY_SEPARATOR . $this->getDirMain() . DIRECTORY_SEPARATOR . $this->getDirSub()
			. DIRECTORY_SEPARATOR . $this->id . "." . $this->extension;
		}

		private function getDirMain() : string {
			return substr($this->md5, 0, 1);
		}

		private function getDirSub() : string {
			return substr($this->md5, 0, 2);
		}

		/* Read Write */

		/**
		 * @param FilesystemInterface $_fs
		 * @return resource $stream
		 */
		public function read(FilesystemInterface $_fs) {
			return $_fs->readStream($this->getPath());
		}

		/**
		 * @param FilesystemInterface $_fs
		 * @param resource $stream
		 */
		public function write(FilesystemInterface $_fs, $stream) {
			$_fs->writeStream($this->getPath(), $stream, [
				"visibility" => AdapterInterface::VISIBILITY_PUBLIC
			]);
		}

		/**
		 * Method removes file only if there are no remaining attachment references.
		 *
		 * @param FilesystemInterface $_fs
		 * @return bool
		 */
		public function remove(FilesystemInterface $_fs) {
			try {
				$this->_pdo->perform("DELETE FROM `file` WHERE id = :i", [
					"i" => $this->id
				]);

				$_fs->delete($this->getPath());
			} catch (Exception $ex) {
				return false;
			}

			return true;
		}

		/* Static Find Functions */

		/**
		 * @param Base $_pdo
		 * @param int $id
		 * @return self
		 */
		public static function find(Base $_pdo, int $id) {
			$query = "SELECT * FROM `file` WHERE id = :id";

			$row = $_pdo->fetchOne($query, [
				"id" => $id
			]);

			return self::getInstance($_pdo, $row);
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
				if ($key != "fulltext_rank")
					$this->{$key} = $item;
		}
	}