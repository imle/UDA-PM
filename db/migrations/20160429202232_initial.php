<?php

	use Phinx\Db\Adapter\MysqlAdapter;
	use Phinx\Migration\AbstractMigration;

	class Initial extends AbstractMigration {
		/**
		 * Change Method.
		 *
		 * Write your reversible migrations using this method.
		 *
		 * More information on writing migrations is available here:
		 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
		 *
		 * The following commands can be used in this method and Phinx will
		 * automatically reverse them when rolling back:
		 *
		 *    createTable
		 *    renameTable
		 *    addColumn
		 *    renameColumn
		 *    addIndex
		 *    addForeignKey
		 *
		 * Remember to call "create()" or "update()" and NOT "save()" when working
		 * with the Table class.
		 */
		public function up() {
			$table_user = $this->table("user");
			$table_user->addColumn("name_first", "string", [
				"limit" => 256
			]);
			$table_user->addColumn("name_last", "string", [
				"limit" => 256
			]);
			$table_user->addColumn("email", "string", [
				"limit" => 256
			]);
			$table_user->addColumn("password_hash", "string", [
				"limit" => 100,
				"null" => true
			]);
			$table_user->addColumn("active", "boolean");
			$table_user->addColumn("type", "integer", [
				"limit" => MysqlAdapter::INT_TINY
			]);
			$table_user->addColumn("image_file_id", "integer", [
				"null" => true
			]);
			$table_user->addColumn("token_account_verify", "integer", [
				"null" => true
			]);
			$table_user->addColumn("token_password_reset", "integer", [
				"null" => true
			]);
			$table_user->create();



			$table_token = $this->table("token");
			$table_token->addColumn("value", "string", [
				"limit" => 32
			]);
			$table_token->addColumn("expire_time", "datetime");
			$table_token->create();



			$table_user->addForeignKey("token_account_verify", "token", "id", [
				"delete" => "CASCADE",
				"update" => "CASCADE",
			]);
			$table_user->addForeignKey("token_password_reset", "token", "id", [
				"delete" => "CASCADE",
				"update" => "CASCADE",
			]);
			$table_user->save();



			$table_file = $this->table("file");
			$table_file->addColumn("user_id", "integer");
			$table_file->addColumn("original_name", "string", [
				"limit" => 256
			]);
			$table_file->addColumn("extension", "string", [
				"limit" => 10
			]);
			$table_file->addColumn("size", "integer");
			$table_file->addColumn("md5", "string", [
				"limit" => 32
			]);
			$table_file->addColumn("mime_type", "string", [
				"limit" => 256
			]);
			$table_file->addColumn("date_added", "datetime");
			$table_file->addForeignKey("user_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_file->create();

			

			$table_user->addForeignKey("image_file_id", "file", "id", [
				"delete" => "SET_NULL",
				"update" => "CASCADE",
			]);
			$table_user->save();



			$table_project = $this->table("project");
			$table_project->addColumn("user_created_id", "integer");
			$table_project->addColumn("user_lmod_id", "integer");
			$table_project->addColumn("project_lead_id", "integer");
			$table_project->addColumn("name", "string", [
				"limit" => 256
			]);
			$table_project->addColumn("notes", "string", [
				"limit" => 8192
			]);
			$table_project->addColumn("date_created", "datetime");
			$table_project->addColumn("date_lmod", "datetime");
			$table_project->addColumn("type", "integer", [
				"limit" => MysqlAdapter::INT_TINY
			]);
			$table_project->addColumn("status", "integer", [
				"limit" => MysqlAdapter::INT_TINY
			]);
			$table_project->addColumn("is_deleted", "boolean", [
				"default" => "0"
			]);
			$table_project->addForeignKey("user_created_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_project->addForeignKey("user_lmod_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_project->addForeignKey("project_lead_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_project->create();



			$table_attachment = $this->table("attachment");
			$table_attachment->addColumn("file_id", "integer");
			$table_attachment->addColumn("user_id", "integer");
			$table_attachment->addColumn("project_id", "integer");
			$table_attachment->addColumn("name", "string", [
				"limit" => 256
			]);
			$table_attachment->addColumn("date_added", "datetime");
			$table_attachment->addForeignKey("file_id", "file", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_attachment->addForeignKey("user_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_attachment->addForeignKey("project_id", "project", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_attachment->create();



			$table_comment = $this->table("comment");
			$table_comment->addColumn("project_id", "integer");
			$table_comment->addColumn("creator_id", "integer");
			$table_comment->addColumn("date_created", "datetime");
			$table_comment->addColumn("text", "string", [
				"limit" => 2048
			]);
			$table_comment->addForeignKey("project_id", "project", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_comment->addForeignKey("creator_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_comment->create();



			$table_email_log = $this->table("email_log");
			$table_email_log->addColumn("user_id", "integer");
			$table_email_log->addColumn("template", "string", [
				"limit" => 32
			]);
			$table_email_log->addColumn("vars", "string", [
				"limit" => 8192
			]);
			$table_email_log->addColumn("date_send", "datetime");
			$table_email_log->addColumn("successful", "boolean");
			$table_email_log->addColumn("view_key", "string", [
				"limit" => 80
			]);
			$table_email_log->addForeignKey("user_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_email_log->create();



			$table_modification_log = $this->table("modification_log");
			$table_modification_log->addColumn("project_id", "integer");
			$table_modification_log->addColumn("modifier_id", "integer");
			$table_modification_log->addColumn("type", "integer", [
				"limit" => MysqlAdapter::INT_TINY
			]);
			$table_modification_log->addColumn("date", "datetime");
			$table_modification_log->addColumn("data", "string", [
				"limit" => 4096
			]);
			$table_modification_log->addForeignKey("project_id", "project", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_modification_log->addForeignKey("modifier_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_modification_log->create();



			$table_rel_user_project = $this->table("rel_user_project");
			$table_rel_user_project->addColumn("project_id", "integer");
			$table_rel_user_project->addColumn("user_id", "integer");
			$table_rel_user_project->addForeignKey("project_id", "project", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_rel_user_project->addForeignKey("user_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_rel_user_project->create();



			$table_remember = $this->table("remember");
			$table_remember->addColumn("user_id", "integer");
			$table_remember->addColumn("token", "string", [
				"length" => 30
			]);
			$table_remember->addColumn("date_expires", "datetime");
			$table_remember->addColumn("user_agent", "string", [
				"length" => 512
			]);
			$table_remember->addForeignKey("user_id", "user", "id", [
				"delete" => "RESTRICT",
				"update" => "CASCADE",
			]);
			$table_remember->create();
		}
	}
