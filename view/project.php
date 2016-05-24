<?php /** @var \PM\Project\Project $project */ ?>
<?php /** @var \PM\Project\Comment[] $comments */ ?>
<?php /** @var \PM\File\Attachment[] $attachments */ ?>

<ol class="breadcrumb">
	<li><a href="/">Home</a></li>
	<li><a href="../">Projects</a></li>
	<li class="active"><?= $project ? $project->getName() : "No Project Found"; ?></li>
</ol>

<?php if ($project) { ?>
<div class="page-header">
	<h3><?= $project->getName(); ?></h3>
	<button type="button" class="btn btn-danger pull-right" data-toggle="modal" data-target="#delete_prj">
		<span>Delete</span>
		<span class="fa fa-trash"></span>
	</button>
</div>

<div class="container-fluid">
	<div class="row">
		<div id="" class="col-lg-6">
		</div>

		<div id="project_data" class="col-lg-6 body-tab-set">
			<div id="project_data_nav" class="tab-set">
				<ul class="nav nav-pills nav-sm">
					<li role="presentation" class="active">
						<a data-target="#project_comments" role="tab" data-toggle="tab">Comments</a>
					</li>
					<li role="presentation">
						<a data-target="#project_attachments" role="tab" data-toggle="tab">Attachments</a>
					</li>
				</ul>
			</div>

			<div id="project_add_data" class="btn btn-primary btn-circle">
				<span class="fa fa-plus"></span>
			</div>

			<div class="tab-content">
				<div id="project_comments" role="tabpanel" class="tab-pane active empty comment_thread">
					<div class="e_message">No comments yet.</div>
				</div>
				<div id="project_attachments" role="tabpanel" class="tab-pane attachment_thread"></div>
			</div>
		</div>
	</div>
</div>
<?php } else { ?>
<div class="container-fluid">
	<div class="row">
		<div class="jumbotron alert-danger">
			<h2>No Project was Found</h2>
			<p>The ID used to get this page does not have a project associated with it.</p>
		</div>
	</div>
</div>
<?php } ?>

<div class="modal fade" id="delete_prj" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<span class="fa fa-warning fa-lg"></span>
					Delete Project
				</h4>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this project?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger" id="remove_project">Delete</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="add_comment" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<span class="fa fa-comment fa-lg"></span>
					Add Comment
				</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="comment">Comment</label>
					<textarea class="form-control" id="comment" placeholder="Enter your comment here..."></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="add_comment_button">Save</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="add_attachment" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<span class="fa fa-file fa-lg"></span>
					Add Attachment
				</h4>
			</div>
			<div class="modal-body">
				<div class="dropzone" id="attachment_upload"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Finished</button>
			</div>
		</div>
	</div>
</div>

<script type="text/html" id="template_comment">
<div class="comment" data-id="<%= id %>">
	<a class="avatar pull-left thumb-sm m-l-n-md">
		<img src="/image/profile/small/<%= user_id %>/" class="img-circle" alt="">
	</a>
	<div class="comment_body">
		<div class="m-b-xs">
			<a href="/users/<%= user_id %>" class="h4"><%= name %></a>
			<span class="text-muted m-l-sm pull-right" title="<%= created %>"><%= created_relative %></span>
		</div>
		<p><%= text %></p>
	</div>
</div>
</script>

<script type="text/html" id="template_file">
<div class="attachment" data-id="<%= id %>">
	<div class="icon fa" data-type="<%= type %>"></div>
	<div class="body">
		<div class="name"><%= name %></div>
		<div class="size">Size: <span><%= size %></span></div>
		<div class="type">Type: <span><%= type %></span></div>
		<div class="uploader">Uploaded By: <span><%= uploader %></span></div>
	</div>
	<div class="options">
		<a class="option fa fa-download" href="/service/file/<%= id %>/"></a>
		<div class="option fa fa-remove" data-action="remove"></div>
	</div>
</div>
</script>

<script type="text/javascript">
	var bootstrap_project;
	var bootstrap_comments;
	var bootstrap_attachments;

	<?php if ($project) { ?>
		bootstrap_project = <?= json_encode($project->toArray()); ?>;
		bootstrap_comments = <?= json_encode(array_map(function(\PM\Project\Comment $comment) {
			return $comment->toArray();
		}, $comments)); ?>;
		bootstrap_attachments = <?= json_encode(array_map(function(\PM\File\Attachment $file) {
			return $file->toArray();
		}, $attachments)); ?>;
	<?php } ?>
</script>
<script type="text/javascript" src="/assets/js/libraries/moment/moment.min.js"></script>
<script type="text/javascript" src="/assets/js/pages/project.js"></script>