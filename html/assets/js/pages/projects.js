Projects = {
	load: function() {
		this.getStaticElements();
		this.setEventListeners();

		this.bootstrapData();
		this.printProjects();
	},
	elements: {
		$projectsList: undefined,
		$projectsListBody: undefined,
		$filterForm: undefined,
		$filterSelect: undefined,
		$newButton: undefined,
		$createButton: undefined
	},
	templates: {
		table_row: ""
	},
	modals: {
		$newProject: undefined
	},
	getStaticElements: function() {
		this.modals.$newProject = $("#new_project");

		this.templates.table_row = $("#template_table_row").html();

		this.elements.$projectsList = $("#items_list");
		this.elements.$projectsListBody = this.elements.$projectsList.find("tbody");
		this.elements.$filterForm = $("#filter_form");
		this.elements.$filterSelect = this.elements.$filterForm.find("select");
		this.elements.$newButton = $("#new_project_button");
		this.elements.$createButton = this.modals.$newProject.find(".create_project_button");
	},
	setEventListeners: function() {
		this.elements.$projectsListBody.on("click", "tr", this.event_functions.rowClick);
		this.elements.$filterSelect.on("change", this.event_functions.filterChange);
		this.elements.$newButton.on("click", this.event_functions.newProject);
		this.elements.$createButton.on("click", this.event_functions.createProject);
	},
	event_functions: {
		rowClick: function() {
			window.location = $(this).data("id") + "/";
		},
		filterChange: function() {
			// TODO: Change to ajax request.
			Projects.elements.$filterForm.submit();
		},
		newProject: function() {
			var disabled_html = Util.parse.template(Util.templates.option, {
				value: -1,
				disabled: true,
				selected: true,
				text: "Select One"
			});


			var type_html = Util.generate.dropdown(PM.Project.type_strings);
			var status_html = Util.generate.dropdown(PM.Project.status_strings);

			Projects.modals.$newProject.find("#project_type").html(disabled_html + type_html);
			Projects.modals.$newProject.find("#project_status").html(disabled_html + status_html);

			var users = PM.data.users.sort(PM.User.compare.lastFirst).map(function(u) {
				return {
					id: u.id,
					type: u.type,
					name: u.getFullName(),
					email: u.email
				};
			});

			var devs = users.filter(function(user) {
				return user.type == PM.User.types.TYPE_DEVELOPER;
			});

			var $lead = Projects.modals.$newProject.find("#project_lead");
			Projects.setupModalSelector($lead, devs, false, "email", "name");

			var $assigned = Projects.modals.$newProject.find("#project_assigned");
			Projects.setupModalSelector($assigned, users, true, "email", "name");

			Projects.modals.$newProject.find("#project_name").val("");
			Projects.modals.$newProject.find("#project_type").val(-1);
			Projects.modals.$newProject.find("#project_status").val(-1);
			Projects.modals.$newProject.find("#project_notes").val("");

			Projects.modals.$newProject.find(".nav.nav-tabs").find("[data-toggle='tab']").eq(0).click();
			Projects.modals.$newProject.modal("toggle");
		},
		createProject: function(e) {
			var $np = Projects.modals.$newProject;
			
			var project = new PM.Project({
				project_lead_id: $np.find("#project_lead").val(),
				name: $np.find("#project_name").val(),
				notes: $np.find("#project_notes").val(),
				type: $np.find("#project_type").val(),
				status: $np.find("#project_status").val(),
				assigned_ids: $np.find("#project_assigned").val()
			});

			project.save(function(data) {
				if (data["err"]) {
					console.log(data["msg"]);
				}
				else {
					var filter = Projects.elements.$filterSelect.val();
					var relation = project.getUserRelationInt(session.user);
					
					if (filter == "0" || filter == relation) {
						Projects.printProject(project);
					}

					if ($(e.currentTarget).data("open")) {
						window.location = "./" + project.id;
					}

					$np.modal("toggle");
				}
			});
		}
	},
	data: {
		/** @type Array.<PM.Project> */
		projects: []
	},
	/**
	 * @param $selector
	 * @param {Array.<{}>} objects
	 * @param {boolean} multi
	 * @param {string|number} value_key
	 * @param {string|number} text_key
	 */
	setupModalSelector: function($selector, objects, multi, value_key, text_key) {
		var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
			'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

		if ($selector[0].selectize) {
			$selector[0].selectize.clear();
		}
		else {
			$selector.selectize({
				persist: false,
				maxItems: multi ? null : 1,
				valueField: "id",
				labelField: text_key,
				searchField: [text_key, value_key],
				options: objects,
				render: {
					item: function(item, escape) {
						return '<div>' +
							(item[text_key] ? '<span class="name">' + escape(item[text_key]) + '</span>' : '') +
							(item[value_key] ? '<span class="email">' + escape(item[value_key]) + '</span>' : '') +
							'</div>';
					},
					option: function(item, escape) {
						var label = item[text_key] || item[value_key];
						var caption = item[text_key] ? item[value_key] : null;
						return '<div>' +
							'<span class="label">' + escape(label) + '</span>' +
							(caption ? '<span class="caption">' + escape(caption) + '</span>' : '') +
							'</div>';
					}
				},
				createFilter: function(input) {
					var match, regex;

					if (!multi)
						return false;

					// email@address.com
					regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
					match = input.match(regex);
					if (match) return !this.options.hasOwnProperty(match[0]);

					// name <email@address.com>
					regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
					match = input.match(regex);
					if (match) return !this.options.hasOwnProperty(match[2]);

					return false;
				},
				create: function(input) {
					var temp = {};
					if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
						temp[value_key] = input;
						return temp;
					}
					var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
					if (match) {
						temp[value_key] = match[2];
						temp[text_key] = $.trim(match[1]);
						return temp;
					}
					alert('Invalid email address.');
					return false;
				}
			});
		}
	},
	bootstrapData: function() {
		this.data.projects = bootstrap_projects.map(function(project) {
			return new PM.Project(project);
		});
	},
	printProjects: function() {
		var projects = Projects.data.projects.sort(PM.Project.compare.desc.date);
		
		this.elements.$projectsListBody.html(projects.reduce(function(str, p) {
			return str + Util.parse.template(Projects.templates.table_row, {
				id: p.id,
				name: p.name,
				relation: p.getUserRelation(session.user),
				lead: PM.User.find(PM.data.users, p.project_lead_id).getFullName(),
				status: p.getStatusText(),
				date_lmod: Util.date.mdY(p.date_lmod)
			});
		}, ""));
	},
	/**
	 * @param {PM.Project} project
	 */
	printProject: function(project) {
		this.elements.$projectsListBody.prepend(Util.parse.template(Projects.templates.table_row, {
			id: project.id,
			name: project.name,
			relation: project.getUserRelation(session.user),
			lead: PM.User.find(PM.data.users, project.project_lead_id).getFullName(),
			status: project.getStatusText(),
			date_lmod: Util.date.mdY(project.date_lmod)
		}));
	}
};

$(document).ready(function() {
	Projects.load();
});