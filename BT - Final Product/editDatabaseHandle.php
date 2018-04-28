<?php
	session_start();
	header('Content-type: application/json');
	$requestData = json_decode(file_get_contents('php://input'), true);
	
	//Chọn bảng
	$selectedTable = $requestData['table'];

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
	if($updateRight < $_SESSION['right']){
		mysqli_close($connector);
		if($_SESSION['right'] == 99)
			header('Location: login.php?referer='.$requestData['referer']);
		else {
			echo 'Không có quyền thực hiện thao tác này';
			http_response_code(401);
		}
	} else {
		//Thành lập query
		$query = 'UPDATE `'.$selectedTable.'` SET ';
		$addComa = false;
		$recheckId;
		foreach($requestData['content'] as $fieldData){
			if(strcmp($fieldData['update'], '') != 0){
				if($addComa == true){
					$query = $query.', ';
				}
				
				switch($fieldData['type']){
				case MYSQLI_TYPE_BIT:		# - Field is defined as BIT (MySQL 5.0.3 and up)
				case MYSQLI_TYPE_TINY:		# - Field is defined as TINYINT
				case MYSQLI_TYPE_SHORT:		# - Field is defined as SMALLINT
				case MYSQLI_TYPE_LONG:		# - Field is defined as INT
				case MYSQLI_TYPE_FLOAT:		# - Field is defined as FLOAT
				case MYSQLI_TYPE_DOUBLE:	# - Field is defined as DOUBLE
				case MYSQLI_TYPE_LONGLONG:	# - Field is defined as BIGINT
				case MYSQLI_TYPE_INT24:		# - Field is defined as MEDIUMINT
					$query = $query.' '.$fieldData['name'].' = '.$fieldData['update'];
					break;
				default:
					$query = $query.' '.$fieldData['name'].' = "'.$fieldData['update'].'"';
					break;
				#MYSQLI_TYPE_NULL - Field is defined as DEFAULT NULL
				#MYSQLI_TYPE_TIMESTAMP - Field is defined as TIMESTAMP
				#MYSQLI_TYPE_DATE - Field is defined as DATE
				#MYSQLI_TYPE_TIME - Field is defined as TIME
				#MYSQLI_TYPE_DATETIME - Field is defined as DATETIME
				#MYSQLI_TYPE_YEAR - Field is defined as YEAR
				#MYSQLI_TYPE_NEWDATE - Field is defined as DATE
				#MYSQLI_TYPE_INTERVAL - Field is defined as INTERVAL
				#MYSQLI_TYPE_ENUM - Field is defined as ENUM
				#MYSQLI_TYPE_SET - Field is defined as SET
				#MYSQLI_TYPE_TINY_BLOB - Field is defined as TINYBLOB
				#MYSQLI_TYPE_MEDIUM_BLOB - Field is defined as MEDIUMBLOB
				#MYSQLI_TYPE_LONG_BLOB - Field is defined as LONGBLOB
				#MYSQLI_TYPE_BLOB - Field is defined as BLOB
				#MYSQLI_TYPE_VAR_STRING - Field is defined as VARCHAR
				#MYSQLI_TYPE_STRING - Field is defined as CHAR or BINARY
				#MYSQLI_TYPE_CHAR - Field is defined as TINYINT. For CHAR, see MYSQLI_TYPE_STRING
				#MYSQLI_TYPE_GEOMETRY - Field is defined as GEOMETRY
				}
				
				if($addComa == false)
					$addComa = true;
			}
		}
		$query = $query.' WHERE ';
		
		$addAND = false;
		foreach($requestData['content'] as $fieldData){
			if($addAND == true){
				$query = $query.' AND ';
			} else $addAND = true;
			if(strcmp($fieldData['name'], $requestData['key']) == 0){
				if(strcmp($fieldData['update'], '') == 0){
					$recheckId = $fieldData['data'];
				} else $recheckId = $fieldData['update'];
			}
			switch($fieldData['type']){
				case MYSQLI_TYPE_BIT:		# - Field is defined as BIT (MySQL 5.0.3 and up)
				case MYSQLI_TYPE_TINY:		# - Field is defined as TINYINT
				case MYSQLI_TYPE_SHORT:		# - Field is defined as SMALLINT
				case MYSQLI_TYPE_LONG:		# - Field is defined as INT
				case MYSQLI_TYPE_FLOAT:		# - Field is defined as FLOAT
				case MYSQLI_TYPE_DOUBLE:	# - Field is defined as DOUBLE
				case MYSQLI_TYPE_LONGLONG:	# - Field is defined as BIGINT
				case MYSQLI_TYPE_INT24:		# - Field is defined as MEDIUMINT
					$query = $query.' '.$fieldData['name'].' = '.$fieldData['data'];
					break;
				default:
					$query = $query.' '.$fieldData['name'].' = "'.$fieldData['data'].'"';
					break;
				#MYSQLI_TYPE_NULL - Field is defined as DEFAULT NULL
				#MYSQLI_TYPE_TIMESTAMP - Field is defined as TIMESTAMP
				#MYSQLI_TYPE_DATE - Field is defined as DATE
				#MYSQLI_TYPE_TIME - Field is defined as TIME
				#MYSQLI_TYPE_DATETIME - Field is defined as DATETIME
				#MYSQLI_TYPE_YEAR - Field is defined as YEAR
				#MYSQLI_TYPE_NEWDATE - Field is defined as DATE
				#MYSQLI_TYPE_INTERVAL - Field is defined as INTERVAL
				#MYSQLI_TYPE_ENUM - Field is defined as ENUM
				#MYSQLI_TYPE_SET - Field is defined as SET
				#MYSQLI_TYPE_TINY_BLOB - Field is defined as TINYBLOB
				#MYSQLI_TYPE_MEDIUM_BLOB - Field is defined as MEDIUMBLOB
				#MYSQLI_TYPE_LONG_BLOB - Field is defined as LONGBLOB
				#MYSQLI_TYPE_BLOB - Field is defined as BLOB
				#MYSQLI_TYPE_VAR_STRING - Field is defined as VARCHAR
				#MYSQLI_TYPE_STRING - Field is defined as CHAR or BINARY
				#MYSQLI_TYPE_CHAR - Field is defined as TINYINT. For CHAR, see MYSQLI_TYPE_STRING
				#MYSQLI_TYPE_GEOMETRY - Field is defined as GEOMETRY
				}
		}
		$query = $query.';';
		#echo 'Debug - Query content: '.$query.'<br>';
		
		//Điều hướng sau khi thực hiện query
		if(mysqli_query($connector, $query)){
			$returnArr = [];
			$contentArr = [];
			$secondaryQuery = 'SELECT * FROM `'.$selectedTable.'` WHERE '.$requestData['key'].' = '.$recheckId;
			if($sresult = mysqli_query($connector, $secondaryQuery)){
				$recheckRow = mysqli_fetch_array($sresult, MYSQLI_NUM);
				$cnt = 0;
				foreach($recheckRow as $fieldData){
					$contentArr[$cnt] = $fieldData;
					$cnt += 1;
				}
			}
			$returnArr['content'] = $contentArr;
			$returnArr['message'] = 'Thay đổi dữ liệu thành công';
			http_response_code(200);
			mysqli_close($connector);
			echo json_encode((object)$returnArr);
		} else {
			$returnArr['message'] = 'Không thể thực hiện thao tác này';
			http_response_code(400);
			echo json_encode((object)$returnArr);
		}
		#$returnData = array(
		#	'view' => $requestData['table']
		#);
		#if(mysqli_query($connector, $query)){
		#	mysqli_close($connector);
		#	header('Location: manageDatabase.php?'.http_build_query($returnData)); 
		#} else {
		#	echo '<br>Update failed, please go <a href="manageDatabase.php?'.http_build_query($returnData).'">back</a>';
		#}
	}
?>