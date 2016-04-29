<?php
	namespace PM\File;

	use DateTime;
	use PM\PDO\Base;
	use PM\Project\Project;
	use PM\Traits\JSON;
	use PM\User\User;
	use PM\Utility;

	class Attachment {
		use JSON;

		private $id, $file_id, $user_id, $project_id, $name, $extension, $mime_type, $size, $md5,
			$original_name, $date_added;

		/* @var Base $_pdo */
		private $_pdo;

		/* @var File $_file */
		private $_file;

		/* @var User $_user */
		private $_user;

		/* @var Project $_project */
		private $_project;

		/* Constructor */

		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;
		}

		/* Create */

		public static function create(Base $_pdo, File $_file, User $_user, Project $_project,
		                              string $name = NULL) : self {
			$query = "INSERT INTO attachment (file_id, user_id, project_id, `name`, date_added)
					  VALUES (:f, :u, :p, :n, :d)";

			$temp = new self($_pdo);
			$temp->_file = $_file;
			$temp->extension = $_file->getExtension();
			$temp->md5 = $_file->getMd5();
			$temp->mime_type = $_file->getMimeType();
			$temp->original_name = $_file->getOriginalName();
			$temp->size = $_file->getSize();
			$temp->_user = $_user;
			$temp->_project = $_project;
			$temp->_pdo->perform($query, [
				"f" => $temp->file_id = $_file->getId(),
				"u" => $temp->user_id = $_user->getId(),
				"p" => $temp->project_id = $_project->getId(),
				"n" => $temp->name = $name,
				"d" => $temp->date_added = Utility::getDateTimeForMySQLDateTime()
			]);

			$temp->id = $temp->_pdo->lastInsertId();

			return $temp;
		}

		/* Getters */

		public function getId() : int {
			return $this->id;
		}

		public function getFileId() : int {
			return $this->file_id;
		}

		public function getFile() : File {
			if (!$this->_file)
				$this->_file = File::find($this->_pdo, $this->file_id);
				
			return $this->_file;
		}

		public function getUserId() : int {
			return $this->user_id;
		}

		public function getUser() : User {
			if (!$this->_user)
				$this->_user = User::find($this->_pdo, $this->user_id);
				
			return $this->_user;
		}

		public function getProjectId() : int {
			return $this->project_id;
		}

		public function getProject() : Project {
			if (!$this->_project)
				$this->_project = Project::find($this->_pdo, $this->project_id);
				
			return $this->_project;
		}

		public function getName() : string {
			return $this->name;
		}

		public function getExtension() : string {
			return $this->extension;
		}

		public function getMimeType() : string {
			return $this->mime_type;
		}

		public function getSize() : int {
			return $this->size;
		}

		public function getMd5() : string {
			return $this->md5;
		}

		public function getOriginalName() : string {
			return $this->original_name;
		}

		public function getDateAdded() : DateTime{
			return Utility::getDateTimeFromMySQLDateTime($this->date_added);
		}

		/* Create */

		/**
		 * @param Base $_pdo
		 * @param int $id
		 * @return self
		 */
		public static function find(Base $_pdo, int $id) {
			$query = "SELECT * FROM attachment WHERE id = :id";

			$row = $_pdo->fetchOne($query, [
				"id" => $id
			]);

			return self::getInstance($_pdo, $row);
		}

		/**
		 * @param Base $_pdo
		 * @param Project $_project
		 * @param int $offset
		 * @param int $limit
		 * @return self[]
		 */
		public static function findAllForProject(Base $_pdo, Project $_project,
		                                         int $offset = 0, int $limit = 30) : array {
			$query = "SELECT a.id, a.user_id, a.file_id, a.project_id, COALESCE(a.name, f.original_name) AS `name`,
					  f.extension, f.size, f.md5, f.mime_type, a.date_added, f.original_name
					  FROM attachment a LEFT JOIN file f ON a.file_id = f.id
					  WHERE a.project_id = :p GROUP BY a.id";

			if ($limit != -1)
				$query .= " LIMIT :o, :l";

			return $_pdo->fetchAll($query, [
				"o" => $offset,
				"l" => $limit,
				"p" => $_project->getId()
			], function($row) use($_pdo, $_project) {
				$temp = self::getInstance($_pdo, $row);
				$temp->_project = $_project;
				return $temp;
			});
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