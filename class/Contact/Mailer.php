<?php
	namespace PM\Contact;

	use PM\Config;
	use PM\PDO\Base;
	use PM\User\User;
	use PM\Utility;
	use PHPMailer\PHPMailer\PHPMailer;

	class Mailer {
		private static $FOLDER_PATH = "/../../emails";
		private static $HTML_FILE_NAME = "template.html";
		private static $TEXT_FILE_NAME = "template.txt";

		private static $T_USER_ADDITION = "user_addition";
		private static $T_USER_CONFIRMATION = "user_confirmation";
		private static $T_USER_PASSWORD_RESET = "user_password_reset";
		private static $T_CREATOR_SIGN_UP_LIST = "creator_sign_up_list";

		/* @var PHPMailer $_phpmailer */
		private $_phpmailer;

		/* @var Base $_pdo */
		private $_pdo;

		private $is_dev;

		public function __construct(Base $_pdo, \bool $is_dev = null) {
			$this->_phpmailer = new PHPMailer();
			$this->_phpmailer->isSendmail();

			$this->is_dev = $is_dev ?? Utility::isDevServer();

			$this->_pdo = $_pdo;
		}

		/* ---- Logic ----------------------------------------------------------------------------------------------- */

		private function send(\string $template_name, User $to_user, array $data) {
			$base_path = __DIR__ . self::$FOLDER_PATH . DIRECTORY_SEPARATOR
				. $template_name . DIRECTORY_SEPARATOR;

			$body_html = file_get_contents($base_path . self::$HTML_FILE_NAME);
			$body_text = file_get_contents($base_path . self::$TEXT_FILE_NAME);

			foreach ($data as $key => $var) {
				$body_html = str_replace("#!" . $key . "!#", $var, $body_html);
				$body_text = str_replace("#!" . $key . "!#", $var, $body_text);
			}

			$body_html = str_replace("#!BASE_URL!#", Config::getBaseUrl(), $body_html);
			$body_text = str_replace("#!BASE_URL!#", Config::getBaseUrl(), $body_text);

			$address = $this->is_dev ? Config::getDevUserEmail() : $to_user->getEmail();

			$this->_phpmailer->addAddress($address, $to_user->getNameFull());

			$this->_phpmailer->msgHTML($body_html);
			$this->_phpmailer->AltBody = $body_text;

			if (Utility::isDevServer())
				$this->logEmail(false, $template_name, $to_user, $data);
			else
				$this->logEmail($this->_phpmailer->send(), $template_name, $to_user, $data);

			$this->_phpmailer->clearAllRecipients();
			$this->_phpmailer->clearAttachments();
			$this->_phpmailer->clearReplyTos();
		}

		private function logEmail(\bool $success, \string $template_name, User $to_user, array $data) {
			$query = "INSERT INTO email_log (user_id, template, vars, successful, view_key)
					  VALUES (:u, :t, :v, :s, :k)";

			$data = json_encode($data);

			$this->_pdo->perform($query, [
				"u" => $to_user->getId(),
				"t" => $template_name,
				"v" => $data,
				"s" => $success,
				"k" => md5($data)
			]);
		}

		public function getEmailHtml(\string $template_name,
		                             User $to_user,
		                             \string $view_key,
		                             \bool $as_plain = false) : \string {
			$base_path = __DIR__ . self::$FOLDER_PATH . DIRECTORY_SEPARATOR
				. $template_name . DIRECTORY_SEPARATOR;

			$body_html = file_get_contents($base_path . ($as_plain ? self::$TEXT_FILE_NAME : self::$HTML_FILE_NAME));

			$query = "SELECT * FROM email_log WHERE view_key = :v AND template = :t AND user_id = :u";

			$data = $this->_pdo->fetchOne($query, [
				"v" => $view_key,
				"t" => $template_name,
				"u" => $to_user->getId()
			]);

			if (!$data)
				return "";

			$data = json_decode($data["vars"]);

			foreach ($data as $key => $var) {
				$body_html = str_replace("#!" . $key . "!#", $var, $body_html);
			}

			return str_replace("#!BASE_URL!#", Config::getBaseUrl(), $body_html);
		}

		/* ---------------------------------------------------------------------------------------------------------- */
		/* ---- START EMAILS ---------------------------------------------------------------------------------------- */
		/* ---------------------------------------------------------------------------------------------------------- */

		/* ---- Setup/Reset ----------------------------------------------------------------------------------------- */

		public function sendUserConfirmationEmail(User $_user) {
			$this->send(self::$T_USER_CONFIRMATION, $_user, [
				"NAME" => $_user->getNameFull(),
				"TOKEN" => $_user->getTokenAccountVerify()->getValue()
			]);
		}

		public function sendUserAdditionEmail(User $_user) {
			$this->send(self::$T_USER_ADDITION, $_user, [
				"NAME" => $_user->getNameFull(),
				"TOKEN" => $_user->getTokenAccountVerify()->getValue()
			]);
		}

		public function sendUserPasswordResetEmail(User $_user) {
			$this->send(self::$T_USER_PASSWORD_RESET, $_user, [
				"NAME" => $_user->getNameFull(),
				"TOKEN" => $_user->getTokenPasswordReset()->getValue()
			]);
		}

		public function sendCreatorSignUpList(User $_user) {
			$this->send(self::$T_CREATOR_SIGN_UP_LIST, $_user, [
				"EMAIL" => $_user->getEmail(),
				"TOKEN" => $_user->getTokenAccountVerify()->getValue()
			]);
		}
	}