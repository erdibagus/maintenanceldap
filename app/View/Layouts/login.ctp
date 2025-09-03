<html>
<head>
<title>Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
<?php 
	  echo $this->Html->meta('icon','/img/berno1.png'); 
	  echo $this->Html->css('util');
	  echo $this->Html->css('login');
	  echo $this->Html->script('sweatalert');
      echo $this->Html->css('/css/sweatalert');
?>
</head>
<body>
    <div class="limiter">
		<div class="container-login100" style="background-image: url('img/bg-01.jpg');">
			<div class="wrap-login100">
				<span class="login100-form-title p-b-48">
					<img src="img/logoBerno.png" class="img-fluid" alt="..." width="100%">
				</span>

				<div class="wrap-input100">
					<input class="input100 user" type="text">
					<span class="focus-input100" data-placeholder="Username"></span>
				</div>

				<div class="wrap-input100" data-validate="Enter password">
					<span class="btn-show-pass">
						<i class="zmdi zmdi-eye"></i>
					</span>
					<input class="input100 pass" type="text" autocomplete="new-password" name="pass" onkeyup="if(event.keyCode === 13){login()}">
					<span class="focus-input100" data-placeholder="Password"></span>
				</div>

				<div class="container-login100-form-btn">
					<div class="wrap-login100-form-btn">
						<div class="login100-form-bgbtn"></div>
						<button class="login100-form-btn btnLogin" onclick="login()">
							Login
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php 
	  echo $this->Html->script('jquery-3.3.1.min');
	  echo $this->Html->script('login');
	  echo $this->Html->script('h1login');
?>
</head>
</body>
</html>
