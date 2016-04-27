<?php
	namespace PM\PDO;

	use Aura\Sql\ExtendedPdo;
	use PDOException;

	abstract class Base extends ExtendedPdo {
		protected static $DUPLICATE_KEY_ERROR;

		public static function isDuplicateKeyError(PDOException $er) {
			return $er->errorInfo[1] === self::$DUPLICATE_KEY_ERROR;
		}
	}