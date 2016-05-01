<?php
	namespace PM\Project;

	use DateTime;
	use League\Flysystem\FilesystemInterface;
	use PM\File\File;
	use PM\PDO\Base;
	use PM\Traits\JSON;
	use PM\User\User;
	use PM\Utility;

	class Comment {
		use JSON;

		protected $id, $project_id, $creator_id, $date_created, $text;

		/* @var Base $_pdo */
		private $_pdo;

		/* @var User $_creator */
		private $_creator;

		/* @var Project $_project */
		private $_project;

		/* Constructor */

		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;
		}

		/* Create */

		/**
		 * @param Base $_pdo
		 * @param Project $_project
		 * @param User $_creator
		 * @param string $text
		 * @return self
		 */
		public static function create(Base $_pdo, Project $_project, User $_creator, string $text) {
			$temp = new self($_pdo);

			$temp->_creator = $_creator;
			$temp->_project = $_project;

			$query = "INSERT INTO `comment` (project_id, creator_id, date_created, `text`)
					  VALUES (:p, :c, :d, :t)";

			$_pdo->perform($query, [
				"p" => $temp->project_id = $_project->getId(),
				"c" => $temp->creator_id = $_creator->getId(),
				"d" => $temp->date_created = Utility::getDateTimeForMySQLDateTime(),
				"t" => $temp->text = $text
			]);

			$temp->id = $_pdo->lastInsertId();

			return $temp;
		}

		/* Setters */

		/* Getters */

		public function getId() : int {
			return $this->id;
		}

		public function getProjectId() : int {
			return $this->project_id;
		}

		public function getProject() : Project {
			if (is_null($this->_project))
				$this->_project = Project::find($this->_pdo, $this->project_id);

			return $this->_project;
		}

		public function getCreatorId() : int {
			return $this->creator_id;
		}

		public function getCreator() : User {
			if (is_null($this->_creator))
				$this->_creator = User::find($this->_pdo, $this->creator_id);

			return $this->_creator;
		}

		public function getDateCreated() : DateTime {
			return $this->date_created;
		}

		public function getText() : string {
			return $this->text;
		}

		/* Static Find Functions */

		public static function find(Base $_pdo, int $id) {
			$query = "SELECT * FROM `comment` WHERE id = :i";

			$row = $_pdo->fetchOne($query, [
				":i" => $id
			]);

			return self::getInstance($_pdo, $row);
		}

		/**
		 * @param Base $_pdo
		 * @param Project $_project
		 * @param int $id
		 * @return self
		 */
		public static function findProject(Base $_pdo, Project $_project, int $id) {
			$query = "SELECT * FROM `comment` WHERE id = :i AND project_id = :pid";

			$row = $_pdo->fetchOne($query, [
				"pid" => $_project->getId(),
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
			$query = "SELECT * FROM `comment` WHERE project_id = :p ORDER BY date_created DESC";

			if ($limit != -1)
				$query .= " LIMIT :o, :l";

			return $_pdo->fetchAll($query, [
				"o" => $offset,
				"l" => $limit,
				"p" => $_project->getId()
			], function($row) use ($_pdo, $_project) {
				$temp = self::getInstance($_pdo, $row);
				$temp->_project = $_project;
				return $temp;
			});
		}

		/**
		 * @param Base $_pdo
		 * @param User $_creator
		 * @param int $offset
		 * @param int $limit
		 * @return self[]
		 */
		public static function findForUser(Base $_pdo, User $_creator,
		                                   int $offset = 0, int $limit = 30) : array {
			$query = "SELECT * FROM `comment` WHERE creator_id = :c ORDER BY date_created DESC";

			if ($limit != -1)
				$query .= " LIMIT :o, :l";

			return $_pdo->fetchAll($query, [
				"o" => $offset,
				"l" => $limit,
				"c" => $_creator->getId()
			], function($row) use ($_pdo, $_creator) {
				$temp = self::getInstance($_pdo, $row);
				$temp->_creator = $_creator;
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
				if (!Utility::stringStartsWith($key, "rank_"))
					$this->{$key} = $item;
		}
	}