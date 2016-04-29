<?php
	namespace PM;

	use Exception;
	use League\Flysystem\Sftp\SftpAdapter;
	use League\Flysystem\Adapter\Local;
	use League\Flysystem\Adapter\NullAdapter;
	use League\Flysystem\FilesystemInterface;
	use League\Flysystem\Filesystem;

	class Config {
		private static $CONFIG = __DIR__ . "/../config/config.json";

		/* @var FilesystemInterface $FILESYSTEM */
		private static $FILESYSTEM = null;

		private static $DATA_READ = false;
		private static $DATA = [];

		const ENV_DEV = "dev";
		const ENV_TEST = "test";
		const ENV_PROD = "prod";

		public static function getFilePath() : string {
			self::readData();

			return self::$DATA["storage"]["path"];
		}

		public static function getSftpConnectionData() : array {
			self::readData();

			return self::$DATA["storage"]["sftp"];
		}

		public static function getEnvironment() : string {
			self::readData();

			return self::$DATA["environment"];
		}

		public static function getBaseUrl() : string {
			self::readData();

			return self::$DATA["base_url"];
		}

		public static function getDevUserEmail() : string {
			self::readData();

			return self::$DATA["dev_user_email"];
		}

		public static function getFileSystem(bool $refresh = false) : FilesystemInterface {
			if (!$refresh && self::$FILESYSTEM)
				return self::$FILESYSTEM;

			self::readData();

			$adapter = null;

			switch (self::$DATA["storage"]["adapter"]) {
				case "local":
					$adapter = new Local(self::getFilePath());
					break;
				case "sftp":
					$adapter = new SftpAdapter(self::getSftpConnectionData());
					break;
				default:
					$adapter = new NullAdapter();
					break;
			}

			return self::$FILESYSTEM = new Filesystem($adapter);
		}

		public static function getMaxFileSize() : string {
			self::readData();

			return self::$DATA["storage"]["max_file_size"];
		}

		public static function getLastUpdate() : string {
			self::readData();

			return self::$DATA["last_update"];
		}

		public static function setLastUpdate($date) {
			self::readData();

			self::$DATA["last_update"] = $date;

			self::writeData();
		}

		private static function readData() {
			if (!self::$DATA_READ) {
				if (!file_exists(self::$CONFIG))
					throw new Exception("A config file should exist in " . dirname(self::$CONFIG) . " by the name of "
					  . "\"config.json\". It is recommended that you copy the default config file in that directory.");

				self::$DATA = json_decode(file_get_contents(self::$CONFIG), true);
				self::$DATA_READ = true;
			}
		}

		public static function writeData() {
			file_put_contents(self::$CONFIG, json_encode(self::$DATA, JSON_PRETTY_PRINT));
		}
	}