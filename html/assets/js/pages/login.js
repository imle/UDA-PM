Login = {
	load: function() {
		Login.getStaticElements();
		Login.setEventListeners();

		if (session) {
			session.user = new PM.User(session.user);

			if (session.user.id) {
				Login.elements.$reauthEmail.text(session.user.email);
				Login.elements.$inputEmail.val(session.user.email).hide();
				Login.elements.$useAnotherAccount.css("display", "block");
			}
		}
	},
	$el: undefined,
	elements: {
		$reauthEmail: undefined,
		$inputEmail: undefined,
		$inputPassword: undefined,
		$rememberMe: undefined,
		$submit: undefined,
		$useAnotherAccount: undefined,
		$errorArea: undefined,
		$togglePassword: undefined
	},
	templates: {
		$error: undefined
	},
	getStaticElements: function() {
		Login.$el = $("#login-form");

		Login.elements.$reauthEmail = Login.$el.find("#reauth-email");
		Login.elements.$inputEmail = Login.$el.find("#input-email");
		Login.elements.$inputPassword = Login.$el.find("#input-password");
		Login.elements.$rememberMe = Login.$el.find("#remember-me");
		Login.elements.$submit = Login.$el.find("#submit");
		Login.elements.$useAnotherAccount = Login.$el.find("#use-another-account");
		Login.elements.$errorArea = Login.$el.find("#error-message");
		Login.elements.$togglePassword = Login.$el.find("#toggle-password");

		Login.templates.error = $("#error-template").html();
	},
	setEventListeners: function() {
		Login.elements.$submit.click(Login.events.attemptLogin);

		Login.elements.$togglePassword.click(Login.events.togglePassword);

		Login.elements.$useAnotherAccount.click(Login.events.useAnotherAccount);
	},
	events: {
		attemptLogin: function(e) {
			e.preventDefault();

			Util.ajax.service("login", {
				email: Login.elements.$inputEmail.val(),
				password: Login.elements.$inputPassword.val(),
				remember: Login.elements.$rememberMe.prop("checked")
			}, function(data) {
				if (data["err"]) {
					Login.elements.$errorArea.html(Util.parse.template(Login.templates.error, {
						message: data["msg"]
					}));
				}
				else {
					localStorage.setItem("session", JSON.stringify({
						user: new PM.User(data["user"])
					}));

					window.location = data["request"];
				}
			});
		},
		togglePassword: function() {
			var type = Login.elements.$inputPassword.attr("type");

			if (type == "password") {
				Login.elements.$inputPassword.attr("type", "text");
			}
			else {
				Login.elements.$inputPassword.attr("type", "password")
			}
		},
		useAnotherAccount: function() {
			Login.elements.$reauthEmail.text("");
			Login.elements.$inputEmail.val("").show().change();
			Login.elements.$inputPassword.val("").change();
			Login.elements.$useAnotherAccount.hide();
		}
	}
};

$(document).ready(function() {
	Login.load();
});