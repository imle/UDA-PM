<?php
	namespace PM\Project;

	use Exception;
	use League\Flysystem\FilesystemInterface;
	use PM\File\File;
	use PM\PDO\Base;
	use PM\Traits\JSON;
	use PM\User\User;
	use PM\Utility;

	class Project {
		use JSON;

		const TYPE_BUG = 0;
		const TYPE_FEATURE = 1;

		const RELATION_ANY = 0;
		const RELATION_ASSIGNED = 1;
		const RELATION_LEAD = 2;
		const RELATION_CREATED = 3;

		const STATUS_IN_PROGRESS = 0;
		const STATUS_READY_FOR_INSPECTION = 1;
		const STATUS_ON_HOLD = 2;
		const STATUS_IN_DISPUTE = 3;
		const STATUS_FINISHED = 4;

		public static $STATUS_STRINGS = [
			"In Progress",
			"Ready For Inspection",
			"On Hold",
			"In Dispute",
			"Finished"
		];

		public static $TYPE_STRINGS = [
			"Bug",
			"Feature"
		];

		private $id, $user_created_id, $user_lmod_id, $project_lead_id, $name, $notes, $date_created,
			$date_lmod, $type, $assigned_ids, $status, $is_deleted;

		/* @var Base $_pdo */
		private $_pdo;

		/* @var User $_user_created */
		private $_user_created;

		/* @var User $_user_lmod */
		private $_user_lmod;

		/* @var User $_project_lead */
		private $_project_lead;

		/* Constructor */
	
		public function __construct(Base $_pdo) {
			$this->_pdo = $_pdo;
		}

		/* Create */

		public function create(User $creator, string $name, string $notes, int $status, int $type, User $lead = null) {
			$this->validateStatus($status);
			$this->validateType($type);

			$this->_user_created = $this->_user_lmod = $creator;
			$this->_project_lead = $lead;
			$this->date_created = $this->date_lmod = Utility::getDateTimeForMySQLDateTime();

			$query = "INSERT INTO project (user_created_id, user_lmod_id, project_lead_id, `name`, notes, date_created,
					  date_lmod, `status`, type) VALUES (:u, :m, :p, :n, :o, :d, :l, :s, :t)";

			$this->_pdo->perform($query, [
				"u" => $this->user_created_id = $creator->getId(),
				"m" => $this->user_lmod_id = $creator->getId(),
				"p" => $this->project_lead_id = $lead ? $lead->getId() : null,
				"n" => $this->name = $name,
				"o" => $this->notes = $notes,
				"d" => $this->date_created,
				"l" => $this->date_lmod,
				"s" => $this->status = $status,
				"t" => $this->type = $type
			]);

			$this->id = $this->_pdo->lastInsertId();
		}

		/* Setters */

		public function setProjectLead(User $project_lead) {
			$this->_project_lead = $project_lead;

			$this->_pdo->perform("UPDATE project SET project_lead_id = :u WHERE id = :i", [
				"u" => $this->project_lead_id = $project_lead->getId(),
				"i" => $this->id
			]);
		}

		public function update(User $user_lmod, string $name, string $notes, int $status) {
			$this->validateStatus($status);

			$this->_user_lmod = $user_lmod;

			$query = "UPDATE project SET user_lmod_id = :u, `name` = :n, notes = :o,
					  date_lmod = :d, `status` = :s WHERE id = :i";

			$this->_pdo->perform($query, [
				"u" => $this->user_lmod_id = $user_lmod->getId(),
				"n" => $this->name = $name,
				"o" => $this->notes = $notes,
				"d" => $this->date_lmod = Utility::getDateTimeForMySQLDateTime(),
				"s" => $this->status = $status
			]);
		}

		public function delete(User $user_lmod) {
			$this->_user_lmod = $user_lmod;

			$query = "UPDATE project SET user_lmod_id = :u, is_deleted = TRUE, date_lmod = :d WHERE id = :i";

			$this->_pdo->perform($query, [
				"u" => $this->user_lmod_id = $user_lmod->getId(),
				"d" => $this->date_lmod = Utility::getDateTimeForMySQLDateTime(),
				"i" => $this->id
			]);
		}

		public function assignUser(User $user) {
			$query = "INSERT INTO rel_user_project (project_id, user_id) VALUES (:p, :u)";

			$this->_pdo->perform($query, [
				"p" => $this->id,
				"u" => $user->getId(),
				"t" => 0
			]);

			if (strlen($this->assigned_ids)) {
				$this->assigned_ids .= "," . $user->getId();
			}
			else {
				$this->assigned_ids = $user->getId() . "";
			}
		}

		public function removeUser(User $user) {
			$query = "DELETE FROM rel_user_project WHERE user_id = :u";

			$this->_pdo->perform($query, [
				"u" => $user->getId()
			]);
		}

		/* Getters */

		public function getId() : int {
			return $this->id;
		}

		public function getType() : int {
			return $this->type;
		}

		public function getUserCreatedId() : int {
			return $this->user_created_id;
		}

		public function getUserCreated() : User {
			if (!$this->_user_created)
				$this->_user_created = User::find($this->_pdo, $this->user_created_id);

			return $this->_user_created;
		}

		public function getUserLmodId() : int {
			return $this->user_lmod_id;
		}

		public function getUserLmod() : int {
			if (!$this->_user_lmod)
				$this->_user_lmod = User::find($this->_pdo, $this->user_lmod_id);

			return $this->_user_lmod;
		}

		public function getProjectLeadId() : int {
			return $this->project_lead_id;
		}

		public function getProjectLead() : User {
			if (!$this->_project_lead)
				$this->_project_lead = User::find($this->_pdo, $this->project_lead_id);

			return $this->_project_lead;
		}

		public function getName() : string {
			return $this->name;
		}

		public function getNotes() : string {
			return $this->notes;
		}

		public function getAssignedIds() : array {
			return explode(",", $this->assigned_ids);
		}

		public function getStatus() : int {
			return $this->status;
		}

		public function isDeleted() : bool {
			return $this->is_deleted;
		}

		public function getStatusText() : string {
			return self::$STATUS_STRINGS[$this->status];
		}

		public function getDateCreated() : \DateTime {
			return Utility::getDateTimeFromMySQLDateTime($this->date_created);
		}

		public function getDateLmod() : \DateTime {
			return Utility::getDateTimeFromMySQLDateTime($this->date_lmod);
		}

		/**
		 * @param int $type
		 * @return User[]
		 * @throws \Exception
		 */
		public function getAllUsersAssigned(int $type = -1) : array {
			if ($type != -1)
				$this->validateStatus($type);

			$types = [-1, User::TYPE_SUPERVISOR, User::TYPE_DEVELOPER, User::TYPE_DESIGNER, User::TYPE_VIEWER];

			if (!in_array($type, $types))
				throw new \Exception("Invalid user type integer given.");

			$query = "SELECT u.* FROM `user` u LEFT JOIN rel_user_project r ON u.id = r.user_id
					  WHERE r.project_id = :u AND (:t = -1 OR u.type = :t)";

			$users = $this->_pdo->fetchAll($query, [
				"u" => $this->id,
				"t" => $type
			], function($row) {
				return User::getInstance($this->_pdo, $row);
			});

			return $users;
		}

		/* Static Find Functions */

		/**
		 * @param Base $_pdo
		 * @param int $id
		 * @param bool $allow_deleted
		 * @return static
		 */
		public static function find(Base $_pdo, int $id, bool $allow_deleted = false) {
			$query = "SELECT p.*, GROUP_CONCAT(r.user_id) AS assigned_ids FROM project p
                      LEFT JOIN rel_user_project r ON p.id = r.project_id WHERE p.id = :id";

			if (!$allow_deleted) {
				$query .= " AND is_deleted = FALSE";
			}

			$query .= " GROUP BY p.id";

			$row = $_pdo->fetchOne($query, [
				"id" => $id
			]);

			return self::getInstance($_pdo, $row);
		}

		/**
		 * @param Base $_pdo
		 * @param File $_file
		 * @param int $offset
		 * @param int $limit
		 * @return static[]
		 */
		public static function findAllReferencingFile(Base $_pdo, File $_file, int $offset = 0, int $limit = 30) : array {
			$query = "SELECT p.*, GROUP_CONCAT(r2.user_id) AS assigned_ids FROM Project p
                      LEFT JOIN rel_user_project r ON p.id = r.project_id
                      LEFT JOIN rel_user_project r2 ON p.id = r2.project_id
                      LEFT JOIN rel_file_project rf ON p.id = rf.project_id
                      WHERE rf.file_id = :f AND p.is_deleted = FALSE
                      GROUP BY p.id";

			if ($limit > 0) {
				$query .= " LIMIT :o, :l";
			}

			return $_pdo->fetchAll($query, [
				"o" => $offset,
				"l" => $limit,
				"f" => $_file->getId()
			], function($row) use ($_pdo) {
				return self::getInstance($_pdo, $row);
			});
		}

		/**
		 * @param Base $_pdo
		 * @param int $offset
		 * @param int $limit
		 * @param string $search
		 * @return static[]
		 */
		public static function findAll(Base $_pdo,
		                               int $offset = 0,
		                               int $limit = 30,
		                               string $search = "") : array {
			if ($search == "") {
				$query = "SELECT p.*, GROUP_CONCAT(r2.user_id) AS assigned_ids FROM Project p
	                      LEFT JOIN rel_user_project r ON p.id = r.project_id
	                      LEFT JOIN rel_user_project r2 ON p.id = r2.project_id
	                      WHERE p.is_deleted = FALSE
	                      GROUP BY p.id";
			}
			else {
				$query = "SELECT MATCH(`name`) AGAINST (:s) AS fulltext_rank, p.*,
						  GROUP_CONCAT(r2.user_id) AS assigned_ids FROM Project p
	                      LEFT JOIN rel_user_project r ON p.id = r.project_id
	                      LEFT JOIN rel_user_project r2 ON p.id = r2.project_id
	                      WHERE MATCH(`name`) AGAINST (:s) AND p.is_deleted = FALSE
						  GROUP BY p.id ORDER BY fulltext_rank DESC";
			}

			if ($limit > 0) {
				$query .= " LIMIT :o, :l";
			}

			return $_pdo->fetchAll($query, [
				"o" => $offset,
				"l" => $limit,
				"s" => trim($search)
			], function($row) use ($_pdo) {
				return self::getInstance($_pdo, $row);
			});
		}

		/**
		 * @param Base $_pdo
		 * @param User $user
		 * @param int $offset
		 * @param int $limit
		 * @param string $search
		 * @return static[]
		 */
		public static function findAllUserAssigned(Base $_pdo,
		                                           User $user,
		                                           int $offset = 0,
		                                           int $limit = 30,
		                                           string $search = "") : array {
			if ($search == "") {
				$query = "SELECT p.*, GROUP_CONCAT(r2.user_id) AS assigned_ids FROM Project p
						  LEFT JOIN rel_user_project r ON p.id = r.project_id
						  LEFT JOIN rel_user_project r2 ON p.id = r2.project_id
						  WHERE (p.project_lead_id = :u OR r.user_id = :u) AND p.is_deleted = FALSE
						  GROUP BY p.id";
			}
			else {
				$query = "SELECT MATCH(`name`) AGAINST (:s) AS fulltext_rank, p.*,
						  GROUP_CONCAT(r2.user_id) AS assigned_ids FROM Project p
						  LEFT JOIN rel_user_project r ON p.id = r.project_id
						  LEFT JOIN rel_user_project r2 ON p.id = r2.project_id
						  WHERE (p.project_lead_id = :u OR r.user_id = :u) AND MATCH(`name`) AGAINST (:s)
						  AND p.is_deleted = FALSE GROUP BY p.id ORDER BY fulltext_rank DESC";
			}

			if ($limit > 0) {
				$query .= " LIMIT :o, :l";
			}

			return $_pdo->fetchAll($query, [
				"u" => $user->getId(),
				"o" => $offset,
				"l" => $limit,
				"s" => trim($search)
			], function($row) use ($_pdo) {
				return self::getInstance($_pdo, $row);
			});
		}

		/**
		 * @param Base $_pdo
		 * @param User $user
		 * @param int $offset
		 * @param int $limit
		 * @param string $search
		 * @return static[]
		 */
		public static function findAllUserRelated(Base $_pdo,
		                                          User $user,
		                                          int $offset = 0,
		                                          int $limit = 30,
		                                          string $search = "") : array {
			if ($search == "") {
				$query = "SELECT p.*, GROUP_CONCAT(r2.user_id) AS assigned_ids FROM Project p
	                      LEFT JOIN rel_user_project r ON p.id = r.project_id
	                      LEFT JOIN rel_user_project r2 ON p.id = r2.project_id
	                      WHERE (p.project_lead_id = :u OR r.user_id = :u OR p.user_created_id = :u)
	                      AND p.is_deleted = FALSE GROUP BY p.id";
			}
			else {
				$query = "SELECT MATCH(`name`) AGAINST (:s) AS fulltext_rank, p.*,
						  GROUP_CONCAT(r2.user_id) AS assigned_ids FROM Project p
	                      LEFT JOIN rel_user_project r ON p.id = r.project_id
	                      LEFT JOIN rel_user_project r2 ON p.id = r2.project_id
	                      WHERE (p.project_lead_id = :u OR r.user_id = :u OR p.user_created_id = :u)
	                      AND MATCH(`name`) AGAINST (:s) AND p.is_deleted = FALSE
						  GROUP BY p.id ORDER BY fulltext_rank DESC";
			}

			if ($limit > 0) {
				$query .= " LIMIT :o, :l";
			}

			return $_pdo->fetchAll($query, [
				"u" => $user->getId(),
				"o" => $offset,
				"l" => $limit,
				"s" => trim($search)
			], function($row) use ($_pdo) {
				return self::getInstance($_pdo, $row);
			});
		}

		/**
		 * @param Base $_pdo
		 * @param User $user
		 * @param int $offset
		 * @param int $limit
		 * @param string $search
		 * @return static[]
		 */
		public static function findAllUserLead(Base $_pdo,
		                                       User $user,
		                                       int $offset = 0,
		                                       int $limit = 30,
		                                       string $search = "") : array {
			if ($search == "") {
				$query = "SELECT p.*, GROUP_CONCAT(r.user_id) AS assigned_ids FROM project p
						  LEFT JOIN rel_user_project r ON p.id = r.project_id
						  WHERE p.project_lead_id = :u AND p.is_deleted = FALSE GROUP BY p.id";
			}
			else {
				$query = "SELECT MATCH(`name`) AGAINST (:s) AS fulltext_rank, p.*,
						  GROUP_CONCAT(r.user_id) AS assigned_ids FROM project p
						  LEFT JOIN rel_user_project r ON p.id = r.project_id
						  WHERE p.project_lead_id = :u AND MATCH(`name`) AGAINST (:s) AND p.is_deleted = FALSE
						  GROUP BY p.id ORDER BY fulltext_rank DESC";
			}

			if ($limit > 0) {
				$query .= " LIMIT :o, :l";
			}

			return $_pdo->fetchAll($query, [
				"u" => $user->getId(),
				"o" => $offset,
				"l" => $limit,
				"s" => trim($search)
			], function($row) use ($_pdo) {
				return self::getInstance($_pdo, $row);
			});
		}

		/**
		 * @param Base $_pdo
		 * @param User $user
		 * @param int $offset
		 * @param int $limit
		 * @param string $search
		 * @return static[]
		 */
		public static function findAllUserCreated(Base $_pdo,
		                                          User $user,
		                                          int $offset = 0,
		                                          int $limit = 30,
		                                          string $search = "") : array {
			if ($search == "") {
				$query = "SELECT p.*, GROUP_CONCAT(r.user_id) AS assigned_ids FROM project p
						  LEFT JOIN rel_user_project r ON p.id = r.project_id
						  WHERE p.user_created_id = :u AND p.is_deleted = FALSE GROUP BY p.id";
			}
			else {
				$query = "SELECT MATCH(`name`) AGAINST (:s) AS fulltext_rank, p.*,
						  GROUP_CONCAT(r.user_id) AS assigned_ids FROM project p
						  LEFT JOIN rel_user_project r ON p.id = r.project_id
						  WHERE p.user_created_id = :u AND MATCH(`name`) AGAINST (:s) AND p.is_deleted = FALSE
						  GROUP BY p.id ORDER BY fulltext_rank DESC";
			}

			if ($limit > 0) {
				$query .= " LIMIT :o, :l";
			}

			return $_pdo->fetchAll($query, [
				"u" => $user->getId(),
				"o" => $offset,
				"l" => $limit
			], function($row) use ($_pdo) {
				return self::getInstance($_pdo, $row);
			});
		}

		/**
		 * @param Base $_pdo
		 * @param int $offset
		 * @param int $limit
		 * @return static[]
		 */
		public static function findAllWithoutLead(Base $_pdo, int $offset = 0, int $limit = 30) : array {
			$query = "SELECT p.*, GROUP_CONCAT(r.user_id) AS assigned_ids FROM project p
					  LEFT JOIN rel_user_project r ON p.id = r.project_id
					  WHERE p.project_lead_id IS NULL AND p.is_deleted = FALSE LIMIT :o, :l";

			return $_pdo->fetchAll($query, [
				"o" => $offset,
				"l" => $limit
			], function($row) use ($_pdo) {
				return self::getInstance($_pdo, $row);
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
				if ($key != "fulltext_rank")
					$this->{$key} = $item;
		}

		/* Validation */

		/**
		 * @param int $status
		 * @throws Exception
		 */
		private function validateStatus(int $status) {
			if (!self::isValidStatus($status))
				throw new Exception("Invalid status");
		}

		/**
		 * @param int $type
		 * @throws Exception
		 */
		private function validateType(int $type) {
			if (!self::isValidType($type))
				throw new Exception("Invalid type");
		}

		public static function isValidStatus(int $status) : bool {
			$available_statuses = [self::STATUS_IN_PROGRESS, self::STATUS_READY_FOR_INSPECTION,
				self::STATUS_ON_HOLD, self::STATUS_IN_DISPUTE, self::STATUS_FINISHED];

			return in_array($status, $available_statuses);
		}

		public static function isValidType(int $type) : bool {
			$available_types = [self::TYPE_BUG, self::TYPE_FEATURE];

			return in_array($type, $available_types);
		}
	}