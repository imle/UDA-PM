Files = {
	load: function() {
		this.getStaticElements();
		this.setEventListeners();

		this.bootstrapData();
		this.printFiles();
	},
	elements: {
		$filesList: undefined,
		$filesListBody: undefined
	},
	templates: {
		table_row: "",
		project_table_row: "",
		spinner: "<div class=\"fa fa-spinner fa-spin\"></div>"
	},
	modals: {
		$referencesModal: undefined
	},
	getStaticElements: function() {
		this.elements.$filesList = $("#items_list");
		this.elements.$filesListBody = this.elements.$filesList.find("tbody");

		this.modals.$referencesModal = $("#references_modal");

		this.templates.table_row = $("#template_table_row").html();
		this.templates.project_table_row = $("#template_project_table_row").html();
	},
	setEventListeners: function() {
		Files.elements.$filesListBody.on("click", "tr", this.event_functions.rowClick.bind(this));
	},
	event_functions: {
		rowClick: function(e) {
			var $row = $(e.currentTarget);
			var file_id = $row.data("id");

			this.modals.$referencesModal.find(".modal-body").html(this.templates.spinner);

			PM.Project.get({
				properties: {
					file_id: file_id
				}
			}, "/files/<%= file_id %>/projects/", (function(projects) {
				// TODO: Hide spinner

				var self = this;

				this.modals.$referencesModal.find(".modal-body").html(projects.reduce(function(str, project) {
					return str + Util.parse.template(self.templates.project_table_row, project);
				}, ""));

				console.log(projects);
			}).bind(this));

			console.log(file_id);

			this.modals.$referencesModal.modal("toggle");
		}
	},
	data: {
		/** @type Array.<PM.Project> */
		files: []
	},
	bootstrapData: function() {
		this.data.files = bootstrap_files.map(function(attachment) {
			return new PM.Attachment(attachment);
		});
	},
	printFiles: function() {
		this.elements.$filesListBody.html(Files.data.files.reduce(function(str, f) {
			return str + Util.parse.template(Files.templates.table_row, {
				id: f.id,
				name: f.getFullName(),
				type: f.extension.toUpperCase(),
				size: f.sizeAs("kb") + " Kb",
				date: Util.date.mdY(f.date_added)
			});
		}, ""));
	}
};

$(document).ready(function() {
	Files.load();
});