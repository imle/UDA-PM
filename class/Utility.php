<?php
	namespace PM;

	use DateInterval;
	use DateTime;

	class Utility {
		const LAST_PUB_TIME = "#!#TIME_HERE#!#";

		const SECONDS_IN_HOUR = 3600;
		const SECONDS_IN_DAY = 86400;

		public static function getAppBasePath() : string {
			return realpath(__DIR__ . "/../");
		}

		public static function getPhoneFormatted(string $phone) : string {
			return "(" . substr($phone, 0, 3) . ") " . substr($phone, 3, 3) . "-" . substr($phone, 6, 4);
		}

		public static function getDateTimeFromYear(string $date_string) {
			return DateTime::createFromFormat("Y|", $date_string);
		}

		public static function getDateTimeFromMySQLDate(string $date_string) : DateTime {
			return DateTime::createFromFormat("Y-m-d|", $date_string);
		}

		public static function getDateTimeForMySQLDate(DateTime $date = null) : string {
			if ($date === null)
				$date = new DateTime();

			return $date->format("Y-m-d");
		}

		public static function getDateTimeFromMySQLDateTime(string $date_string) : DateTime {
			return DateTime::createFromFormat("Y-m-d H:i:s|", $date_string);
		}

		public static function getDateTimeForMySQLDateTime(DateTime $date = null) : string {
			if ($date === null)
				$date = new DateTime();

			return $date->format("Y-m-d H:i:s");
		}

		public static function getExpiryAsMySQLDateTime(DateInterval $interval) : string {
			return Utility::getDateTimeForMySQLDateTime((new DateTime())->add($interval));
		}

		/**
		 * @param string $date_string
		 * @return DateTime
		 */
		public static function getDateTimeFromYmd(string $date_string) {
			return DateTime::createFromFormat("Y-m-d|", $date_string);
		}

		/**
		 * @param string $date_string
		 * @return DateTime
		 */
		public static function getDateTimeFromDateYmdHis(string $date_string) {
			return DateTime::createFromFormat("Y-m-d H:i:s|", $date_string);
		}

		public static function cleanPhoneString(string $phone) : string {
			$phone = preg_replace("/[^0-9]/", "", $phone);
			return self::charAt($phone) == "1" ? substr($phone, 1) : $phone;
		}

		public static function hashPassword(string $password) : string {
			return password_hash($password, PASSWORD_BCRYPT);
		}

		public static function verifyPassword(string $password, string $password_hash) : bool {
			return password_verify($password, $password_hash);
		}

		public static function getRandomNumber(int $min, int $max) : int {
			return rand($min, $max);
		}

		public static function getRandomString(int $length = 16, bool $numbers = true, bool $lowercase = true,
		                                       bool $uppercase = true) : string {
			$possible_chars = $numbers ? "0123456789" : "";
			$possible_chars .= $lowercase ? "abcdefghijklmnopqrstuvwxyz" : "";
			$possible_chars .= $uppercase ? "ABCDEFGHIJKLMNOPQRSTUVWXYZ" : "";

			$char_list_len = strlen($possible_chars);

			$randomString = "";
			for ($i = 0; $i < $length; $i++)
				$randomString .= $possible_chars[rand(0, $char_list_len - 1)];

			return $randomString;
		}

		/**
		 * Used to match
		 * @param string $haystack
		 * @param string|array $needle
		 * @param bool $match_all
		 * @return bool
		 */
		public static function stringContains(string $haystack, $needle, bool $match_all = true) : bool {
			if (!is_array($needle) && !is_string($needle))
				throw new \InvalidArgumentException("Variable \$needle must be either an array or string.");

			if (!is_array($needle))
				return strpos($haystack, $needle) !== false;

			if ($match_all) {
				foreach ($needle as $item)
					if (!Utility::stringContains($haystack, $item))
						return false;
			}
			else {
				foreach ($needle as $item)
					if (Utility::stringContains($haystack, $item))
						return true;
			}

			return $match_all;
		}

		public static function stringStartsWith(string $haystack, string $needle) : bool {
			return strrpos($haystack, $needle, -strlen($haystack)) !== false;
		}

		public static function charAt(string $str, int $index = 0) : string {
			return $str[$index];
		}

		public static function requestHasPost() {
			return !empty($_POST);
		}

		public static function requestHasGet() {
			return !empty($_GET);
		}

		public static function formatNumber(\float $number) : string {
			return number_format($number);
		}

		public static function getNameLink(string $name) : string {
			return strtolower(preg_replace("/\\W/", "", $name));
		}

		public static function isDevServer() : bool {
			return Utility::stringContains(Config::getEnvironment(), ["dev", "test"], false);
		}

		public static function getLastPubTime() : string {
			return self::isDevServer() ? "" : self::LAST_PUB_TIME;
		}

		public static function displayPage(string $page, array $args = []) {
			$args = empty($args) ? "" :  "?" . http_build_query($args);

			header("Location: /" . trim($page, "/") . "/" . $args);
			exit();
		}

		public static function cleanBoolean($bool) : bool {
			return filter_var($bool, FILTER_VALIDATE_BOOLEAN);
		}

		public static function cleanString($str) : string {
			return $str ?? "";
		}

		/**
		 * Converts a string to an integer. If min or max is set then any invalid value will return false.
		 * @param $int
		 * @param int $default
		 * @param int $min
		 * @param int $max
		 * @return int|bool
		 */
		public static function cleanInt($int, int $default = 0, int $min = null, int $max = null) {
			$opt["options"] = [];

			$opt["options"]["default"] = $default;

			if (!is_null($min))
				$opt["options"]["min_range"] = $min;

			if (!is_null($max))
				$opt["options"]["max_range"] = $max;

			return filter_var($int, FILTER_VALIDATE_INT, $opt);
		}

		public static function isValidEmail(string $email) : bool {
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}

		public static function isValidPassword(string $password) : bool {
			return strlen($password) >= 6;
		}

		public static function errorJson(string $message) : array {
			return [
				"err" => true,
				"msg" => $message
			];
		}
	}