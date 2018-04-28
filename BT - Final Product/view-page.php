<?php
	session_start();
	if(!isset($_SESSION['right'])){
		$_SESSION['right'] = 99;
	}
?>
<!DOCTYPE html>
<html style="height: 100%">
	<head>
		<title>View Page</title>
		<meta charset="UTF-8">
		<link rel="stylesheet"  type="text/css" href="../style/global.css">
		<style type="text/css">
			body {
				text-align: center;
			}
		</style>
	</head>
	<body>
		<form class="formView" name="formView" action="viewDatabase.php" method="GET">
			<h1>Xem cơ sở dữ liệu</h1>
			<?php
				if(isset($_GET['error'])){
					switch($_GET['error']){
					case 0: echo '<div class="error">Bạn không có quyền truy cập vào bảng này.</div>';
					}
				}
				//Kết nối cơ sở dữ liệu
				$connector = mysqli_connect('localhost', 'equallocal', '181196ABCDE!@#$%') or die('Could not connect: ' . mysql_error());
				$db_selected = mysqli_select_db($connector, 'user');
				mysqli_set_charset($connector,"utf8");
				//Thành lập query
				$result = mysqli_query($connector, 'SHOW TABLES FROM user');
				while($table = mysqli_fetch_array($result, MYSQLI_BOTH)){
					echo '<button class="selectTable" type="submit" name="view" value="'.$table[0].'">'.$table[0].'</button>';
				}
				
				mysqli_free_result($result);
				mysqli_close($connector);
			?>
		</form>
	</body>
</html>