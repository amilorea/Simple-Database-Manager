<?php
	session_start();
	if(!isset($_SESSION['right'])){
		$_SESSION['right'] = 99;
	}
	
	if(isset($_SESSION['user'])){
		header("Location: main-page.html"); 
	}
?>
<html>
	<head>
		<script type="text/javascript">
			function formTrap(){
				var username = document.forms["formLogin"].getElementsByTagName("div")[0].getElementsByTagName("input")[0].value;
				var password = document.forms["formLogin"].getElementsByTagName("div")[0].getElementsByTagName("input")[1].value;
				var errorDiv = document.forms["formLogin"].getElementsByTagName("div")[3];
				if(username.length == 0){
					errorDiv.innerHTML = "Nhập tên đăng nhập!";
					return false;
				}
				if(password.length == 0){
					errorDiv.innerHTML = "Nhập mật khẩu!";
					return false;
				}
				document.forms["formLogin"].submit();
				return true;
			}
			function formReset(){
				document.forms["formLogin"].getElementsByTagName("div")[0].getElementsByTagName("input")[0].value = '';
				document.forms["formLogin"].getElementsByTagName("div")[0].getElementsByTagName("input")[1].value = '';
			}
			function formExit(){
				window.location.href = "main-page.html";
			}
		</script>
		<style type="text/css">
			body {
				text-align: center;
			}
			.formLogin {
				border: 5px double #000;
				padding: 10px 30px 10px 30px;
				margin: 5px;
				background-color: #fafafa;
				display: inline-block;
				width: 500px;
			}
			.content {
				text-align: left;
				margin-left: 30px;
				margin-right: 30px;
			}
			.caption {
				display: inline-block;
				width: 150px;
			}
			.button {
				width: 100px;
			}
			.formError {
				font-weight: bold;
				color: #f00;
			}
		</style>
	</head>
	<body>
		<form class="formLogin" name="formLogin" action="loginHandle.php" method="POST">
			<h1>Đăng nhập</h1>
			<div class="content">
				<div class="caption">Username: </div>	<input type="text" name="username"><br>
				<div class="caption">Password: </div>	<input type="password" name="password"><br>
			</div>
			<div class="formError"></div>
			<?php
				if(isset($_GET['referer']))
					echo '<input type="hidden" class="referer" name="referer" value="'.$_GET['referer'].'">';
			?>
			<input class="button" type="button" value="OK" name="ok" onclick="formTrap()">
			<input class="button" type="button" value="Reset" name="reset" onclick="formReset()">
			<input class="button" type="button" value="Exit" name="exit" onclick="formExit()">
		</form>
	</body>
</html>