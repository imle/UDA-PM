Util = {
	templates: {
		option: "<option value=\"<%= value %>\"<% print(selected ? ' selected' : '') %>"
				+ "<% print(disabled ? ' disabled' : '') %>><%= text %></option>"
	},
	parse: {
		template: function(t, d) {
			return _.template(t)(d);
		}
	},
	generate: {
		/**
		 * @param {Array.<{}>} objects
		 * @param {string|number} [value_key]
		 * @param {string|number} [text_key]
		 * @param {string|number} [selected]
		 * @returns {string}
		 */
		dropdown: function(objects, value_key, text_key, selected) {
			if (!Array.isArray(objects))
				throw new TypeError("Invalid type");

			if (!value_key && !text_key) {
				return objects.reduce(function(str, row, i) {
					return str + Util.parse.template(Util.templates.option, {
						value: i,
						disabled: i == "-1",
						selected: i == value_key,
						text: row
					});
				}, "");
			}
			else {
				return objects.reduce(function(str, row) {
					return str + Util.parse.template(Util.templates.option, {
						value: row[value_key],
						disabled: row[value_key] == "-1" ? " disabled" : "",
						selected: row[value_key] == selected ? " selected" : "",
						text: typeof row[text_key] == "function" ? row[text_key]() : row[text_key]
					});
				}, "");
			}
		}
	},
	compare: {
		date: {
			daysBetween: function(d1, d2) {
				return Math.round(Math.abs((d1.getTime() - d2.getTime()) / Util.data.date.ms_in_day));
			},
			/**
			 * Returns 0 if dates are equal up to the day otherwise returns a
			 * negative number if d1 < d2 or a positive number if d1 > d2.
			 * @param {Date} d1
			 * @param {Date} d2
			 * @returns {number}
			 */
			day: function(d1, d2) {
				if (d1.getFullYear() == d2.getFullYear()) {
					if (d1.getMonth() == d2.getMonth())
						return d1.getDate() - d2.getDate();

					return Util.compare.date.month(d1, d2);
				}

				return Util.compare.date.year(d1, d2);
			},
			/**
			 * Returns 0 if dates are equal up to the month otherwise returns a
			 * negative number if d1 < d2 or a positive number if d1 > d2.
			 * @param {Date} d1
			 * @param {Date} d2
			 * @returns {number}
			 */
			month: function(d1, d2) {
				return (d1.getFullYear() * 12 + d1.getMonth()) - (d2.getFullYear() * 12 + d2.getMonth());
			},
			/**
			 * Returns 0 if dates are equal up to the year otherwise returns a
			 * negative number if d1 < d2 or a positive number if d1 > d2.
			 * @param {Date} d1
			 * @param {Date} d2
			 * @returns {number}
			 */
			year: function(d1, d2) {
				return d1.getFullYear() - d2.getFullYear();
			}
		}
	},
	date: {
		/**
		 * @param {Date} d
		 * @returns {string}
		 */
		His: function(d) {
			var hour = d.getHours() + "";
			hour = hour.length == 1 ? "0" + hour : hour;
			var min = d.getMinutes() + "";
			min = min.length == 1 ? "0" + min : min;
			var sec = d.getSeconds() + "";
			sec = sec.length == 1 ? "0" + sec : sec;
			return hour + ":" + min + ":" + sec;
		},
		/**
		 * @param {Date} d
		 * @returns {string}
		 */
		gis: function(d) {
			var hour = d.getHours();
			var min = d.getMinutes();
			var sec = d.getSeconds();
			var period = hour >= 12 ? 'pm' : 'am';

			hour = hour % 12;
			hour = hour ? hour : 12;

			min = min < 10 ? '0' + min : min;
			sec = sec < 10 ? '0' + sec : sec;

			return hour + ':' + min + ':' + sec  + ' ' + period;
		},
		/**
		 * @param {Date} d
		 * @param {string} [sep]
		 * @returns {string}
		 */
		Ymd: function(d, sep) {
			sep = typeof sep === "undefined" ? "-" : sep;
			var day = d.getDate(),
				mon = d.getMonth() + 1;
			return d.getFullYear() + sep + (mon < 10 ? "0" + mon : mon) + sep + (day < 10 ? "0" + day : day);
		},
		/**
		 * @param {Date} d
		 * @param {string} [sep]
		 * @returns {string}
		 */
		mdY: function(d, sep) {
			sep = typeof sep === "undefined" ? "-" : sep;
			var day = d.getDate(),
				mon = d.getMonth() + 1;
			return (mon < 10 ? "0" + mon : mon) + sep + (day < 10 ? "0" + day : day) + sep + d.getFullYear();
		},
		/**
		 * @param {Date} d
		 * @returns {string}
		 */
		FjY: function(d) {
			return Util.data.date.month.long[d.getMonth()] + " " + d.getDate() + ", " + d.getFullYear();
		},
		/**
		 * @param {Date} d
		 * @returns {string}
		 */
		YmdHis: function(d) {
			return Util.date.Ymd(d) + " " + Util.date.His(d);
		}
	},
	data: {
		date: {
			ms_in_day: 86400000,
			ms_in_hour: 3600000,
			ms_in_second: 60000,
			month: {
				long: [
					"January", "February", "March", "April", "May", "June",
					"July", "August", "September", "October", "November", "December"
				],
				short: ["Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec"]
			},
			week: {
				long: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
				short: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
				letter: ["S", "M", "T", "W", "T", "F", "S"]
			}
		},
		validate: {
			/**
			 * @param {Date} date
			 * @returns {boolean}
			 */
			date: function(date) {
				return Object.prototype.toString.call(date) === "[object Date]" ? !isNaN(date.getTime()) : false;
			},
			/**
			 * @param {number|string|boolean} value
			 * @returns {boolean}
			 */
			integer: function(value) {
				var x;
				if (isNaN(value))
					return false;

				x = parseFloat(value);
				return (x | 0) === x;
			}
		},
		booleans: {
			true: ["1", "true", "on", "yes", 1, true],
			false: ["0", "false", "off", "no", "", 0, false]
		}
	},
	clean: {
		/**
		 * @param {*} obj
		 * @param {{}} [def]
		 * @returns {{}}
		 */
		object: function(obj, def) {
			return obj !== null && typeof obj === "object" ? obj : (typeof def === "object" ? def : {});
		},
		/**
		 * @param func
		 * @param {function} [def]
		 * @returns {function}
		 */
		func: function(func, def) {
			return typeof func === "function" ? func : (typeof def === "function" ? def : function() {});
		},
		/**
		 * @param date
		 * @param {Date} [def]
		 * @returns {Date}
		 */
		date: function(date, def) {
			date = new Date(date);
			return Util.data.validate.date(date) ? date : (Util.data.validate.date(def) ? def : new Date());
		},
		/**
		 * @param str
		 * @param {string} [def]
		 * @returns {string}
		 */
		string: function(str, def) {
			return (str || def || "") + "";
		},
		/**
		 * @param bool
		 * @param {boolean} [def]
		 * @returns {boolean}
		 */
		boolean: function(bool, def) {
			if (Util.data.booleans.true.indexOf(bool) !== -1) {
				return true;
			}
			else if (Util.data.booleans.false.indexOf(bool) !== -1) {
				return false;
			}
			else {
				return def || false;
			}
		},
		/**
		 * @param int
		 * @param {number} [def]
		 * @returns {number}
		 */
		integer: function(int, def) {
			return Util.data.validate.integer(int) ? parseInt(int, 10) : (def || 0);
		}
	},
	ajax: {
		/**
		 * @param {object} obj
		 * @param {string} [prefix]
		 * @returns {string}
		 */
		_paramify: function(obj, prefix) {
			var str = [];

			for (var p in obj) {
				if (obj.hasOwnProperty(p)) {
					var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
					if (Util.data.validate.date(v))
						str.push(encodeURIComponent(k) + "=" + encodeURIComponent(Util.date.YmdHis(v)));

					else if (typeof v == "object")
						str.push(Util.ajax._paramify(v, k));

					else
						str.push(encodeURIComponent(k) + "=" + encodeURIComponent(v));
				}
			}

			return str.join("&");
		},
		/**
		 * Request information from server.
		 * @param {string} data_type
		 * @param {object} [options]
		 * @param {function} [callback]
		 * @returns {jQuery.ajax}
		 */
		request: function(data_type, options, callback) {
			return Util.ajax._post("request", data_type, options, callback);
		},
		/**
		 * Send information to server.
		 * @param {string} data_type
		 * @param {object} [options]
		 * @param {function} [callback]
		 * @returns {jQuery.ajax}
		 */
		submit: function(data_type, options, callback) {
			return Util.ajax._post("submit", data_type, options, callback);
		},
		/**
		 * Send information to server.
		 * @param {string} data_path
		 * @param {object} [options]
		 * @param {function} [callback]
		 * @returns {jQuery.ajax}
		 */
		service: function(data_path, options, callback) {
			return Util.ajax._request("POST", "/service/" + data_path, options, {}, callback);
		},
		/**
		 * Post data to server.
		 * @param {string} post_type
		 * @param {string} data_type
		 * @param {object|function} [options]
		 * @param {function} [callback]
		 * @returns {jQuery.ajax}
		 */
		_post: function(post_type, data_type, options, callback) {
			if (["submit", "request"].indexOf(post_type) === -1)
				throw {
					message: "Post type is invalid",
					name: "Invalid Input Exception"
				};

			if (typeof options === "function") {
				callback = options;
				options = {};
			}

			if (typeof options !== "object")
				options = {};

			if (typeof callback !== "function")
				callback = function() {};

			options["REQUEST_NAME"] = data_type.toString().toUpperCase();

			return Util.ajax._request("POST", "/service/" + post_type + ".php", options, {}, callback);
		},
		/**
		 * @param {string} type
		 * @param {string} path
		 * @param {object} obj
		 * @param {object} data
		 * @param {function} [callback]
		 * @private
		 */
		_request: function(type, path, obj, data, callback) {
			callback = typeof callback === "function" ? callback : function() {};

			var params = Util.ajax._paramify(data);
			path = path + "/" + (params ? "?" + params : "");
			path = path.replace(/\/\/+/g, '\/');

			var request = new XMLHttpRequest();
			request.open(type, path, true);
			request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");

			request.onload = function() {
				if (request.status >= 200 && request.status < 400) {
					callback(JSON.parse(request.responseText));
				} else {
					callback({
						err: true,
						msg: "The server encountered an error. If the problem persists, please check back later."
					});
				}
			};

			request.onerror = function() {
				callback({
					err: true,
					msg: "Please check your internet connection and try again."
				});
			};

			request.send(Util.ajax._paramify(obj));

			return request;
		}
	},
	REST: {
		/**
		 * @param {string} path
		 * @param {object|function} [data]
		 * @param {function} [callback]
		 */
		GET: function(path, data, callback) {
			if (typeof data == "function") {
				callback = data;
				data = {};
			}

			Util.ajax._request("GET", "/api/v1/" + path, {}, data, callback);
		},
		/**
		 * @param {string} path
		 * @param {object} obj
		 * @param {function} [callback]
		 */
		POST: function(path, obj, callback) {
			Util.ajax._request("POST", "/api/v1/" + path, obj, {}, callback);
		},
		/**
		 * @param {string} path
		 * @param {object} obj
		 * @param {function} [callback]
		 */
		PUT: function(path, obj, callback) {
			Util.ajax._request("PUT", "/api/v1/" + path, obj, {}, callback);
		},
		/**
		 * @param {string} path
		 * @param {object|function} [obj]
		 * @param {object|function} [data]
		 * @param {function} [callback]
		 */
		DELETE: function(path, obj, data, callback) {
			if (typeof data == "function") {
				callback = data;
				data = {};
			}

			if (typeof obj == "function") {
				callback = obj;
				obj = {};
				data = {};
			}

			Util.ajax._request("DELETE", "/api/v1/" + path, obj, data, callback);
		}
	}
};

Dropzone.autoDiscover = false;


var session = JSON.parse(localStorage.getItem("session"));


PM = {
	data: {
		/** @type Array.<PM.User> */
		users: []
	}
};

/**
 * @param data
 * @constructor
 * @template P
 */
PM.Model = function(data) {
	data = Util.clean.object(data);

	this.id = Util.clean.integer(data["id"]);
};

/**
 * @param {string} path
 * @param {function} [callback]
 */
PM.Model.prototype.save = function(path, callback) {
	path = Util.parse.template(path, this);

	if (!this.id) {
		Util.REST.POST(path, this.toJSON(), function(data) {
			Util.clean.func(callback)(data);
		});
	}
	else {
		Util.REST.PUT(path + "/" + this.id + "/", this.toJSON(), function(data) {
			Util.clean.func(callback)(data);
		});
	}
};

// /**
//  * @param {string} path
//  * @param {function} [callback]
//  */
PM.Model.prototype.delete = function(path, callback) {
	path = Util.parse.template(path, this);

	path = path + "/" + this.id + "/";

	Util.REST.DELETE(path, function(data) {
		Util.clean.func(callback)(data);
	});
};

/**
 * @param {string} path
 * @param {{}} options
 * @param {function} callback
 */
PM.Model.get = function(path, options, callback) {
	options = Util.clean.object(options);

	options = {
		limit: Util.clean.integer(options.limit, 30),
		offset: Util.clean.integer(options.offset, 0),
		properties: Util.clean.object(options.properties)
	};

	path = Util.parse.template(path, options.properties);

	Util.REST.GET(path, function(data) {
		Util.clean.func(callback)(data);
	});
};

PM.Model.prototype.toJSON = function() {
	return {
		id: this.id
	};
};


/**
 * @param data
 * @constructor
 * @template P
 */
PM.User = function(data) {
	data = Util.clean.object(data);

	PM.Model.call(this, data);

	this.name_first = Util.clean.string(data["name_first"]);
	this.name_last = Util.clean.string(data["name_last"]);
	this.email = Util.clean.string(data["email"]);
	this.type = Util.clean.integer(data["type"], 3);
};

PM.User.prototype = Object.create(PM.Model.prototype);
PM.User.prototype.constructor = PM.User;

/**
 * @param {Array.<PM.User>} users
 * @param {number} id
 * @returns PM.User
 */
PM.User.find = function(users, id) {
	return _.find(users, function(user) {
			return user.id == id;
		}) || new PM.User();
};

PM.User.path = "/users/";

PM.User.prototype.save = function(path, callback) {
	if (typeof path === "function") {
		callback = typeof path === "function" ? path : callback;
		path = "";
	}

	PM.Model.prototype.save.call(this, path || PM.User.path, callback);
};

PM.User.prototype.toJSON = function() {
	var obj = PM.Model.prototype.toJSON.call(this);

	obj.name_first = this.name_first;
	obj.name_last = this.name_last;
	obj.email = this.email;
	obj.type = this.type;

	return obj;
};

PM.User.prototype.getFullName = function() {
	return this.name_first + " " + this.name_last;
};

PM.User.compare = {
	/**
	 * @param {PM.User} a
	 * @param {PM.User} b
	 * @returns {number}
	 */
	lastFirst: function(a, b) {
		return a.name_last.localeCompare(b.name_last) || a.name_first.localeCompare(b.name_first);
	},
	/**
	 * @param {PM.User} a
	 * @param {PM.User} b
	 * @returns {number}
	 */
	firstLast: function(a, b) {
		return a.name_first.localeCompare(b.name_first) || a.name_last.localeCompare(b.name_last);
	}
};


/**
 * @param data
 * @constructor
 * @template P
 */
PM.Project = function(data) {
	data = Util.clean.object(data);

	PM.Model.call(this, data);

	this.user_created_id = Util.clean.integer(data["user_created_id"]);
	this.user_lmod_id = Util.clean.integer(data["user_lmod_id"]);
	this.project_lead_id = Util.clean.integer(data["project_lead_id"]);

	this.name = Util.clean.string(data["name"]);
	this.notes = Util.clean.string(data["notes"]);
	this.date_created = Util.clean.date(data["date_created"]);
	this.date_lmod = Util.clean.date(data["date_lmod"]);
	this.type = Util.clean.integer(data["type"]);
	this.status = Util.clean.string(data["status"]);
	this.assigned_ids = Util.clean.string(data["assigned_ids"]).split(",").filter(function(v) {
		return !!v;
	}).map(Number);
};

PM.Project.prototype = Object.create(PM.Model.prototype);
PM.Project.prototype.constructor = PM.Project;

PM.Project.path = "/projects/";

PM.Project.prototype.save = function(path, callback) {
	if (typeof path === "function") {
		callback = typeof path === "function" ? path : callback;
		path = "";
	}

	PM.Model.prototype.save.call(this, path || PM.Project.path, (function(data) {
		if (!data["err"]) {
			this.constructor(data["project"]);
		}

		callback(data);
	}).bind(this));
};

/**
 * @param {function} callback
 */
PM.Project.prototype.delete = function(callback) {
	PM.Model.prototype.delete.call(this, PM.Project.path, callback);
};

PM.Project.prototype.toJSON = function() {
	var obj = PM.Model.prototype.toJSON.call(this);

	obj.user_created_id = this.user_created_id;
	obj.user_lmod_id = this.user_lmod_id;
	obj.project_lead_id = this.project_lead_id;
	obj.name = this.name;
	obj.notes = this.notes;
	obj.date_created = Util.date.YmdHis(this.date_created);
	obj.date_lmod = Util.date.YmdHis(this.date_lmod);
	obj.type = this.type;
	obj.status = this.status;
	obj.assigned_ids = this.assigned_ids;

	return obj;
};

PM.Project.prototype.getStatusText = function() {
	return PM.Project.status_strings[this.status];
};

/**
 * @param {PM.User} user
 * @returns {number}
 */
PM.Project.prototype.getUserRelationInt = function(user) {
	if (this.project_lead_id == user.id)
		return 2;

	if (this.user_created_id == user.id)
		return 3;

	if (this.isUserAssigned(user))
		return 1;

	return 0;
};

/**
 * @param {PM.User} user
 * @returns {string}
 */
PM.Project.prototype.getUserRelation = function(user) {
	return ["Lead", "Creator", "Assigned", "None"][this.getUserRelationInt(user)];
};

/**
 * @param {PM.User} user
 * @returns {boolean}
 */
PM.Project.prototype.isUserAssigned = function(user) {
	return this.assigned_ids.indexOf(user.id) !== -1;
};

/**
 * @param {{}} options
 * @param {string|function(P=)} [path]
 * @param {function(P=)} callback
 */
PM.Project.get = function(options, path, callback) {
	if (typeof path === "function") {
		callback = path;
		path = "";
	}

	PM.Model.get.call(this, path || PM.Project.path, options, function(data) {
		callback(data["projects"].map(function(p) {
			return new PM.Project(p);
		}));
	});
};

PM.Project.compare = {
	asc: {
		/**
		 * @param {PM.Project} a
		 * @param {PM.Project} b
		 * @returns {number}
		 */
		date: function(a, b) {
			return a.date_lmod.getTime() - b.date_lmod.getTime();
		},
		/**
		 * @param {PM.Project} a
		 * @param {PM.Project} b
		 * @returns {number}
		 */
		name: function(a, b) {
			return a.name.localeCompare(b.name);
		}
	},
	desc: {
		/**
		 * @param {PM.Project} a
		 * @param {PM.Project} b
		 * @returns {number}
		 */
		date: function(a, b) {
			return b.date_lmod.getTime() - a.date_lmod.getTime();
		},
		/**
		 * @param {PM.Project} a
		 * @param {PM.Project} b
		 * @returns {number}
		 */
		name: function(a, b) {
			return b.name.localeCompare(a.name);
		}
	}
};




/**
 * @param data
 * @constructor
 * @template P
 */
PM.Comment = function(data) {
	data = Util.clean.object(data);

	PM.Model.call(this, data);

	this.project_id = Util.clean.integer(data["project_id"]);
	this.creator_id = Util.clean.integer(data["creator_id"]);
	this.date_created = Util.clean.date(data["date_created"]);
	this.text = Util.clean.string(data["text"]);
};

PM.Comment.prototype = Object.create(PM.Model.prototype);
PM.Comment.prototype.constructor = PM.Comment;

PM.Comment.path = "/projects/<%= project_id %>/comments/";

PM.Comment.prototype.save = function(path, callback) {
	if (typeof path === "function") {
		callback = typeof path === "function" ? path : callback;
		path = "";
	}

	PM.Model.prototype.save.call(this, path || PM.Comment.path, callback);
};

PM.Comment.prototype.toJSON = function() {
	var obj = PM.Model.prototype.toJSON.call(this);

	obj.project_id = this.project_id;
	obj.creator_id = this.creator_id;
	obj.date_created = Util.date.YmdHis(this.date_created);
	obj.text = this.text;

	return obj;
};




/**
 * @param data
 * @constructor
 * @template P
 */
PM.Attachment = function(data) {
	data = Util.clean.object(data);

	PM.Model.call(this, data);

	this.file_id = Util.clean.integer(data["file_id"]);
	this.user_id = Util.clean.integer(data["user_id"]);
	this.project_id = Util.clean.integer(data["project_id"]);
	this.name = Util.clean.string(data["name"]);
	this.extension = Util.clean.string(data["extension"]);
	this.mime_type = Util.clean.string(data["mime_type"]);
	this.size = Util.clean.integer(data["size"]);
	this.md5 = Util.clean.string(data["md5"]);
	this.original_name = Util.clean.string(data["original_name"]);
	this.date_added = Util.clean.date(data["date_added"]);
};

PM.Attachment.prototype = Object.create(PM.Model.prototype);
PM.Attachment.prototype.constructor = PM.Attachment;

PM.Attachment.path = "/projects/<%= project_id %>/attachments/";
PM.Attachment.file_size_base = 1000;

PM.Attachment.prototype.save = function(path, callback) {
	if (typeof path === "function") {
		callback = typeof path === "function" ? path : callback;
		path = "";
	}

	PM.Model.prototype.save.call(this, path || PM.Attachment.path, (function(data) {
		if (!data["err"]) {
			this.constructor(data["attachment"]);
		}

		callback(data);
	}).bind(this));
};

/**
 * @param {function} callback
 */
PM.Attachment.prototype.delete = function(callback) {
	PM.Model.prototype.delete.call(this, PM.Attachment.path, callback);
};

PM.Attachment.prototype.toJSON = function() {
	var obj = PM.Model.prototype.toJSON.call(this);

	obj.file_id = this.file_id;
	obj.user_id = this.user_id;
	obj.project_id = this.project_id;
	obj.name = this.name;
	obj.extension = this.extension;
	obj.mime_type = this.mime_type;
	obj.size = this.size;
	obj.md5 = this.md5;
	obj.original_name = this.original_name;
	obj.date_added = Util.date.YmdHis(this.date_added);

	return obj;
};

PM.Attachment.prototype.getFullName = function() {
	return (this.name || this.original_name) + "." + this.extension;
};

/**
 * @param {string} format
 * @param {number} [fix]
 * @returns {string}
 */
PM.Attachment.prototype.sizeAs = function(format, fix) {
	format = format.toLowerCase();

	var size = this.size;

	//noinspection FallThroughInSwitchStatementJS
	switch (format) {
		case "gb":
			size = size / PM.Attachment.file_size_base;
		case "mb":
			size = size / PM.Attachment.file_size_base;
		case "kb":
			size = size / PM.Attachment.file_size_base;
		case "b":
			return size.toFixed(fix || 2);
		default:
			throw new Error("Invalid format.");
	}
};

/**
 * @param {number} [fix]
 * @returns {string}
 */
PM.Attachment.prototype.sizeMin = function(fix) {
	var size = this.size;

	if (size < PM.Attachment.file_size_base)
		return size.toFixed(fix || 2) + " B";

	size = size / PM.Attachment.file_size_base;

	if (size < PM.Attachment.file_size_base)
		return size.toFixed(fix || 2) + " KB";

	size = size / PM.Attachment.file_size_base;

	if (size < PM.Attachment.file_size_base)
		return size.toFixed(fix || 2) + " MB";

	size = size / PM.Attachment.file_size_base;

	return size.toFixed(fix || 2) + " GB";
};