<?php
	session_start();

	session_unset(); 
	session_destroy();
	echo '<script type="text/javascript">';
	echo 'function func(){';
	echo 'var iframe = parent.document.getElementById("left-frame");';
	echo 'iframe.src = iframe.src;';
	echo 'window.location.href = "main-page.html";}';
	echo 'func()</script>';
?>