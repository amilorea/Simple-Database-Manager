<?php
	session_start();
	if(isset($_SESSION['user'])){
		header('Location:main-page.html'); 
	}
 
	$username = $_POST["username"];
	$password = md5($_POST["password"]);
	
	//Connect
	$connector = mysqli_connect('localhost', 'equallocal', '181196ABCDE!@#$%') or die('Could not connect: ' . mysql_error());
	//Pick database
	$db_selected = mysqli_select_db($connector, 'user');
	//Select
	if($result = mysqli_query($connector, 'SELECT * FROM `user` WHERE username="' .$username .'" AND password="' .$password .'"')){
		if(mysqli_num_rows($result) == 1){
			$userinfo = mysqli_fetch_array($result, MYSQLI_BOTH);
			mysqli_free_result($result);
			mysqli_close($connector);
			$_SESSION['user'] = $_POST["username"];
			$_SESSION['right'] = $userinfo['userright'];
			echo '<script type="text/javascript">';
			echo 'function func(){';
			echo 'var iframe = parent.document.getElementById("left-frame");';
			echo 'iframe.src = iframe.src; window.location.href = ';
			if(isset($_POST['referer']))
				echo '"'.$_POST['referer'].'"';
			else echo '"main-page.html"';
			echo ';}';
			echo 'func()</script>';
		} else {
			mysqli_free_result($result);
			mysqli_close($connector);
			echo 'Login refused';
		}
	}
?>