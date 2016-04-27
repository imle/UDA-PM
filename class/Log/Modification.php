<?php
	namespace PM\Log;

	use DateTime;
	use Exception;
	use PM\File\File;
	use PM\PDO\Base;
	use PM\Project\Comment;
	use PM\Project\Project;
	use PM\Traits\JSON;
	use PM\User\User;
	use PM\Utility;

	class Modification {
		use JSON;

		const TYPE_PROJECT_NAME = 0;
		const TYPE_PROJECT_NOTES = 1;
		const TYPE_PROJECT_TYPE = 2;
		const TYPE_PROJECT_STATUS = 3;
		const TYPE_PROJECT_CREATED = 4;
		const TYPE_PROJECT_DELETED = 5;

		const TYPE_PROJECT_LEAD_ADDED = 10;
		const TYPE_PROJECT_LEAD_CHANGED = 11;
		const TYPE_PROJECT_LEAD_REMOVED = 12;
		const TYPE_PROJECT_ASSIGNED = 13;
		const TYPE_PROJECT_UNASSIGNED = 14;


		const TYPE_PROJECT_FILE_ATTACHED = 20;
		const TYPE_PROJECT_FILE_REMOVED = 21;
		const TYPE_FILE_DELETED = 22;
		const TYPE_FILE_DOWNLOADED = 23;

		const TYPE_PROJECT_COMMENT_CREATED = 30;
		const TYPE_PROJECT_COMMENT_DELETED = 31;

		private static $VALID_TYPES = [ 0, 1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 20, 21, 22, 23, 30, 31 ];

		private $id, $project_id, $modifier_id, $type, $date, $data;

		/* @var Base $_pdo */
		private $_pdo;

		/* @var Project $_project */
		private $_project;

		/* @var User $_modifier */
		private $_modifier;

		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;
		}

		/* Create */

		private function create(Project $project, User $modifier, int $type, array $data) {
			$this->validateType($type);

			$this->_project = $project;
			$this->_modifier = $modifier;

			$query = "INSERT INTO modification_log (project_id, modifier_id, type, `date`, `data`)
					  VALUES (:p, :m, :t, :d, :j)";

			$this->_pdo->perform($query, [
				"p" => $this->project_id = $project->getId(),
				"m" => $this->modifier_id = $modifier->getId(),
				"t" => $this->type = $type,
				"d" => $this->date = Utility::getDateTimeForMySQLDateTime(),
				"j" => $this->data = json_encode($data)
			]);

			$this->id = $this->_pdo->lastInsertId();
		}

		public static function createBasic(Base $_pdo, Project $project, User $modifier, int $type) {
			$m = new Modification($_pdo);
			$m->create($project, $modifier, $type, []);
			return $m;
		}

		public static function createUser(Base $_pdo, Project $project, User $modifier, int $type, User $user) {
			$m = new Modification($_pdo);
			$m->create($project, $modifier, $type, [
				"user" => $user->getId()
			]);
			return $m;
		}

		public static function createUserMulti(Base $_pdo, Project $project, User $modifier, int $type, array $users) {
			$m = new Modification($_pdo);
			$m->create($project, $modifier, $type, [
				"users" => array_map(function(User $user) {
					return $user->getId();
				}, $users)
			]);
			return $m;
		}

		public static function createFile(Base $_pdo, Project $project, User $modifier, int $type, File $file) {
			$m = new Modification($_pdo);
			$m->create($project, $modifier, $type, [
				"file" => $file->getId()
			]);
			return $m;
		}

		public static function createComment(Base $_pdo, Project $project, User $modifier, int $type, Comment $comment) {
			$m = new Modification($_pdo);
			$m->create($project, $modifier, $type, [
				"comment" => $comment->getId()
			]);
			return $m;
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
			if (!$this->_project)
				$this->_project = Project::find($this->_pdo, $this->project_id);

			return $this->_project;
		}

		public function getModifierId() : int {
			return $this->modifier_id;
		}

		public function getModifier() : User {
			if (!$this->_modifier)
				$this->_modifier = User::find($this->_pdo, $this->modifier_id);

			return $this->_modifier;
		}

		public function getType() : int {
			return $this->type;
		}

		public function getDate() : DateTime {
			return Utility::getDateTimeFromMySQLDateTime($this->date);
		}

		public function getData() : array {
			return json_decode($this->data);
		}

		/* Validation */

		/**
		 * @param int $type
		 * @throws Exception
		 */
		private function validateType(int $type) {
			if (!in_array($type, static::$VALID_TYPES)) {
				throw new Exception("Invalid type");
			}
		}
	}