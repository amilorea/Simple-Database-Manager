<?php
$myArr = [
	'age' => 39,
	'fast' => 45
];
$myArr['x'] = 'y';
$myObj = (object)$myArr;
$myJSON = json_encode($myObj);

echo $myJSON;
?>