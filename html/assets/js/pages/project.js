Project = {
	load: function() {
		this.getStaticElements();
		this.setEventListeners();
		this.instanceFunctions();

		this.bootstrapData();
		this.printComments();
		this.printAttachments();
		this.setupDropzone();
	},
	elements: {
		$projectComments: undefined,
		$projectAttachments: undefined,
		$removeProject: undefined,
		$projectAddData: undefined,
		$dropzone: undefined
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
		this.elements.$dropzone = $("#attachment_upload");

		this.modals.$deleteProject = $("#delete_prj");
		this.modals.$addComment = $("#add_comment");
		this.modals.$addAttachment = $("#add_attachment");

		this.templates.comment = $("#template_comment").html();
		this.templates.file = $("#template_file").html();
	},
	setEventListeners: function() {
		this.elements.$removeProject.click(this.event_functions.deleteProject);
		this.elements.$projectAddData.click(this.event_functions.addData);
		this.elements.$projectAttachments.on("click", "[data-action='remove']", this.event_functions.removeAttachment);
		this.modals.$addComment.on("click", "#add_comment_button", this.event_functions.saveComment);
	},
	instanceFunctions: function() {
		PM.Comment.prototype.toTemplate = function() {
			return Util.parse.template(Project.templates.comment, {
				id: this.id,
				user_id: this.creator_id,
				name: PM.User.find(PM.data.users, this.creator_id).getFullName(),
				created: Util.date.FjY(this.date_created) + " @ " + Util.date.gis(this.date_created),
				created_relative: moment(this.date_created).fromNow(),
				text: this.text
			});
		};

		PM.Attachment.prototype.toTemplate = function() {
			return Util.parse.template(Project.templates.file, {
				id: this.id,
				name: this.getFullName(),
				size: this.sizeMin(1),
				type: this.extension,
				uploader: PM.User.find(PM.data.users, this.user_id).getFullName()
			});
		};
	},
	event_functions: {
		imgError: function() {
			if (!$(this).data("err")) {
				$(this).attr("src", "/assets/images/account/avatar.png").data("err", "true");
			}
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
				Project.elements.$dropzone[0].dropzone.removeAllFiles();

				Project.modals.$addAttachment.modal("toggle");
			}
		},
		removeAttachment: function(e) {
			var $row = $(e.currentTarget).parents(".attachment");

			var attachment_id = $row.data("id");

			var attachment = Project.data.attachments.find(function(a) {
				return a.id == attachment_id;
			});
			
			attachment.delete(function(data) {
				if (Util.clean.boolean(data["err"])) {
					alert(data["msg"]);
				}
				else {
					Project.data.attachments = Project.data.attachments.filter(function(a) {
						return a.id != attachment_id;
					});

					$row.remove();
				}
			});
		},
		saveComment: function() {
			var comment_text = Project.modals.$addComment.find("#comment").val();

			var comment = new PM.Comment({
				project_id: Project.data.project.id,
				creator_id: session.user.id,
				date_created: new Date(),
				text: comment_text
			});

			comment.save(function(data) {
				if (Util.clean.boolean(data["err"])) {
					alert(data["msg"]);
				}
				else {
					Project.data.comments.push(comment);

					Project.printComment(comment);

					Project.modals.$addComment.modal("toggle");
				}
			});
		}
	},
	data: {
		/** @type PM.Project */
		project: null,
		/** @type Array.<PM.Comment> */
		comments: [],
		/** @type Array.<PM.Attachment> */
		attachments: []
	},
	bootstrapData: function() {
		this.data.project = new PM.Project(bootstrap_project);

		this.data.comments = bootstrap_comments.map(function(comment) {
			return new PM.Comment(comment);
		});

		this.data.attachments = bootstrap_attachments.map(function(attachment) {
			return new PM.Attachment(attachment);
		});
	},
	printComments: function() {
		if (this.data.comments.length) {
			this.elements.$projectComments.html(this.data.comments.reduce(function(str, c) {
				return str + c.toTemplate();
			}, ""));

			this.elements.$projectComments.find("img").on("error", this.event_functions.imgError);
		}
	},
	/**
	 * @param {PM.Comment} comment
	 */
	printComment: function(comment) {
		var $comment = $(comment.toTemplate());

		$comment.prependTo(Project.elements.$projectComments);

		Project.elements.$projectComments.removeClass("empty");

		$comment.find("img").on("error", Project.event_functions.imgError);
	},
	printAttachments: function() {
		this.elements.$projectAttachments.html(this.data.attachments.reduce(function(str, a) {
			return str + a.toTemplate();
		}, ""));
	},
	/**
	 * @param {PM.Attachment} attachment
	 */
	printAttachment: function(attachment) {
		Project.elements.$projectAttachments.append(attachment.toTemplate());
	},
	createNewAttachment: function(file) {
		var attachment = new PM.Attachment({
			file_id: file["id"],
			user_id: session.user.id,
			project_id: Project.data.project.id,
			name: file["name"],
			extension: file["extension"],
			mime_type: file["mime_type"],
			size: file["size"],
			md5: file["md5"],
			original_name: file["name"],
			date_added: new Date()
		});

		attachment.save(function(data) {
			if (Util.clean.boolean("err")) {
				alert(data["msg"]);
			}
			else {
				Project.data.attachments.push(attachment);

				Project.printAttachment(attachment);
			}
		});
	},
	setupDropzone: function() {
		this.elements.$dropzone.dropzone({
			url: "/service/file/",
			method: "POST",
			success: function(file, data) {
				Project.createNewAttachment(data["file"]);

				Project.elements.$dropzone[0].dropzone.removeFile(file);
			},
			error: function(file, response) {
				console.log(file, response);

				var node, _i, _len, _ref, _results, message = "";

				var data = JSON.parse(file.xhr.responseText);

				if (Util.clean.boolean(data["err"], true)) {
					message = JSON.parse(file.xhr.responseText)["msg"];
				}

				file.previewElement.classList.add("dz-error");
				_ref = file.previewElement.querySelectorAll("[data-dz-errormessage]");
				_results = [];
				for (_i = 0, _len = _ref.length; _i < _len; _i++) {
					node = _ref[_i];
					_results.push(node.textContent = message);
				}
				return _results;
			}
		});
	}
};

$(document).ready(function() {
	Project.load();
});