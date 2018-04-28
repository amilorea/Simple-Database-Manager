<?php
	session_start();
	if(!isset($_SESSION['right'])){
		$_SESSION['right'] = 99;
	}
	//Chọn bảng
	$selectedTable = $_GET['view'];

	//Kết nối
	$connector = mysqli_connect('localhost', 'equallocal', '181196ABCDE!@#$%') or die('Could not connect: ' . mysql_error());
	//Đặt charset
	mysqli_set_charset($connector, 'utf8');

	//Chọn cơ sở dữ liệu
	$db_selected = mysqli_select_db($connector, 'user');

	$resultRight = mysqli_query($connector, 'SELECT * FROM `phanquyen` WHERE tenbang = "'.$selectedTable.'"');
	$right = mysqli_fetch_array($resultRight, MYSQLI_BOTH);
	$viewRight = $right['viewright'];
	$insertRight = $right['insertright'];
	$deleteRight = $right['deleteright'];
	$updateRight = $right['updateright'];
	if($viewRight < $_SESSION['right']){
		if($_SESSION['right'] == 99)
			header('Location: login.php?referer=manage-page.php');
		else header('Location: manage-page.php?error=0');
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
		
			function editOrder(t, num){
				var removeButton = t.nextElementSibling;
				var cancelButton = removeButton.nextElementSibling;
				
				//Kích hoạt nút hủy
				cancelButton.removeAttribute('disabled');
				cancelButton.style['display'] = 'inline-block';
				
				//Thay đổi nút sửa thành nút lưu
				t.attributes['class'].value = 'saveButton';
				t.attributes['onclick'].value = 'saveOrder(this, ' + num + ')';
				t.innerHTML = 'Lưu';
				
				var selectedRow = document.getElementById('row' + num).getElementsByTagName('td');
				for(var i = 0; i < selectedRow.length - 1; i++){
					//Input #6 - Nội dung sửa
					var ele = document.createElement('input');
					ele.setAttribute('class', 'update-data');
					ele.setAttribute('type', 'text');
					selectedRow[i].appendChild(ele);
				}
			}
			function saveOrder(t, num){
				var removeButton = t.nextElementSibling;
				var cancelButton = removeButton.nextElementSibling;
				
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
				var dataRow = document.getElementById('row' + num);
				var dataCell = dataRow.getElementsByClassName('data-cell');
				var updateCell = dataRow.getElementsByClassName('update-data');
				for(var cnt = 0; cnt < dataCell.length; cnt++){
					var columnData = {};
					columnData['name'] = columnName[cnt].value;
					columnData['type'] = columnType[cnt].value;
					columnData['data'] = dataCell[cnt].innerHTML;
					updateCell[cnt].value = updateCell[cnt].value.trim();
					columnData['update'] = updateCell[cnt].value;
					contentObject['field' + cnt] = columnData;
				}
				paramObject['content'] = contentObject;
				var param = JSON.stringify(paramObject);
				dataRow.style['background-color'] = '#afa';
				cancelButton.setAttribute('disabled', '');
				cancelButton.style['display'] = 'none';
				
				//Thay đổi nút lưu thành nút sửa
				t.attributes['class'].value = 'editButton';
				t.attributes['onclick'].value = 'editOrder(this, ' + num + ')';
				t.innerHTML = 'Sửa';
				
				//Bắt đầu request
				var request = new XMLHttpRequest();
				request.onreadystatechange = function() {
					if (this.readyState == 4) {
						var returnObject = JSON.parse(this.responseText);
						switch(this.status){
						case 200:
							notification(returnObject['message'], 'success');
							dataRow.style['background-color'] = '';
							var cnt = 0;
							for(let returnData of returnObject['content']){
								dataCell[cnt].innerHTML = returnData;
								cnt += 1;
							}
							var selectedInput = dataRow.getElementsByTagName('input');
							while(selectedInput.length != 0){
								dataRow.getElementsByTagName('input')[0].remove();
							}
							break;
						case 400:
						case 401:
							notification(returnObject['message'], 'error');
							dataRow.style['background-color'] = '';
							var selectedInput = dataRow.getElementsByTagName('input');
							while(selectedInput.length != 0){
								dataRow.getElementsByTagName('input')[0].remove();
							}
							break;
						}
					}
				};
				request.open('POST', 'editDatabaseHandle.php');
				request.setRequestHeader('Content-type', 'application/json');
				request.send(param);
			}
			
			function cancelOrder(t, num){
				var removeButton = t.previousElementSibling;
				var activeButton = removeButton.previousElementSibling;
				
				//Vô hiệu nút hủy
				t.setAttribute('disable', '');
				t.style['display'] = 'none';
				
				//Kích hoạt lại mọi nút sửa
				activeButton.removeAttribute('disabled');
				
				//Thay đổi nút lưu thành nút sửa
				activeButton.attributes['class'].value = 'editButton';
				activeButton.attributes['onclick'].value = 'editOrder(this, ' + num + ')';
				activeButton.innerHTML = 'Sửa';
				
				//Hủy các input 5 và 6
				var selectedRow = document.getElementById('row' + num).getElementsByTagName('td');
				for(var i = 0; i < selectedRow.length - 1; i++){
					var selectedInput = selectedRow[i].getElementsByTagName('input');
					while(selectedInput.length != 0){
						selectedRow[i].getElementsByTagName('input')[0].remove();
					}
				}
			}
			function removeOrder(t, num){
				var paramObject = {};
				paramObject['extra'] = false;
				
				if(num == -1)
					if(confirm("Bạn có chắc là muốn xóa toàn bộ dữ liệu?") == false)
						return;
					else 
						paramObject['extra'] = true;
				
				paramObject['table'] = document.getElementById('tableName').value;
				paramObject['referer'] = document.getElementById('refererPage').value;
				
				if(paramObject['extra'] == false){
					var contentObject = {};
					var fieldRow = document.getElementById('rowMetadata');
					var columnName = fieldRow.getElementsByClassName('column-name');
					var columnType = fieldRow.getElementsByClassName('column-type');
					var dataRow = document.getElementById('row' + num);
					var dataCell = dataRow.getElementsByClassName('data-cell');
					for(var cnt = 0; cnt < dataCell.length; cnt++){
						var columnData = {};
						columnData['name'] = columnName[cnt].value;
						columnData['type'] = columnType[cnt].value;
						columnData['data'] = dataCell[cnt].innerHTML;
						contentObject['field' + cnt] = columnData;
					}
					paramObject['content'] = contentObject;
					dataRow.style['background-color'] = '#faa';
				}
				var param = JSON.stringify(paramObject);
				
				var request = new XMLHttpRequest();
				request.onreadystatechange = function() {
					if (this.readyState == 4) {
						var returnObject = JSON.parse(this.responseText);
						switch(this.status){
						case 200:
							notification(returnObject['message'], 'success');
							if(paramObject['extra'] == true) {
								var allRow = document.getElementsByClassName('dataRow');
								while(allRow.length != 0){
									allRow[0].remove();
								}
							} else {
								dataRow.remove();
							}
							break;
						case 400:
						case 401:
							notification(returnObject['message'], 'error');
							dataRow.style['background-color'] = '';
							break;
						}
					}
				};
				request.open('POST', 'deleteDatabaseHandle.php');
				request.setRequestHeader('Content-type', 'application/json');
				request.send(param);
			}
			function insertOrder(t){
				//Chuẩn bị request param
				var paramObject = {};
				paramObject['extra'] = false;
				paramObject['table'] = document.getElementById('tableName').value;
				paramObject['referer'] = document.getElementById('refererPage').value;
				paramObject['key'] = document.getElementById('uniqueColumn') === null ? '' : document.getElementById('uniqueColumn').value;
				
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
				
				// Chuẩn bị hàng mới để thêm vào bảng
				var prototypeRow = document.getElementsByClassName('prototype')[0];
				var cloneRow = prototypeRow.cloneNode(true);
				var newId = parseInt(cloneRow.id);
				prototypeRow.id = (newId + 1);
				cloneRow.id = 'row' + newId;
				cloneRow.className = 'dataRow visibleRow';
				var cloneCell = cloneRow.getElementsByClassName('data-cell');
				for(var cnt = 0; cnt < cloneCell.length; cnt++){
					cloneCell[cnt].innerHTML = '';
				}
				if(cloneRow.getElementsByClassName('editButton') !== undefined){
					cloneRow.getElementsByClassName('editButton')[0].setAttribute('onclick', 'editOrder(this, ' + newId + ')');
				}
				if(cloneRow.getElementsByClassName('removeButton') !== undefined){
					cloneRow.getElementsByClassName('removeButton')[0].setAttribute('onclick', 'removeOrder(this, ' + newId + ')');
				}
				if(cloneRow.getElementsByClassName('cancelButton') !== undefined){
					cloneRow.getElementsByClassName('cancelButton')[0].setAttribute('onclick', 'cancelOrder(this, ' + newId + ')');
				}
				prototypeRow.after(cloneRow);
				cloneRow.style['background-color'] = '#ffa';
				
				//Bắt đầu request
				var request = new XMLHttpRequest();
				request.onreadystatechange = function() {
					if (this.readyState == 4) {
						var returnObject = JSON.parse(this.responseText);
						switch(this.status){
						case 200:
						console.log(returnObject);
							notification(returnObject['message'], 'success');
							cloneRow.style['background-color'] = '';
							var cnt = 0;
							for(let returnData of returnObject['content']){
								cloneCell[cnt].innerHTML = returnData;
								dataCell[cnt].value = '';
								cnt += 1;
							}
							break;
						case 206:
							notification(returnObject['message'], 'warning');
							cloneRow.style['background-color'] = '';
							var cnt = 0;
							for(let returnData of returnObject['content']){
								cloneCell[cnt].innerHTML = returnData;
								dataCell[cnt].value = '';
								cnt += 1;
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
				request.open('POST', 'insertDatabaseHandle.php');
				request.setRequestHeader('Content-type', 'application/json');
				request.send(param);
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
								if(cloneRow.getElementsByClassName('editButton') !== undefined){
									cloneRow.getElementsByClassName('editButton')[0].setAttribute('onclick', 'editOrder(this, ' + newId + ')');
								}
								if(cloneRow.getElementsByClassName('removeButton') !== undefined){
									cloneRow.getElementsByClassName('removeButton')[0].setAttribute('onclick', 'removeOrder(this, ' + newId + ')');
								}
								if(cloneRow.getElementsByClassName('cancelButton') !== undefined){
									cloneRow.getElementsByClassName('cancelButton')[0].setAttribute('onclick', 'cancelOrder(this, ' + newId + ')');
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
		<button class="backButton" onclick="window.location.href = 'manage-page.php'">← Quay lại</button>
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
					
					//Input #3 - Trường auto_increment
					if(!(strpos($fieldComment[1], 'auto_increment') === FALSE))
						echo '<input id="uniqueColumn" type="hidden" value="'.$fieldInfo->name.'">';
					
					echo '</td>';
					$columnCount += 1;
				}
				
				echo '<td class="column-cell action-cell" style="width: 130px">Hành động</td>';
				echo '</tr>';
				
			##############################################################################################
			# Dòng số 2: Các ô nhập liệu cho thao tác tìm kiếm và thêm
				echo '<tr id="rowInsert">';
				for($cnt = 0; $cnt < $columnCount; $cnt++){
					// Input #4 - Thêm trường
					echo '<td class="insert-cell">';
					
					echo '<input class="insert-data" type=text" placeholder="Nhập dữ liệu">';
					
					echo '</td>';
				}
				
				echo '<td>';
				if($insertRight >= $_SESSION['right'])
					echo '<button type="button" class="insertButton" onclick="insertOrder(this)">Thêm</button>';
				if($viewRight >= $_SESSION['right'])
					echo '<button type="button" class="findButton" onclick="findOrder(this)">Tìm</button>';
				echo '</td>';
				echo '</tr>';
				
			##############################################################################################
			# Dòng số 3: Nguyên mẫu hàng, dùng để làm khuôn sao chép ra các hàng mới khi cần thiết
				$rowCount = mysqli_fetch_array($cresult, MYSQLI_BOTH)[0];
				echo '<tr class="prototype hiddenRow" id="'.$rowCount.'">';
					
				for($cnt = 0; $cnt < $columnCount; $cnt++){
					echo '<td><div class="data-cell"></div></td>';
				}
				
				echo '<td>';
				
				if($updateRight >= $_SESSION['right'])
					echo '<button type="button" class="editButton" onclick="editOrder(this, '.$rowCount.')">Sửa</button>';
				else echo '<button type="button" class="editButton hide" disabled>Vô hiệu</button>';
				if($deleteRight >= $_SESSION['right'])
					echo '<button type="button" class="removeButton" onclick="removeOrder(this, '.$rowCount.')">Xóa</button>';
				else echo '<button type="button" class="removeButton hide" disabled>Vô hiệu</button>';
				if($updateRight >= $_SESSION['right'])
					echo '<button type="button" class="cancelButton" onclick="cancelOrder(this, '.$rowCount.')" disabled>Hủy</button>';
				else echo '<button type="button" class="cancelButton hide" disabled>Vô hiệu</button>';
				
				echo '</td>';
				echo '</tr>';
				
			##############################################################################################
			# Dòng 4+: Dữ liệu thực sự của bảng
				$rowCount -= 1;
				while($row = mysqli_fetch_array($result, MYSQLI_BOTH)){
					echo '<tr class="dataRow visibleRow" id="row'.$rowCount.'">';
					
					for($cnt = 0; $cnt < $columnCount; $cnt++){
						echo '<td><div class="data-cell">'.$row[$cnt].'</div></td>';
					}
					
					echo '<td>';
					
					if($updateRight >= $_SESSION['right'])
						echo '<button type="button" class="editButton" onclick="editOrder(this, '.$rowCount.')">Sửa</button>';
					else echo '<button type="button" class="editButton hide" disabled>Vô hiệu</button>';
					if($deleteRight >= $_SESSION['right'])
						echo '<button type="button" class="removeButton" onclick="removeOrder(this, '.$rowCount.')">Xóa</button>';
					else echo '<button type="button" class="removeButton hide" disabled>Vô hiệu</button>';
					if($updateRight >= $_SESSION['right'])
						echo '<button type="button" class="cancelButton" onclick="cancelOrder(this, '.$rowCount.')" disabled>Hủy</button>';
					else echo '<button type="button" class="cancelButton hide" disabled>Vô hiệu</button>';
					
					echo '</td>';
					echo '</tr>';
					
					$rowCount -= 1;
				}
				
				echo '</table>';
				
			##############################################################################################
			# Phần chân của table
				if($deleteRight >= $_SESSION['right']){
					echo '<div style="text-align: center">';
					echo '<input type="hidden" name="extra" value="removeAll">';
					echo '<button type="button" class="removeAllButton" onclick="removeOrder(this, -1)">Xóa tất cả</button>';
					echo '</div>';
				}
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