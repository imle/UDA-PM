<?php
	namespace PM\PDO;

	class MySQL extends Base {
		protected static $DUPLICATE_KEY_ERROR = 1062;

		protected static $HOSTNAME = "127.0.0.1";
		protected static $DATABASE = "UDA_PM";
		protected static $USERNAME = "root";
		protected static $PASSWORD = "leftbeh!nd10";

		public function __construct(array $options = array(), array $attributes = array()) {
			$dsn = "mysql:host=" . self::$HOSTNAME . ";dbname=" . self::$DATABASE;
			parent::__construct($dsn, self::$USERNAME, self::$PASSWORD, $options, $attributes);
		}
	}