<?php
	namespace PM\PDO;

	use PM\Config;

	class MySQL extends Base {
		protected static $DUPLICATE_KEY_ERROR = 1062;

		protected static $HOSTNAME = "127.0.0.1";
		protected static $USERNAME = "root";
		protected static $PASSWORD = "leftbeh!nd10";

		public function __construct(array $options = array(), array $attributes = array()) {
			if (Config::getEnvironment() == Config::ENV_PROD) {
				$database = "uda_pm_prod";
			}
			else if (Config::getEnvironment() == Config::ENV_TEST) {
				$database = "uda_pm_test";
			}
			else {
				$database = "uda_pm_dev";
			}

			$dsn = "mysql:host=" . self::$HOSTNAME . ";dbname=" . $database;
			parent::__construct($dsn, self::$USERNAME, self::$PASSWORD, $options, $attributes);
		}
	}