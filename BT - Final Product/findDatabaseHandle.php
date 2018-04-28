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
	if($viewRight < $_SESSION['right']){
		mysqli_close($connector);
		if($_SESSION['right'] == 99)
			header('Location: login.php?referer='.$requestData['referer']);
		else {
			echo 'Không có quyền thực hiện thao tác này';
			http_response_code(401);
		}
	} else {
		$selectAllFlag = true;
		//Thành lập query
		$query = 'SELECT * FROM `'.$selectedTable.'` WHERE ';
		$addAND = false;
		$recheckId;
		foreach($requestData['content'] as $fieldData){
			if(strcmp($fieldData['data'], '') != 0){
				$selectAllFlag = false;
				if($addAND == true){
					$query = $query.' AND ';
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
					$query = $query.$fieldData['name'].' = '.$fieldData['data'];
					break;
				default:
					$query = $query.'UPPER('.$fieldData['name'].') LIKE UPPER("%'.$fieldData['data'].'%")';
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
				
				if($addAND == false)
					$addAND = true;
			}
		}
		$query = $query.';';
		
		if($selectAllFlag == true){
			$query = 'SELECT * FROM `'.$selectedTable.'`';
		}
		#echo 'Debug - Query content: '.$query.'<br>';
		
		//Điều hướng sau khi thực hiện query
		if($sresult = mysqli_query($connector, $query)){
			$returnArr = [];
			$contentArr = [];
			$cnt = 0;
			while($dataRow = mysqli_fetch_array($sresult, MYSQLI_NUM)){
				$rowArr = [];
				$fcnt = 0;
				foreach($dataRow as $fieldData){
					$rowArr[$fcnt] = $fieldData;
					$fcnt += 1;
				}
				$contentArr[$cnt] = $rowArr;
				$cnt += 1;
			}
			$returnArr['content'] = $contentArr;
			$returnArr['message'] = 'Tìm thấy '.mysqli_affected_rows($connector).' kết quả';
			http_response_code(200);
			mysqli_close($connector);
			echo json_encode((object)$returnArr);
		} else {
			$returnArr['message'] = 'Không thể thực hiện thao tác này';
			http_response_code(400);
			echo json_encode((object)$returnArr);
		}
	}
?>