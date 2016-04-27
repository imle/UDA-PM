<ol class="breadcrumb">
	<li><a href="/">Home</a></li>
	<li class="active">Projects</li>
</ol>

<div class="page-header">
	<h3>Projects</h3>
	<button type="button" id="new_project_button" class="btn btn-primary pull-right">
		<span>Create New</span>
		<span class="fa fa-plus"></span>
	</button>
</div>

<div class="row wrapper">
	<form action="" method="get" id="filter_form">
		<div class="col-md-3 col-sm-4 m-b-xs">
			<select title="Filter" name="filter" class="input-sm form-control w-sm inline v-middle">
				<option value="0" <?= \PM\Utility::cleanInt($_GET["filter"]) == 0 ? "selected" : "" ?>>All</option>
				<option value="3" <?= \PM\Utility::cleanInt($_GET["filter"]) == 3 ? "selected" : "" ?>>Creator</option>
				<option value="2" <?= \PM\Utility::cleanInt($_GET["filter"]) == 2 ? "selected" : "" ?>>Lead</option>
				<option value="1" <?= \PM\Utility::cleanInt($_GET["filter"]) == 1 ? "selected" : "" ?>>Assigned</option>
			</select>
		</div>
		<div class="col-md-5 col-sm-4">
		</div>
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
				<th>Relation</th>
				<th>Lead</th>
				<th>Status</th>
				<th>Last Modified</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>

<div class="modal fade" id="new_project" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">
					<span class="fa fa-briefcase fa-lg"></span>
					Create New Project
				</h4>
			</div>
			<div class="modal-body">
				<div class="tabbable">
					<ul class="nav nav-tabs">
						<li class="active">
							<a href="#tab1" data-toggle="tab">Basic Info</a>
						</li>
						<li>
							<a href="#tab2" data-toggle="tab">Assignment</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tab1">
							<br>
							<div class="form-group">
								<label for="project_name">Name</label>
								<input type="text" class="form-control" id="project_name" placeholder="Name">
							</div>
							<div class="form-group">
								<label for="project_type">Type</label>
								<select class="form-control" id="project_type"></select>
							</div>
							<div class="form-group">
								<label for="project_status">Status</label>
								<select class="form-control" id="project_status"></select>
							</div>
							<div class="form-group">
								<label for="project_notes">Notes</label>
								<textarea class="form-control" id="project_notes" placeholder="Notes"></textarea>
							</div>
						</div>
						<div class="tab-pane" id="tab2">
							<br>
							<div class="form-group">
								<label for="project_lead">Lead</label>
								<input type="text" class="contacts" id="project_lead">
							</div>
							<div class="form-group">
								<label for="project_assigned">Assigned</label>
								<select class="contacts" id="project_assigned"></select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary create_project_button" data-open="true">Save & Open</button>
				<button type="button" class="btn btn-primary create_project_button">Save</button>
			</div>
		</div>
	</div>
</div>


<script type="text/html" id="template_table_row">
	<tr data-id="<%= id %>">
		<td><%= id %></td>
		<td><%- name %></td>
		<td><span><%= relation %></span></td>
		<td><%= lead %></td>
		<td><%= status %></td>
		<td><%= date_lmod %></td>
	</tr>
</script>

<?php /** @var \PM\Project\Project[] $projects */ ?>
<script type="text/javascript">
	var bootstrap_projects = <?= json_encode(array_map(function(\PM\Project\Project $project) {
	    return $project->toArray();
	}, $projects)); ?>;
</script>
<script type="text/javascript" src="/assets/js/pages/projects.js"></script>