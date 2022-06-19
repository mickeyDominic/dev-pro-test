<?php
	$DB_HOST='localhost';
	$DB_USER='root';
	$DB_PASSWORD='';
	$DB_NAME='devprox';
	
	function test_input($data)
	{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
?>