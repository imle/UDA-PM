Project = {
	load: function() {
		this.getStaticElements();
		this.setEventListeners();

		this.bootstrapData();
		this.printComments();
		this.printAttachments();
	},
	elements: {
		$projectComments: undefined,
		$projectAttachments: undefined,
		$removeProject: undefined,
		$projectAddData: undefined,
		$dropZone: undefined
	},
	templates: {
		comment: "",
		file: ""
	},
	modals: {
		$deleteProject: undefined,
		$addComment: undefined,
		$addAttachment: undefined
	},
	getStaticElements: function() {
		this.elements.$projectComments = $("#project_comments");
		this.elements.$projectAttachments = $("#project_attachments");
		this.elements.$removeProject = $("#remove_project");
		this.elements.$projectAddData = $("#project_add_data");
		this.elements.$dropZone = $("#drop_zone");

		this.modals.$deleteProject = $("#delete_prj");
		this.modals.$addComment = $("#add_comment");
		this.modals.$addAttachment = $("#add_attachment");

		this.templates.comment = $("#template_comment").html();
		this.templates.file = $("#template_file").html();
	},
	setEventListeners: function() {
		this.elements.$removeProject.click(this.event_functions.deleteProject);
		this.elements.$projectAddData.click(this.event_functions.addData);
	},
	event_functions: {
		imgError: function() {
			$(this).attr("src", "//ssl.gstatic.com/accounts/ui/avatar_2x.png");
		},
		deleteProject: function() {
			Project.data.project.delete(function(data) {
				if (data["err"]) {
					console.log(data["msg"]);
				}
				else {
					Project.modals.$deleteProject.modal("toggle");

					window.location = "../";
				}
			});
		},
		addData: function() {
			if (Project.elements.$projectComments.hasClass("active")) {
				Project.modals.$addComment.modal("toggle");
			}
			else if (Project.elements.$projectAttachments.hasClass("active")) {
				Project.modals.$addAttachment.modal("toggle");
			}
		}
	},
	data: {
		/** @type PM.Project */
		project: null,
		/** @type Array.<PM.Comment> */
		comments: [],
		/** @type Array.<PM.Attachment> */
		files: []
	},
	bootstrapData: function() {
		this.data.project = new PM.Project(bootstrap_project);

		this.data.comments = bootstrap_comments.map(function(comment) {
			return new PM.Comment(comment);
		});

		this.data.files = bootstrap_files.map(function(attachment) {
			return new PM.Attachment(attachment);
		});
	},
	printComments: function() {
		if (this.data.comments.length) {
			this.elements.$projectComments.html(this.data.comments.reduce(function(str, c) {
				return str + Util.parse.template(Project.templates.comment, {
						id: c.id,
						user_id: c.creator_id,
						name: PM.User.find(PM.data.users, c.creator_id).getFullName(),
						created: Util.date.FjY(c.date_created) + " @ " + Util.date.gis(c.date_created),
						created_relative: moment(c.date_created).fromNow(),
						text: c.text
					});
			}, ""));

			this.elements.$projectComments.find("img").on("error", this.event_functions.imgError);
		}
	},
	printAttachments: function() {
		this.elements.$projectAttachments.html(this.data.files.reduce(function(str, f) {
			return str + Util.parse.template(Project.templates.file, {
				id: f.id,
				name: f.getFullName(),
				size: f.sizeMin(1),
				type: f.extension,
				uploader: PM.User.find(PM.data.users, f.user_id).getFullName()
			});
		}, ""));
	},
	setupDropzone: function() {
		Project.elements.$dropZone.dropzone({
			url: "/service/file/post"
		});
	}
};

$(document).ready(function() {
	Project.load();
});