<?php
	session_start();
	if(!isset($_SESSION['right'])){
		$_SESSION['right'] = 99;
	}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet"  type="text/css" href="../style/global.css">
	<style type="text/css">
		body {
			text-align: left;
			overflow-x: hidden;
			overflow-y: visible;
		}
		.button {
			position: relative;
			right: -10px;
			display: inline-block;
			width: 100%;
			font-weight: bold;
			text-align: center;
			vertical-align: middle;
			font-family: 'Arial', Times, serif;
			font-size: 18px;
			box-shadow: 0px 0px 2px 2px gray;
			border-radius: 5px 0px 0px 5px;
			background-color: #eee;
			background-image: url('../img/noise.png');
			margin: 5px 2px 5px 2px;
			padding: 3px;
			cursor: pointer;
			transition: all 0.3s ease;
			-webkit-transition: all 0.3s ease;
			-o-transition: all 0.3s ease;
			-moz-transition: all 0.3s ease;
			-ms-transition: all 0.3s ease;
		}
		.button:hover {
			background-color: #fff;
			right: -3px;
		}
	</style>
</head>
<body>
	<?php
		if(!isset($_SESSION['right'])){
			$_SESSION['right'] = 99;
		}
		echo '<div style="word-wrap: break-word;">';
		if(isset($_SESSION['user'])) echo 'Xin chào <b>'.$_SESSION['user'].'</b><br>';
		else 'Xin chào <b>khách viếng thăm</b><br>';
		if(isset($_SESSION['right'])) echo 'Cấp bậc của bạn: <b>'.$_SESSION['right'].'</b><br>';
		echo '</div><hr>';
	?>
	<a href="main-page.html" target="middle-frame"><div class="button">Trang chính</div></a><br>
	<?php
		if($_SESSION['right'] == 99){
			echo '<a href="login.php" target="middle-frame"><div class="mini button">Đăng nhập</div></a>';
		} else {
			echo '<a href="logoutHandle.php" target="middle-frame"><div class="mini button">Đăng xuất</div></a>';
			echo '<a href="view-page.php" target="middle-frame"><div class="button">Xem dữ liệu</div></a><br>';
			echo '<a href="manage-page.php" target="middle-frame"><div class="button">Điều chỉnh dữ liệu</div></a><br>';
		}
	?>
</body>
</html>