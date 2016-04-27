		<script type="text/javascript">
			PM.Project.status_strings = <?= json_encode(\PM\Project\Project::$STATUS_STRINGS) ?>;
			PM.Project.type_strings = <?= json_encode(\PM\Project\Project::$TYPE_STRINGS) ?>;
			PM.User.type_strings = <?= json_encode(\PM\User\User::$TYPE_STRINGS) ?>;

			PM.User.types = {
				TYPE_SUPERVISOR: <?= \PM\User\User::TYPE_SUPERVISOR ?>,
				TYPE_DEVELOPER: <?= \PM\User\User::TYPE_DEVELOPER ?>,
				TYPE_DESIGNER: <?= \PM\User\User::TYPE_DESIGNER ?>,
				TYPE_VIEWER: <?= \PM\User\User::TYPE_VIEWER ?>
			};
		</script>

		<script type="text/javascript">
			$(document).on('change', '.btn-file :file', function() {
				var input = $(this),
					num_files = input.get(0).files ? input.get(0).files.length : 1,
					label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
				input.trigger('fileselect', [num_files, label]);
			});

			$(document).ready( function() {
				$('.btn-file :file').on('fileselect', function(event, num_files, label) {
					var $input = $($(this).data("target"));

					if (num_files == 0) {
						$input.val("");
					}
					else if (num_files == 1) {
						$input.val(label);
					}
					else {
						$input.val(num_files + " files selected");
					}
				});
			});
		</script>
	</div>
</div>
</body>
</html>