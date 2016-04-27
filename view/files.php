<?php /** @var \PM\File\File[] $files */ ?>

<ol class="breadcrumb">
	<li><a href="/">Home</a></li>
	<li class="active">Files</li>
</ol>

<div class="page-header">
	<h3>Files</h3>
</div>

<div class="row wrapper">
	<form action="" method="get" id="filter_form">
		<div class="col-sm-8 hidden-xs"></div>
		<div class="col-sm-4">
			<div class="input-group">
				<input type="text" name="search" value="<?= \PM\Utility::cleanString($_GET["search"]) ?>"
				       class="input-sm form-control" placeholder="Search" autocomplete="off">
				<span class="input-group-btn">
					<button class="btn btn-sm btn-default" type="button">Search</button>
				</span>
			</div>
		</div>
	</form>
</div>

<div class="table-responsive">
	<table class="table table-striped" id="items_list">
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Size</th>
				<th>Type</th>
				<th>References</th>
				<th>Date Uploaded</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>

<div class="modal fade" id="references_modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="fa fa-file fa-lg"></span>
					File References
				</h4>
			</div>
			<div class="modal-body">
				<div class="fa fa-spinner fa-spin"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script type="text/html" id="template_table_row">
	<tr data-id="<%= id %>">
		<td><%= id %></td>
		<td><%= name %></td>
		<td><%= size %></td>
		<td><%= type %></td>
		<td><%= references %></td>
		<td><%= date %></td>
	</tr>
</script>

<script type="text/html" id="template_project_table_row">
	<div data-id="<%= id %>">
		<a href="/projects/<%= id %>/"><%= name %></a>
	</div>
</script>

<script type="text/javascript">
	var bootstrap_files = <?= json_encode(array_map(function(\PM\File\File $file) {
		return $file->toArray();
	}, $files)); ?>;
</script>
<script type="text/javascript" src="/assets/js/pages/files.js"></script>