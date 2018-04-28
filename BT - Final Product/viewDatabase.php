<?php
	session_start();
	if(!isset($_SESSION['right'])){
		$_SESSION['right'] = 99;
	}
	
	//Selected table
	$selectedTable = $_GET['view'];
	
	//Kết nối
	$connector = mysqli_connect('localhost', 'equallocal', '181196ABCDE!@#$%') or die('Could not connect: ' . mysql_error());
	mysqli_set_charset($connector,"utf8");
	//Chọn cơ sở dữ liệu
	$db_selected = mysqli_select_db($connector, 'user');
	
	$resultRight = mysqli_query($connector, 'SELECT * FROM `phanquyen` WHERE tenbang = "'.$selectedTable.'"');
	$right = mysqli_fetch_array($resultRight, MYSQLI_BOTH);
	$viewRight = $right['viewright'];
	if($viewRight < $_SESSION['right']){
		if($_SESSION['right'] == 99)
			header('Location: login.php?referer=view-page.php');
		else header('Location: view-page.php?error=0');
	}
	mysqli_free_result($resultRight);
?>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet"  type="text/css" href="../style/global.css">
		<style type="text/css">
			
		</style>
		<script type="text/javascript">
			function notification(message, type){
				var notif = document.getElementById('notification');
				notif.innerHTML = message;
				notif.className = '';
				if(type === 'success'){
					notif.classList.add('success');
				}
				if(type === 'error'){
					notif.classList.add('error');
				}
				if(type === 'warning'){
					notif.classList.add('warning');
				}
			}
			
			function findOrder(t){
				//Chuẩn bị request param
				var paramObject = {};
				paramObject['extra'] = false;
				paramObject['table'] = document.getElementById('tableName').value;
				paramObject['referer'] = document.getElementById('refererPage').value;
				paramObject['key'] = document.getElementById('uniqueColumn').value;
				
				var contentObject = {};
				var fieldRow = document.getElementById('rowMetadata');
				var columnName = fieldRow.getElementsByClassName('column-name');
				var columnType = fieldRow.getElementsByClassName('column-type');
				var dataRow = document.getElementById('rowInsert');
				var dataCell = dataRow.getElementsByClassName('insert-data');
				for(var cnt = 0; cnt < dataCell.length; cnt++){
					var columnData = {};
					columnData['name'] = columnName[cnt].value;
					columnData['type'] = columnType[cnt].value;
					dataCell[cnt].value = dataCell[cnt].value.trim();
					columnData['data'] = dataCell[cnt].value;
					contentObject['field' + cnt] = columnData;
				}
				paramObject['content'] = contentObject;
				var param = JSON.stringify(paramObject);
				
				//Bắt đầu request
				var request = new XMLHttpRequest();
				request.onreadystatechange = function() {
					if (this.readyState == 4) {
						var returnObject = JSON.parse(this.responseText);
						switch(this.status){
						case 200:
							notification(returnObject['message'], 'success');
							var allRow = document.getElementsByClassName('dataRow');
							while(allRow.length != 0){
								allRow[0].remove();
							}
							// Chuẩn bị hàng mới để thêm vào bảng
							var prototypeRow = document.getElementsByClassName('prototype')[0];
							prototypeRow.id = 0;
							for(let returnRow of returnObject['content']){
								var cloneRow = prototypeRow.cloneNode(true);
								var newId = parseInt(cloneRow.id);
								prototypeRow.id = (newId + 1);
								cloneRow.id = 'row' + newId;
								cloneRow.className = 'dataRow visibleRow';
								var cloneCell = cloneRow.getElementsByClassName('data-cell');
								var cnt = 0;
								for(let returnData of returnRow){
									cloneCell[cnt].innerHTML = returnData;
									cnt += 1;
								}
								prototypeRow.after(cloneRow);
							}
							break;
						case 400:
						case 401:
							notification(returnObject['message'], 'error');
							cloneRow.remove();
							break;
						}
					}
				};
				request.open('POST', 'findDatabaseHandle.php');
				request.setRequestHeader('Content-type', 'application/json');
				request.send(param);
			}
		</script>
	</head>
	<body>
		<button class="backButton" onclick="window.location.href = 'view-page.php'">← Quay lại</button>
		<?php
			// Input #0 - Tên bảng
			echo '<input type="hidden" id="tableName" value="'.$selectedTable.'">';
			echo '<input type="hidden" id="refererPage" value="manageDatabase.php">';
			
			##############################################################################################
			# Phần đầu của table
			echo '<div class="table-name">Sửa dữ liệu</div>';
			echo '<table border="1" width="100%" class="table-result" id="table-result">';
			echo '<caption id="notification"></caption>';
			
			##############################################################################################
			# Dòng số 1: Tên cột, dữ liệu về số hiệu cột và kiểu dữ liệu của cột
			echo '<tr id="rowMetadata">';

			$result = mysqli_query($connector, 'SELECT * FROM `'.$selectedTable.'`');
			$cresult = mysqli_query($connector, 'SELECT COUNT(*) FROM `'.$selectedTable.'`');
			$xresult = mysqli_query($connector, 'SELECT column_comment, extra FROM information_schema.columns WHERE table_name = "'.$selectedTable.'" AND table_schema = "user"');
			$columnCount = 0;
			if($result){
				while($fieldInfo = mysqli_fetch_field($result)){
					$fieldComment = mysqli_fetch_array($xresult, MYSQLI_BOTH);
					echo '<td class="column-cell">';
					
					echo $fieldComment[0];
					
					//Input #1 - Tên trường
					echo '<input class="column-name" type="hidden" value="'.$fieldInfo->name.'">';
					
					//Input #2 - Kiểu dữ liệu của trường
					echo '<input class="column-type" type="hidden" value="'.$fieldInfo->type.'">';
					
					echo '</td>';
					$columnCount += 1;
				}
				
				echo '</tr>';
				
			##############################################################################################
			# Dòng số 2: Các ô nhập liệu cho thao tác tìm kiếm và thêm
				echo '<tr id="rowInsert">';
				
				for($cnt = 0; $cnt < $columnCount; $cnt++){
					// Input #3 - Thêm trường
					echo '<td class="insert-cell">';
					
					echo '<input class="insert-data" type=text" placeholder="Nhập dữ liệu">';
					
					echo '</td>';
				}
				
				echo '</tr>';
				
			##############################################################################################
			# Dòng số 3: Chứa nút tìm kiếm
				echo '<tr id="rowAction">';
				echo '<td style="text-align: center" colspan="'.$columnCount.'">';
				echo '<button type="button" class="findButton largeButton" onclick="findOrder(this)">Tìm</button>';
				echo '</td>';
				echo '</tr>';
			##############################################################################################
			# Dòng số 4: Nguyên mẫu hàng, dùng để làm khuôn sao chép ra các hàng mới khi cần thiết
				$rowCount = mysqli_fetch_array($cresult, MYSQLI_BOTH)[0];
				echo '<tr class="prototype hiddenRow" id="'.$rowCount.'">';
					
				for($cnt = 0; $cnt < $columnCount; $cnt++){
					echo '<td><div class="data-cell"></div></td>';
				}
				
				echo '</tr>';
				
			##############################################################################################
			# Dòng 5+: Dữ liệu thực sự của bảng
				$rowCount -= 1;
				while($row = mysqli_fetch_array($result, MYSQLI_BOTH)){
					echo '<tr class="dataRow visibleRow" id="row'.$rowCount.'">';
					
					for($cnt = 0; $cnt < $columnCount; $cnt++){
						echo '<td><div class="data-cell">'.$row[$cnt].'</div></td>';
					}
					
					echo '</tr>';
					
					$rowCount -= 1;
				}
				
				echo '</table>';
				
			##############################################################################################
			# Phần chân của table
				mysqli_free_result($result);
				mysqli_free_result($cresult);
				mysqli_free_result($xresult);
			} else {
				echo 'Lỗi khi hiển thị';
			}
			
			mysqli_close($connector);
		?>
	</body>
</html>