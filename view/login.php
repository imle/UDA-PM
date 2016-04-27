<div id="login-form" class="card">
	<div id="profile-img"></div>
	<p id="profile-name"></p>
	<div id="error-message"></div>
	<form>
		<span id="reauth-email"></span>
		<input type="email" id="input-email" class="form-control" placeholder="Email address" autofocus>
		<div class="input-group">
			<input type="password" class="form-control" placeholder="Password" id="input-password">
			<span id="toggle-password" class="input-group-addon" data-plain="false">Show</span>
		</div>
		<div id="remember" class="checkbox">
			<label>
				<input id="remember-me" type="checkbox" checked> Remember me
			</label>
		</div>
		<button id="submit" class="btn btn-primary" type="submit">Sign in</button>
	</form>
	<a href="#" id="forgot-password">Forgot password?</a>
	<a id="use-another-account">Use a Different Account</a>
</div>

<script type="text/template" id="error-template">
	<p class="alert alert-danger"><%= message %></p>
</script>

<script type="text/javascript" src="/assets/js/pages/login.js"></script>